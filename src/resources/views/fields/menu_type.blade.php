<?php


//Default Variables

$default['options'] =
    [
        'internal_link' => trans('backpack::crud.internal_link'),
        'external_link' => trans('backpack::crud.external_link'),
    ];

$field['allows_null'] = true;
$entity_model = $crud->model;
//

$field['options'] = array_merge($default['options'],$field['options']);

?>
<div @include('crud::inc.field_wrapper_attributes') >
    <div class="row">
        <div class="col-md-4">
            <label>{!! $field['label'] !!}</label>
            @include('crud::inc.field_translatable_icon')
            <div class="clearfix"></div>


            <select
                    id="type_or_link_select"
                    name="{{ $field['name'] or 'type' }}"
                    @include('crud::inc.field_attributes')
            >

                @if (isset($field['allows_null']) && $field['allows_null']==true)
                    <option value="">-</option>
                @endif

                @if (count($field['options']))
                    @foreach ($field['options'] as $key => $value)
                        <option value="{{ $key }}"
                                @if (isset($field['value']) && $key==$field['value'])
                                selected
                                @endif
                        >{{ $value }}</option>
                    @endforeach
                @endif
            </select>
        </div>


        <div class="col-md-8">
            <label>{!! $field['choose_label'] or 'Option' !!}</label>
            <div class="type_or_link_value <?php if (!isset($entry) || $entry->type != 'external_link') {
                echo 'hidden';
            } ?>" id="type_or_link_external_link">
                <input
                        type="url"
                        class="form-control"
                        name="link"
                        placeholder="{{ trans('backpack::crud.page_link_placeholder') }}"

                        @if (!isset($entry) || $entry->type!='external_link')
                        disabled="disabled"
                        @endif

                        @if (isset($entry) && $entry->type=='external_link' && isset($entry->link) && $entry->link!='')
                        value="{{ $entry->link }}"
                        @endif
                >
            </div>
            <!-- internal link input -->
            <div class="type_or_link_value <?php if (!isset($entry) || $entry->type != 'internal_link') {
                echo 'hidden';
            } ?>" id="type_or_link_internal_link">
                <input
                        type="text"
                        class="form-control"
                        name="link"
                        placeholder="{{ trans('backpack::crud.internal_link_placeholder', ['url', url(config('backpack.base.route_prefix').'/page')]) }}"

                        @if (!isset($entry) || $entry->type!='internal_link')
                        disabled="disabled"
                        @endif

                        @if (isset($entry) && $entry->type=='internal_link' && isset($entry->link) && $entry->link!='')
                        value="{{ $entry->link }}"
                        @endif
                >
            </div>

            @foreach($field['types'] as $key => $value)


            <div class="type_or_link_value <?php if(!isset($entry) || $entry->type !== $key) {
                    echo 'hidden';
                } ?>"  id="type_or_link_{{$key}}">
                <select
                        id = "{{$value['name']}}"
                        @include('crud::inc.field_attributes', ['default_class' =>  'form-control select2'])
                >

                    {{--@if ($entity_model::isColumnNullable($field['name']))
                        <option value="">-</option>
                    @endif--}}

                    @if ($entity_model::isColumnNullable($field['name']))
                        <option id="empty" value="">-</option>
                    @endif

                    @if (isset($value['model']))
                        @foreach ($value['model']::all() as $connected_entity_entry)

                                <?php

                            ?>
                            <option value="{{ $connected_entity_entry->getKey() }}"
                                    id="option_{{$key}}_{{$connected_entity_entry->getKey()}}"


                                    @if (
                                    ( old($value['name']) && old($value['name']) == $connected_entity_entry->getKey() ) ||
                                    (isset($field['values']) && $connected_entity_entry->getKey()==$field['values'][$value['name']])
                                    )
                                    selected
                                    @endif
                            >{{ $connected_entity_entry->{$value['attribute']} }}</option>
                        @endforeach
                    @endif
                </select>
            </div>


                @if(isset($field['values'])

                && $connected_entity_entry->getKey() == $field['values'][$value['name']]
                )

                    <input type="hidden" id="setter" name="{{$value['name']}}" value="{{$field['values'][$value['name']]}}">


                @endif

            @endforeach


            <input type="hidden" id="old_setter" name="" value="">

        </div>

    </div>


    <div class="clearfix"></div>



    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

</div>


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    <link href="{{ asset('vendor/backpack/select2/select2.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/backpack/select2/select2-bootstrap-dick.css') }}" rel="stylesheet" type="text/css" />
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script src="{{ asset('vendor/backpack/select2/select2.js') }}"></script>

    <script>
        jQuery(document).ready(function($) {

            $("#type_or_link_select").change(function(e) {
                $(".type_or_link_value input").attr('disabled', 'disabled');
                $(".type_or_link_value select").attr('disabled', 'disabled');
                $(".type_or_link_value").removeClass("hidden").addClass("hidden");


                switch($(this).val()) {



                    case 'external_link':
                        $("#type_or_link_external_link input").removeAttr('disabled');
                        $("#type_or_link_external_link").removeClass('hidden');
                        break;

                    case 'internal_link':
                        $("#type_or_link_internal_link input").removeAttr('disabled');
                        $("#type_or_link_internal_link").removeClass('hidden');
                        break;

                    @foreach($field['types'] as $key => $value)

                     case '{{$key}}':

                         $("#type_or_link_<?php echo e($key); ?> select").removeAttr('disabled');
                         $("#type_or_link_<?php echo e($key); ?>").removeClass('hidden');
                         break;

                    @endforeach

                    default: // page_link
                        $("#page_or_link_page select").removeAttr('disabled');
                        $("#page_or_link_page").removeClass('hidden');
                }
            });

            var oldSet = false;

            $('.select2').each(function (i, obj) {
                if (!$(obj).data("select2"))
                {
                    $(obj).select2();
                }
            }).change(function () {

                var setter = $("#setter");
                var oldSetter = $("#old_setter");

                if (oldSet != true) {

                    oldSetter.val('');
                    oldSetter.attr('name', setter.attr('name'));
                    setter.val(($(this).val()));
                    setter.attr('name', ($(this).attr('id')));
                    oldSet = true;

                }else {

                    setter.val(($(this).val()));
                    setter.attr('name', ($(this).attr('id')));
                    oldSet = true;

                }


            });



        });


    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}


