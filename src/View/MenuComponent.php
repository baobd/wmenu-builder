<?php

namespace Harimayco\Menu\View;

use Harimayco\Menu\WMenu;
use Illuminate\View\Component;

/**
 * Renders a menu as nested lists: <x-wmenu-menu name="main" />
 */
class MenuComponent extends Component
{
    public $items;

    public $activeClass;

    public $submenuClass;

    /**
     * @param string|null $name menu name
     * @param int|null $id menu id, used instead of the name when given
     * @param int|null $role with roles enabled, only show the items of this role and the ones for everyone
     */
    public function __construct($name = null, $id = null, $role = null, $activeClass = 'active', $submenuClass = 'sub-menu')
    {
        $items = $id !== null ? WMenu::get($id) : ($name !== null ? WMenu::getByName($name) : []);

        if ($role !== null && config('menu.use_roles')) {
            $items = $this->forRole($items, (int) $role);
        }

        $this->items = $items;
        $this->activeClass = $activeClass;
        $this->submenuClass = $submenuClass;
    }

    /**
     * Whether the item, or one of its sub items, links to the current page.
     */
    public function isActive(array $item)
    {
        $link = (string) ($item['link'] ?? '');
        if ($link !== '' && $link[0] !== '#' && rtrim(url($link), '/') === rtrim(url()->current(), '/')) {
            return true;
        }

        foreach ($item['child'] ?? [] as $child) {
            if ($this->isActive($child)) {
                return true;
            }
        }

        return false;
    }

    protected function forRole(array $items, $role)
    {
        $visible = [];
        foreach ($items as $item) {
            if (empty($item['role_id']) || (int) $item['role_id'] === $role) {
                $item['child'] = $this->forRole($item['child'] ?? [], $role);
                $visible[] = $item;
            }
        }

        return $visible;
    }

    public function render()
    {
        return view('wmenu::components.menu');
    }
}
