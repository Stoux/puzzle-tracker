<?php

namespace App\Data\Shopify;

/**
 * @property int $id
 * @property string $title
 * @property string $handle
 * @property string $body_html
 * @property string $published_at
 * @property string $created_at
 * @property string $updated_at
 * @property string $vendor
 * @property string $product_type
 * @property string[] $tags
 *
 */
class JsonProduct {


    public function __construct(
        protected readonly array $data,
    ) {

    }

    /**
     * @var JsonProductVariant[] $variants
     */
    public array $variants {
        get {
            return array_map(
                fn( array $variant ) => new JsonProductVariant( $variant ),
                $this->data['variants'] ?? [],
            );
        }
    }

    /**
     * @var JsonProductImage[] $images
     */
    public array $images {
        get {
            return array_map(
                fn( array $image ) => new JsonProductImage( $image ),
                $this->data['images'] ?? [],
            );
        }
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->data[$name] ?? null;
    }



}
