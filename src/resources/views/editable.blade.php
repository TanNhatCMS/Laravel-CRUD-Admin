@if(config('backpack.base.enable_editable') && !empty($column['editable']))
    @if(is_array($column['editable']))
        @php
            if (isset($column['type'])) {
                /*
                * Backpack column type => Editable type
                *
                * Only one type is supported for now.
                */
                $typesMap = [
                    'text' => 'text',
                ];

                if (in_array($column['type'], array_keys($typesMap))) {
                    $column['editable']['type'] = $typesMap[$column['type']];
                }
            }
        @endphp
    @endif

    @if(!isset($column['editable']['type']))
        @php
            $column['editable']['type'] = 'text'; // fallback type
        @endphp
    @endif
    @if(isset($column['editable']['mode']))
        @php
            $column['editable']['mode'] = in_array($column['editable']['mode'], ['popup', 'inline']) ?
                        $column['editable']['mode'] : null;
        @endphp
    @endif

    <!-- editable for column [{{ $column['name'] }}] -->
    <script>
        $(document).ready(function() {
            $('#{{ $column['name'] . '-' . $entry->getKey() }}').editable({
                {!! !empty($column['editable']['mode']) ? "mode: '{$column['editable']['mode']}'," : '' !!}
                title:  '{{ $column['editable']['title'] ?? "Enter {$column['editable']['title']}" }}',
                name:   '{{ $column['name'] }}',
                type:   '{{ $column['editable']['type'] }}',
                pk:     '{{ $entry->getKey() }}',
                params: {
                    '_editable': true,
                },
                ajaxOptions: {
                    type: 'POST',
                    dataType: 'json'
                },
                url: '{{ url(trim($crud->getRoute(), '/') . '/editable' ) }}'
            });
        });
    </script>
@endif