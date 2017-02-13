<!-- text input -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @if (isset($field['email_validation']) && $field['email_validation'])
    <div class="input-group">
    @endif
        <input
            email-validation="{{isset($field['email_validation']) && $field['email_validation'] ? 1 : 0}}"
        	type="email"
        	name="{{ $field['name'] }}"
            value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
            @include('crud::inc.field_attributes')
        	>
    @if (isset($field['email_validation']) && $field['email_validation'])
        <div class="input-group-addon"><i class="fa fa-times"></i></div>
    </div>
    @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

@if (isset($field['email_validation']) && $field['email_validation'])

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <style>

        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script>
        jQuery(document).ready(function($){
            $('[email-validation]').each(function(){

                $field = $(this),
                $icon = $field.parent().find('i'),
                $classList = 'fa-times fa-spin fa-spinner fa-check';

                function simpleEmailValidate( string ){
                    var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return regex.test(string);
                }

                function updateView(){
                    var email = $field.val();

                    if( simpleEmailValidate(email) ){
                        $icon.removeClass($classList).addClass('fa-check');
                    } else {
                        $icon.removeClass($classList).addClass('fa-times');
                    }
                }

                $field.on('blur keyup paste update change', updateView);
                updateView();
            });
        });
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}

@endif;
