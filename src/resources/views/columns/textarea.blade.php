{{-- regular object attribute --}}
@if(is_array($entry->{$column['name']}))
    @include('backpack::crud.columns.array')
@else
    <span>{!! $entry->{$column['name']} !!}</span>
@endif

