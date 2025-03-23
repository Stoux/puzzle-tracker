<?php

namespace App\Data\Shopify;

/**
 * @property int $id
 * @property string $sku
 * @property int|null $grams
 */
class JsonProductVariant {

    public function __construct(
        protected readonly array $data,
    ) {

    }

    public function __get( string $name ) {
        return $this->data[$name] ?? null;
    }

}
