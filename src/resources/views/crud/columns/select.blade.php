{{-- single relationships (1-1, 1-n) --}}
@php
    $column['escaped'] = $column['escaped'] ?? true;
    $column['limit'] = $column['limit'] ?? 40;
    $column['attribute'] = $column['attribute'] ?? (new $column['model'])->identifiableAttribute();

    $attributes = $crud->getRelatedEntriesAttributes($entry, $column['entity'], $column['attribute']);
    foreach ($attributes as $key => $text) {
        $text = Str::limit($text, $column['limit'], '[...]');
    }
@endphp

<span>
    @if(count($attributes))

        @foreach($attributes as $key => $text)
            @php
                $related_key = $key;
            @endphp

            @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
                @if($column['escaped'])
                    {{ $text }}<?php
                ?>@else
                    {!! $text !!}<?php
                ?>@endif<?php
            ?>@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')<?php

            ?>@if(!$loop->last), @endif
        @endforeach
    @else
        -
    @endif
</span>
