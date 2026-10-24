<?php

namespace Harimayco\Menu\Tests;

use Harimayco\Menu\Facades\Menu;
use Harimayco\Menu\Models\MenuItems;
use Harimayco\Menu\Models\Menus;
use Illuminate\Support\Facades\DB;

class BuilderViewTest extends TestCase
{
    public function test_builder_renders_items_roles_and_routes()
    {
        DB::table('roles')->insert([['name' => 'admin'], ['name' => 'editor']]);
        $menu = Menus::create(['name' => 'Main']);
        MenuItems::create(['label' => '<b>A</b>', 'link' => '/', 'menu' => $menu->id, 'role_id' => 2]);
        $this->app['request']->merge(['menu' => $menu->id]);

        $html = Menu::render()->render() . Menu::scripts()->render();

        $this->assertStringContainsString('&lt;b&gt;A&lt;/b&gt;', $html);
        $this->assertMatchesRegularExpression('/selected\s+value="2"/', $html);
        $this->assertStringContainsString('harimayco/menus/__MENU__/items/__ITEM__', $html);
    }
}
