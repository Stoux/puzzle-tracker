<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\Conversions\Manipulations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Puzzle extends Model implements HasMedia {

    use InteractsWithMedia;

    public const string MEDIA_COLLECTION_IMAGES = 'images';
    public const string MEDIA_COLLECTION_HINTS = 'hints';

    protected $fillable = [
		'shopify_title',
		'shopify_id',
		'shopify_url_handle',
        'published_at',
        'crawled_at',
		'sku',
        'puzzle_title',
        'collection_tag',
        'collection_number',
		'number_of_pieces',
		'number_of_pieces_label',
		'year',
		'year_label',
		'artist',
		'dimensions',
		'description',
        'website_label',
	];

    protected $casts = [
        'shopify_id' => 'integer',
        'collection_number' => 'integer',
        'number_of_pieces' => 'integer',
        'year' => 'integer',
        'published_at' => 'immutable_datetime',
        'crawled_at' => 'immutable_datetime',
    ];


    public function getWebshopUrl(): string {
        return 'https://wasgij.com/nl-nl/products/' . $this->shopify_url_handle;
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PuzzleTag::class);
    }

    public function purchases(): HasMedia {
        return $this->hasMany(PurchasedPuzzle::class);
    }

    public function progressions(): HasMany {
        return $this->hasMany(PuzzleProgression::class);
    }

    /**
     * Get all PuzzleRelation models related to this Puzzle, regardless of whether this Puzzle is the 'puzzle_id' or 'relates_to_id'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function relations(): HasMany
    {
        return $this->hasMany(PuzzleRelation::class, 'puzzle_id')
            ->orWhere('relates_to_id', $this->id);
    }


    public function images(): MediaCollection {
        return $this->getMedia(self::MEDIA_COLLECTION_IMAGES);
    }

    public function hints(): MediaCollection {
        return $this->getMedia(self::MEDIA_COLLECTION_HINTS);
    }

    /**
     * Get a specific hint image (if it exists)
     * @param int $number hint 1 or 2
     * @return \Spatie\MediaLibrary\MediaCollections\Models\Media|null
     */
    public function getHint(int $number): ?Media
    {
        return $this->hints()->keyBy(fn(Media $media) => $media->getCustomProperty('hint'))->get($number);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->performOnCollections(self::MEDIA_COLLECTION_IMAGES)
            ->format('webp');

        $this->addMediaConversion('preview')
            ->format('webp')
            ->fit(Fit::Contain, 500, 500);

    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION_IMAGES)->useDisk('public-media-synced');
        $this->addMediaCollection(self::MEDIA_COLLECTION_HINTS)->useDisk('public-media-synced');
    }

    /**
     * Whether this product's web page should be crawled for details
     *
     * This is influenced by how long ago it was scraped vs how old the product is
     *
     * @return bool
     */
    public function shouldCrawlWebPage(): bool
    {
        if ($this->crawled_at === null) {
            return true;
        }

        // More than 3 months ago? Check again.
        if ($this->crawled_at->isBefore(now()->addMonths(-3))) {
            return true;
        }

        return false;
    }
}
