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
            'target' => 'nullable|in:_self,_blank',
            'role_id' => 'nullable|integer',
        ];
    }

    public function attributes()
    {
        return [
            'label' => trans('wmenu::builder.label'),
            'link' => trans('wmenu::builder.link'),
            'class' => trans('wmenu::builder.class'),
            'target' => trans('wmenu::builder.target'),
            'role_id' => trans('wmenu::builder.role'),
        ];
    }
}
