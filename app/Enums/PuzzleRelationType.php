<?php

namespace App\Enums;

enum PuzzleRelationType: string {

    case RERELEASE_UPDATED = 'rerelease-updated';
    case RERELEASE_IDENTICAL = 'rerelease-identical';
    case RETRO = 'retro';


}
