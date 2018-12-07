<!-- bootstrap timepicker input -->
<div @include('crud::inc.field_wrapper_attributes') >
    <input type="hidden" name="{{ $field['name'] }}" value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}">
    <div class="input-group bootstrap-timepicker timepicker">
        <input
            data-bs-timepicker="{{ isset($field['time_picker_options']) ? json_encode($field['time_picker_options']) : '{}'}}"
            type="text"
            @include('crud::inc.field_attributes')
            >
    </div>
</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap-timepicker/css/bootstrap-timepicker.css') }}">
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script src="{{ asset('plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}"></script>
    <script>
        if (jQuery.ui) {
            var timepicker = $.fn.timepicker.noConflict();
            $.fn.bootstrapTP = timepicker;
        } else {
            $.fn.bootstrapTP = $.fn.timepicker;
        }

        jQuery(document).ready(function($){
            $('[data-bs-timepicker]').each(function(){

                var $fake = $(this),
                $field = $fake.parents('.form-group').find('input[type="hidden"]'),
                $customConfig = $.extend({}, $fake.data('bs-timepicker'));
                $picker = $fake.timepicker($customConfig);

                var $existingVal = $field.val();
                if( $existingVal == ""){
                    //is empty so do nothing
                }else {
                    //Sanitize the existing time from the DB before setting timepicker, first check if were using 24 hour time
                    if( $customConfig.showMeridian === false ){
                            //if yes then were all good
                            $fake.timepicker('setTime', $existingVal);
                        } else {
                            //if not then lets convert it before storing it
                            var $existingValM = $existingVal.toString().substring(0, 2);
                            if( $existingValM < 12 ){
                                //its AM
                                $fake.timepicker('setTime', $existingVal);
                            } else {
                                //its PM
                                $existingValM = $existingValM - 12;
                                $existingValM = $existingValM.toString() + $existingVal.substring(2, 8) + " PM";
                                $fake.timepicker('setTime', $existingValM);
                            }       
                        } 
                }
                    
                $fake.focus(function (){
                    $fake.timepicker('showWidget');
                });
                
                $fake.on('changeTime.timepicker', function(e) {
                    try {
                        //Santize the updated time before setting the field
                        var tpHour = e.time.hours.toString().length === 1 ? '0' + e.time.hours : e.time.hours;
                        var tpMin = e.time.minutes.toString().length === 1 ? '0' + e.time.minutes : e.time.minutes;
                        var tpSec = e.time.seconds.toString().length === 1 ? '0' + e.time.seconds : e.time.seconds;

                        //check if were using 24 hour time
                        if( $customConfig.showMeridian === false ){
                            //if yes then were all good
                            var sqlTime = tpHour + ':' + tpMin + ':' + (isNaN(tpSec) ? '00' : tpSec);
                        } else { 
                            //if not then lets convert it before storing it
                            if (e.time.hours == "12" && e.time.meridian == "AM" ){
                                //fix if its 12 AM
                                var sqlTime = '00:' + tpMin + ':' + (isNaN(tpSec) ? '00' : tpSec);
                            } else if (e.time.hours == "12" && e.time.meridian == "PM") {
                                //fix if its 12 PM
                                var sqlTime = tpHour + ':' + tpMin + ':' + (isNaN(tpSec) ? '00' : tpSec);
                            } else {
                                //otherwise check for AM/PM and convert
                                var tpHourF = e.time.meridian == "PM" ? e.time.hours + 12: e.time.hours;
                                var sqlTime = tpHourF + ':' + tpMin + ':' + (isNaN(tpSec) ? '00' : tpSec);
                            }
                        }      
                    } catch(e){
                        if( $fake.val() ){
                            PNotify.removeAll();
                            new PNotify({
                                title: 'Whoops!',
                                text: 'Sorry we did not recognise that time format, please make sure it uses a h:m:s combination',
                                type: 'error',
                                icon: false
                            });
                        }
                    }
                    $field.val(sqlTime);
                });
            });
        });
    </script>
    @endpush
@endif
{{-- End of Extra CSS and JS --}}