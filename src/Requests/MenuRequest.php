<?php

namespace Harimayco\Menu\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
