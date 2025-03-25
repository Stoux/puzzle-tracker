<?php

namespace App\Http\Requests\Puzzles;

use App\Enums\PuzzleProgressionStatus;
use Illuminate\Foundation\Http\FormRequest;

class PurchasedPuzzleRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'owner_id' => [
                'int',
                'exists:users,id',
            ],
            'currently_at_id' => [
                'nullable',
                'int',
                'exists:users,id',
            ],
            'purchased_on' => [
                'nullable',
                'string',
                'date_format:Y-m-d',
            ],
        ];
    }

}
