@if(config('backpack.base.enable_editable') && !empty($column['editable']))

    @if(is_array($column['editable']))
        @php
            // map types
            // performs options check
        @endphp
    @endif
    <!-- editable for column [{{ $column['name'] }}] -->
    <script>
        $(document).ready(function() {
            //toggle `popup` / `inline` mode
            $.fn.editable.defaults.mode = 'inline'; //set via config

            $('#{{ $column['name'] . '-' . $entry->getKey() }}').editable({
                {!! !empty($column['editable']['mode']) ? 'mode: ' . '"'.$column['editable']['mode'].'",' : '' !!}
                title:  'Enter {{ $column['name'] }}',
                name:   '{{ $column['name'] }}',
                type:   '{{ $column['editable']['type'] ?? 'text' }}',
                pk:     '{{ $entry->getKey() }}',

                params: {
                    '_editable': true,
                },
                ajaxOptions: {
                    type: 'POST',
                    dataType: 'json'
                },
                url: '{{ url( trim($crud->getRoute(), '/') . '/editable' ) }}'
            });
        });
    </script>
@endif