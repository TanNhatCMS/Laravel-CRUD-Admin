<!-- checkbox field -->

<div @include('crud::inc.field_wrapper_attributes') >
    <label class="col-md-3 control-label"></label>
    @include('crud::inc.field_translatable_icon')
    <div class="col-md-9">
        <div class="mt-checkbox-list" style="padding: 0px;">
            <label class="mt-checkbox" style="margin-bottom: 0px;">
            <input type="hidden" name="{{ $field['name'] }}" value="0">
            <input type="checkbox" value="1"

            name="{{ $field['name'] }}"

            @if(old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : false )))
                    checked="checked"
            @endif

            @if (isset($field['attributes']))
                @foreach ($field['attributes'] as $attribute => $value)
                    {{ $attribute }}="{{ $value }}"
                @endforeach
            @endif
            > {!! $field['label'] !!}
            <span></span>
            </label>

            {{-- HINT --}}
            @if (isset($field['hint']))
                <p class="form-text">{!! $field['hint'] !!}</p>
            @endif
        </div>
    </div>
</div>
