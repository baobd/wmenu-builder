<?php

namespace Harimayco\Menu\Tests;

use Harimayco\Menu\Models\MenuItems;
use Harimayco\Menu\Models\Menus;
use Illuminate\Support\Facades\Schema;

class MenusApiTest extends TestCase
{
    public function test_menu_and_items_crud()
    {
        $menu = $this->postAs('menus', ['name' => 'Main'])->assertStatus(201)->json('data.id');

        $a = $this->postAs("menus/$menu/items", ['label' => 'A', 'link' => '/a', 'role_id' => 2])->assertStatus(201)->json('data.id');
        $b = $this->postAs("menus/$menu/items", ['label' => 'B', 'link' => 'https://b.test'])->assertStatus(201)->json('data.id');
        $c = $this->postAs("menus/$menu/items", ['label' => 'C'])->assertStatus(201)->json('data.id');
        $this->assertSame('', MenuItems::find($c)->link);
        $this->assertSame(2, (int) MenuItems::find($a)->role_id);
        $this->assertSame([1, 2, 3], MenuItems::orderBy('id')->pluck('sort')->map(function ($sort) { return (int) $sort; })->all());

        $this->putAs("menus/$menu", ['name' => 'Main 2', 'items' => [
            ['id' => $a, 'parent' => 0, 'sort' => 0, 'label' => 'A1', 'link' => '/a1', 'class' => 'x', 'role_id' => 2],
            ['id' => $b, 'parent' => $a, 'sort' => 1, 'label' => 'B', 'link' => '#b'],
            ['id' => $c, 'parent' => $b, 'sort' => 2, 'label' => 'C', 'link' => 'mailto:a@b.c'],
        ]])->assertOk()->assertJsonPath('data.name', 'Main 2');

        $this->assertSame('A1', MenuItems::find($a)->label);
        $this->assertSame('x', MenuItems::find($a)->class);
        $this->assertSame([0, 1, 2], [(int) MenuItems::find($a)->depth, (int) MenuItems::find($b)->depth, (int) MenuItems::find($c)->depth]);

        $this->putAs("menus/$menu/items/$c", ['label' => 'C2', 'link' => 'tel:123'])->assertOk()->assertJsonPath('data.label', 'C2');

        $this->deleteAs("menus/$menu")->assertStatus(409);

        // deleting B moves C up under A
        $this->deleteAs("menus/$menu/items/$b")->assertStatus(204);
        $this->assertSame($a, (int) MenuItems::find($c)->parent);
        $this->assertSame(1, (int) MenuItems::find($c)->depth);

        $this->deleteAs("menus/$menu/items/$a")->assertStatus(204);
        $this->deleteAs("menus/$menu/items/$c")->assertStatus(204);
        $this->deleteAs("menus/$menu")->assertStatus(204);
        $this->assertSame(0, Menus::count());
    }

    public function test_structure_only_save_keeps_content()
    {
        $menu = Menus::create(['name' => 'Main']);
        $a = MenuItems::create(['label' => 'A', 'link' => '/a', 'menu' => $menu->id]);

        $this->putAs("menus/{$menu->id}", ['name' => 'Main', 'items' => [
            ['id' => $a->id, 'parent' => 0, 'sort' => 5],
        ]])->assertOk();

        $this->assertSame('A', $a->fresh()->label);
        $this->assertSame(5, (int) $a->fresh()->sort);
    }

    public function test_depth_is_computed_from_parents()
    {
        $menu = Menus::create(['name' => 'Main']);
        $a = MenuItems::create(['label' => 'A', 'link' => '/', 'menu' => $menu->id]);
        $b = MenuItems::create(['label' => 'B', 'link' => '/', 'menu' => $menu->id]);

        $this->putAs("menus/{$menu->id}", ['name' => 'Main', 'items' => [
            ['id' => $a->id, 'parent' => 0, 'sort' => 0, 'depth' => 7],
            ['id' => $b->id, 'parent' => $a->id, 'sort' => 1, 'depth' => 0],
        ]])->assertOk();

        $this->assertSame(0, (int) $a->fresh()->depth);
        $this->assertSame(1, (int) $b->fresh()->depth);
    }

