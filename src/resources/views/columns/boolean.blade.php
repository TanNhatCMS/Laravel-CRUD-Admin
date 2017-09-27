{{-- converts 1/true or 0/false to yes/no/lang --}}
<td data-order="{{ $entry->{$column['name']} }}">
	@if ($entry->{$column['name']} === true || $entry->{$column['name']} === 1 || $entry->{$column['name']} === '1')
        @if ( isset( $column['options'][1] ) )
            @if ( isset( $column['options']['unesc'] )  && $column['options']['unesc'])
                {!! $column['options'][1] !!}
            @else
                {{ $column['options'][1] }}
            @endif
        @else
            {{ Lang::has('backpack::crud.yes')?trans('backpack::crud.yes'):'Yes' }}
        @endif
    @else
        @if ( isset( $column['options'][0] ) )
            @if ( isset( $column['options']['unesc'] )  && $column['options']['unesc'])
                {!! $column['options'][0] !!}
            @else
                {{ $column['options'][0] }}
            @endif
        @else
            {{ Lang::has('backpack::crud.no')?trans('backpack::crud.no'):'No' }}
        @endif
    @endif
</td>
