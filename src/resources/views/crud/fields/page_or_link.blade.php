{{-- PAGE OR LINK field --}}
{{-- Used in Backpack\MenuCRUD --}}

<?php
    $field['options'] = [
        'page_link'     => trans('backpack::crud.page_link'),
        'internal_link' => trans('backpack::crud.internal_link'),
        'external_link' => trans('backpack::crud.external_link'),
    ];
    $field['allows_null'] = false;

    $pageModel = $field['page_model'];
    $pages = $pageModel::all();

    $entryLink = $entry->{$field['name']}['link'] ?? null;
    $entryType = $entry->{$field['name']}['type'] ?? null;
?>

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    <div class="row" data-init-function="bpFieldInitPageOrLinkElement">
        <div class="col-sm-3">
            {{-- type select --}}
            <select
                data-identifier="page_or_link_select"
                name="{!! $field['name'] !!}[type]"
                @include('crud::fields.inc.attributes')
                >

                @if (isset($field['allows_null']) && $field['allows_null'] === true)
                    <option value="">-</option>
                @endif

                @foreach ($field['options'] as $key => $value)
                    <option value="{{ $key }}"
                        @if ($key === $entryType)
                            selected
                        @endif
                    >{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-9">
            {{-- page slug input --}}
            <div class="page_or_link_value page_link {{ $entryType === 'page_link' || (!$entryType && !$field['allows_null']) ? '' : 'd-none' }}">
                <select
                    class="form-control"
                    name="{!! $field['name'] !!}[link]"
                    required
                    >
                    @foreach ($pages as $page)
                        <option value="{{ $page->slug }}"
                            @if ($page->slug === $entryLink)
                                    selected
                            @endif
                        >{{ $page->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- internal link input --}}
            <div class="page_or_link_value internal_link {{ $entryType === 'internal_link' ? '' : 'd-none' }}">
                <input
                    type="text"
                    class="form-control"
                    name="{!! $field['name'] !!}[link]"
                    placeholder="{{ trans('backpack::crud.internal_link_placeholder', ['url', url(config('backpack.base.route_prefix').'/page')]) }}"
                    required

                    @if ($entryType !== 'internal_link')
                        disabled="disabled"
                    @endif

                    @if ($entryType === 'internal_link' && $entryLink)
                        value="{{ $entry->link['link'] }}"
                    @endif
                    >
            </div>

            {{-- external link input --}}
            <div class="page_or_link_value external_link {{ $entryType === 'external_link' ? '' : 'd-none' }}">
                <input
                    type="url"
                    class="form-control"
                    name="{!! $field['name'] !!}[link]"
                    placeholder="{{ trans('backpack::crud.page_link_placeholder') }}"
                    required

                    @if ($entryType !== 'external_link')
                        disabled="disabled"
                    @endif

                    @if ($entryType === 'external_link' && $entryLink)
                        value="{{ $entryLink }}"
                    @endif
                    >
            </div>
        </div>
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script>
        function bpFieldInitPageOrLinkElement(element) {
            element = element[0]; // jQuery > Vanilla

            let select = element.querySelector('select[data-identifier=page_or_link_select]');
            let values = element.querySelectorAll('.page_or_link_value');

            select.addEventListener('change', e => {
                let type = e.target.value;

                values.forEach(value => {
                    let selected = value.classList.contains(type);
                    
                    value.classList.toggle('d-none', !selected);
                    value.firstElementChild.toggleAttribute('disabled', !selected);
                })
            });
        }
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
