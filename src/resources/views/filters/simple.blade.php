{{-- Simple Backpack CRUD filter --}}

<li filter-name="{{ $filter->name }}"
	filter-type="{{ $filter->type }}"
	class="nav-item">
    <a 	href="" class="tool-action btn green {{ Request::get($filter->name)?'':'btn-outline' }} btn-sm"
		parameter="{{ $filter->name }}"
    	>{{ $filter->label }}</a>
  </li>


{{-- ########################################### --}}
{{-- Extra CSS and JS for this particular filter --}}

{{-- FILTERS EXTRA CSS  --}}
{{-- push things in the after_styles section --}}

    {{-- @push('crud_list_styles')
        <!-- no css -->
    @endpush --}}


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

@push('crud_list_scripts')
    <script>
		jQuery(document).ready(function($) {
			$("li[filter-name={{ $filter->name }}] > a").click(function(e) {
				e.preventDefault();

				var parameter = $(this).attr('parameter');

		    	// behaviour for ajax table
				var ajax_table = $("#crudTable").DataTable();
				var current_url = ajax_table.ajax.url();

				if (URI(current_url).hasQuery(parameter)) {
					var new_url = URI(current_url).removeQuery(parameter, true);
				} else {
					var new_url = URI(current_url).addQuery(parameter, true);
				}

				new_url = normalizeAmpersand(new_url.toString());

				// replace the datatables ajax url with new_url and reload it
				ajax_table.ajax.url(new_url).load();

				// mark this filter as active in the navbar-filters
				if (URI(new_url).hasQuery('{{ $filter->name }}', true)) {
					$("li[filter-name={{ $filter->name }}] > a").addClass('btn-outline').removeClass('btn-outline');
					$('#remove_filters_button').removeClass('hidden');
				}
				else
				{
					$("li[filter-name={{ $filter->name }}] > a").trigger("filter:clear");
				}
			});

			// clear filter event (used here and by the Remove all filters button)
			$("li[filter-name={{ $filter->name }}] > a ").on('filter:clear', function(e) {
				// console.log('dropdown filter cleared');
				$("li[filter-name={{ $filter->name }}] > a").addClass('btn-outline');
			});
			
		});
	</script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}