<?php

namespace App\Http\Requests;

class UuidListRequest extends ListRequest
{
    protected function cursorSpec(): array
    {
        return [
            'rules' => ['uuid'],
            'messages' => ['cursor.uuid' => 'Cursor must be a valid UUID.'],
        ];
    }
}
