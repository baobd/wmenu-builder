<?php

use Harimayco\Menu\Controllers\MenuController;
use Harimayco\Menu\Controllers\MenuItemsController;
use Harimayco\Menu\Controllers\MenusController;
use Illuminate\Support\Facades\Route;

$middleware = (array) config('menu.middleware', []);
if (config('menu.gate')) {
    $middleware[] = 'can:' . config('menu.gate');
}

Route::middleware($middleware)
    ->prefix(trim(config('menu.route_path'), '/'))
    ->group(function () {
        Route::post('menus', [MenusController::class, 'store'])->name('wmenu.menus.store');
        Route::put('menus/{menu}', [MenusController::class, 'update'])->name('wmenu.menus.update');
        Route::delete('menus/{menu}', [MenusController::class, 'destroy'])->name('wmenu.menus.destroy');
        Route::post('menus/{menu}/items', [MenuItemsController::class, 'store'])->name('wmenu.items.store');
        Route::put('menus/{menu}/items/{item}', [MenuItemsController::class, 'update'])->name('wmenu.items.update');
        Route::delete('menus/{menu}/items/{item}', [MenuItemsController::class, 'destroy'])->name('wmenu.items.destroy');

        // deprecated: endpoints of the builder scripts published before 2.0
        Route::post('addcustommenu', [MenuController::class, 'addcustommenu'])->name('haddcustommenu');
        Route::post('deleteitemmenu', [MenuController::class, 'deleteitemmenu'])->name('hdeleteitemmenu');
        Route::post('deletemenug', [MenuController::class, 'deletemenug'])->name('hdeletemenug');
        Route::post('createnewmenu', [MenuController::class, 'createnewmenu'])->name('hcreatenewmenu');
        Route::post('generatemenucontrol', [MenuController::class, 'generatemenucontrol'])->name('hgeneratemenucontrol');
        Route::post('updateitem', [MenuController::class, 'updateitem'])->name('hupdateitem');
    });
