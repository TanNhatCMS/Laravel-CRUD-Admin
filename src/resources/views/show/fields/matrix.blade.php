<?php
    // Check to see if an entry for this matrix exists
    $value = old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : null ));
    $labels = [];
    foreach ($field['blocks'] as $block) {
        $labels[$block['name']] = $block['label'];
    }
?>

<!-- matrix input -->
<div @include('crud::inc.field_wrapper_attributes') >
<label>{!! $field['label'] !!}</label>

{{-- Setup an ajax wrapper --}}
<div id="{{ $field['name'] }}" class="sortable">
    @if($value)
        @foreach($value as $block)
            <?php $index = $loop->index; ?>
            @foreach($block as $blockName => $blockField)
                <div class="block">
                    <label>{!! $labels[$blockName] !!}</label>
                    @include('vendor.backpack.crud.inc.show_matrix_fields', ['block' => $field['blocks'][$blockName], 'name' => $field['name'], 'values' => $blockField, 'index' => $index])
                </div>
            @endforeach
        @endforeach
    @endif
</div>

{{-- Setup add block dropdown --}}
<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <span class="btn-text">Add Content Block</span><span class="fa fa-plus"></span>
    </button>
    <ul class="dropdown-menu">
        @foreach($field['blocks'] as $block)
        <li><a href="#" class="block-select" data-block="{{ $block['name'] }}">{{ $block['label'] }}</a></li>
        @endforeach
    </ul>
</div>

{{-- HINT --}}
@if (isset($field['hint']))
<p class="help-block">{!! $field['hint'] !!}</p>
@endif
</div>

{{-- BLOCK TEMPLATES --}}
<div class="{{ $field['name'] }}_templates">
    @foreach($field['blocks'] as $block)
        <div class="block" data-block-template="{{ $block['name'] }}">
            <label>{!! $block['label'] !!}</label>
            @include('vendor.backpack.crud.inc.show_matrix_fields', ['block' => $block, 'name' => $field['name']])
        </div>
    @endforeach
</div>

@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))
{{-- FIELD CSS - will be loaded in the after_styles section --}}
@push('crud_fields_styles')
{{-- YOUR CSS HERE --}}
<style>
    .{{ $field['name'] }}_templates {
        display: none;
    }
    .btn-text {
        padding-right: 10px;
    }
</style>
@endpush
@endif

@section('after_scripts')
    @if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))
        <script src="{{ asset('vendor/backpack/crud/js/reorder.js') }}"></script>
        <script src="https://code.jquery.com/ui/1.11.3/jquery-ui.min.js" type="text/javascript"></script>
        <script src="{{ url('vendor/backpack/nestedSortable/jquery.mjs.nestedSortable2.js') }}" type="text/javascript"></script>
    @endif
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var $index = parseInt({{ $value ? count($value) : 1 }});

            $('.block-select').each(function() {
                var $this = $(this);
                (function() {
                    $this.click(function(e) {
                        e.preventDefault();
                        var $blockName = e.target.getAttribute('data-block');
                        var $block = $('.{{ $field['name'] }}_templates').find("*[data-block-template=" + $blockName + "]")[0];
                        var $newBlock = $($block).clone();
                        $newBlock.find('input,textarea,select').each(function() {
                            $(this).attr('name', "{{ $field['name'] }}[" + $index + "][" + $blockName + "][" + $(this).attr('name') + "]");
                        });
                        $('#{{ $field['name'] }}').append($newBlock);

                        $index = $index + 1;
                    });
                })($this);
            });

            $.ajaxPrefilter(function(options, originalOptions, xhr) {
                var token = $('meta[name="csrf_token"]').attr('content');

                if (token) {
                    return xhr.setRequestHeader('X-XSRF-TOKEN', token);
                }
            });

        });
    </script>
@endsection