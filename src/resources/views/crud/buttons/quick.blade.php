@if ($button->access == true || $crud->hasAccess($button->access))
    <a href="{{ $button->url }}" class="{{ $button->classes }}">
        <i class="{{ $button->icon }}"></i> {{ $button->text }}
    </a>
@endif
