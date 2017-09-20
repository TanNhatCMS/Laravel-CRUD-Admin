{{-- Show the inputs --}}
@foreach ($fields as $field)
    <!-- load the view from the application if it exists, otherwise load the one in the package -->
    @if(view()->exists('vendor.backpack.crud.show.fields.'.$field['type']))
        @include('vendor.backpack.crud.show.fields.'.$field['type'], array('field' => $field))
    @else
        @include('crud::show.fields.'.$field['type'], array('field' => $field))
    @endif
@endforeach
