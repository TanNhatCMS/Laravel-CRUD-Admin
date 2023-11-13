{{-- text input --}}

@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')

@if(isset($field['prefix']) || isset($field['suffix']))
    <div class="input-group"> @endif
        @if(isset($field['prefix']))
            <div class="input-group-prepend"><span class="input-group-text">{!! $field['prefix'] !!}</span></div>
        @endif
        <input
            type="text"
            name="{{ $field['name'] }}"
            value="{{ old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '' }}"
            @include('crud::fields.inc.attributes')
        >
        @if(isset($field['suffix']))
            <div class="input-group-append"><span class="input-group-text">{!! $field['suffix'] !!}</span></div>
        @endif
        @if(isset($field['prefix']) || isset($field['suffix'])) </div>
@endif

{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')

{{-- FIELD CSS - will be loaded in the after_styles section --}}
@push('crud_fields_styles')
    {{-- include jQuery UI --}}
    @loadOnce('packages/jquery-ui-dist/jquery-ui.css')
@endpush

{{-- FIELD JS - will be loaded in the after_scripts section --}}
@push('crud_fields_scripts')
    {{-- include jQuery UI --}}
    @loadOnce('packages/jquery-ui-dist/jquery-ui.min.js')

    <script>
        $(function () {
            var availableOptions = {!! json_encode(($field['options'] ?? [])) !!};

            $("input[name='{{ $field['name'] }}']").autocomplete({
                source: availableOptions
            });
        });
    </script>
@endpush
