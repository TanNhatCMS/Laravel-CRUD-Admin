@if ($crud->model->translationEnabled())
    <input type="hidden" name="locale" value={{ $crud->request->input('locale')?$crud->request->input('locale'):App::getLocale() }}>
@endif

{{-- See if we're using tabs --}}
@if ($crud->tabsEnabled())
    @if(view()->exists('vendor.backpack.crud.show.show_tabbed_fields'))
        @include('vendor.backpack.crud.show.show_tabbed_fields')
    @else
        @include('crud::show.show_tabbed_fields')
    @endif
@else
    @if(view()->exists('vendor.backpack.crud.show.show_fields'))
        @include('vendor.backpack.crud.show.show_fields', ['fields' => $fields])
    @else
        @include('crud::show.show_fields', ['fields' => $fields])
    @endif
@endif

{{-- Define blade stacks so css and js can be pushed from the fields to these sections. --}}

@section('after_styles')
    <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/crud.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/form.css') }}">

    <!-- CRUD FORM CONTENT - crud_fields_styles stack -->
    @stack('crud_fields_styles')
@endsection

@section('after_scripts')
    <script src="{{ asset('vendor/backpack/crud/js/crud.js') }}"></script>
    <script src="{{ asset('vendor/backpack/crud/js/form.js') }}"></script>
@endsection
