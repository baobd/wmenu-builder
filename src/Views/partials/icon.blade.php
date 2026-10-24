{{-- 16px stroke icons, they take the text color --}}
<svg class="wmenu-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
@switch($name)
@case('grip')<circle cx="6" cy="4" r=".6"/><circle cx="10" cy="4" r=".6"/><circle cx="6" cy="8" r=".6"/><circle cx="10" cy="8" r=".6"/><circle cx="6" cy="12" r=".6"/><circle cx="10" cy="12" r=".6"/>@break
@case('chevron')<path d="M4 6l4 4 4-4"/>@break
@case('up')<path d="M8 13V3M4 7l4-4 4 4"/>@break
@case('down')<path d="M8 3v10M4 9l4 4 4-4"/>@break
@case('in')<path d="M3 4h10M7 8h6M7 12h6M3 7l2 2-2 2"/>@break
@case('out')<path d="M3 4h10M7 8h6M7 12h6M5 7L3 9l2 2"/>@break
@case('trash')<path d="M3 4h10M6.5 4V2.5h3V4M4.5 4l.7 9.5h5.6l.7-9.5"/>@break
@endswitch
</svg>
