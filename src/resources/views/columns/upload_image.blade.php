{{-- regular object attribute --}}
<td>
    @if ( isset($entry->{$column['name']}) && is_array($entry->{$column['name']}) )
    <img src="{{ $entry->getUploadedImageFromDisk($column['name'], 'thumb') }}" alt="entry image preview" style="height: 25px;" />
    @else
    n/a
    @endif
</td>
