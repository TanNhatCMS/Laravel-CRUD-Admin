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
        $counter_fields = array_filter($fields, function($f){
            return (isset($f['count_down']) && $f['count_down']) || (isset($f['count_up']) && $f['count_up']);
        });
        @endphp

        @if (count($counter_fields))

            var counterFields = {!! json_encode($counter_fields) !!};

            $.each(counterFields, function(name, field){
                field.$field = $('[name="'+name+'"]'),
                field.$container = field.$field.parents('.form-group'),
                field.$countDown = field.count_down ? true : false,
                field.$counterMax = field.$countDown ? field.count_down : field.count_up;

                //Setup our Virtual DOM
                field.$container.css({
                    position: 'relative', zIndex: 10,
                    marginBottom: 25
                });

                field.$label = $('<div><span class="used"></span><span class="max"></span></div>');
                field.$label.css({
                    pointerEvents: 'none',
                    position: 'absolute', zIndex: 11, left: 15, top: '100%',
                    width: 60, height: 20, marginTop: -1,
                    background: '#fff', border: '1px solid #d2d6de', borderTop: '1px solid #fff',
                    textAlign: 'center', fontFamily: 'monospace', fontSize: 10,
                    transition: 'border-color ease-in-out .15s'
                });
                field.$label.appendTo(field.$container);

                field.$usedLabel = field.$label.find('.used');
                field.$maxLabel = field.$label.find('.max');

                //Setup intial counter based off HTML
                if( field.$countDown ){
                    field.$maxLabel.remove();
                    field.$usedLabel.text( field.$counterMax - field.$field.val().length );
                } else {
                    field.$maxLabel.text( ' / ' + field.$counterMax );
                    field.$usedLabel.text( field.$field.val().length );
                }

                //Listen to modifications on the field
                field.$field.on('keydown keyup change update paste blur focus', function(e){
                    var used = field.$field.val().length;

                    if( field.$countDown ){
                        field.$usedLabel.text( field.$counterMax - used );
                    } else {
                        field.$usedLabel.text( used );
                    }

                    if( used > field.$counterMax ){
                        field.$label.css('color', 'red');
                    } else {
                        field.$label.css('color', 'black');
                    }
                });

                //Emulate the styling from the text box
                field.$field.on('focus blur', function(e){
                    if( e.type == 'focus' ){
                        field.$label.css({
                            borderColor: '#3c8dbc', borderTopColor: '#fff'
                        });
                    } else {
                        field.$label.css({
                            borderColor: '#d2d6de', borderTopColor: '#fff'
                        });
                    }

                    setTimeout(function(){
                        var borderColor = field.$field.css('borderColor');
                        field.$label.css({
                            borderColor: borderColor, borderTopColor: '#fff'
                        });
                    }, 200);
                })
            });

        });
        @endif
	</script>
@endsection
