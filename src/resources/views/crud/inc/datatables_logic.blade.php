 @php
    // as it is possible that we can be redirected with persistent table we save the alerts in a variable
    // and flush them from session, so we will get them later from localStorage.
    $backpack_alerts = \Alert::getMessages();
    \Alert::flush();
 @endphp

  {{-- DATA TABLES SCRIPT --}}
@push('after_scripts')
    @basset("https://cdn.datatables.net/2.1.8/js/dataTables.min.js")
    @basset("https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js")
    @basset("https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js")
    @basset('https://cdn.datatables.net/fixedheader/4.0.1/js/dataTables.fixedHeader.min.js')
    @basset(base_path('vendor/backpack/crud/src/resources/assets/img/spinner.svg'), false)
@endpush

@push('before_styles')
    @basset('https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css')
    @basset("https://cdn.datatables.net/responsive/3.0.3/css/responsive.dataTables.min.css")
    @basset('https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.dataTables.min.css')
@endpush
@push('after_scripts')
  <script>
    // here we will check if the cached dataTables paginator length is conformable with current paginator settings.
    // datatables caches the ajax responses with pageLength in LocalStorage so when changing this
    // settings in controller users get unexpected results. To avoid that we will reset
    // the table cache when both lengths don't match.
    let $dtCachedInfo = JSON.parse(localStorage.getItem('DataTables_crudTable_/{{$crud->getOperationSetting("datatablesUrl")}}'))
        ? JSON.parse(localStorage.getItem('DataTables_crudTable_/{{$crud->getOperationSetting("datatablesUrl")}}')) : [];
    var $dtDefaultPageLength = {{ $crud->getDefaultPageLength() }};
    let $pageLength = @json($crud->getPageLengthMenu());
    
    let $dtStoredPageLength = parseInt(localStorage.getItem('DataTables_crudTable_/{{$crud->getOperationSetting("datatablesUrl")}}_pageLength'));

    if(!$dtStoredPageLength && $dtCachedInfo.length !== 0 && $dtCachedInfo.length !== $dtDefaultPageLength) {
        localStorage.removeItem('DataTables_crudTable_/{{$crud->getOperationSetting("datatablesUrl")}}');
    }

    if($dtCachedInfo.length !== 0 && $pageLength[0].indexOf($dtCachedInfo.length) === -1) {
        localStorage.removeItem('DataTables_crudTable_/{{$crud->getRoute()}}');
    }


    // in this page we always pass the alerts to localStorage because we can be redirected with
    // persistent table, and this way we guarantee non-duplicate alerts.
    $oldAlerts = JSON.parse(localStorage.getItem('backpack_alerts'))
        ? JSON.parse(localStorage.getItem('backpack_alerts')) : {};

    $newAlerts = @json($backpack_alerts);

    Object.entries($newAlerts).forEach(function(type) {
        if(typeof $oldAlerts[type[0]] !== 'undefined') {
            type[1].forEach(function(msg) {
                $oldAlerts[type[0]].push(msg);
            });
        } else {
            $oldAlerts[type[0]] = type[1];
        }
    });

    // always store the alerts in localStorage for this page
    localStorage.setItem('backpack_alerts', JSON.stringify($oldAlerts));

    @if ($crud->getPersistentTable())

        var saved_list_url = localStorage.getItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}_list_url');

        //check if saved url has any parameter or is empty after clearing filters.
        if (saved_list_url && saved_list_url.indexOf('?') < 1) {
            var saved_list_url = false;
        } else {
            var persistentUrl = saved_list_url+'&persistent-table=true';
        }

    var arr = window.location.href.split('?');
    // check if url has parameters.
    if (arr.length > 1 && arr[1] !== '') {
        // IT HAS! Check if it is our own persistence redirect.
        if (window.location.search.indexOf('persistent-table=true') < 1) {
            // IF NOT: we don't want to redirect the user.
            saved_list_url = false;
        }
    }

    @if($crud->getPersistentTableDuration())
        var saved_list_url_time = localStorage.getItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}_list_url_time');

        if (saved_list_url_time) {
            var $current_date = new Date();
            var $saved_time = new Date(parseInt(saved_list_url_time));
            $saved_time.setMinutes($saved_time.getMinutes() + {{$crud->getPersistentTableDuration()}});

            // if the save time is not expired we force the filter redirection.
            if($saved_time > $current_date) {
                if (saved_list_url && persistentUrl!=window.location.href) {
                    window.location.href = persistentUrl;
                }
            } else {
                // persistent table expired, let's not redirect the user
                saved_list_url = false;
            }
        }

    @endif
        if (saved_list_url && persistentUrl!=window.location.href) {
            // finally redirect the user.
            window.location.href = persistentUrl;
        }
    @endif

    window.crud = {
      exportButtons: JSON.parse('{!! json_encode($crud->get('list.export_buttons')) !!}'),
      functionsToRunOnDataTablesDrawEvent: [],
      addFunctionToDataTablesDrawEventQueue: function (functionName) {
          if (this.functionsToRunOnDataTablesDrawEvent.indexOf(functionName) == -1) {
          this.functionsToRunOnDataTablesDrawEvent.push(functionName);
        }
      },
      responsiveToggle: function(dt) {
          $(dt.table().header()).find('th').toggleClass('all');
          dt.responsive.rebuild();
          dt.responsive.recalc();
      },
      executeFunctionByName: function(str, args) {
        var arr = str.split('.');
        var fn = window[ arr[0] ];

        for (var i = 1; i < arr.length; i++)
        { fn = fn[ arr[i] ]; }
        fn.apply(window, args);
      },
      updateUrl : function (url) {
        let urlStart = "{{ url($crud->getOperationSetting("datatablesUrl")) }}";
        let urlEnd = url.replace(urlStart, '');
        urlEnd = urlEnd.replace('/search', '');
        let newUrl = urlStart + urlEnd;
        let tmpUrl = newUrl.split("?")[0],
        params_arr = [],
        queryString = (newUrl.indexOf("?") !== -1) ? newUrl.split("?")[1] : false;

        // exclude the persistent-table parameter from url
        if (queryString !== false) {
            params_arr = queryString.split("&");
            for (let i = params_arr.length - 1; i >= 0; i--) {
                let param = params_arr[i].split("=")[0];
                if (param === 'persistent-table') {
                    params_arr.splice(i, 1);
                }
            }
            newUrl = params_arr.length ? tmpUrl + "?" + params_arr.join("&") : tmpUrl;
        }
        window.history.pushState({}, '', newUrl);
        @if ($crud->getPersistentTable())
            localStorage.setItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}_list_url', newUrl);
        @endif
      },
      dataTableConfiguration: {
        bInfo: {{ var_export($crud->getOperationSetting('showEntryCount') ?? true) }},
        @if ($crud->getResponsiveTable())
        responsive: {
            details: {
                display: DataTable.Responsive.display.modal( {
                    header: function ( row ) {
                        // show the content of the first column
                        // as the modal header
                        // var data = row.data();
                        // return data[0];
                        return '';
                    }
                }),
                type: 'none',
                target: '.dtr-control',
                renderer: function ( api, rowIdx, columns ) {

                  var data = $.map( columns, function ( col, i ) {
                      var columnHeading = crud.table.columns().header()[col.columnIndex];

                      // hide columns that have VisibleInModal false
                      if ($(columnHeading).attr('data-visible-in-modal') == 'false') {
                        return '';
                      }

                      return '<tr data-dt-row="'+col.rowIndex+'" data-dt-column="'+col.columnIndex+'">'+
                                '<td style="vertical-align:top; border:none;"><strong>'+col.title.trim()+':'+'<strong></td> '+
                                '<td style="padding-left:10px;padding-bottom:10px; border:none;">'+col.data+'</td>'+
                              '</tr>';
                  } ).join('');

                  return data ?
                      $('<table class="table table-striped mb-0">').append( '<tbody>' + data + '</tbody>' ) :
                      false;
                },
            }
        },
        fixedHeader: true,
        @else
        responsive: false,
        scrollX: true,
        @endif

        @if ($crud->getPersistentTable())
        stateSave: true,
        /*
            if developer forced field into table 'visibleInTable => true' we make sure when saving datatables state
            that it reflects the developer decision.
        */

        stateSaveParams: function(settings, data) {

            localStorage.setItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}_list_url_time', data.time);

            data.columns.forEach(function(item, index) {
                var columnHeading = crud.table.columns().header()[index];
                if ($(columnHeading).attr('data-visible-in-table') == 'true') {
                    return item.visible = true;
                }
            });
        },
        @if($crud->getPersistentTableDuration())
        stateLoadParams: function(settings, data) {
            var $saved_time = new Date(data.time);
            var $current_date = new Date();

            $saved_time.setMinutes($saved_time.getMinutes() + {{$crud->getPersistentTableDuration()}});

            //if the save time as expired we force datatabled to clear localStorage
            if($saved_time < $current_date) {
                if (localStorage.getItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl"))}}_list_url')) {
                    localStorage.removeItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}_list_url');
                }
                if (localStorage.getItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl"))}}_list_url_time')) {
                    localStorage.removeItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}_list_url_time');
                }
               return false;
            }
        },
        @endif
        @endif
        autoWidth: false,
        pageLength: $dtDefaultPageLength,
        lengthMenu: $pageLength,
        /* Disable initial sort */
        aaSorting: [],
        language: {
              "emptyTable":     "{{ trans('backpack::crud.emptyTable') }}",
              "info":           "{{ trans('backpack::crud.info') }}",
              "infoEmpty":      "{{ trans('backpack::crud.infoEmpty') }}",
              "infoFiltered":   "{{ trans('backpack::crud.infoFiltered') }}",
              "infoPostFix":    "{{ trans('backpack::crud.infoPostFix') }}",
              "thousands":      "{{ trans('backpack::crud.thousands') }}",
              "lengthMenu":     "{{ trans('backpack::crud.lengthMenu') }}",
              "loadingRecords": "{{ trans('backpack::crud.loadingRecords') }}",
              "processing":     "<img src='{{ Basset::getUrl('vendor/backpack/crud/src/resources/assets/img/spinner.svg') }}' alt='{{ trans('backpack::crud.processing') }}'>",
              "search": "_INPUT_",
              "searchPlaceholder": "{{ trans('backpack::crud.search') }}...",
              "zeroRecords":    "{{ trans('backpack::crud.zeroRecords') }}",
              "paginate": {
                  "first":      "{{ trans('backpack::crud.paginate.first') }}",
                  "last":       "{{ trans('backpack::crud.paginate.last') }}",
                  "next":       '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M8 5l5 5l-5 5"></path></svg>',
                  "previous":   '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-5 5l5 5"></path></svg>'
              },
              "aria": {
                  "sortAscending":  "{{ trans('backpack::crud.aria.sortAscending') }}",
                  "sortDescending": "{{ trans('backpack::crud.aria.sortDescending') }}"
              },
              "buttons": {
                  "copy":   "{{ trans('backpack::crud.export.copy') }}",
                  "excel":  "{{ trans('backpack::crud.export.excel') }}",
                  "csv":    "{{ trans('backpack::crud.export.csv') }}",
                  "pdf":    "{{ trans('backpack::crud.export.pdf') }}",
                  "print":  "{{ trans('backpack::crud.export.print') }}",
                  "colvis": "{{ trans('backpack::crud.export.column_visibility') }}"
              },
          },
          processing: true,
          serverSide: true,
          searchDelay: {{ $crud->getOperationSetting('searchDelay') }},
          @if($crud->getOperationSetting('showEntryCount') === false)
            pagingType: "simple",
          @endif
          searching: @json($crud->getOperationSetting('searchableTable') ?? true),
          ajax: {
              "url": "{!! url($crud->getOperationSetting("datatablesUrl").'/search').'?'.Request::getQueryString() !!}",
              "type": "POST",
              "data": {
                "totalEntryCount": "{{$crud->getOperationSetting('totalEntryCount') ?? false}}"
            },
          },
          dom:
            "<'row hidden'<'col-sm-6'i><'col-sm-6 d-print-none'f>>" +
            "<'table-content row'<'col-sm-12'tr>>" +
            "<'table-footer row mt-2 d-print-none align-items-center '<'col-sm-12 col-md-4'l><'col-sm-0 col-md-4 text-center'B><'col-sm-12 col-md-4 'p>>",
      }
  }
  </script> 
  @include('crud::inc.export_buttons')

  <script type="text/javascript">
    // TODO: this needs to be agnostic per filter navbar as in the future hopefully we can have more than one 
    // table in the same page and setup filters for each one.
    document.addEventListener('backpack:filters:cleared', function (event) {       
        // behaviour for ajax table
        var new_url = '{{ url($crud->getOperationSetting("datatablesUrl").'/search') }}';
        var ajax_table = new DataTable('#crudTable');

        // replace the datatables ajax url with new_url and reload it
        ajax_table.ajax.url(new_url).load();

        // remove filters from URL
        crud.updateUrl(new_url);       
    });

    document.addEventListener('backpack:filter:changed', function (event) {
        let filterName = event.detail.filterName;
        let filterValue = event.detail.filterValue;
        let shouldUpdateUrl = event.detail.shouldUpdateUrl;
        let debounce = event.detail.debounce;
        updateDatatablesOnFilterChange(filterName, filterValue, filterValue || shouldUpdateUrl, debounce);
    });

    jQuery(document).ready(function($) {

      window.crud.table = $("#crudTable").DataTable(window.crud.dataTableConfiguration);
      window.crud.updateUrl(location.href);

      // move search bar
      $(".dt-search input").remove();
      $(".dt-search input").appendTo($('#datatable_search_stack .input-icon'));
      $("#datatable_search_stack input").removeClass('form-control-sm');
      $(".dt-search").remove();

      // remove btn-secondary from export and column visibility buttons
      $("#crudTable_wrapper .table-footer .btn-secondary").removeClass('btn-secondary');

      // remove forced overflow on load
      $(".navbar.navbar-filters + div").css('overflow','hidden');

      // move "showing x out of y" info to header
      @if($crud->getSubheading())
      $('#crudTable_info').hide();
      @else
      $("#datatable_info_stack").html($('#crudTable_info')).css('display','inline-flex').addClass('animated fadeIn');
      @endif

      @if($crud->getOperationSetting('resetButton') ?? true)
        // create the reset button
        var crudTableResetButton = '<a href="{{url($crud->getOperationSetting("datatablesUrl"))}}" class="ml-1 ms-1" id="crudTable_reset_button">{{ trans('backpack::crud.reset') }}</a>';

        $('#datatable_info_stack').append(crudTableResetButton);

          // when clicking in reset button we clear the localStorage for datatables.
        $('#crudTable_reset_button').on('click', function() {

          //clear the filters
          if (localStorage.getItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl"))}}_list_url')) {
              localStorage.removeItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}_list_url');
          }
          if (localStorage.getItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl"))}}_list_url_time')) {
              localStorage.removeItem('{{ Str::slug($crud->getOperationSetting("datatablesUrl")) }}_list_url_time');
          }

          //clear the table sorting/ordering/visibility
          if(localStorage.getItem('DataTables_crudTable_/{{ $crud->getOperationSetting("datatablesUrl") }}')) {
              localStorage.removeItem('DataTables_crudTable_/{{ $crud->getOperationSetting("datatablesUrl") }}');
          }
        });
      @endif

      // move the bottom buttons before pagination
      $("#bottom_buttons").insertBefore($('#crudTable_wrapper .row:last-child' ));

      // override ajax error message
      $.fn.dataTable.ext.errMode = 'none';
      $('#crudTable').on('error.dt', function(e, settings, techNote, message) {
          new Noty({
              type: "error",
              text: "<strong>{{ trans('backpack::crud.ajax_error_title') }}</strong><br>{{ trans('backpack::crud.ajax_error_text') }}"
          }).show();
      });

        // when changing page length in datatables, save it into localStorage
        // so in next requests we know if the length changed by user
        // or by developer in the controller.
        $('#crudTable').on( 'length.dt', function ( e, settings, len ) {
            localStorage.setItem('DataTables_crudTable_/{{$crud->getOperationSetting("datatablesUrl")}}_pageLength', len);
        });

        $('#crudTable').on( 'page.dt', function () {
            localStorage.setItem('page_changed', true);
        });

      // on DataTable draw event run all functions in the queue
      // (eg. delete and details_row buttons add functions to this queue)
      $('#crudTable').on( 'draw.dt',   function () {
         crud.functionsToRunOnDataTablesDrawEvent.forEach(function(functionName) {
            crud.executeFunctionByName(functionName);
         });
         if ($('#crudTable').data('has-line-buttons-as-dropdown')) {
          formatActionColumnAsDropdown();
         }

        if (! crud.table.responsive.hasHidden()) {
            crud.table.columns().header()[0].style.paddingLeft = '0.6rem';
        }

         if (crud.table.responsive.hasHidden()) {
            $('.dtr-control').removeClass('d-none'); 
            $('.dtr-control').addClass('d-inline');
            $("#crudTable").removeClass('has-hidden-columns').addClass('has-hidden-columns');
         }

         // in datatables 2.0.3 the implementation was changed to use `replaceChildren`, for that reason scripts 
         // that came with the response are no longer executed, like the delete button script or any other ajax 
         // button created by the developer. For that reason, we move them to the end of the body
         // ensuring they are re-evaluated on each draw event.
        document.getElementById('crudTable').querySelectorAll('script').forEach(function(script) {
            const newScript = document.createElement('script');
            newScript.text = script.text;
            document.body.appendChild(newScript);
        });
      }).dataTable();

      // when datatables-colvis (column visibility) is toggled
      // rebuild the datatable using the datatable-responsive plugin
      $('#crudTable').on( 'column-visibility.dt',   function (event) {
        console.log('column-visibility.dt');
         crud.table.responsive.rebuild();
      } ).dataTable();

      @if ($crud->getResponsiveTable())
        // when columns are hidden by reponsive plugin,
        // the table should have the has-hidden-columns class
        crud.table.on( 'responsive-resize', function ( e, datatable, columns ) {
            console.log('responsive-resize', crud.table.responsive.hasHidden());
            if (crud.table.responsive.hasHidden()) {
                $('.dtr-control').removeClass('d-none'); 
                $('.dtr-control').addClass('d-inline');
                $("#crudTable").removeClass('has-hidden-columns').addClass('has-hidden-columns');
             } else {
              $('.dtr-control').removeClass('d-none').removeClass('d-inline').addClass('d-none');  
              $("#crudTable").removeClass('has-hidden-columns');
             }
        } );
      @else
        // make sure the column headings have the same width as the actual columns
        // after the user manually resizes the window
        var resizeTimer;
        function resizeCrudTableColumnWidths() {
          clearTimeout(resizeTimer);
          resizeTimer = setTimeout(function() {
            // Run code here, resizing has "stopped"
            crud.table.columns.adjust();
          }, 250);
        }
        $(window).on('resize', function(e) {
          resizeCrudTableColumnWidths();
        });
        $('.sidebar-toggler').click(function() {
          resizeCrudTableColumnWidths();
        });
      @endif

    });
 
    function formatActionColumnAsDropdown() {
        // Get action column
        const actionColumnIndex = $('#crudTable').find('th[data-action-column=true]').index();
        if (actionColumnIndex === -1) return;

        const minimumButtonsToBuildDropdown = $('#crudTable').data('line-buttons-as-dropdown-minimum');
        const buttonsToShowBeforeDropdown = $('#crudTable').data('line-buttons-as-dropdown-show-before-dropdown');

        $('#crudTable tbody tr').each(function (i, tr) {
            const actionCell = $(tr).find('td').eq(actionColumnIndex);
            const actionButtons = actionCell.find('a.btn.btn-link');
            if (actionCell.find('.actions-buttons-column').length) return;
            if (actionButtons.length < minimumButtonsToBuildDropdown) return;

            // Prepare buttons as dropdown items
            const dropdownItems = actionButtons.slice(buttonsToShowBeforeDropdown).map((index, action) => {
                $(action).addClass('dropdown-item').removeClass('btn btn-sm btn-link');
                $(action).find('i').addClass('me-2 text-primary');
                return action;
            });

            // Only create dropdown if there are items to drop
            if (dropdownItems.length > 0) {
                // Wrap the cell with the component needed for the dropdown
                actionCell.wrapInner('<div class="nav-item dropdown"></div>');
                actionCell.wrapInner('<div class="dropdown-menu dropdown-menu-left"></div>');

                actionCell.prepend('<a class="btn btn-sm px-2 py-1 btn-outline-primary dropdown-toggle actions-buttons-column" href="#" data-toggle="dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">{{ trans('backpack::crud.actions') }}</a>');
                
                // Move the remaining buttons outside the dropdown
                const remainingButtons = actionButtons.slice(0, buttonsToShowBeforeDropdown);
                actionCell.prepend(remainingButtons);
            }
        });
    }
  </script>
 
  @include('crud::inc.details_row_logic')
@endpush