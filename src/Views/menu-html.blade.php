<link href="{{ asset('vendor/harimayco-menu/wmenu.css') }}" rel="stylesheet">

<div class="wmenu" id="wmenu" data-menu-id="{{ $menu ? $menu->id : '' }}">
    <div class="wmenu-bar">
        @if(count($menulist))
        <form method="get" action="{{ $currentUrl }}" class="wmenu-select">
            <label for="wmenu-select">{{ __('wmenu::builder.select_menu') }}</label>
            <select id="wmenu-select" name="menu">
                @foreach($menulist as $id => $name)
                <option value="{{ $id }}" @if($menu && $menu->id == $id) selected @endif>{{ $name }}</option>
                @endforeach
            </select>
            <button type="submit" class="wmenu-btn">{{ __('wmenu::builder.choose') }}</button>
        </form>
        @else
        <p class="wmenu-muted">{{ __('wmenu::builder.no_menus') }}</p>
        @endif
        @if($menu)
        <a class="wmenu-link" href="{{ $currentUrl }}">{{ __('wmenu::builder.create_new') }}</a>
        @endif
    </div>

    @if(!$menu)
    <form class="wmenu-panel wmenu-create" data-form="create">
        <h3>{{ __('wmenu::builder.create_title') }}</h3>
        <p class="wmenu-muted">{{ __('wmenu::builder.create_help') }}</p>
        <label class="wmenu-field">
            <span>{{ __('wmenu::builder.menu_name') }}</span>
            <input type="text" name="name" required maxlength="255">
        </label>
        <button type="submit" class="wmenu-btn wmenu-btn-primary">{{ __('wmenu::builder.create') }}</button>
    </form>
    @else
    <div class="wmenu-columns">
        <form class="wmenu-panel wmenu-add" data-form="add">
            <h3>{{ __('wmenu::builder.add_title') }}</h3>
            <label class="wmenu-field">
                <span>{{ __('wmenu::builder.label') }}</span>
                <input type="text" name="label" required maxlength="255">
            </label>
            <label class="wmenu-field">
                <span>{{ __('wmenu::builder.link') }}</span>
                <input type="text" name="link" maxlength="255" placeholder="{{ __('wmenu::builder.link_placeholder') }}">
            </label>
            <label class="wmenu-field">
                <span>{{ __('wmenu::builder.target') }}</span>
                <select name="target">
                    <option value="">{{ __('wmenu::builder.target_self') }}</option>
                    <option value="_blank">{{ __('wmenu::builder.target_blank') }}</option>
                </select>
            </label>
            @if(count($roles))
            <label class="wmenu-field">
                <span>{{ __('wmenu::builder.role') }}</span>
                <select name="role_id">
                    <option value="0">{{ __('wmenu::builder.no_role') }}</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->$role_pk }}">{{ ucfirst($role->$role_title_field) }}</option>
                    @endforeach
                </select>
            </label>
            @endif
            <button type="submit" class="wmenu-btn">{{ __('wmenu::builder.add') }}</button>
        </form>

        <form class="wmenu-panel wmenu-structure" data-form="save">
            <div class="wmenu-structure-head">
                <label class="wmenu-field wmenu-name">
                    <span>{{ __('wmenu::builder.menu_name') }}</span>
                    <input type="text" name="name" value="{{ $menu->name }}" required maxlength="255">
                </label>
                <span class="wmenu-status" data-status aria-live="polite"></span>
                <button type="submit" class="wmenu-btn wmenu-btn-primary">{{ __('wmenu::builder.save') }}</button>
            </div>

            <h3>{{ __('wmenu::builder.structure') }}</h3>
            <p class="wmenu-muted">{{ __('wmenu::builder.structure_help') }}</p>

            <ul class="wmenu-list wmenu-root">
                @foreach($menu->items as $item)
                @include('wmenu::partials.item', ['item' => $item])
                @endforeach
            </ul>
            <p class="wmenu-muted wmenu-empty" data-empty @if(count($menu->items)) hidden @endif>{{ __('wmenu::builder.empty') }}</p>

            <div class="wmenu-structure-foot">
                <button type="button" class="wmenu-btn wmenu-btn-danger" data-action="delete-menu">{{ __('wmenu::builder.delete_menu') }}</button>
            </div>
        </form>
    </div>

    <template id="wmenu-item-template">
        @include('wmenu::partials.item', ['item' => new \Harimayco\Menu\Models\MenuItems()])
    </template>
    @endif

    <div class="wmenu-toasts" data-toasts aria-live="polite"></div>
</div>
