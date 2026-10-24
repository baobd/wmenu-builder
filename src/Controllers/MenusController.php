<?php

namespace Harimayco\Menu\Controllers;

use Harimayco\Menu\MenuManager;
use Harimayco\Menu\Models\Menus;
use Harimayco\Menu\Requests\MenuRequest;
use Harimayco\Menu\Requests\SaveMenuRequest;
use Illuminate\Routing\Controller;

class MenusController extends Controller
{
    protected $menus;

    public function __construct(MenuManager $menus)
    {
        $this->menus = $menus;
    }

    public function store(MenuRequest $request)
    {
        $menu = $this->menus->createMenu($request->validated()['name']);

        return response()->json(['data' => $menu], 201);
    }

    public function update(SaveMenuRequest $request, $menu)
    {
        $menu = Menus::findOrFail($menu);
        $data = $request->validated();

        $this->menus->saveMenu($menu, $data['name'], $data['items'] ?? []);

        return response()->json(['data' => $menu->fresh()]);
    }

    public function destroy($menu)
    {
        if (!$this->menus->deleteMenu(Menus::findOrFail($menu))) {
            return response()->json(['message' => trans('wmenu::messages.menu_not_empty')], 409);
        }

        return response()->json(null, 204);
    }
}
