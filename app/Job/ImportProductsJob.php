<?php

namespace App\Job;

use App\Data\Shopify\JsonProduct;
use App\Models\Puzzle;
use App\Models\PuzzleTag;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Attempts to import any new products using the Shopify JSON
 */
class ImportProductsJob implements ShouldQueue
{
    public const int PER_PAGE = 250;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {

    }

    public function handle(): void
    {
        // Fetch all products from the JSON
        $all_products = $this->fetchProducts();

        // Update or insert new entries
        $known_puzzles = Puzzle::all(['shopify_id'])->keyBy('shopify_id');
        $known_tags = PuzzleTag::all()->keyBy('label');

        \Log::info('Syncing products', [
            'found' => $all_products->count(),
            'known' => $known_puzzles->count(),
        ]);

        $images_fetched = 0;

        foreach ($all_products as $product) {
            /** @var JsonProduct $product */
            $puzzle = Puzzle::updateOrCreate([
                'shopify_id' => $product->id,
            ], array_filter([
                'shopify_title' => $product->title,
                'shopify_id' => $product->id,
                'shopify_url_handle' => $product->handle,
                'sku' => $product->variants[0]?->sku ?: null,
                'description' => $product->body_html,
                'published_at' => Carbon::parse($product->published_at)->setTimezone(config('app.timezone')),
            ]));

            // Dissect the title parts & update the puzzle
            $this->dissectTitle($puzzle);

            // Add or sync product tags
            $tag_ids = [];
            foreach ($product->tags as $tag) {
                // Find or create the new tag
                $found_tag = $known_tags->get($tag);
                if (! $found_tag) {
                    $found_tag = PuzzleTag::create(['label' => $tag]);
                    $known_tags->put($tag, $found_tag);
                }
                $tag_ids[] = $found_tag->id;
            }
            $puzzle->tags()->sync($tag_ids);

            // Sync images
            $known_images = $puzzle->images()->keyBy(fn( Media $media ) => $media->getCustomProperty( 'shopify_id' ) );
            foreach( $product->images as $image ) {
                // Skip if we already have the image
                if ( $known_images->has( $image->id ) ) {
                    continue;
                }

                try {
                    // Otherwise fetch & associate
                    $puzzle->addMediaFromUrl($image->src)
                        ->withCustomProperties([
                            'shopify_id' => $image->id,
                        ])
                        ->toMediaCollection(Puzzle::MEDIA_COLLECTION_IMAGES);
                } catch (FileDoesNotExist|FileIsTooBig|FileCannotBeAdded $e) {
                    // Tough shit
                    \Log::warning('Failed to downlaod image', [
                        'image' => $image->src,
                        'puzzle' => $puzzle->id,
                    ]);
                }

                // Prevent spamming the Shopify API
                $images_fetched++;
                if ($images_fetched % 30) {
                    sleep(1);
                }
            }

            // Crawl the web page if needed
            if ($puzzle->shouldCrawlWebPage()) {
                ScrapeProductPageJob::dispatch($puzzle);
            }

        }
    }

    protected function dissectTitle(Puzzle $puzzle): void
    {
        if (! preg_match('/^Was?gij (?P<collection>[a-z ]+)(?P<number>\d+)? - (?P<title>.+)(?: - | \()(?P<pieces_label>(?P<multiplier>\d+x)?(?P<pieces>\d+) (?:stuk|piec)[a-z]{0,4})\)?$/i', $puzzle->shopify_title, $matches)) {
            return;
        }

        $puzzle->collection_tag = trim($matches['collection']);
        if (! empty($matches['number'])) {
            $puzzle->collection_number = (int) $matches['number'];
        }
        $puzzle->puzzle_title = trim($matches['title']);

        $puzzle->number_of_pieces_label = $matches['pieces_label'];

        // Calculate the actual number of pieces
        $pieces = (int) $matches['pieces'];
        if (! empty($matches['multiplier'])) {
            $pieces = ((int) preg_replace('/[^0-9]/', '', $matches['multiplier'])) * $pieces;
        }
        $puzzle->number_of_pieces = $pieces;

        $puzzle->save();
    }

    /**
     * @return \Illuminate\Support\Collection<JsonProduct>
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function fetchProducts(): \Illuminate\Support\Collection
    {
        // Fetch the JSON
        $client = new Client([
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::HTTP_ERRORS => true,
            RequestOptions::HEADERS => [
                'User-Agent' => config('app.wasgij.user-agent'),
            ],
        ]);

        // Create a collection with all products
        $all_products = collect([]);

        // Fetch the products per page
        $page = 1;
        while (true) {
            $response = $client->get('https://wasgij.com/nl-nl/products.json', [
                RequestOptions::QUERY => [
                    'limit' => self::PER_PAGE,
                    'page' => $page,
                ],
            ]);

            // Convert to JSON
            $products = json_decode((string) $response->getBody(), true)['products'] ?? [];
            $all_products->push(...$products);

            // Check if we should continue to the next page
            if (count($products) < self::PER_PAGE) {
                break;
            } else {
                $page++;
            }
        }

        return $all_products->map(fn(array $product) => new JsonProduct($product));
    }
}
