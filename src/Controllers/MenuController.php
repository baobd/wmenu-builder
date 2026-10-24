<?php

namespace Harimayco\Menu\Controllers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Harimayco\Menu\Models\Menus;
use Harimayco\Menu\Models\MenuItems;

class MenuController extends Controller
{
    /**
     * URL schemes a menu link may use. Links without a scheme (relative paths, #anchors) are always allowed.
     */
    protected $allowedSchemes = ['http', 'https', 'mailto', 'tel'];

    public function createnewmenu(Request $request)
    {
        $data = $request->validate([
            'menuname' => 'required|string|max:255',
        ]);

        $menu = new Menus();
        $menu->name = $data['menuname'];
        $menu->save();

        return response()->json(['resp' => $menu->id]);
    }

    public function deleteitemmenu(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $menuitem = MenuItems::findOrFail($request->input('id'));
        $menuitem->delete();

        return response()->json(['resp' => 1]);
    }

    public function deletemenug(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $menu = Menus::findOrFail($request->input('id'));

        if (MenuItems::where('menu', $menu->id)->exists()) {
            return response()->json(['resp' => 'You have to delete all items first', 'error' => 1]);
        }

        $menu->delete();

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
                'arraydata.*.link' => ['nullable', 'string', 'max:255', $this->linkRule()],
                'arraydata.*.class' => 'nullable|string|max:255',
                'arraydata.*.role_id' => 'nullable|integer',
            ]);

            $items = $this->itemsOfMenu($data['idmenu'], $data['arraydata']);

            DB::transaction(function () use ($data, $items) {
                foreach ($data['arraydata'] as $value) {
                    $this->fillContent($items[$value['id']], $value)->save();
                }
            });
        } else {
            $data = $request->validate([
                'id' => 'required|integer',
                'label' => 'required|string|max:255',
                'url' => ['nullable', 'string', 'max:255', $this->linkRule()],
                'clases' => 'nullable|string|max:255',
                'role_id' => 'nullable|integer',
            ]);

            $menuitem = MenuItems::findOrFail($data['id']);
            $this->fillContent($menuitem, [
                'label' => $data['label'],
                'link' => $data['url'] ?? null,
                'class' => $data['clases'] ?? null,
                'role_id' => $data['role_id'] ?? null,
            ])->save();
        }

        return response()->json(['resp' => 1]);
    }

    public function addcustommenu(Request $request)
    {
        $data = $request->validate([
            'idmenu' => 'required|integer',
            'labelmenu' => 'required|string|max:255',
            'linkmenu' => ['nullable', 'string', 'max:255', $this->linkRule()],
            'rolemenu' => 'nullable|integer',
        ]);

        $menu = Menus::findOrFail($data['idmenu']);

        $menuitem = new MenuItems();
        $menuitem->label = $data['labelmenu'];
        $menuitem->link = $data['linkmenu'] ?? '';
        if (config('menu.use_roles')) {
            $menuitem->role_id = $data['rolemenu'] ?? 0;
        }
        $menuitem->menu = $menu->id;
        $menuitem->sort = MenuItems::getNextSortRoot($menu->id);
        $menuitem->save();

        return response()->json(['resp' => $menuitem->id]);
    }

    /**
     * Saves the menu name, the structure (parent, sort, depth) and, when sent, the content
     * (label, link, class, role) of every item in a single transaction.
     */
    public function generatemenucontrol(Request $request)
    {
        $data = $request->validate([
            'idmenu' => 'required|integer',
            'menuname' => 'required|string|max:255',
            'arraydata' => 'nullable|array',
            'arraydata.*.id' => 'required|integer',
            'arraydata.*.parent' => 'required|integer|min:0',
            'arraydata.*.sort' => 'required|integer|min:0',
            'arraydata.*.depth' => 'required|integer|min:0',
            'arraydata.*.label' => 'sometimes|required|string|max:255',
            'arraydata.*.link' => ['sometimes', 'nullable', 'string', 'max:255', $this->linkRule()],
            'arraydata.*.class' => 'sometimes|nullable|string|max:255',
            'arraydata.*.role_id' => 'sometimes|nullable|integer',
        ]);

        $menu = Menus::findOrFail($data['idmenu']);
        $arraydata = $data['arraydata'] ?? [];
        $items = $this->itemsOfMenu($menu->id, $arraydata);

        foreach ($arraydata as $index => $value) {
            if ($value['parent'] != 0 && (!isset($items[$value['parent']]) || $value['parent'] == $value['id'])) {
                throw ValidationException::withMessages([
                    "arraydata.$index.parent" => 'The parent must be another item of this menu.',
                ]);
            }
        }

        DB::transaction(function () use ($menu, $data, $arraydata, $items) {
            $menu->name = $data['menuname'];
            $menu->save();

            foreach ($arraydata as $value) {
                $menuitem = $items[$value['id']];
                $menuitem->parent = $value['parent'];
                $menuitem->sort = $value['sort'];
                $menuitem->depth = $value['depth'];
                if (array_key_exists('label', $value)) {
                    $this->fillContent($menuitem, $value);
                }
                $menuitem->save();
            }
        });

        return response()->json(['resp' => 1]);
    }

    /**
     * Loads the items referenced in $arraydata, keyed by id, making sure all of them belong to the menu.
     */
    protected function itemsOfMenu($menuId, array $arraydata)
    {
        $items = MenuItems::where('menu', $menuId)->get()->keyBy('id');

        foreach ($arraydata as $index => $value) {
            if (!isset($items[$value['id']])) {
                throw ValidationException::withMessages([
                    "arraydata.$index.id" => 'The item does not belong to this menu.',
                ]);
            }
        }

        return $items;
    }

    protected function fillContent(MenuItems $menuitem, array $value)
    {
        $menuitem->label = $value['label'];
        $menuitem->link = $value['link'] ?? '';
        $menuitem->class = $value['class'] ?? null;
        if (config('menu.use_roles')) {
            $menuitem->role_id = $value['role_id'] ?? 0;
        }

        return $menuitem;
    }

    /**
     * Rejects links such as "javascript:..." or "data:..." that would run code when clicked.
     */
    protected function linkRule()
    {
        return function ($attribute, $value, Closure $fail) {
            // browsers ignore whitespace and control characters inside the scheme ("java\tscript:")
            $normalized = preg_replace('/[\x00-\x20\x7F]+/', '', (string) $value);

            if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $normalized, $matches)
                && !in_array(strtolower($matches[1]), $this->allowedSchemes, true)) {
                $fail('The link must be a relative path or use one of: ' . implode(', ', $this->allowedSchemes) . '.');
            }
        };
    }
}
