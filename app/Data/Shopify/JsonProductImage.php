<?php

namespace App\Data\Shopify;

/**
 * @property int $id
 * @property string $src
 * @property int $width
 * @property int $height
 */
class JsonProductImage {

    public function __construct(
        protected readonly array $data,
    ) {

    }

    public function __get( string $name ) {
        return $this->data[$name] ?? null;
    }

}
