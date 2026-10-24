# Laravel Drag and Drop menu editor like wordpress
[![Latest Stable Version](https://poser.pugx.org/harimayco/laravel-menu/v/stable)](https://packagist.org/packages/harimayco/laravel-menu) [![Latest Unstable Version](https://poser.pugx.org/harimayco/laravel-menu/v/unstable)](https://packagist.org/packages/harimayco/laravel-menu) [![Total Downloads](https://poser.pugx.org/harimayco/laravel-menu/downloads)](https://packagist.org/packages/harimayco/laravel-menu) [![Monthly Downloads](https://poser.pugx.org/harimayco/laravel-menu/d/monthly)](https://packagist.org/packages/harimayco/laravel-menu)

forked from https://github.com/lordmacu/wmenu
![Laravel drag and drop menu](https://raw.githubusercontent.com/harimayco/wmenu-builder/master/screenshot.png)

### Installation

1. Run

```php
composer require harimayco/laravel-menu
```

**_Step 2 & 3 are optional, Laravel discovers the package automatically_**

2. Add the following class, to "providers" array in the file config/app.php (optional on laravel 5.5)

```php
Harimayco\Menu\MenuServiceProvider::class,
```

3. add facade in the file config/app.php (optional on laravel 5.5)

```php
'Menu' => Harimayco\Menu\Facades\Menu::class,
```

4. Run publish

```php
php artisan vendor:publish --provider="Harimayco\Menu\MenuServiceProvider"
```

5. Configure (optional) in **_config/menu.php_** :

- **_CUSTOM MIDDLEWARE:_** The builder routes use `['web', 'auth']` by default. Keep `web` (session + CSRF protection) and an authentication/authorization middleware (e.g. `['web', 'auth', 'can:manage-menus']`), otherwise anyone can edit your menus
- **_TABLE PREFIX:_** By default this package will create 2 new tables named "menus" and "menu_items" but you can still add your own table prefix avoiding conflict with existing table
- **_TABLE NAMES_** If you want use specific name of tables you have to modify that and the migrations
- **_Custom routes_** If you want to edit the route path you can edit the field
- **_Role Access_** If you want to enable roles (permissions) on menu items

6. Run migrate

```php
php artisan migrate
```

DONE

### Menu Builder Usage Example - displays the builder

On your view blade file

```php
@extends('app')

@section('contents')
    {!! Menu::render() !!}
@endsection

//YOU MUST HAVE JQUERY LOADED BEFORE menu scripts
@push('scripts')
    {!! Menu::scripts() !!}
@endpush
```

### Using The Model

Call the model class

```php
use Harimayco\Menu\Models\Menus;
use Harimayco\Menu\Models\MenuItems;

```

### Menu Usage Example (a)

A basic two-level menu can be displayed in your blade template

##### Using Model Class
```php

/* get menu by id*/
$menu = Menus::find(1);
/* or by name */
$menu = Menus::where('name','Test Menu')->first();

/* or get menu by name and the items with EAGER LOADING (RECOMENDED for better performance and less query call)*/
$menu = Menus::where('name','Test Menu')->with('items')->first();
/*or by id */
$menu = Menus::where('id', 1)->with('items')->first();

//you can access by model result
$public_menu = $menu->items;

//or you can convert it to array
$public_menu = $menu->items->toArray();

```

##### or Using helper
```php
// Using Helper 
$public_menu = Menu::getByName('Public'); //return array

```

### Menu Usage Example (b)

Now inside your blade template file place the menu using this simple example

```php
<div class="nav-wrap">
    <div class="btn-menu">
        <span></span>
    </div><!-- //mobile menu button -->
    <nav id="mainnav" class="mainnav">

        @if($public_menu)
        <ul class="menu">
            @foreach($public_menu as $menu)
            <li class="">
                <a href="{{ $menu['link'] }}" title="">{{ $menu['label'] }}</a>
                @if( $menu['child'] )
                <ul class="sub-menu">
                    @foreach( $menu['child'] as $child )
                        <li class=""><a href="{{ $child['link'] }}" title="">{{ $child['label'] }}</a></li>
                    @endforeach
                </ul><!-- /.sub-menu -->
                @endif
            </li>
            @endforeach
        @endif

        </ul><!-- /.menu -->
    </nav><!-- /#mainnav -->
 </div><!-- /.nav-wrap -->
```

### HELPERS

### Get Menu Items By Menu ID

```php
use Harimayco\Menu\Facades\Menu;
...
/*
Parameter: Menu ID
Return: Array
*/
$menuList = Menu::get(1);
```

### Get Menu Items By Menu Name

In this example, you must have a menu named _Admin_

```php
use Harimayco\Menu\Facades\Menu;
...
/*
Parameter: Menu ID
Return: Array
*/
$menuList = Menu::getByName('Admin');
```

### Customization

you can edit the menu interface in **_resources/views/vendor/wmenu/menu-html.blade.php_**

### Credits

- [wmenu](https://github.com/lordmacu/wmenu) laravel package menu like wordpress

### Compatibility

- PHP 8.0+, Laravel 8.x, 9.x, 10.x, 11.x, 12.x
- For Laravel 5.x – 7.x use an older release of this package

### Upgrading from 1.x

- The default `middleware` in `config/menu.php` is now `['web', 'auth']`. If you published the config before, check that your `middleware` setting contains `web` and an authentication middleware: the old default `[]` left the builder routes open to everyone.
- Re-publish the assets so the builder uses the new save request and error messages: `php artisan vendor:publish --provider="Harimayco\Menu\MenuServiceProvider" --tag=public --force`
- The role migration was previously published to `database/` instead of `database/migrations/` (and with an invalid timestamp). If you use roles and never ran it, publish the migrations again and run `php artisan migrate`; you can delete the old `database/2019_01_05_293551_add-role-id-to-menu-items-table.php` file.
- Menu item links are validated: they must be relative (`/about`, `#top`) or use `http`, `https`, `mailto` or `tel`. Links such as `javascript:` are rejected.
- Deleting an item moves its sub items one level up (as the builder UI shows) instead of leaving them pointing to a missing parent.
- The controller no longer extends `App\Http\Controllers\Controller`.

### KNOWN ISSUES
- Not working with RTL websites [#21](https://github.com/harimayco/wmenu-builder/issues/21) (pull requests are welcome)
