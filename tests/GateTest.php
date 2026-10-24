<?php

namespace Harimayco\Menu\Tests;

use Harimayco\Menu\Models\Menus;
use Illuminate\Support\Facades\Gate;

class GateTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('menu.gate', 'manage-menus');
    }

    public function test_gate_is_checked_when_configured()
    {
        Gate::define('manage-menus', function ($user) {
            return $user->id === 1;
        });

        $this->postAs('menus', ['name' => 'x'])->assertStatus(201);

        $other = $this->user();
        $other->id = 2;
        $this->actingAs($other)->postJson('/harimayco/menus', ['name' => 'y'])->assertStatus(403);
        $this->assertSame(1, Menus::count());
    }
}
