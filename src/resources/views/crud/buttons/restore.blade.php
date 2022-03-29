@if ($crud->hasAccess('restore') && $entry->deleted_at !== null)
<a href="javascript:void(0)"
   onclick="restoreEntry(this)"
   data-route="{{ url($crud->route.'/'.$entry->getKey().'/restore') }}"
   class="btn btn-sm btn-link"
   data-button-type="restore">
    <i style="color: green" class="la la-recycle"></i>
    @if(config('siberfx.base.action_title')){{ trans('siberfx::crud.restore') }}@endif
</a>
@endif

{{-- Button Javascript --}}
{{-- - used right away in AJAX operations (ex: List) --}}
{{-- - pushed to the end of the page, after jQuery is loaded, for non-AJAX operations (ex: Show) --}}
@push('after_scripts') @if (request()->ajax()) @endpush @endif
<script>

    if (typeof restoreEntry != 'function') {
        $("[data-button-type=restore]").unbind('click');

        function restoreEntry(button) {
            // ask for confirmation before deleting an item
            // e.preventDefault();
            var route = $(button).attr('data-route');

            swal({
                title: "{!! trans('siberfx::base.notice') !!}",
                text: "{!! trans('siberfx::crud.restore_confirm') !!}",
                icon: "info",
                buttons: ["{!! trans('siberfx::crud.cancel') !!}", "{!! trans('siberfx::crud.restore') !!}"],
                dangerMode: true,
            }).then((value) => {
                if (value) {
                    $.ajax({
                        url: route,
                        type: 'POST',
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
                                    text: "{!! '<strong>'.trans('siberfx::crud.restore_confirmation_title').'</strong><br>'.trans('siberfx::crud.restore_confirmation_message') !!}"
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
                                        title: "{!! trans('siberfx::crud.restore_confirmation_not_title') !!}",
                                        text: "{!! trans('siberfx::crud.restore_confirmation_not_message') !!}",
                                        icon: "info",
                                        timer: 4000,
                                        buttons: false,
                                    });
                                }
                            }
                        },
                        error: function(result) {
                            // Show an alert with the result
                            swal({
                                title: "{!! trans('siberfx::crud.restore_confirmation_not_title') !!}",
                                text: "{!! trans('siberfx::crud.restore_confirmation_not_message') !!}",
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
    // crud.addFunctionToDataTablesDrawEventQueue('restoreEntry');
</script>
@if (!request()->ajax()) @endpush @endif
