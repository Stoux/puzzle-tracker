<?php

namespace App\Http\Requests\Puzzles;

use App\Enums\PuzzleRelationType;
use Illuminate\Foundation\Http\FormRequest;

class PuzzleRelationRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'puzzle_id' => [
                'int',
                'exists:puzzles,id',
            ],
            'relates_to_id' => [
                'int',
                'exists:puzzles,id',
            ],
            'type' => [
                'string',
                'required',
                'in:' . implode(',', PuzzleRelationType::values()),
            ],
            'comment' => [
                'nullable',
                'string',
                'max:255'
            ],
        ];
    }

}
