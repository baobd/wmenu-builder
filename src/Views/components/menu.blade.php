@if(count($items))
<ul {{ $attributes->merge(['class' => 'menu']) }}>
    @include('wmenu::components.menu-items', ['items' => $items])
</ul>
@endif
