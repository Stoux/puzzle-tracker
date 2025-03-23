<?php

namespace App\Enums;

enum PuzzleProgressionStatus: string {

    case STARTED = 'started';
    case FINISHED = 'finished';
    case ABORTED = 'aborted';

}
