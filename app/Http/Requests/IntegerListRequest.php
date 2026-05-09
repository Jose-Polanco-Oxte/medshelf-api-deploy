<?php

namespace App\Http\Requests;

class IntegerListRequest extends ListRequest
{
    protected function cursorSpec(): array
    {
        return [
            'rules' => ['integer', 'min:1'],
            'messages' => [
                'cursor.integer' => 'Cursor must be an integer.',
                'cursor.min' => 'Cursor must be at least 1.'
            ],
        ];
    }
}

