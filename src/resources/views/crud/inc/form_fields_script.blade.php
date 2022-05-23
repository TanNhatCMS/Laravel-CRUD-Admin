<script>
    /**
     * A front-end representation of a Backpack field, with its main components.
     *
     * Makes it dead-simple for the developer to perform the most common
     * javascript manipulations, and makes it easy to do custom stuff
     * too, by exposing the main components (name, wrapper, input).
     */
    class CrudField {
        constructor(fieldName, subfieldHolder = false, rowNumber = false) {
            this.isSubfield = false;
            this.name = fieldName;
            let wrapperSearchString = '[data-repeatable-identifier="'+subfieldHolder+'"]';
            wrapperSearchString = rowNumber ? wrapperSearchString+'[data-row-number="'+rowNumber+'"]' : wrapperSearchString;

            if(subfieldHolder) {
                this.wrapper = $(wrapperSearchString).children('[bp-field-wrapper][bp-field-name$="'+this.name+'"]');
                this.subfieldHolder = subfieldHolder;
                this.isSubfield = true;
            }
            
            this.wrapper = this.wrapper ?? $('[bp-field-name="'+ this.name +'"]');
            this.input = this.wrapper.closest("[bp-field-main-input]");
            // if no bp-field-main-input has been declared in the field itself,
            // assume it's the first input in that wrapper, whatever it is
            if (this.input.length == 0) {
                this.input = this.wrapper.find('[data-row-number="'+rowNumber+'"][data-repeatable-input-name$="'+this.name+'"], input, textarea, select').first();
            }
            
            this.value = this.input.val();
        }

        change(closure) {

            if(this.isSubfield) {
                window.crud.subfieldsCallbacks =  window.crud.subfieldsCallbacks ?? new Array();
                window.crud.subfieldsCallbacks[this.subfieldHolder] = window.crud.subfieldsCallbacks[this.subfieldHolder] ?? new Array();
                if(!window.crud.subfieldsCallbacks[this.subfieldHolder].some( callbacks => callbacks['fieldName'] === this.name )) {
                    window.crud.subfieldsCallbacks[this.subfieldHolder].push({fieldName:  this.name, closure: closure});
                }
                return this;
            }

            this.input.change(function(event) {
                var fieldWrapper = $(this).closest('[bp-field-wrapper=true]');
                var fieldName = fieldWrapper.attr('bp-field-name');
                var fieldType = fieldWrapper.attr('bp-field-type');
                var fieldValue = $(this).val();

                // console.log('Changed field ' + fieldName + ' (type '+ fieldType + '), value is now ' + fieldValue);
                closure(event, fieldValue, fieldName, fieldType);
            }).change();

            return this;
        }

        onChange(closure) {
            this.change(closure);
        }

        hide(e) {
            this.wrapper.hide();
            this.input.trigger('backpack:field.hide');
            return this;
        }

        show(e) {
            this.wrapper.show();
            this.input.trigger('backpack:field.show');
            return this;
        }

        enable(e) {
            this.input.removeAttr('disabled');
            this.input.trigger('backpack:field.enable');
            return this;
        }

        disable(e) {
            this.input.attr('disabled', 'disabled');
            this.input.trigger('backpack:field.disable');
            return this;
        }

        require(e) {
            this.wrapper.removeClass('required').addClass('required');
            this.input.trigger('backpack:field.require');
            return this;
        }

        unrequire(e) {
            this.wrapper.removeClass('required');
            this.input.trigger('backpack:field.unrequire');
            return this;
        }

        check(e) {
            this.wrapper.find('input[type=checkbox]').prop('checked', true).trigger('change');
            return this;
        }

        uncheck(e) {
            this.wrapper.find('input[type=checkbox]').prop('checked', false).trigger('change');
            return this;
        }
    }

    /**
     * Window functions that help the developer easily select one or more fields.
     */
    window.crud = {
        field: function(fieldName) {
            return new CrudField(fieldName);
        },
        fields: function(fieldNamesArray) {
            return fieldNamesArray.map(function(fieldName) {
                return new CrudField(fieldName);
            });
        },
        subfield: function(field, repeatableName, rowNumber = false) {
            return new CrudField(fields, repeatableName, rowNumber);
        },
        subfields: function(fields, repeatableName, rowNumber = false) {
            return fields.map(function(fieldName) {
                return new CrudField(fieldName, repeatableName, rowNumber);
            });  
        }
    }
</script>
