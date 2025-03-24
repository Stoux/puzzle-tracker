<?php

namespace App\Job;

use App\Models\Puzzle;
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
use Symfony\Component\DomCrawler\Crawler;

class ScrapeProductPageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Puzzle $puzzle,
    ) {

    }

    public function handle(): void
    {
        \Log::info('Fetching product HTML page', [
            'puzzle' => $this->puzzle->id,
        ]);

        // Fetch the page
        $client = new Client([
            RequestOptions::ALLOW_REDIRECTS => true,
            RequestOptions::HTTP_ERRORS => true,
            RequestOptions::HEADERS => [
                'User-Agent' => config('app.wasgij.user-agent'),
            ],
        ]);
        $response = $client->get($this->puzzle->getWebshopUrl());

        // Parse into HTML
        $crawler = new Crawler((string) $response->getBody());

        // Parse the tag/label above the title (if any)
        $this->parseTag($crawler);

        // Parse the label section for tags
        $this->parseLabels($crawler);

        // Parse the dimensions text
        $this->parseDimensions($crawler);

        // Update crawled time
        $this->puzzle->crawled_at = now();
        $this->puzzle->save();

        // Parse the additional hints for this puzzle
        $this->parseHints($crawler);
    }

    protected function parseTag(Crawler $crawler): void
    {
        $block = $crawler->filter('.product-tag.');
        if (! $block->count()) {
            return;
        }

        $this->puzzle->website_label = trim($block->text());
    }

    protected function parseLabels(Crawler $crawler): void
    {
        // Find the labels block
        $block = $crawler->filter('.product-block-text');
        if (! $block->count()) {
            return;
        }

        // Parse the text and for all key => value ones
        $text = $block->first()->text();
        $parts = explode(' | ', $text);
        // Should be format '{label}: {value}'
        $parts = array_filter($parts, fn(string $s) => str_contains($s, ': '));

        // Loop through each part to find the ones we want
        foreach ($parts as $part) {
            [$label, $value] = array_map(fn($s) => trim($s), explode(': ', $part, 2));

            // Slim chance but the SKU is sometimes missing from the JSON
            if ($label === 'Artikelnummer' && $this->puzzle->sku !== $value) {
                $this->puzzle->sku = $value;
            }

            // Save the year
            if ($label === 'Jaar') {
                $this->puzzle->year_label = $value;
                // Super dumb parsing by just assuming any 4 digits combo is the year
                if (preg_match('/\d{4}/', $value, $match)) {
                    $this->puzzle->year = (int) $match[0];
                }
            }

            // Artist
            if ($label === 'Kunstenaar') {
                $this->puzzle->artist = $value;
            }
        }
    }

    protected function parseHints(Crawler $crawler): void
    {
        $hints = [1 => '#sizepopup', 2 => '#sizepopup2'];
        foreach ($hints as $hintNumber => $divId) {
            // Find the image element
            $imageCrawler = $crawler->filter($divId.' img');
            if (! $imageCrawler->count()) {
                continue;
            }

            // Resolve the image URL for that element
            $imageElement = $imageCrawler->first();
            $imageUrl = $imageElement->attr('src');
            if (str_starts_with($imageUrl, '//')) {
                $imageUrl = 'https:'.$imageUrl;
            }
            $imageBasename = basename(explode('?', $imageUrl)[0]);

            // Check if we've previously saved that image
            $savedMedia = $this->puzzle->getHint($hintNumber);
            $oldBaseName = $savedMedia?->getCustomProperty('shopify_basename');
            if ($imageBasename === $oldBaseName) {
                // Already saved
                continue;
            }

            // Delete the current hint
            if ($savedMedia) {
                \Log::info('Replacing hint for puzzle', [
                    'hint' => $hintNumber,
                    'puzzle' => $this->puzzle->id,
                    'new' => $imageBasename,
                    'old' => $oldBaseName,
                ]);
                $savedMedia->delete();
            } else {
                \Log::info('Saving new hint for puzzle', [
                    'hint' => $hintNumber,
                    'puzzle' => $this->puzzle->id,
                ]);
            }

            // Save the media
            try {
                $this->puzzle->addMediaFromUrl($imageUrl)->withCustomProperties([
                    'shopify_basename' => $imageBasename,
                    'hint' => $hintNumber,
                ])->toMediaCollection(Puzzle::MEDIA_COLLECTION_HINTS);
            } catch (FileDoesNotExist|FileIsTooBig|FileCannotBeAdded $e) {
                // Tough shit
                \Log::warning('Failed to download hint', [
                    'hint' => $hintNumber,
                    'image' => $imageUrl,
                    'puzzle' => $this->puzzle->id,
                ]);
            }
        }
    }

    protected function parseDimensions(Crawler $crawler): void
    {
        // Should be a main level div that contains '_text_columns_' in the ID
        $sections = $crawler->filter('#MainContent > .shopify-section');
        for ($i = 0; $i < $sections->count(); $i++) {
            $section = $sections->eq($i);
            if (str_contains($section->attr('id'), '_text_columns')) {
                break;
            } else {
                $section = null;
            }
        }
        if (empty($section)) {
            // Didn't find it
            return;
        }

        // Should contain some <li>'s, assuming the second one is about size.
        $liElements = $section->filter('ul > li');
        if ($liElements->count() < 2) {
            return;
        }

        // Clean up (if possible)
        $text = $liElements->eq(1)->text();
        if (preg_match('/heeft een afmeting van ongeveer (?P<dimensions>[0-9xcm ]+)\./', $text, $match)) {
            $text = $match['dimensions'];
        }

        $this->puzzle->dimensions = $text;
    }
}
