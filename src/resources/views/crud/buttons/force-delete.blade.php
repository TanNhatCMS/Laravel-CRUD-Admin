@if ($crud->hasAccess('forceDelete') && $entry->deleted_at !== null)
	<a href="javascript:void(0)"
       onclick="forceDeleteEntry(this)"
       data-route="{{ url($crud->route.'/'.$entry->getKey().'/forceDelete') }}"
       class="btn btn-sm btn-link"
       data-button-type="forceDelete">
        <i style="color: red" class="la la-times"></i>
        @if(config('siberfx.base.action_title')){{ trans('siberfx::crud.forceDelete') }}@endif
	</a>
@endif

{{-- Button Javascript --}}
{{-- - used right away in AJAX operations (ex: List) --}}
{{-- - pushed to the end of the page, after jQuery is loaded, for non-AJAX operations (ex: Show) --}}
@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>

	if (typeof forceDeleteEntry != 'function') {
	  $("[data-button-type=forceDelete]").unbind('click');

	  function forceDeleteEntry(button) {
		// ask for confirmation before deleting an item
		// e.preventDefault();
		var route = $(button).attr('data-route');

		swal({
		  title: "{!! trans('siberfx::base.warning') !!}",
		  text: "{!! trans('siberfx::crud.force_delete_confirm') !!}",
		  icon: "warning",
		  buttons: ["{!! trans('siberfx::crud.cancel') !!}", "{!! trans('siberfx::crud.delete') !!}"],
		  dangerMode: true,
		}).then((value) => {
			if (value) {
				$.ajax({
			      url: route,
			      type: 'DELETE',
			      success: function(result) {
			          if (result == 1) {
						  // Redraw the table
						  if (typeof crud != 'undefined' && typeof crud.table != 'undefined') {
							  // Move to previous page in case of deleting the only item in table
							  if(crud.table.rows().count() === 1) {
							    crud.table.page("previous");
							  }

							  crud.table.draw(false);
						  }

			          	  // Show a success notification bubble
			              new Noty({
		                    type: "success",
		                    text: "{!! '<strong>'.trans('siberfx::crud.foce_delete_confirmation_title').'</strong><br>'.trans('siberfx::crud.force_delete_confirmation_message') !!}"
		                  }).show();

			              // Hide the modal, if any
			              $('.modal').modal('hide');
			          } else {
			              // if the result is an array, it means
			              // we have notification bubbles to show
			          	  if (result instanceof Object) {
			          	  	// trigger one or more bubble notifications
			          	  	Object.entries(result).forEach(function(entry, index) {
			          	  	  var type = entry[0];
			          	  	  entry[1].forEach(function(message, i) {
					          	  new Noty({
				                    type: type,
				                    text: message
				                  }).show();
			          	  	  });
			          	  	});
			          	  } else {// Show an error alert
				              swal({
				              	title: "{!! trans('siberfx::crud.force_delete_confirmation_not_title') !!}",
	                            text: "{!! trans('siberfx::crud.force_delete_confirmation_not_message') !!}",
				              	icon: "error",
				              	timer: 4000,
				              	buttons: false,
				              });
			          	  }
			          }
			      },
			      error: function(result) {
			          // Show an alert with the result
			          swal({
		              	title: "{!! trans('siberfx::crud.delete_confirmation_not_title') !!}",
                        text: "{!! trans('siberfx::crud.delete_confirmation_not_message') !!}",
		              	icon: "error",
		              	timer: 4000,
		              	buttons: false,
		              });
			      }
			  });
			}
		});

      }
	}

	// make it so that the function above is run after each DataTable draw event
	// crud.addFunctionToDataTablesDrawEventQueue('forceDeleteEntry');
</script>
@if (!request()->ajax()) @endpush @endif
