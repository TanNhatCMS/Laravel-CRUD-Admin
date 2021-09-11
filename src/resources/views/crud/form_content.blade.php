<input type="hidden" name="http_referrer" value={{ session('referrer_url_override') ?? old('http_referrer') ?? \URL::previous() ?? url($crud->route) }}>

{{-- See if we're using tabs --}}
@if ($crud->tabsEnabled() && count($crud->getTabs()))
    @include('crud::inc.show_tabbed_fields')
    <input type="hidden" name="current_tab" value="{{ Str::slug($crud->getTabs()[0]) }}" />
@else
    <div class="card">
        <div class="card-body row">
            @include('crud::inc.show_fields', ['fields' => $crud->fields()])
        </div>
    </div>
@endif


{{-- Define blade stacks so css and js can be pushed from the fields to these sections. --}}

@section('after_styles')
    <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/crud.css').'?v='.config('backpack.base.cachebusting_string') }}">
    <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/form.css').'?v='.config('backpack.base.cachebusting_string') }}">
    <link rel="stylesheet" href="{{ asset('packages/backpack/crud/css/'.$action.'.css').'?v='.config('backpack.base.cachebusting_string') }}">

    <!-- CRUD FORM CONTENT - crud_fields_styles stack -->
    @stack('crud_fields_styles')
@endsection

@section('after_scripts')
    <script src="{{ asset('packages/backpack/crud/js/crud.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
    <script src="{{ asset('packages/backpack/crud/js/form.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>
    <script src="{{ asset('packages/backpack/crud/js/'.$action.'.js').'?v='.config('backpack.base.cachebusting_string') }}"></script>

    <!-- CRUD FORM CONTENT - crud_fields_scripts stack -->
    @stack('crud_fields_scripts')

    <script>
        function initializeFieldsWithJavascript(container) {
            var selector;
            if (container instanceof jQuery) {
                selector = container;
            } else {
                selector = $(container);
            }
            selector.find("[data-init-function]").not("[data-initialized=true]").each(function () {
                var element = $(this);
                var functionName = element.data('init-function');

                if (typeof window[functionName] === "function") {
                    window[functionName](element);

                    // mark the element as initialized, so that its function is never called again
                    element.attr('data-initialized', 'true');
                }
            });
        }

        function initializeFieldsVisibility() {
            // get all fields has visibility
                @php
                    $visibiltiesFields = [];
                    foreach ($fields as $field => $options) {
                        if (array_key_exists('fields', $options)) {
                             foreach ($options['fields'] as $repField => $repOptions) {
                                 if (array_key_exists('visibility', $repOptions)) {
                                     $repOptions['repeatable'] = true;
                                     $repOptions['repeatable_field_name'] = $options['name'];
                                     $visibiltiesFields[] = $repOptions;
                                 }
                             }
                        }
                        if (array_key_exists('visibility', $options)) {
                            $options['repeatable'] = false;
                            $visibiltiesFields[] = $options;
                        }
                    }
                @endphp
            const JSVisibilitiesFields = {!! json_encode($visibiltiesFields) !!}
                    // console.log("JSVisibilitiesFields:: "+JSON.stringify(JSVisibilitiesFields, null, 2))
                    JSVisibilitiesFields.forEach(function (item) {
                        let isInsideRepeatable = item['repeatable'];
                        let fieldName = item['name'];
                        let shouldDisable = item['visibility']['add_disabled'];
                        let conditionValue = item['visibility']['value'];
                        let parentName = item['visibility']['field_name'];
                        let fieldGroup = $('#' + fieldName + '');
                        let fieldElement = $('[name='+fieldName+']');
                        let parent = null;
                        if (isInsideRepeatable == true) {
                            let repeatableFieldName = item['repeatable_field_name'];
                            let mainContainer = $('[data-repeatable-holder=' + repeatableFieldName + ']');
                            let container = $('[data-repeatable-identifier=' + repeatableFieldName + ']');

                            repeatableVisibilities(parentName, fieldName, conditionValue, shouldDisable, mainContainer, container);
                        } else {
                            parent = $('[name=' + parentName + ']');
                            nonRepeatableVisibilities(parent, fieldGroup, fieldElement, conditionValue, shouldDisable);
                        }
                    });

            function repeatableVisibilities(parentName, fieldName, conditionValue, shouldDisable, mainContainer, container) {

                mainContainer.children().each(function(i, el) {

                    let parent = $(el).find('[data-repeatable-input-name=' + parentName + ']');

                    let fieldElement =  $(el).find('[data-repeatable-input-name=' + fieldName + ']');
                    if (!fieldElement.length) {
                        fieldElement =  $(el).find('[data-repeatable-input-name=\"' + fieldName + '[]\"]');
                    }
                    let fieldGroup = fieldElement.parent();


                    let parentValue = parent.val();

                    if (parentValue == conditionValue) {
                        fieldGroup.show(650);
                        if (fieldElement.prop('disabled')) {
                            fieldElement.removeAttr("disabled");
                        }
                    } else {
                        fieldGroup.hide(650);
                        if (shouldDisable) {
                            fieldElement.attr("disabled", "disabled");
                        }
                    }
                    parent.change(function(){

                        if ($(this).val() == conditionValue) {
                            fieldGroup.show(650);
                            if (fieldElement.prop('disabled')) {
                                fieldElement.removeAttr("disabled");
                            }
                        } else {
                            fieldGroup.hide(650);
                            if (shouldDisable) {
                                fieldElement.attr("disabled", "disabled");
                            }
                        }
                    });
                })
            }

            function nonRepeatableVisibilities(parent, fieldGroup, fieldElement, conditionValue, shouldDisable) {
                let parentValue = parent.val();
                if (parentValue == conditionValue) {
                    fieldGroup.show(650);
                    if (fieldElement.prop('disabled')) {
                        fieldElement.removeAttr("disabled");
                    }
                } else {
                    fieldGroup.hide(650);
                    if (shouldDisable) {
                        fieldElement.attr("disabled", "disabled");
                    }
                }
                parent.change(function(){
                    console.log("$(this).val()::: "+$(this).val())
                    if ($(this).val() == conditionValue) {
                        fieldGroup.show(650);
                        if (fieldElement.prop('disabled')) {
                            fieldElement.removeAttr("disabled");
                        }
                    } else {
                        fieldGroup.hide(650);
                        if (shouldDisable) {
                            fieldElement.attr("disabled", "disabled");
                        }
                    }
                });
            }
        }

        jQuery('document').ready(function($){

            // trigger the javascript for all fields that have their js defined in a separate method
            initializeFieldsWithJavascript('form');

            // toggle fields visibilities
            initializeFieldsVisibility();

            // Save button has multiple actions: save and exit, save and edit, save and new
            var saveActions = $('#saveActions'),
                crudForm        = saveActions.parents('form'),
                saveActionField = $('[name="save_action"]');

            saveActions.on('click', '.dropdown-menu a', function(){
                var saveAction = $(this).data('value');
                saveActionField.val( saveAction );
                crudForm.submit();
            });

            // Ctrl+S and Cmd+S trigger Save button click
            $(document).keydown(function(e) {
                if ((e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey))
                {
                    e.preventDefault();
                    $("button[type=submit]").trigger('click');
                    return false;
                }
                return true;
            });

            // prevent duplicate entries on double-clicking the submit form
            crudForm.submit(function (event) {
                $("button[type=submit]").prop('disabled', true);
            });

            // Place the focus on the first element in the form
            @if( $crud->getAutoFocusOnFirstField() )
                @php
                    $focusField = Arr::first($fields, function($field) {
                        return isset($field['auto_focus']) && $field['auto_focus'] == true;
                    });
                @endphp

                @if ($focusField)
                @php
                    $focusFieldName = isset($focusField['value']) && is_iterable($focusField['value']) ? $focusField['name'] . '[]' : $focusField['name'];
                @endphp
                window.focusField = $('[name="{{ $focusFieldName }}"]').eq(0),
                @else
            var focusField = $('form').find('input, textarea, select').not('[type="hidden"]').eq(0),
                @endif

                fieldOffset = focusField.offset().top,
                scrollTolerance = $(window).height() / 2;

            focusField.trigger('focus');

            if( fieldOffset > scrollTolerance ){
                $('html, body').animate({scrollTop: (fieldOffset - 30)});
            }
            @endif

            // Add inline errors to the DOM
            @if ($crud->inlineErrorsEnabled() && $errors->any())

                window.errors = {!! json_encode($errors->messages()) !!};
            // console.error(window.errors);

            $.each(errors, function(property, messages){

                var normalizedProperty = property.split('.').map(function(item, index){
                    return index === 0 ? item : '['+item+']';
                }).join('');

                var field = $('[name="' + normalizedProperty + '[]"]').length ?
                    $('[name="' + normalizedProperty + '[]"]') :
                    $('[name="' + normalizedProperty + '"]'),
                    container = field.parents('.form-group');

                container.addClass('text-danger');
                container.children('input, textarea, select').addClass('is-invalid');

                $.each(messages, function(key, msg){
                    // highlight the input that errored
                    var row = $('<div class="invalid-feedback d-block">' + msg + '</div>');
                    row.appendTo(container);

                    // highlight its parent tab
                        @if ($crud->tabsEnabled())
                    var tab_id = $(container).closest('[role="tabpanel"]').attr('id');
                    $("#form_tabs [aria-controls="+tab_id+"]").addClass('text-danger');
                    @endif
                });
            });

            @endif

            $("a[data-toggle='tab']").click(function(){
                currentTabName = $(this).attr('tab_name');
                $("input[name='current_tab']").val(currentTabName);
            });

            if (window.location.hash) {
                $("input[name='current_tab']").val(window.location.hash.substr(1));
            }

        });
    </script>
@endsection
