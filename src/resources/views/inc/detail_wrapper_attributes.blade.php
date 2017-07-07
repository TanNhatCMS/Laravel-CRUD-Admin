@if (isset($detail['wrapperAttributes']))
    @foreach ($detail['wrapperAttributes'] as $attribute => $value)
        @if (is_string($attribute))
        {{ $attribute }}="{{ $value }}"
        @endif
    @endforeach

    @if (!isset($detail['wrapperAttributes']['class']))
        class="col-md-12"
    @endif
@else
    class="col-md-12"
@endif
