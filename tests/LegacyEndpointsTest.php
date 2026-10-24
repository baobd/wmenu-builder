<?php

namespace Harimayco\Menu\Tests;

use Harimayco\Menu\Facades\Menu;
use Harimayco\Menu\Models\MenuItems;
use Harimayco\Menu\Models\Menus;

/**
 * The endpoints used by builder scripts published before 2.0 keep working.
 */
class LegacyEndpointsTest extends TestCase
{
    public function test_crud_flow()
    {
        $id = $this->postAs('createnewmenu', ['menuname' => 'Main'])->assertOk()->json('resp');
        $a = $this->postAs('addcustommenu', ['idmenu' => $id, 'labelmenu' => 'A', 'linkmenu' => '/a', 'rolemenu' => 2])->assertOk()->json('resp');
        $b = $this->postAs('addcustommenu', ['idmenu' => $id, 'labelmenu' => 'B', 'linkmenu' => 'https://b.test'])->assertOk()->json('resp');

        $this->postAs('generatemenucontrol', ['idmenu' => $id, 'menuname' => 'Main 2', 'arraydata' => [
            ['id' => $a, 'parent' => 0, 'sort' => 0, 'depth' => 0],
            ['id' => $b, 'parent' => $a, 'sort' => 1, 'depth' => 1],
        ]])->assertOk();
        $this->assertSame(2, (int) MenuItems::find($a)->role_id, 'saving the structure keeps the roles');

        $this->postAs('updateitem', ['idmenu' => $id, 'arraydata' => [
            ['id' => $a, 'label' => 'A1', 'link' => '/a1', 'class' => 'c', 'role_id' => 2],
        ]])->assertOk();
        $this->postAs('updateitem', ['id' => $b, 'label' => 'B1', 'url' => '#b', 'clases' => 'd'])->assertOk();

        $tree = Menu::getByName('Main 2');
        $this->assertSame('A1', $tree[0]['label']);
        $this->assertSame('B1', $tree[0]['child'][0]['label']);

        $this->postAs('deletemenug', ['id' => $id])->assertOk()->assertJson(['error' => 1]);
        $this->postAs('deleteitemmenu', ['id' => $a])->assertOk();
        $this->postAs('deleteitemmenu', ['id' => $b])->assertOk();
        $this->postAs('deletemenug', ['id' => $id])->assertOk()->assertJsonMissing(['error' => 1]);
        $this->assertNull(Menus::find($id));
    }

    public function test_validation_and_ownership()
    {
        $one = Menus::create(['name' => 'One']);
        $two = Menus::create(['name' => 'Two']);
        $x = MenuItems::create(['label' => 'X', 'link' => '/', 'menu' => $two->id]);

        $this->postAs('addcustommenu', ['idmenu' => $one->id, 'labelmenu' => 'L', 'linkmenu' => 'javascript:alert(1)'])->assertStatus(422);
        $this->postAs('addcustommenu', ['idmenu' => 999, 'labelmenu' => 'L'])->assertStatus(404);
        $this->postAs('generatemenucontrol', ['idmenu' => $one->id, 'menuname' => 'One', 'arraydata' => [
            ['id' => $x->id, 'parent' => 0, 'sort' => 0, 'depth' => 0],
        ]])->assertStatus(422)->assertJsonValidationErrors('arraydata.0.id');
        $this->postAs('updateitem', ['idmenu' => $one->id, 'arraydata' => [['id' => $x->id, 'label' => 'hack']]])->assertStatus(422);
        $this->postAs('deleteitemmenu', ['id' => 12345])->assertStatus(404);
        $this->assertSame('X', $x->fresh()->label);
    }
}
