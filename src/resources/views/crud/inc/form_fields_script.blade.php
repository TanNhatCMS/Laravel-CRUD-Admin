<script>
    /**
     * A front-end representation of a Backpack field, with its main components.
     *
     * Makes it dead-simple for the developer to perform the most common
     * javascript manipulations, and makes it easy to do custom stuff
     * too, by exposing the main components (name, wrapper, input).
     */
    class CrudField {
        constructor(name) {
            this.name = name;
            this.wrapper = document.querySelector(`[bp-field-name="${this.name}"]`);
            // if no bp-field-main-input has been declared in the field itself,
            // assume it's the first input in that wrapper, whatever it is
            this.input = this.wrapper?.querySelector('[bp-field-main-input], input, textarea, select');

            // Validate that the field has been found
            if(!this.wrapper || !this.input) {
                console.error(`No wrapper or input found for the field ${this.name}`);
            }
        }

        get value() {
            let value = this.input.value;

            // Parse the value if it's a number
            if (value.length && !isNaN(value)) {
                value = Number(value);
            }

            return value;
        }

        change(closure) {
            const fieldChanged = event => {
                const wrapper = this.input.closest('[bp-field-wrapper=true]');
                const name = wrapper.getAttribute('bp-field-name');
                const type = wrapper.getAttribute('bp-field-type');
                const value = this.value;

                closure(event, value, name, type);
            };

            // Change event Listeners
            new MutationObserver(fieldChanged).observe(this.input, { attributes: true });
            this.input.addEventListener('change', fieldChanged, false);

            // Detect input changes only on inputs and textareas
            if(['INPUT', 'TEXTAREA'].includes(this.input.nodeName)) {
                this.input.addEventListener('input', fieldChanged, false);
            }

            fieldChanged();

            return this;
        }

        onChange(closure) {
            this.change(closure);
        }

        show(value = true) {
            this.wrapper.classList.toggle('d-none', !value);
            this.input.dispatchEvent(new CustomEvent(`backpack:field.${value ? 'show' : 'hide'}`, { bubbles: true }));
            // $(this.input).trigger(`backpack:field.${value ? 'show' : 'hide'}`);
            return this;
        }

        hide() {
            return this.show(false);
        }

        enable(value = true) {
            if(value) {
                this.input.removeAttribute('disabled');
            } else {
                this.input.setAttribute('disabled', 'disabled');
            }
            this.input.dispatchEvent(new CustomEvent(`backpack:field.${value ? 'enable' : 'disable'}`, { bubbles: true }));
            // $(this.input).trigger(`backpack:field.${value ? 'enable' : 'disable'}`);
            return this;
        }

        disable() {
            return this.enable(false);
        }

        require(value = true) {
            this.wrapper.classList.toggle('required', value);
            this.input.dispatchEvent(new CustomEvent(`backpack:field.${value ? 'require' : 'unrequire'}`, { bubbles: true }));
            // $(this.input).trigger(`backpack:field.${value ? 'require' : 'unrequire'}`);
            return this;
        }

        unrequire() {
            return this.require(false);
        }

        check(value = true) {
            let checkbox = this.wrapper.querySelector('input[type=checkbox]');
            checkbox.checked = value;
            checkbox.dispatchEvent(new Event('change'));
            return this;
        }

        uncheck() {
            return this.check(false);
        }
    }

    /**
     * Window functions that help the developer easily select one or more fields.
     */
    window.crud = {
        ...window.crud,

        // Create a field from a given name
        field: name => new CrudField(name),

        // Create all fields from a given name list
        fields: names => names.map(window.crud.field),
    };
</script>