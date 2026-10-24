<?php

namespace Harimayco\Menu\Tests;

use Harimayco\Menu\Facades\Menu;
use Harimayco\Menu\Models\MenuItems;
use Harimayco\Menu\Models\Menus;
use Harimayco\Menu\WMenu;
use Illuminate\Support\Facades\DB;

class MenuTreeTest extends TestCase
{
    protected function tree()
    {
        $menu = Menus::create(['name' => 'Main']);
        $a = MenuItems::create(['label' => 'A', 'link' => '/a', 'menu' => $menu->id, 'sort' => 1]);
        $b = MenuItems::create(['label' => 'B', 'link' => '/b', 'menu' => $menu->id, 'parent' => $a->id, 'sort' => 2, 'depth' => 1]);
        MenuItems::create(['label' => 'C', 'link' => '/c', 'menu' => $menu->id, 'parent' => $b->id, 'sort' => 3, 'depth' => 2]);
        MenuItems::create(['label' => 'D', 'link' => '/d', 'menu' => $menu->id, 'sort' => 0]);

        return $menu;
    }

    public function test_helper_returns_nested_tree()
    {
        $menu = $this->tree();

        $tree = Menu::get($menu->id);

        $this->assertSame(['D', 'A'], array_column($tree, 'label'));
        $this->assertSame('C', $tree[1]['child'][0]['child'][0]['label']);
        $this->assertSame($tree, Menu::getByName('Main'));
        $this->assertSame([], Menu::getByName('Missing'));
    }

    public function test_items_relation_eager_loads_every_level()
    {
        $this->tree();

        DB::enableQueryLog();
        $menu = Menus::where('name', 'Main')->with('items')->first();
        $label = $menu->items[1]->child[0]->child[0]->label;
        $queries = count(DB::getQueryLog());

        $this->assertSame('C', $label);
        // menu, roots, then one query per level (2, 3, and the empty 4th)
        $this->assertLessThanOrEqual(5, $queries);
    }

    public function test_cache_is_used_and_cleared_on_changes()
    {
        config(['menu.cache.enabled' => true, 'cache.default' => 'array']);
        $menu = $this->tree();

        $this->assertSame('A', Menu::get($menu->id)[1]['label']);

        // a change that bypasses the models is not seen, the cached tree is returned
        DB::table((new MenuItems())->getTable())->where('label', 'A')->update(['label' => 'stale']);
        $this->assertSame('A', Menu::get($menu->id)[1]['label']);

        // a change through the models clears the cache
        MenuItems::where('label', 'D')->first()->update(['label' => 'D2']);
        $tree = Menu::get($menu->id);
        $this->assertSame('D2', $tree[0]['label']);
        $this->assertSame('stale', $tree[1]['label']);

        MenuItems::where('label', 'D2')->first()->delete();
        $this->assertCount(1, Menu::get($menu->id));
    }

    public function test_select_escapes_menu_names()
    {
        Menus::create(['name' => '<script>alert(1)</script>']);

        $html = (new WMenu())->select('menu', Menus::pluck('name', 'id')->all());

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
