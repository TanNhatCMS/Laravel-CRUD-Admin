<!-- text input -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')

	{{-- Show the file name and a "Clear" button on EDIT form. --}}
    @if (isset($field['value']) && $field['value']!=null)
        <div>
            <a class="btn btn-success" target="_blank" href="{{ isset($field['disk'])?asset(\Storage::disk($field['disk'])->url($field['value'])):asset($field['value']) }}"><i class="fa fa-download"></i> Download</a>
        </div>
    @endif
</div>