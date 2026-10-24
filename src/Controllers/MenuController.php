<?php

namespace Harimayco\Menu\Controllers;

use Harimayco\Menu\MenuManager;
use Harimayco\Menu\Models\MenuItems;
use Harimayco\Menu\Models\Menus;
use Harimayco\Menu\Rules\SafeLink;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Endpoints used by the builder scripts published before 2.0.
 *
 * @deprecated use MenusController and MenuItemsController (the "wmenu.*" routes)
 */
class MenuController extends Controller
{
    protected $menus;

    public function __construct(MenuManager $menus)
    {
        $this->menus = $menus;
    }

    public function createnewmenu(Request $request)
    {
        $data = $request->validate([
            'menuname' => 'required|string|max:255',
        ]);

        return response()->json(['resp' => $this->menus->createMenu($data['menuname'])->id]);
    }

    public function deleteitemmenu(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $this->menus->deleteItem(MenuItems::findOrFail($request->input('id')));

        return response()->json(['resp' => 1]);
    }

    public function deletemenug(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        if (!$this->menus->deleteMenu(Menus::findOrFail($request->input('id')))) {
            return response()->json(['resp' => trans('wmenu::messages.menu_not_empty'), 'error' => 1]);
        }

        return response()->json(['resp' => 'you delete this item']);
    }

    public function updateitem(Request $request)
    {
        if (is_array($request->input('arraydata'))) {
            $data = $request->validate([
                'idmenu' => 'required|integer',
                'arraydata' => 'array',
                'arraydata.*.id' => 'required|integer',
                'arraydata.*.label' => 'required|string|max:255',
                'arraydata.*.link' => ['nullable', 'string', 'max:255', new SafeLink()],
                'arraydata.*.class' => 'nullable|string|max:255',
                'arraydata.*.role_id' => 'nullable|integer',
            ]);

            $this->menus->updateItems(Menus::findOrFail($data['idmenu']), $data['arraydata'], 'arraydata');
        } else {
            $data = $request->validate([
                'id' => 'required|integer',
                'label' => 'required|string|max:255',
                'url' => ['nullable', 'string', 'max:255', new SafeLink()],
                'clases' => 'nullable|string|max:255',
                'role_id' => 'nullable|integer',
            ]);

            $this->menus->updateItem(MenuItems::findOrFail($data['id']), [
                'label' => $data['label'],
                'link' => $data['url'] ?? null,
                'class' => $data['clases'] ?? null,
                'role_id' => $data['role_id'] ?? null,
            ]);
        }

        return response()->json(['resp' => 1]);
    }

    public function addcustommenu(Request $request)
    {
        $data = $request->validate([
            'idmenu' => 'required|integer',
            'labelmenu' => 'required|string|max:255',
            'linkmenu' => ['nullable', 'string', 'max:255', new SafeLink()],
            'rolemenu' => 'nullable|integer',
        ]);

        $item = $this->menus->addItem(Menus::findOrFail($data['idmenu']), [
            'label' => $data['labelmenu'],
            'link' => $data['linkmenu'] ?? null,
            'role_id' => $data['rolemenu'] ?? null,
        ]);

        return response()->json(['resp' => $item->id]);
    }

    public function generatemenucontrol(Request $request)
    {
        $data = $request->validate([
            'idmenu' => 'required|integer',
            'menuname' => 'required|string|max:255',
            'arraydata' => 'nullable|array',
            'arraydata.*.id' => 'required|integer',
            'arraydata.*.parent' => 'required|integer|min:0',
            'arraydata.*.sort' => 'required|integer|min:0',
            'arraydata.*.depth' => 'nullable|integer|min:0',
            'arraydata.*.label' => 'sometimes|required|string|max:255',
            'arraydata.*.link' => ['sometimes', 'nullable', 'string', 'max:255', new SafeLink()],
            'arraydata.*.class' => 'sometimes|nullable|string|max:255',
            'arraydata.*.role_id' => 'sometimes|nullable|integer',
        ]);

        $this->menus->saveMenu(Menus::findOrFail($data['idmenu']), $data['menuname'], $data['arraydata'] ?? [], 'arraydata');

        return response()->json(['resp' => 1]);
    }
}
