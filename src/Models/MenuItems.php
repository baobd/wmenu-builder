<?php

namespace Harimayco\Menu\Models;

use Harimayco\Menu\WMenu;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $label
 * @property string $link
 * @property int $parent
 * @property int $sort
 * @property string|null $class
 * @property string|null $target
 * @property int $menu
 * @property int $depth
 * @property int $role_id
 */
class MenuItems extends Model
{
    protected $fillable = ['label', 'link', 'parent', 'sort', 'class', 'target', 'menu', 'depth', 'role_id'];

    protected static function booted()
    {
        // like the builder UI does, move sub items one level up instead of leaving them pointing at a missing parent
        static::deleting(function (MenuItems $item) {
            $descendants = [];
            $parents = [$item->id];
            while ($parents) {
                $ids = static::whereIn('parent', $parents)
                    ->whereNotIn('id', array_merge($descendants, [$item->id]))
                    ->pluck('id')->all();
                $descendants = array_merge($descendants, $ids);
                $parents = $ids;
            }

            if ($descendants) {
                static::whereIn('id', $descendants)->where('depth', '>', 0)->decrement('depth');
            }
            static::where('parent', $item->id)->update(['parent' => $item->parent]);
        });

        static::saved(function (MenuItems $item) {
            WMenu::forget($item->menu);
            if ($item->wasChanged('menu')) {
                WMenu::forget($item->getOriginal('menu'));
            }
        });

        static::deleted(function (MenuItems $item) {
            WMenu::forget($item->menu);
        });
    }

    public function getTable()
    {
        return config('menu.table_prefix') . config('menu.table_name_items');
    }

    public function getsons($id)
    {
        return $this->where("parent", $id)->get();
    }
    public function getall($id)
    {
        return $this->where("menu", $id)->orderBy("sort", "asc")->get();
    }

    public static function getNextSortRoot($menu)
    {
        return self::where('menu', $menu)->max('sort') + 1;
    }

    /**
     * @return BelongsTo
     */
    public function parent_menu()
    {
        return $this->belongsTo(Menus::class, 'menu');
    }

    /**
     * @return HasMany
     */
    public function child()
    {
        // loads every level of sub items, not only the first one
        return $this->hasMany(MenuItems::class, 'parent')->with('child')->orderBy('sort', 'ASC');
    }
}
