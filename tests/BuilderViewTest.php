<?php

namespace Harimayco\Menu\Tests;

use Harimayco\Menu\Facades\Menu;
use Harimayco\Menu\Models\MenuItems;
use Harimayco\Menu\Models\Menus;
use Illuminate\Support\Facades\DB;

class BuilderViewTest extends TestCase
{
    protected function builder(array $query = [])
    {
        $this->app['request']->merge($query);

        return Menu::render()->render() . Menu::scripts()->render();
    }

    public function test_shows_the_create_form_without_a_menu()
    {
        Menus::create(['name' => 'Main']);

        foreach ([[], ['menu' => 999]] as $query) {
            $html = $this->builder($query);

            $this->assertStringContainsString('data-form="create"', $html);
            $this->assertStringNotContainsString('data-form="save"', $html);
            $this->assertStringContainsString('<option value="1"', $html);
        }
    }

    public function test_renders_the_tree_of_the_selected_menu()
    {
        DB::table('roles')->insert([['name' => 'admin'], ['name' => 'editor']]);
        $menu = Menus::create(['name' => 'Main']);
        $a = MenuItems::create(['label' => '<b>A</b>', 'link' => '/a', 'menu' => $menu->id, 'role_id' => 2, 'target' => '_blank']);
        MenuItems::create(['label' => 'B', 'link' => '/b', 'menu' => $menu->id, 'parent' => $a->id, 'depth' => 1]);

        $html = $this->builder(['menu' => $menu->id]);

        $this->assertStringContainsString('data-form="save"', $html);
        $this->assertStringContainsString('&lt;b&gt;A&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>A</b>', $html);
        $this->assertMatchesRegularExpression('/value="2"\s+selected/', $html);
        $this->assertMatchesRegularExpression('/value="_blank"\s+selected/', $html);
        // B is rendered inside the sub list of A
        $this->assertMatchesRegularExpression('/data-id="' . $a->id . '".*<ul class="wmenu-list">\s*<li class="wmenu-item" data-id="2"/s', $html);
        $this->assertStringContainsString('id="wmenu-item-template"', $html);
        $this->assertStringContainsString('menus\/__MENU__\/items\/__ITEM__', $html);
        $this->assertStringContainsString('wmenu.js', $html);
        $this->assertStringNotContainsString('jquery', strtolower($html));
    }

    public function test_is_translated()
    {
        $menu = Menus::create(['name' => 'Main']);
        $this->app->setLocale('vi');

        $html = $this->builder(['menu' => $menu->id]);

        $this->assertStringContainsString('Lưu menu', $html);
        $this->assertStringContainsString('"save":"Lưu menu"', $html);
    }
}
