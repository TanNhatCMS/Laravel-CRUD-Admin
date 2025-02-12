<li filter-name="{{ $filter->name }}" filter-type="{{ $filter->type }}" filter-key="{{ $filter->name }}" class="nav-item dropdown ">
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Extra {{ $filter->name }} <span class="caret"></span></a>
    <div class="dropdown-menu p-0">
        <div class="form-group backpack-filter mb-0">
            <select
                id="filter_{{ $filter->name }}"
                name="filter_{{ $filter->name }}"
                class="form-control input-sm {{ $filter->type }}"
                placeholder=""
                data-filter-key="{{ $filter->name }}"
                data-filter-type="{{ $filter->type }}"
                data-filter-name="{{ $filter->name }}"
                data-language="en"
            >
                <option value="">-</option>
                @foreach ($filter->values as $option)
                <option value="{{ $loop->iteration }}" class="text-uppercase">{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>
</li>
@basset(base_path('vendor/backpack/crud/src/resources/assets/css/select2.min.css'))
@basset(base_path('vendor/backpack/crud/src/resources/assets/css/select2-bootstrap.min.css'))
@push('crud_fields_styles')
<style>
    .form-inline .select2-container {
        display: inline-block;
    }
    .select2-drop-active {
        border:none;
    }
    .select2-container .select2-choices .select2-search-field input, .select2-container .select2-choice, .select2-container .select2-choices {
        border: none;
    }
    .select2-container-active .select2-choice {
        border: none;
        box-shadow: none;
    }
    .select2-container--bootstrap .select2-dropdown {
        margin-top: -2px;
        margin-left: -1px;
    }
    .select2-container--bootstrap {
        position: relative!important;
        top: 0px!important;
    }
</style>
@endBassetBlock
@endpush

@push('after_scripts')
@basset(base_path('vendor/backpack/crud/src/resources/assets/js/select2.full.min.js'))
@endpush
@push('crud_list_scripts')
<script>
    jQuery(document).ready(function ($) {
        // trigger select2 for each untriggered select2 box
        $('select[data-filter-type=select2]').not('[data-filter-enabled]').each(function () {
            var filterName = $(this).attr('data-filter-name');
            var filterKey = $(this).attr('data-filter-key');
            var element = $(this);

            $(this).attr('data-filter-enabled', 'true');

            var obj = $(this).select2({
                allowClear: true,
                closeOnSelect: false,
                theme: "bootstrap",
                dropdownParent: $(this).parent('.form-group'),
                placeholder: $(this).attr('placeholder'),
            }).on('change', function (c) {
                var value = $(this).val();
                var parameter = $(this).attr('data-filter-name');

                if (!value) {
                    return;
                }

                var new_url = updateDatatablesOnFilterChange(filterName, value, true, 0);

                // mark this filter as active in the navbar-filters
                if (URI(new_url).hasQuery(filterName, true)) {
                    $("li[filter-key=" + filterKey + "]").addClass('active');
                }
            }).on('select2:unselecting', function (e) {

                updateDatatablesOnFilterChange(filterName, null, true, 0);

                $('#filter_' + filterKey).val(null)
                $("li[filter-key=" + filterKey + "]").removeClass("active");
                $("li[filter-key=" + filterKey + "]").find('.dropdown-menu').removeClass("show");

                e.stopPropagation();
                return true;
            });


            // when the dropdown is opened, autofocus on the select2
            $("li[filter-key=" + filterKey + "]").on('shown.bs.dropdown', function () {
                setTimeout(() => {
                    $('select[data-filter-key=' + filterKey + ']').select2('open');
                    element.data('select2').dropdown.$search.get(0).focus();
                }, 50);
            });

            // clear filter event (used here and by the Remove all filters button)
            $("li[filter-key=" + filterKey + "]").on('filter:clear', function (e) {
                $("li[filter-key=" + filterKey + "]").removeClass('active');
                $('#filter_' + filterKey).val(null).trigger('change');
            });
        });
    });
</script>
@endpush
