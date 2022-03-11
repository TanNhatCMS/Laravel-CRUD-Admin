@php
    // if field name is array we check if any of the arrayed fields is translatable
    $translatable = false;
    $showOriginal = config('backpack.crud.show_original_text_for_non_translated_keys', true);
    if($crud->model->translationEnabled()) {
        foreach((array) $field['name'] as $field_name){
            
            if($crud->model->isTranslatableAttribute($field_name)) {
                if(is_array($field['name'])) {
                    $showOriginal = false;
                }
                $translatable = true;
            }
        }
        
        // if the field is a fake one (value is stored in a JSON column instead of a direct db column)
        // and that JSON column is translatable, then the field itself should be translatable
        if(isset($field['store_in']) && $crud->model->isTranslatableAttribute($field['store_in'])) {
                $translatable = true;
                $showOriginal = false;
        }
    }

@endphp
@if ($translatable)
    @if(app()->getLocale() ?? '' !== $crud->model->getLocale() && config('backpack.crud.show_original_text_for_non_translated_keys', true))
        @php
            $entryOriginalValue = $entry->getBackpackTranslation($field['name'], app()->getLocale());
        @endphp
        @if(is_string($entryOriginalValue))
            Original: <i>« {{\Str::of($entryOriginalValue)->limit($field['limit'] ?? 30)->append(' ...')}} <button type="button" class="copy-btn" content="{{$entryOriginalValue}}" >copy</button>@endif @if(config('backpack.crud.show_translatable_field_icon')) »</i><i class="la la-flag-checkered pull-{{ config('backpack.crud.translatable_field_icon_position') }}" style="margin-top: 3px;" title="This field is translatable."></i>
        @endif
    @endif
    @loadOnce('translatable_scripts')
        @push('after_scripts')
        <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function(event) { 
            const copyButtons = document.getElementsByClassName('copy-btn');
            copyButtons.forEach(function(button) {
                button.addEventListener('click', (event) => {
                    var aux_input = document.createElement("div");
                    aux_input.setAttribute("contentEditable", true);
                    console.log(button.getAttribute('content'));
                    aux_input.innerHTML = button.getAttribute('content');
                    aux_input.setAttribute("onfocus", "document.execCommand('selectAll',false,null)"); 
                    document.body.appendChild(aux_input);
                    aux_input.focus();
                    document.execCommand("copy");
                    document.body.removeChild(aux_input);
            
                new Noty({
                    type: "success",
                    text: "Value is now on your clipboard."
                    }).show();
                })
            })
        })
        </script>
        @endpush
    @endLoadOnce
@endif
