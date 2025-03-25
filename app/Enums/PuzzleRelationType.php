<?php

namespace App\Enums;

enum PuzzleRelationType: string {

    case RERELEASE_CHANGED = 'rerelease-changed';
    case RERELEASE_NEAR_IDENTICAL = 'rerelease-near-identical';
    case RERELEASE_IDENTICAL = 'rerelease-identical';
    case RETRO = 'retro';

    public static function values(): array
    {
        return array_map(
            fn(PuzzleRelationType $type) => $type->value,
            PuzzleRelationType::cases(),
        );
    }

}
