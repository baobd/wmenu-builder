<script>
    window.wmenuConfig = {!! json_encode([
        'csrf' => csrf_token(),
        'currentUrl' => url()->current(),
        // __MENU__ and __ITEM__ are replaced in wmenu.js
        'routes' => [
            'menusStore' => route('wmenu.menus.store'),
            'menusUpdate' => route('wmenu.menus.update', ['menu' => '__MENU__']),
            'menusDestroy' => route('wmenu.menus.destroy', ['menu' => '__MENU__']),
            'itemsStore' => route('wmenu.items.store', ['menu' => '__MENU__']),
            'itemsUpdate' => route('wmenu.items.update', ['menu' => '__MENU__', 'item' => '__ITEM__']),
            'itemsDestroy' => route('wmenu.items.destroy', ['menu' => '__MENU__', 'item' => '__ITEM__']),
        ],
        'strings' => trans('wmenu::builder'),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!};
</script>
<script src="{{ asset('vendor/harimayco-menu/sortable.min.js') }}"></script>
<script src="{{ asset('vendor/harimayco-menu/wmenu.js') }}"></script>
