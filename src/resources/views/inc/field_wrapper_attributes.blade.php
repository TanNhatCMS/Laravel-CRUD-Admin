@if (isset($field['wrapperAttributes']))
    @foreach ($field['wrapperAttributes'] as $attribute => $value)
    	@if (is_string($attribute))
        {{ $attribute }}="{{ $value }}"
        @endif
    @endforeach

    @if (!isset($field['wrapperAttributes']['class']))
      @if (isset($default_class))
        class="{{ $default_class }}"
      @else
        class="form-group col-md-12"
      @endif
    @endif
@else
  @if (isset($default_class))
    class="{{ $default_class }}"
  @else
    class="form-group col-md-12"
  @endif
@endif
