<?php

namespace Harimayco\Menu\Requests;

use Harimayco\Menu\Rules\SafeLink;
use Illuminate\Foundation\Http\FormRequest;

class MenuItemRequest extends FormRequest
{
    public function rules()
    {
        return [
            'label' => 'required|string|max:255',
            'link' => ['nullable', 'string', 'max:255', new SafeLink()],
            'class' => 'nullable|string|max:255',
            'role_id' => 'nullable|integer',
        ];
    }
}
