<script>
	var menus = {
		"oneThemeLocationNoMenus" : "",
		"moveUp" : "Move up",
		"moveDown" : "Mover down",
		"moveToTop" : "Move top",
		"moveUnder" : "Move under of %s",
		"moveOutFrom" : "Out from under  %s",
		"under" : "Under %s",
		"outFrom" : "Out from %s",
		"menuFocus" : "%1$s. Element menu %2$d of %3$d.",
		"subMenuFocus" : "%1$s. Menu of subelement %2$d of %3$s."
	};
	var arraydata = [];     
	// __MENU__ and __ITEM__ are replaced in menu.js
	var wmenuroutes = {
		menusStore: '{{ route("wmenu.menus.store") }}',
		menusUpdate: '{{ route("wmenu.menus.update", ["menu" => "__MENU__"]) }}',
		menusDestroy: '{{ route("wmenu.menus.destroy", ["menu" => "__MENU__"]) }}',
		itemsStore: '{{ route("wmenu.items.store", ["menu" => "__MENU__"]) }}',
		itemsUpdate: '{{ route("wmenu.items.update", ["menu" => "__MENU__", "item" => "__ITEM__"]) }}',
		itemsDestroy: '{{ route("wmenu.items.destroy", ["menu" => "__MENU__", "item" => "__ITEM__"]) }}'
	};
	var csrftoken="{{ csrf_token() }}";
	var menuwr = "{{ url()->current() }}";

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': csrftoken
		}
	});
</script>
<script type="text/javascript" src="{{asset('vendor/harimayco-menu/scripts.js')}}"></script>
<script type="text/javascript" src="{{asset('vendor/harimayco-menu/scripts2.js')}}"></script>
<script type="text/javascript" src="{{asset('vendor/harimayco-menu/menu.js')}}"></script>