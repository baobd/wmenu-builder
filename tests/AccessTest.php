<?php

namespace Harimayco\Menu\Tests;

use Harimayco\Menu\Models\Menus;

class AccessTest extends TestCase
{
    public function test_guests_are_rejected()
    {
        $this->postJson('/harimayco/menus', ['name' => 'x'])->assertStatus(401);
        $this->postJson('/harimayco/createnewmenu', ['menuname' => 'x'])->assertStatus(401);
        $this->assertSame(0, Menus::count());
    }

    public function test_routes_use_the_web_group()
    {
        // Laravel skips the CSRF check while running tests, so check the middleware instead
        foreach (['wmenu.menus.store', 'wmenu.items.update', 'hgeneratemenucontrol'] as $name) {
            $middleware = app('router')->getRoutes()->getByName($name)->gatherMiddleware();
            $this->assertContains('web', $middleware, $name);
            $this->assertContains('auth', $middleware, $name);
        }
    }
}
