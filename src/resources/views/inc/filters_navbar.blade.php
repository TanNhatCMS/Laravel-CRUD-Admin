	
	<div class="portlet-title py-1 my-0 navbar navbar-light navbar-expand-lg navbar-filters hidden-print" role="navigation">
		<button class="navbar-toggler tool-action btn dark btn-outline btn-sm " type="button" title="{{ trans('backpack::crud.toggle_filters') }}" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ trans('backpack::crud.toggle_filters') }}">
			<span class="navbar-toggler-icon"></span> {{ trans('backpack::crud.filters') }}
		</button>
		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse py-0 my-0 actions" id="bs-example-navbar-collapse-1">
			<ul class="btn-group nav navbar-nav mr-auto py-1 navbar-filters flex-row">
				<!-- THE ACTUAL FILTERS -->
				@foreach ($crud->filters as $filter)
					@include($filter->view)
				@endforeach
				<li class="nav-item"><a href="#" id="remove_filters_button" class="tool-action btn red btn-outline hidden btn-sm"><i class="fa fa-eraser"></i> {{ trans('backpack::crud.remove_filters') }}</a></li>
			</ul>
		</div><!-- /.navbar-collapse -->
	</div>


@push('crud_list_styles')
<!-- <style>
    .backpack-filter label {
      color: #868686;
      font-weight: 600;
      text-transform: uppercase;
    }

    .navbar-filters {
      min-height: 25px;
      border-radius: 0;
      margin-bottom: 10px;
      margin-left: -10px;
      margin-right: -10px;
      margin-top: -11px;
      background: #f9f9f9;
      border-color: #f4f4f4;
    }

    .navbar-filters .navbar-collapse {
    	padding: 0;
    }

    .navbar-filters .navbar-toggle {
      padding: 10px 15px;
      border-radius: 0;
    }

    .navbar-filters .navbar-brand {
      height: 25px;
      padding: 5px 15px;
      font-size: 14px;
      text-transform: uppercase;
    }
    @media (min-width: 768px) {
      .navbar-filters .navbar-nav>li>a {
          padding-top: 5px;
          padding-bottom: 5px;
      }
    }

    @media (max-width: 768px) {
      .navbar-filters .navbar-nav {
        margin: 0;
      }
    }
    </style> -->

@endpush

@push('crud_list_scripts')
	<script src="https://cdnjs.cloudflare.com/ajax/libs/URI.js/1.18.2/URI.min.js" type="text/javascript"></script>
		<script>
			function addOrUpdateUriParameter(uri, parameter, value) {
						var new_url = normalizeAmpersand(uri);

						new_url = URI(new_url).normalizeQuery();

						if (new_url.hasQuery(parameter)) {
							new_url.removeQuery(parameter);
						}

						if (value != '') {
							new_url = new_url.addQuery(parameter, value);
						}

						$('#remove_filters_button').removeClass('hidden');

				return new_url.toString();

			}

			function normalizeAmpersand(string) {
				return string.replace(/&amp;/g, "&").replace(/amp%3B/g, "");
			}

			// button to remove all filters
			jQuery(document).ready(function($) {
				$("#remove_filters_button").click(function(e) {
					e.preventDefault();

					// behaviour for ajax table
					var new_url = '{{ url($crud->route.'/search') }}';
					var ajax_table = $("#crudTable").DataTable();

					// replace the datatables ajax url with new_url and reload it
					ajax_table.ajax.url(new_url).load();

					// clear all filters
					$(".navbar-filters > li[filter-name] > a").trigger('filter:clear');
					$('#remove_filters_button').addClass('hidden');
				})
			});
		</script>
@endpush
