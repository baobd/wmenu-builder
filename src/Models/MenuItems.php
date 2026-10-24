<?php

namespace Harimayco\Menu\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItems extends Model
{
    protected $fillable = ['label', 'link', 'parent', 'sort', 'class', 'menu', 'depth', 'role_id'];

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

    public function parent_menu()
    {
        return $this->belongsTo('Harimayco\Menu\Models\Menus', 'menu');
    }

    public function child()
    {
        return $this->hasMany('Harimayco\Menu\Models\MenuItems', 'parent')->orderBy('sort', 'ASC');
    }
}
