<div @include('crud::inc.field_wrapper_attributes') >

    <h3>{!! $field['label'] !!}</h3>

    @if (isset($field['model']))

        <?php
            $pivot_entries = $entry->{$field['entity']}->keyBy(function($item) {
                return $item->getKey();
            });
        ?>

        @foreach ($field['model']::all() as $connected_entity_entry)
            <div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="{{ $field['name'] }}[{{ $connected_entity_entry->getKey() }}]" value="{{ $connected_entity_entry->getKey() }}"
                            @if ( (isset($field['value']) && in_array($connected_entity_entry->getKey(), $field['value']->pluck($connected_entity_entry->getKeyName(), $connected_entity_entry->getKeyName())->toArray())) || ( old( $field["name"] ) && in_array($connected_entity_entry->getKey(), old( $field["name"])) ) )
                                checked="checked"
                            @endif
                        />
                        {!! $connected_entity_entry->{$field['attribute']} !!}
                    </label>
                </div>

                @if(isset($field['pivotFields']))
                    <div class="container-fluid">
                        @foreach(array_chunk($field['pivotFields'], 2, true) as $pivot_chunk)
                            <div class="row">
                                @foreach ($pivot_chunk as $pivot_field => $pivot_name)
                                    <?php
                                        $pivot_attr = null;
                                        if ($pivot_entries->has($connected_entity_entry->getKey())) {
                                            $pivot = $pivot_entries->get($connected_entity_entry->getKey())->pivot;
                                            $pivot_attr = $pivot->getAttribute($pivot_field);
                                        }
                                    ?>

                                    <div class="col-sm-6">
                                        <label>{!! $pivot_name !!}</label>
                                        <input type="text" name="{!! $pivot_field !!}[{{ $connected_entity_entry->getKey() }}]" value="{{ $pivot_attr or null }}" @include('crud::inc.field_attributes') />
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>