    public function test_rejects_items_and_parents_of_other_menus_and_cycles()
    {
        $one = Menus::create(['name' => 'One']);
        $two = Menus::create(['name' => 'Two']);
        $a = MenuItems::create(['label' => 'A', 'link' => '/', 'menu' => $one->id]);
        $b = MenuItems::create(['label' => 'B', 'link' => '/', 'menu' => $one->id]);
        $x = MenuItems::create(['label' => 'X', 'link' => '/', 'menu' => $two->id]);

        $this->putAs("menus/{$one->id}", ['name' => 'One', 'items' => [
            ['id' => $x->id, 'parent' => 0, 'sort' => 0],
        ]])->assertStatus(422)->assertJsonValidationErrors('items.0.id');

        $this->putAs("menus/{$one->id}", ['name' => 'One', 'items' => [
            ['id' => $a->id, 'parent' => $x->id, 'sort' => 0],
        ]])->assertStatus(422)->assertJsonValidationErrors('items.0.parent');

        $this->putAs("menus/{$one->id}", ['name' => 'One', 'items' => [
            ['id' => $a->id, 'parent' => $b->id, 'sort' => 0],
            ['id' => $b->id, 'parent' => $a->id, 'sort' => 1],
        ]])->assertStatus(422)->assertJsonValidationErrors('items.0.parent');

        $this->putAs("menus/{$one->id}", ['name' => 'One', 'items' => [
            ['id' => $a->id, 'parent' => $a->id, 'sort' => 0],
        ]])->assertStatus(422);

        // nothing was written
        $this->assertSame(0, (int) $a->fresh()->parent);
        $this->assertSame(0, (int) $b->fresh()->parent);

        $this->putAs("menus/{$one->id}/items/{$x->id}", ['label' => 'hack'])->assertStatus(404);
        $this->deleteAs("menus/{$one->id}/items/{$x->id}")->assertStatus(404);
        $this->assertSame('X', $x->fresh()->label);
    }

    public function test_validation()
    {
        $menu = Menus::create(['name' => 'Main']);

        $this->postAs('menus', [])->assertStatus(422)->assertJsonValidationErrors('name');
        $this->postAs("menus/{$menu->id}/items", ['link' => '/'])->assertStatus(422)->assertJsonValidationErrors('label');
        $this->postAs("menus/{$menu->id}/items", ['label' => 'L', 'link' => 'javascript:alert(1)'])
            ->assertStatus(422)->assertJsonValidationErrors('link');
        $this->postAs('menus/999/items', ['label' => 'L'])->assertStatus(404);
        $this->putAs('menus/999', ['name' => 'x'])->assertStatus(404);
        $this->deleteAs('menus/999')->assertStatus(404);
        $this->assertSame(0, MenuItems::count());
    }

    public function test_roles_are_ignored_when_disabled()
    {
        config(['menu.use_roles' => false]);
        $menu = Menus::create(['name' => 'Main']);

        $id = $this->postAs("menus/{$menu->id}/items", ['label' => 'A', 'role_id' => 3])->json('data.id');

        $this->assertSame(0, (int) MenuItems::find($id)->role_id);
    }

    public function test_target()
    {
        $menu = Menus::create(['name' => 'Main']);

        $id = $this->postAs("menus/{$menu->id}/items", ['label' => 'A', 'target' => '_blank'])->assertStatus(201)->json('data.id');
        $this->assertSame('_blank', MenuItems::find($id)->target);

        $this->postAs("menus/{$menu->id}/items", ['label' => 'B', 'target' => '_top'])->assertStatus(422)->assertJsonValidationErrors('target');

        $this->putAs("menus/{$menu->id}", ['name' => 'Main', 'items' => [
            ['id' => $id, 'parent' => 0, 'sort' => 0, 'label' => 'A', 'target' => '_self'],
        ]])->assertOk();
        $this->assertNull(MenuItems::find($id)->target);
    }

    public function test_saves_without_the_target_column()
    {
        // apps that upgraded without running the target migration yet
        Schema::table((new MenuItems())->getTable(), function ($table) {
            $table->dropColumn('target');
        });
        $menu = Menus::create(['name' => 'Main']);

        $id = $this->postAs("menus/{$menu->id}/items", ['label' => 'A', 'target' => '_blank'])->assertStatus(201)->json('data.id');
        $this->putAs("menus/{$menu->id}", ['name' => 'Main', 'items' => [
            ['id' => $id, 'parent' => 0, 'sort' => 0, 'label' => 'A2', 'target' => '_blank'],
        ]])->assertOk();

        $this->assertSame('A2', MenuItems::find($id)->label);
    }

    public function test_messages_are_translated()
    {
        $this->app->setLocale('vi');
        $menu = Menus::create(['name' => 'Main']);
        MenuItems::create(['label' => 'A', 'link' => '/', 'menu' => $menu->id]);

        $this->postAs("menus/{$menu->id}/items", ['label' => 'L', 'link' => 'javascript:x'])
            ->assertStatus(422)->assertJsonFragment(['link' => ['URL phải là đường dẫn tương đối hoặc dùng một trong các giao thức: http, https, mailto, tel.']]);
        $this->deleteAs("menus/{$menu->id}")->assertStatus(409)->assertJson(['message' => 'Bạn phải xoá hết các mục trước.']);
    }
}
