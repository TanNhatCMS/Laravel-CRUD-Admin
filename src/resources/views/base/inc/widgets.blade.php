@if (!empty($widgets))
    @foreach ($widgets as $widget)
		@if (isset($widget['viewNamespace']))
			@include($widgetsViewNamespace.'.'.$widget['type'], ['widget' => $widget])
		@else
			@include(backpack_view('widgets.'.$widget['type']), ['widget' => $widget])
		@endif
        @if(!CRUD::widgetTypeLoaded($widget))
            @php(CRUD::markWidgetTypeAsLoaded($widget))
        @endif
	@endforeach
@endif
