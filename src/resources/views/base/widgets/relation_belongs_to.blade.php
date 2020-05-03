@php
    if(!isset($widget['columns'])){
        foreach ($entry->{$widget['name']}->getFillable() as $propertyName){
            $widget['columns'][$propertyName] = $crud->makeLabel($propertyName);
        }
    }
@endphp

@if($entry->{$widget['name']} !== null)
    <div>
        <h5>{{$widget['label']}}</h5>
        <table class="table table-striped mb-0">
            <tbody>
                @foreach($widget['columns'] as $propertyName => $propertyLabel)
                    <tr>
                        <td>
                            <strong>{{$propertyLabel}}:</strong>
                        </td>
                        <td>
                            <span>{{$entry->{$widget['name']}->$propertyName}}</span>
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td>
                        <strong>{{ trans('backpack::crud.actions') }}</strong>
                    </td>
                    <td>
                        <a href="/admin/{{$widget['backpack_crud']}}/{{$entry->{$widget['name']}->id}}/edit"
                           class="btn btn-sm btn-link">
                            <i class="la la-edit"></i> {{ trans('backpack::crud.edit') }}
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endif
