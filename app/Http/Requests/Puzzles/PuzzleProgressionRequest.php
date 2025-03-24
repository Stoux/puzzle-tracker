<?php

namespace App\Http\Requests\Puzzles;

use App\Enums\PuzzleProgressionStatus;
use Illuminate\Foundation\Http\FormRequest;

class PuzzleProgressionRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                'in:' . implode(',', PuzzleProgressionStatus::values()),
            ],
            'started_on' => [
                'nullable',
                'string',
                'date_format:Y-m-d',
            ],
            'completed_on' => [
                'nullable',
                'string',
                'date_format:Y-m-d',
            ],
            'comments' => [
                'nullable',
                'string',
                'max:255'
            ],
        ];
    }

}
