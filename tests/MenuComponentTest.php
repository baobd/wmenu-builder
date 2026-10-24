<?php

namespace Harimayco\Menu\Tests;

use Harimayco\Menu\Models\MenuItems;
use Harimayco\Menu\Models\Menus;

class MenuComponentTest extends TestCase
{
    protected function menu()
    {
        $menu = Menus::create(['name' => 'Main']);
        $about = MenuItems::create(['label' => 'About', 'link' => '/about', 'menu' => $menu->id, 'sort' => 1, 'class' => 'about']);
        MenuItems::create(['label' => 'Team', 'link' => '/about/team', 'menu' => $menu->id, 'parent' => $about->id, 'sort' => 2, 'depth' => 1]);
        MenuItems::create(['label' => '<i>Docs</i>', 'link' => 'https://docs.test', 'target' => '_blank', 'menu' => $menu->id, 'sort' => 3]);
        MenuItems::create(['label' => 'Admin', 'link' => '/admin', 'menu' => $menu->id, 'sort' => 4, 'role_id' => 1]);

        return $menu;
    }

    protected function renderTemplate($template)
    {
        // Blade::render() only exists since Laravel 9
        $file = tempnam(sys_get_temp_dir(), 'wmenu') . '.blade.php';
        file_put_contents($file, $template);

        try {
            return preg_replace('/\s+/', ' ', view()->file($file)->render());
        } finally {
            unlink($file);
        }
    }

    public function test_renders_nested_lists()
    {
        $this->menu();

        $html = $this->renderTemplate('<x-wmenu-menu name="Main" class="nav" />');

        $this->assertStringContainsString('<ul class="menu nav">', $html);
        $this->assertStringContainsString('<li class="about"> <a href="/about">About</a> <ul class="sub-menu"> <li> <a href="/about/team">Team</a>', $html);
        $this->assertStringContainsString('<a href="https://docs.test" target="_blank" rel="noopener noreferrer">&lt;i&gt;Docs&lt;/i&gt;</a>', $html);
        $this->assertStringContainsString('Admin', $html);
    }

    public function test_marks_the_current_page_and_its_parents()
    {
        $this->menu();
        $this->app['url']->setRequest(\Illuminate\Http\Request::create('http://localhost/about/team'));

        $html = $this->renderTemplate('<x-wmenu-menu name="Main" active-class="is-active" />');

        $this->assertStringContainsString('<li class="about is-active">', $html);
        $this->assertStringContainsString('<li class="is-active"> <a href="/about/team">', $html);
        $this->assertStringNotContainsString('<li class="is-active"> <a href="https://docs.test"', $html);
    }

    public function test_filters_by_role()
    {
        $this->menu();

        $this->assertStringNotContainsString('Admin', $this->renderTemplate('<x-wmenu-menu name="Main" :role="2" />'));
        $this->assertStringContainsString('Admin', $this->renderTemplate('<x-wmenu-menu name="Main" :role="1" />'));
    }

    public function test_by_id_and_unknown_menus()
    {
        $menu = $this->menu();

        $this->assertStringContainsString('About', $this->renderTemplate('<x-wmenu-menu :id="' . $menu->id . '" />'));
        $this->assertSame('', trim($this->renderTemplate('<x-wmenu-menu name="Missing" />')));
    }
}
