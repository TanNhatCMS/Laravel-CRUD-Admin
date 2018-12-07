{{-- Show the errors, if any --}}
@if ($crud->groupedErrorsEnabled() && $errors->any())
    <div class="note note-danger">
        <h4 class="block">{{ trans('backpack::crud.please_fix') }}</h4>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif