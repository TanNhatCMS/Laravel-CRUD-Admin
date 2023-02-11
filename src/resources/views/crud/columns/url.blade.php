{{-- regular object attribute --}}
@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['limit'] = $column['limit'] ?? 32;
    $column['text'] = $column['default'] ?? '-';

    if(is_array($column['value'])) {
        $column['value'] = json_encode($column['value']);
    }

    if(!empty($column['value'])) {
        $column['text'] = '<a href="'.$column['value'].'" target="_blank">'.Str::limit($column['value'], $column['limit'], '…').'</a>';
    }
@endphp

<span>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
    {!! $column['text'] !!}
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
