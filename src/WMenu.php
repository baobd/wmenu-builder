<?php

namespace Harimayco\Menu;

use Harimayco\Menu\Models\Menus;
use Harimayco\Menu\Models\MenuItems;
use Illuminate\Support\Facades\DB;

class WMenu
{

    /**
     * The menu builder: the menu chosen with ?menu=ID, or the form to create one.
     */
    public function render()
    {
        $menulist = Menus::orderBy('name')->pluck('name', 'id')->all();
        $menu = request()->filled('menu') ? Menus::with('items')->find(request()->input('menu')) : null;

        $data = [
            'menulist' => $menulist,
            'menu' => $menu,
            'currentUrl' => url()->current(),
            'roles' => [],
            'role_pk' => config('menu.roles_pk'),
            'role_title_field' => config('menu.roles_title_field'),
        ];
        if (config('menu.use_roles')) {
            $data['roles'] = DB::table(config('menu.roles_table'))->select([config('menu.roles_pk'), config('menu.roles_title_field')])->get();
        }

        return view('wmenu::menu-html', $data);
    }

    /**
     * The builder scripts, to output after render(). They do not need jQuery.
     */
    public function scripts()
    {
        return view('wmenu::scripts');
    }

    public function select($name = "menu", $menulist = array())
    {
        $html = '<select name="' . e($name) . '">';

        foreach ($menulist as $key => $val) {
            $active = '';
            if (request()->input('menu') == $key) {
                $active = 'selected="selected"';
            }
            $html .= '<option ' . $active . ' value="' . e($key) . '">' . e($val) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }


    /**
     * Returns empty array if menu not found now.
     * Thanks @sovichet
     *
     * @param $name
     * @return array
     */
    public static function getByName($name)
    {
        $menu = Menus::byName($name);
        return is_null($menu) ? [] : self::get($menu->id);
    }

    public static function get($menu_id)
    {
        if (!config('menu.cache.enabled')) {
            return self::build($menu_id);
        }

        return self::cache()->remember(self::cacheKey($menu_id), config('menu.cache.ttl', 3600), function () use ($menu_id) {
            return self::build($menu_id);
        });
    }

    /**
     * Clears the cached tree of a menu, called when the menu or one of its items changes.
     */
    public static function forget($menu_id)
    {
        if (config('menu.cache.enabled')) {
            self::cache()->forget(self::cacheKey($menu_id));
        }
    }

    protected static function cache()
    {
        return cache()->store(config('menu.cache.store'));
    }

    protected static function cacheKey($menu_id)
    {
        return 'wmenu.menu.' . $menu_id;
    }

    protected static function build($menu_id)
    {
        $menuItem = new MenuItems;
        $menu_list = $menuItem->getall($menu_id);

        $roots = $menu_list->where('menu', (integer) $menu_id)->where('parent', 0);

        $items = self::tree($roots, $menu_list);
        return $items;
    }

    private static function tree($items, $all_items)
    {
        $data_arr = array();
        $i = 0;
        foreach ($items as $item) {
            $data_arr[$i] = $item->toArray();
            $find = $all_items->where('parent', $item->id);

            $data_arr[$i]['child'] = array();

            if ($find->count()) {
                $data_arr[$i]['child'] = self::tree($find, $all_items);
            }

            $i++;
        }

        return $data_arr;
    }

}
