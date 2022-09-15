{{-- custom return value --}}
@php
    $column['value'] = $entry->{$column['function_name']}(...($column['function_parameters'] ?? []));
    $column['escaped'] = $column['escaped'] ?? true;
@endphp

<span>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
        @if($column['escaped'])
            {{ $column['text'] }}
        @else
            {!! $column['text'] !!}
        @endif
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
