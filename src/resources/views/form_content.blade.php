<form role="form">
  {{-- Show the erros, if any --}}
  @if ($errors->any())
  	<div class="col-md-12">
  		<div class="callout callout-danger">
	        <h4>{{ trans('backpack::crud.please_fix') }}</h4>
	        <ul>
			@foreach($errors->all() as $error)
				<li>{{ $error }}</li>
			@endforeach
			</ul>
		</div>
  	</div>
  @endif

  {{-- Show the inputs --}}
  @foreach ($fields as $field)
    <!-- load the view from the application if it exists, otherwise load the one in the package -->
	@if(view()->exists('vendor.backpack.crud.fields.'.$field['type']))
		@include('vendor.backpack.crud.fields.'.$field['type'], array('field' => $field))
	@else
		@include('crud::fields.'.$field['type'], array('field' => $field))
	@endif
  @endforeach
</form>

{{-- Define blade stacks so css and js can be pushed from the fields to these sections. --}}

@section('after_styles')
	<!-- CRUD FORM CONTENT - crud_fields_styles stack -->
	@stack('crud_fields_styles')
@endsection

@section('after_scripts')
	<!-- CRUD FORM CONTENT - crud_fields_scripts stack -->
	@stack('crud_fields_scripts')

	<script>
        jQuery('document').ready(function($){

      		// Ctrl+S and Cmd+S trigger Save button click
      		$(document).keydown(function(e) {
      		    if ((e.which == '115' || e.which == '83' ) && (e.ctrlKey || e.metaKey))
      		    {
      		        e.preventDefault();
      		        // alert("Ctrl-s pressed");
      		        $("button[type=submit]").trigger('click');
      		        return false;
      		    }
      		    return true;
      		});

          @if( $crud->autoFocusOnFirstField )
            //Focus on first field
            @php
              $focusField = array_first($fields, function($field){
                  return isset($field['auto_focus']) && $field['auto_focus'] == true;
              })
            @endphp

            @if($focusField)
              window.focusField = $('[name="{{$focusField['name']}}"]').eq(0),
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

          @php
               $unique_fields = array_filter($fields, function($f){
                   return isset($f['unique']) && $f['unique'];
               });
           @endphp

           @if ( count($unique_fields) )
           //Unique field checks
           $('[data-unique]').each(function(){

               var $field = $(this),
               $container = $field.parent(),
               $icon = $('<i class="fa fa-pencil"></i>'),
               $hint = $('<div class="col-xs-12" style="display: none;"><div class="alert alert-info"><p><!-- hint message --></p></div></div>'),
               $uniqueConfig = $field.data('unique'),
               $entityKey = $('[name="id"]').val(),
               $endPoint = $entityKey ? '../ajax/checkUniqueString' : 'ajax/checkUniqueString';

               //prepare container
               $container.css({position: 'relative'});
               $container.append($icon);
               $icon.css({'position': 'absolute', 'right': 25, 'bottom': 10, 'pointer-events': 'none'});
               $container.after($hint);

               //handle typing events
               var debounceTimer,
               lastCheck = $field.val(),
               xhr,
               classList = 'fa-check fa-times fa-spinner fa-spin';

               $field.on('keyup', function(){
                   $icon.removeClass(classList).addClass('fa-pencil');
                   if( xhr && xhr.abort ) xhr.abort();
                   clearTimeout(debounceTimer);
                   debounceTimer = setTimeout(checkUnicity, 400);
               });

               //lookup field
               function checkUnicity()
               {
                   //only look it up if its actually changed
                   if( lastCheck != $field.val() && $field.val().length ){
                       lastCheck = $field.val()
                       $icon.removeClass(classList).addClass('fa-spinner fa-spin');
                       xhr = $.post($endPoint, {
                           field_name: $uniqueConfig.field_name,
                           check_value: $field.val(),
                           display_name: $uniqueConfig.display_name
                       }, null, 'json')
                       .then(function( response ){
                           $icon.removeClass(classList).removeClass('fa-pencil');
                           if( response.success || response.meta.entity_key == $entityKey){
                               $icon.addClass('fa-check');
                               $hint.slideUp();
                           } else {
                               $icon.addClass('fa-times');

                               if( $uniqueConfig.hint ){

                                   var msg = response.message;

                                   if( response.meta && response.meta.link ){
                                       msg += ' - <a href="'+ response.meta.link +'" target="_blank">' + response.meta.snippet + '</a>';
                                   }

                                   $hint.find('p').html(msg);
                                   $hint.slideDown();
                               }
                           }

                       }, function( response ){
                           var msg = response.message || '{{trans('backpack::crud.unique_error')}}';
                           alert(msg);
                       })
                   }
               }
           });

           @endif

        });
	</script>
@endsection
