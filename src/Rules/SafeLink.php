<?php

namespace Harimayco\Menu\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Rejects links such as "javascript:..." or "data:..." that would run code when clicked.
 * Links without a scheme (relative paths, #anchors) are always allowed.
 */
class SafeLink implements Rule
{
    protected $schemes;

    public function __construct(array $schemes = ['http', 'https', 'mailto', 'tel'])
    {
        $this->schemes = $schemes;
    }

    public function passes($attribute, $value)
    {
        // browsers ignore whitespace and control characters inside the scheme ("java\tscript:")
        $normalized = preg_replace('/[\x00-\x20\x7F]+/', '', (string) $value);

        if (!preg_match('/^([a-z][a-z0-9+.\-]*):/i', $normalized, $matches)) {
            return true;
        }

        return in_array(strtolower($matches[1]), $this->schemes, true);
    }

    public function message()
    {
        return trans('wmenu::messages.unsafe_link', ['schemes' => implode(', ', $this->schemes)]);
    }
}
