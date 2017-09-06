<!-- dependencyJson -->
<div class="form-group col-md-12 checklist_dependency"  data-entity ="{{ $field['field_unique_name'] }}" @include('crud::inc.field_wrapper_attributes')>

    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')

    <?php
    $fieldRole = $field['subfields']['primary'];
    $fieldPermission = $field['subfields']['secondary'];

    $entity_model = $crud->getModel();

    $roles = $fieldRole['model']::with($fieldRole['entity_secondary'])->get();

    // Gets the entity roles and permissions
    $entityRoles = collect(isset($field['value']) && is_array($field['value']) ? array_get($field, 'value.0', []) : []);
    $entityPermissions = collect(isset($field['value']) && is_array($field['value']) ? array_get($field, 'value.1', []) : []);

    // Gets the permissions granted by the entity roles (update form only)
    $entityRolesPermissions = collect();

    // Gets the entity with roles and permissions
    $entityWithDependencies = $entity_model->with($fieldRole['entity'])
        ->with($fieldRole['entity'].'.'.$fieldRole['entity_secondary'])
        ->find($id);

    // Creates secondary dependency from primary relation, used to check which checkbox must be checked from second checklist
    if (old($fieldRole['name'])) {
        foreach (old($fieldRole['name']) as $role_item) {
            $entityRolesPermissions = $entityRolesPermissions->merge($roles->search(function($role) use ($role_item) {
                return $role->id;
            }) ?: []);
        }
    }
    // Creates dependencies from relation if not from validate error
    else {
        foreach ($entityWithDependencies->{$fieldRole['entity']} as $role) {
            $entityRolesPermissions = $entityRolesPermissions->merge($role->{$fieldPermission['entity']}->pluck('id'));
        }
    }

    // Builds a simple matrix of roles and permissions (role id as key and array of permission ids as value)
    $rolesPermissions = $roles->pluck($fieldRole['entity_secondary'], 'id')->map(function($permissions) {
        return $permissions->pluck('id');
    });

    ?>
    <script>
        var  {{ $field['field_unique_name'] }} = {!! $rolesPermissions->toJson() !!};
    </script>

    <div class="row" >

        <div class="col-xs-12">
            <label>{!! $fieldRole['label'] !!}</label>
        </div>

        <div class="hidden_fields_primary" data-name = "{{ $fieldRole['name'] }}">
            @if (isset($field['value']))
                @if (old($fieldRole['name']))
                    @foreach(old($fieldRole['name']) as $item)
                        <input type="hidden" class="primary_hidden" name="{{ $fieldRole['name'] }}[]" value="{{ $item }}">
                    @endforeach
                @else
                    @foreach($field['value'][0]->pluck('id', 'id')->toArray() as $item)
                        <input type="hidden" class="primary_hidden" name="{{ $fieldRole['name'] }}[]" value="{{ $item }}">
                    @endforeach
                @endif
            @endif
        </div>

        <?php $columns = array_get($fieldRole, 'columns') ?>

        @if (is_bool($columns))
        <div class="col-sm-12">
        @endif

        @foreach ($fieldRole['model']::all() as $role)
            @if (is_int($columns))
            <div class="col-sm-{{ is_int($columns) ? intval(12 / $columns) : '12' }}">
            @endif
                <div class="checkbox {{ $columns === true ? 'inline' : '' }}">
                    <label>
                        <input
                            type="checkbox"
                            data-id = "{{ $role->id }}"
                            class="primary_list"
                            @foreach ($fieldRole as $attribute => $value)
                                @if (is_string($attribute) && $attribute != 'value')
                                    @if ($attribute=='name')
                                    {{ $attribute }}="{{ $value }}_show[]"
                                    @else
                                    {{ $attribute }}="{{ $value }}"
                                    @endif
                                @endif
                            @endforeach
                            value="{{ $role->id }}"
                            @if (
                                (isset($field['value']) && is_array($field['value']) && in_array($role->id, $field['value'][0]->pluck('id', 'id')->toArray()))
                                 || (old($fieldRole["name"]) && in_array($role->id, old($fieldRole["name"])))
                             )
                            checked = "checked"
                            @endif >
                            {{ $role->{$fieldRole['attribute']} }}
                    </label>
                    {{ $columns === true ? '&nbsp;' : '' }}
                </div>
            @if (is_int($columns))
            </div>
            @endif
        @endforeach

        @if (is_bool($columns))
        </div>
        @endif

    </div>

    <div class="row">
        <div class="col-xs-12">
            <label>{!! $fieldPermission['label'] !!}</label>
        </div>

        <div class="hidden_fields_secondary" data-name="{{ $fieldPermission['name'] }}">
          @if (isset($field['value']))
            @if (old($fieldPermission['name']))
              @foreach(old($fieldPermission['name']) as $item)
                <input type="hidden" class="secondary_hidden" name="{{ $fieldPermission['name'] }}[]" value="{{ $item }}">
              @endforeach
            @else
              @foreach($field['value'][1]->pluck('id', 'id')->toArray() as $item)
                <input type="hidden" class="secondary_hidden" name="{{ $fieldPermission['name'] }}[]" value="{{ $item }}">
              @endforeach
            @endif
          @endif
        </div>

        <div class="col-sm-12">
            @foreach ($fieldPermission['model']::all()->groupBy(function($permission) { return $permission->prefix(); }) as $prefix => $permissions)
                <hr/>
                <div class="row">
                    <div class="col-sm-3">
                        <label class="no-margin">
                            <strong>{{ $prefix }}</strong>
                        </label>
                    </div>
                    <div class="col-sm-7">
                        @foreach ($permissions as $permission)
                            <div class="checkbox inline no-margin">
                                <label>
                                    <input
                                        type="checkbox"
                                        class = 'secondary_list'
                                        data-id = "{{ $permission->id }}"
                                        value="{{ $permission->id }}"
                                        @foreach ($fieldPermission as $attribute => $value)
                                            @if (is_string($attribute) && $attribute != 'value' && !is_callable($value))
                                                @if ($attribute=='name')
                                                    {{ $attribute }}="{{ $value }}_show[]"
                                                @else
                                                    {{ $attribute }}="{{ $value }}"
                                                @endif
                                            @endif
                                        @endforeach
                                        @if (
                                            (!empty($field['value']) && is_array($field['value']) && ($field['value'][1]->pluck('id')->contains($permission->id)))
                                            || (old($fieldPermission['name']) && in_array($permission->id, old($fieldPermission['name'])))
                                            || $entityRolesPermissions->contains($permission->id))
                                            checked = "checked"
                                            @if ($entityRolesPermissions->contains($permission->id))
                                                disabled = disabled
                                            @endif
                                        @endif >
                                    {{ is_callable($fieldPermission['attribute']) ? $fieldPermission['attribute']($permission) : $permission->{$fieldPermission['attribute']} }} &nbsp;
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-sm-2">
                        <div class="pull-right">
                            <a href="" class="btn btn-default btn-xs" title="Uncheck all">
                                <i class="fa fa-square-o"></i>&nbsp; None
                            </a>
                            &nbsp;
                            <a href="" class="btn btn-default btn-xs" title="Check all">
                                <i class="fa fa-check-square-o"></i>&nbsp; All
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

  </div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <!-- include checklist_dependency js-->
    <script>
        jQuery(document).ready(function($) {

            $('.checklist_dependency').each(function(index, item) {
                var $field = $(this);

                var unique_name = $field.data('entity');

                // Gets the permissions granted by each role
                var rolesPermissions = window[unique_name];

                /**
                 * Handles click on a role
                 */
                $field.find('.primary_list').change(function() {
                    var $input = $(this);
                    var roleId = $input.data('id');

                    // Check
                    if ($input.is(':checked')) {
                        //add hidden field with this value
                        var nameInput = $field.find('.hidden_fields_primary').data('name');
                        var inputToAdd = $('<input type="hidden" class="primary_hidden" name="'+nameInput+'[]" value="'+roleId+'">');
                        $field.find('.hidden_fields_primary').append(inputToAdd);

                        if ($.isArray(rolesPermissions[roleId])) {
                            $.each(rolesPermissions[roleId], function (key, permissionId) {
                                //check and disable secondaries checkboxes
                                $field.find('input.secondary_list[value="' + permissionId + '"]').prop("checked", true).prop("disabled", true);
                                //remove hidden fields with secondary dependency if was setted
                                var hidden = $field.find('input.secondary_hidden[value="' + permissionId + '"]');
                                if (hidden)
                                    hidden.remove();
                            });
                        }
                    }
                    // Uncheck
                    else {
                        // Remove hidden field with this value
                        $field.find('input.primary_hidden[value="'+roleId+'"]').remove();

                        // Uncheck and active secondary checkboxs if are not in other selected primary.
                        var selectedRoles = [];
                        $field.find('input.primary_hidden').each(function(index, input) {
                            selectedRoles.push($(this).val());
                        });

                        if ($.isArray(rolesPermissions[roleId])) {
                            $.each(rolesPermissions[roleId], function (index, permissionId) {

                                // Searches if the permission is granted by another role
                                var inOtherRoles = $.grep(selectedRoles, function (otherRoleId) {
                                    return $.isArray(rolesPermissions[otherRoleId]) && rolesPermissions[otherRoleId].indexOf(permissionId) !== -1;
                                });

                                // If not granted by another role, removes the disabled state and resets to the last checked state
                                if (inOtherRoles.length === 0) {
                                    var $input = $field.find('input.secondary_list[value="' + permissionId + '"]');
                                    $input.prop('checked', $input.data('last-checked-state')).prop('disabled', false);
                                }
                            });
                        }
                    }
                });

                /**
                 * Handles click on a permission
                 */
                $field.find('.secondary_list').each(function() {
                    var $input = $(this);

                    $input.data('last-checked-state', $input.is(':checked') && !$input.is(':disabled'));

                    $input.click(function () {
                        var idCurrent = $input.data('id');

                        if ($input.is(':checked')) {
                            // Add hidden field with this value
                            var nameInput = $field.find('.hidden_fields_secondary').data('name');
                            var inputToAdd = $('<input type="hidden" class="secondary_hidden" name="' + nameInput + '[]" value="' + idCurrent + '">');
                            $field.find('.hidden_fields_secondary').append(inputToAdd);
                        } else {
                            // Remove hidden field with this value
                            $field.find('input.secondary_hidden[value="' + idCurrent + '"]').remove();
                        }

                        $input.data('last-checked-state', $input.is(':checked'));
                    })
                });

            });
        });
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
