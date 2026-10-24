<?php

namespace Harimayco\Menu\Requests;

use Harimayco\Menu\Rules\SafeLink;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Menu name, structure and (optionally) the content of its items, saved at once.
 */
class SaveMenuRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'items' => 'nullable|array',
            'items.*.id' => 'required|integer',
            'items.*.parent' => 'required|integer|min:0',
            'items.*.sort' => 'required|integer|min:0',
            'items.*.label' => 'sometimes|required|string|max:255',
            'items.*.link' => ['sometimes', 'nullable', 'string', 'max:255', new SafeLink()],
            'items.*.class' => 'sometimes|nullable|string|max:255',
            'items.*.role_id' => 'sometimes|nullable|integer',
        ];
    }
}
