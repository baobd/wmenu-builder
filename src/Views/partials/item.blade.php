{{-- one item of the builder, with its sub items; also rendered empty as the template of new items --}}
@php($itemId = $item->id ?? '__ID__')
<li class="wmenu-item" data-id="{{ $itemId }}">
    <div class="wmenu-item-bar">
        <span class="wmenu-handle" title="{{ __('wmenu::builder.drag') }}">@include('wmenu::partials.icon', ['name' => 'grip'])</span>
        <span class="wmenu-item-text">
            <span class="wmenu-item-title">{{ $item->label }}</span>
            <span class="wmenu-item-link">{{ $item->link }}</span>
        </span>
        <button type="button" class="wmenu-icon-btn wmenu-toggle" data-action="toggle" aria-expanded="false" aria-controls="wmenu-settings-{{ $itemId }}" aria-label="{{ __('wmenu::builder.edit', ['label' => $item->label]) }}">@include('wmenu::partials.icon', ['name' => 'chevron'])</button>
    </div>

    <div class="wmenu-item-settings" id="wmenu-settings-{{ $itemId }}" hidden>
        <div class="wmenu-fields">
            <label class="wmenu-field">
                <span>{{ __('wmenu::builder.label') }}</span>
                <input type="text" data-field="label" value="{{ $item->label }}" required maxlength="255">
            </label>
            <label class="wmenu-field">
                <span>{{ __('wmenu::builder.link') }}</span>
                <input type="text" data-field="link" value="{{ $item->link }}" maxlength="255" placeholder="{{ __('wmenu::builder.link_placeholder') }}">
            </label>
            <label class="wmenu-field">
                <span>{{ __('wmenu::builder.class') }}</span>
                <input type="text" data-field="class" value="{{ $item->class }}" maxlength="255">
            </label>
            <label class="wmenu-field">
                <span>{{ __('wmenu::builder.target') }}</span>
                <select data-field="target">
                    <option value="">{{ __('wmenu::builder.target_self') }}</option>
                    <option value="_blank" @if($item->target === '_blank') selected @endif>{{ __('wmenu::builder.target_blank') }}</option>
                </select>
            </label>
            @if(count($roles))
            <label class="wmenu-field">
                <span>{{ __('wmenu::builder.role') }}</span>
                <select data-field="role_id">
                    <option value="0">{{ __('wmenu::builder.no_role') }}</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->$role_pk }}" @if($role->$role_pk == $item->role_id) selected @endif>{{ ucfirst($role->$role_title_field) }}</option>
                    @endforeach
                </select>
            </label>
            @endif
        </div>
        <div class="wmenu-item-actions">
            <span class="wmenu-moves">
                <button type="button" class="wmenu-icon-btn" data-action="up" title="{{ __('wmenu::builder.move_up') }}" aria-label="{{ __('wmenu::builder.move_up') }}">@include('wmenu::partials.icon', ['name' => 'up'])</button>
                <button type="button" class="wmenu-icon-btn" data-action="down" title="{{ __('wmenu::builder.move_down') }}" aria-label="{{ __('wmenu::builder.move_down') }}">@include('wmenu::partials.icon', ['name' => 'down'])</button>
                <button type="button" class="wmenu-icon-btn" data-action="in" title="{{ __('wmenu::builder.move_in') }}" aria-label="{{ __('wmenu::builder.move_in') }}">@include('wmenu::partials.icon', ['name' => 'in'])</button>
                <button type="button" class="wmenu-icon-btn" data-action="out" title="{{ __('wmenu::builder.move_out') }}" aria-label="{{ __('wmenu::builder.move_out') }}">@include('wmenu::partials.icon', ['name' => 'out'])</button>
            </span>
            <button type="button" class="wmenu-btn wmenu-btn-danger" data-action="delete">@include('wmenu::partials.icon', ['name' => 'trash']) {{ __('wmenu::builder.delete') }}</button>
            <button type="button" class="wmenu-btn" data-action="toggle">{{ __('wmenu::builder.done') }}</button>
        </div>
    </div>

    <ul class="wmenu-list">
        @if(isset($item->id))
        @foreach($item->child as $child)
        @include('wmenu::partials.item', ['item' => $child])
        @endforeach
        @endif
    </ul>
</li>
