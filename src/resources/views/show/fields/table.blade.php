<!-- array input -->

<?php
    $max = isset($field['max']) && (int) $field['max'] > 0 ? $field['max'] : -1;
    $min = isset($field['min']) && (int) $field['min'] > 0 ? $field['min'] : -1;
    $item_name = strtolower(isset($field['entity_singular']) && !empty($field['entity_singular']) ? $field['entity_singular'] : $field['label']);
    $items = old($field['name']) ? (old($field['name'])) : (isset($field['value']) ? ($field['value']) : (isset($field['default']) ? ($field['default']) : '' ));
?>
<div @include('crud::inc.field_wrapper_attributes')>
    <label>{!! $field['label'] !!}</label>
    <div class="array-container form-group">
        <table class="table table-bordered table-striped m-b-0">
            <thead>
                <tr>
                    @foreach( $field['columns'] as $prop )
                        <th style="font-weight: 600!important;">
                            {{ $prop }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="table-striped">
                @foreach($items as $item)
                    <tr class="array-row">
                        @foreach( $field['columns'] as $prop => $label)
                            <td>
                                <p>{{ isset($item[$prop]) ? $item[$prop] : '' }}</p>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>