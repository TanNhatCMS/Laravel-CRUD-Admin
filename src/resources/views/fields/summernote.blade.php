<!-- summernote editor -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <textarea
            name="{{ $field['name'] }}"
            @include('crud::inc.field_attributes', ['default_class' =>  'form-control summernote'])
    >{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}</textarea>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <!-- include summernote css-->
        <link href="{{ asset('vendor/backpack/summernote/summernote.css') }}" rel="stylesheet" type="text/css"/>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <!-- include summernote js-->
        <script src="{{ asset('vendor/backpack/summernote/summernote.min.js') }}"></script>
    @endpush

@endif

@push('crud_fields_scripts')
    <!-- include summernote js with related options for this field -->
    <script>
      jQuery(document).ready(function ($) {
        // get the alert options if set from the crud controller
        let $alert = @json($field['s3_alert'] ?? null);
        // get the options set on the crud controller
        let $options = @json($field['options'] ?? (object)[]);
        // if s3 is true (default false), we loop through all the files uploaded
        if ('{{ $field['s3'] ?? false }}') {
          $options.onImageUpload = function (files) {
            for (let i = 0; i < files.length; i++) {
              $.upload(files[i]);
            }
          }
        }
        // display a notification
        // https://laravel-backpack.readme.io/v3.0/docs/base#section-triggering-notification-bubbles-in-javascript
        $.showNotification = function (alertType, filename, type) {
          new PNotify({
            title: $alert[type].title,
            text: $alert.show_file_name ? filename +' '+ $alert[type].text : $alert[type].text,
            type: alertType
          });
        }

        // ajax request to the controller set in the crud controller
        // the controller should handle the file and return the url
        $.upload = function (file) {
          let formData = new FormData();
          formData.append('file', file);

          $.ajax({
            method: 'POST',
            url: '{{ $field['s3_upload'] ?? '#' }}',
            //check laravel document: https://laravel.com/docs/5.6/csrf#csrf-x-csrf-token
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            processData: false,
            contentType: false,
            dataType: 'json',
            data: formData,
            success: function (data) {
              if ($alert) {
                $.showNotification($alert.success.type, file.name, 'success')
              }
              $(".summernote[name='{{ $field['name'] }}']").summernote('insertImage', data.url);
            },
            error: function () {
              // here we just display a simple notification to let the user know what happened
              if ($alert) {
                $.showNotification($alert.error.type, file.name, 'error')
              }
            }
          });
        };

        $(".summernote[name='{{ $field['name'] }}']").summernote($options);
      });
    </script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
