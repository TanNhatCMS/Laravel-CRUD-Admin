<!-- text input -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>

	{{-- Show the file name and a "Clear" button on EDIT form. --}}
	@if (isset($field['value']) && count($field['value']))
    <div class="well well-sm file-preview-container">
    	@foreach($field['value'] as $key => $file_path)
    		<div class="file-preview">
	    		<a target="_blank" href="{{ isset($field['disk'])?asset(\Storage::disk($field['disk'])->url($file_path)):asset($file_path) }}">{{ $file_path }}</a>
		    	<a id="{{ $field['name'] }}_{{ $key }}_clear_button" href="#" class="btn btn-default btn-xs pull-right file-clear-button" title="Clear file" data-filename="{{ $file_path }}"><i class="fa fa-remove"></i></a>
		    	<div class="clearfix"></div>
	    	</div>
    	@endforeach
    </div>
    @endif
	{{-- Show the file picker on CREATE form. --}}
	<input
        type="file"
        id="{{ $field['name'] }}_file_input"
        name="{{ $field['name'] }}[]"
        value="{{ old($field['name']) ? old($field['name']) : (isset($field['default']) ? $field['default'] : '' ) }}"
        @include('crud::inc.field_attributes')
        multiple
    >

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>