<!-- checkbox field -->

@include('crud::fields.inc.wrapper_start')
    @include('crud::fields.inc.translatable_icon')
    <div class="checkbox">
        <input type="hidden" name="{{ $field['name'] }}" value="0">
        <label class="form-check-label font-weight-normal">
    	  <input type="checkbox"
               name="{{ $field['name'] }}"
               value="1"
          @if (old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? false)
                 checked
          @endif

          @if (isset($field['attributes']))
              @foreach ($field['attributes'] as $attribute => $value)
    			{{ $attribute }}="{{ $value }}"
        	  @endforeach
          @endif
          >
        {!! $field['label'] !!}</label>

        {{-- HINT --}}
        @if (isset($field['hint']))
            <p class="help-block">{!! $field['hint'] !!}</p>
        @endif
    </div>
@include('crud::fields.inc.wrapper_end')