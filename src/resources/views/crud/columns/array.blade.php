{{-- enumerate the values in an array  --}}
@php
    $value = data_get($entry, $column['name']);
    $column['escaped'] = $column['escaped'] ?? false;

    // the value should be an array wether or not attribute casting is used
    if (!is_array($value)) {
        $value = json_decode($value, true);
    }
@endphp

<span>
    @if($value && count($value))
        @foreach($value as $key => $text)
            @php
                $column['text'] = $text;
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
