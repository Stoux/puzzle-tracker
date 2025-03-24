<?php

namespace App\Enums;

enum PuzzleProgressionStatus: string {

    case STARTED = 'started';
    case FINISHED = 'finished';
    case ABORTED = 'aborted';

    public static function values(): array
    {
        return array_map(
            fn(PuzzleProgressionStatus $status) => $status->value,
            PuzzleProgressionStatus::cases(),
        );
    }

}
