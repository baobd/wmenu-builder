<?php

namespace Harimayco\Menu;

use Harimayco\Menu\Models\MenuItems;
use Harimayco\Menu\Models\Menus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Write operations of the menu builder, shared by the REST and the legacy controllers.
 */
class MenuManager
{
    protected $hasTarget;

    public function createMenu($name)
    {
        $menu = new Menus();
        $menu->name = $name;
        $menu->save();

        return $menu;
    }

    /**
     * Returns false when the menu still has items.
     */
    public function deleteMenu(Menus $menu)
    {
        if (MenuItems::where('menu', $menu->id)->exists()) {
            return false;
        }

        $menu->delete();

        return true;
    }

    /**
     * Saves the menu name and, for each given item, its position (parent, sort) and,
     * when a label is given, its content. Depths are computed from the parents.
     *
     * @param array $items list of ['id', 'parent', 'sort', 'label'?, 'link'?, 'class'?, 'target'?, 'role_id'?]
     */
    public function saveMenu(Menus $menu, $name, array $items, $field = 'items')
    {
        $existing = $this->itemsOf($menu, $items, $field);

        $parents = $existing->pluck('parent', 'id')->all();
        foreach ($items as $value) {
            $parents[$value['id']] = (int) $value['parent'];
        }

        $depths = [];
        foreach ($items as $index => $value) {
            if ($value['parent'] != 0 && !isset($existing[$value['parent']])) {
                throw ValidationException::withMessages([
                    "$field.$index.parent" => trans('wmenu::messages.parent_not_in_menu'),
                ]);
            }

            $depth = $this->depthOf($value['id'], $parents);
            if ($depth === null) {
                throw ValidationException::withMessages([
                    "$field.$index.parent" => trans('wmenu::messages.parent_loop'),
                ]);
            }
            $depths[$value['id']] = $depth;
        }

        DB::transaction(function () use ($menu, $name, $items, $existing, $depths) {
            $menu->name = $name;
            $menu->save();

            foreach ($items as $value) {
                $item = $existing[$value['id']];
                $item->parent = $value['parent'];
                $item->sort = $value['sort'];
                $item->depth = $depths[$value['id']];
                if (array_key_exists('label', $value)) {
                    $this->fill($item, $value);
                }
                $item->save();
            }
        });
    }

    /**
     * Updates the content (label, link, class, role) of several items of the menu.
     */
    public function updateItems(Menus $menu, array $items, $field = 'items')
    {
        $existing = $this->itemsOf($menu, $items, $field);

        DB::transaction(function () use ($items, $existing) {
            foreach ($items as $value) {
                $this->fill($existing[$value['id']], $value)->save();
            }
        });
    }

    public function addItem(Menus $menu, array $data)
    {
        $item = new MenuItems();
        $this->fill($item, $data);
        $item->menu = $menu->id;
        $item->sort = MenuItems::getNextSortRoot($menu->id);
        $item->save();

        return $item;
    }

    public function updateItem(MenuItems $item, array $data)
    {
        $this->fill($item, $data)->save();

        return $item;
    }

    public function deleteItem(MenuItems $item)
    {
        $item->delete();
    }

    /**
     * Loads all items of the menu keyed by id, making sure the given ones belong to it.
     */
    protected function itemsOf(Menus $menu, array $items, $field)
    {
        $existing = MenuItems::where('menu', $menu->id)->get()->keyBy('id');

        foreach ($items as $index => $value) {
            if (!isset($existing[$value['id']])) {
                throw ValidationException::withMessages([
                    "$field.$index.id" => trans('wmenu::messages.item_not_in_menu'),
                ]);
            }
        }

        return $existing;
    }

    /**
     * Number of ancestors of the item, or null when following the parents loops back.
     */
    protected function depthOf($id, array $parents)
    {
        $depth = 0;
        $seen = [$id => true];
        while (!empty($parents[$id])) {
            $id = $parents[$id];
            if (isset($seen[$id])) {
                return null;
            }
            $seen[$id] = true;
            $depth++;
        }

        return $depth;
    }

    /**
     * The target column comes with a later migration, apps that have not run it yet can still save.
     */
    protected function hasTargetColumn()
    {
        if ($this->hasTarget === null) {
            $this->hasTarget = Schema::hasColumn((new MenuItems())->getTable(), 'target');
        }

        return $this->hasTarget;
    }

    protected function fill(MenuItems $item, array $value)
    {
        $item->label = $value['label'];
        $item->link = $value['link'] ?? '';
        $item->class = $value['class'] ?? null;
        if (array_key_exists('target', $value) && $this->hasTargetColumn()) {
            $item->target = $value['target'] === '_blank' ? '_blank' : null;
        }
        if (config('menu.use_roles')) {
            $item->role_id = $value['role_id'] ?? 0;
        }

        return $item;
    }
}
