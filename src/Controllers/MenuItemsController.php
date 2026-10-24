<?php

namespace Harimayco\Menu\Controllers;

use Harimayco\Menu\MenuManager;
use Harimayco\Menu\Models\MenuItems;
use Harimayco\Menu\Models\Menus;
use Harimayco\Menu\Requests\MenuItemRequest;
use Illuminate\Routing\Controller;

class MenuItemsController extends Controller
{
    protected $menus;

    public function __construct(MenuManager $menus)
    {
        $this->menus = $menus;
    }

    public function store(MenuItemRequest $request, $menu)
    {
        $item = $this->menus->addItem(Menus::findOrFail($menu), $request->validated());

        return response()->json(['data' => $item], 201);
    }

    public function update(MenuItemRequest $request, $menu, $item)
    {
        $item = $this->menus->updateItem($this->find($menu, $item), $request->validated());

        return response()->json(['data' => $item]);
    }

    public function destroy($menu, $item)
    {
        $this->menus->deleteItem($this->find($menu, $item));

        return response()->json(null, 204);
    }

    protected function find($menu, $item)
    {
        return MenuItems::where('menu', $menu)->findOrFail($item);
    }
}
