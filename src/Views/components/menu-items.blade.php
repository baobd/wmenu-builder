@foreach($items as $item)
@php($classes = trim(($item['class'] ?? '') . ' ' . ($isActive($item) ? $activeClass : '')))
<li{!! $classes !== '' ? ' class="' . e($classes) . '"' : '' !!}>
    <a href="{{ ($item['link'] ?? '') !== '' ? $item['link'] : '#' }}"@if(($item['target'] ?? null) === '_blank') target="_blank" rel="noopener noreferrer"@endif>{{ $item['label'] }}</a>
    @if(!empty($item['child']))
    <ul class="{{ $submenuClass }}">
        @include('wmenu::components.menu-items', ['items' => $item['child']])
    </ul>
    @endif
</li>
@endforeach
