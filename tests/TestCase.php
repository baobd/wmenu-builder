<?php

namespace Harimayco\Menu\Tests;

use Harimayco\Menu\Facades\Menu;
use Harimayco\Menu\MenuServiceProvider;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [MenuServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return ['Menu' => Menu::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        $app['config']->set('menu.use_roles', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
        Schema::create('roles', function ($table) {
            $table->increments('id');
            $table->string('name');
        });
    }

    protected function user()
    {
        $user = new User();
        $user->id = 1;

        return $user;
    }

    protected function postAs($uri, array $data = [])
    {
        return $this->actingAs($this->user())->postJson('/harimayco/' . $uri, $data);
    }

    protected function putAs($uri, array $data = [])
    {
        return $this->actingAs($this->user())->putJson('/harimayco/' . $uri, $data);
    }

    protected function deleteAs($uri)
    {
        return $this->actingAs($this->user())->deleteJson('/harimayco/' . $uri);
    }
}
