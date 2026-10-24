<?php

namespace Harimayco\Menu\Models;

use Harimayco\Menu\WMenu;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 */
class Menus extends Model
{
    protected $fillable = ['name'];

    protected static function booted()
    {
        static::saved(function (Menus $menu) {
            WMenu::forget($menu->id);
        });

        static::deleted(function (Menus $menu) {
            WMenu::forget($menu->id);
        });
    }

    public function getTable()
    {
        return config('menu.table_prefix') . config('menu.table_name_menus');
    }

    public static function byName($name)
    {
        return self::where('name', '=', $name)->first();
    }

    /**
     * @return HasMany
     */
    public function items()
    {
        return $this->hasMany(MenuItems::class, 'menu')->with('child')->where('parent', 0)->orderBy('sort', 'ASC');
    }
}
