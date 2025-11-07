
/**
 * ACPT Back-end Live conditional rendering
 */
class ACPTConditionalRules
{
    constructor(page, action, belongsTo, elementId)
    {
        this.#init();
        this.action = action;
        this.page = page;
        this.belongsTo = belongsTo;
        this.elementId = elementId;
        this.#hideBoxesIfNotVisible();
    }

    /**
     * Init the values
     */
    #init()
    {
        this.formElements = document.body.querySelectorAll('.acpt-form-element');
        this.metaBoxes = document.body.querySelectorAll('.acpt-metabox');
        this.elements = document.body.querySelectorAll('[data-conditional-rules-id]');
        this.values = [];
        this.checks = [];

        this.elements.forEach((element) => {

            const value = this.#getValue(element);
            const id = element.name;
            const formId = element.getAttribute("data-conditional-rules-id");
            const forgedBy = element.getAttribute("data-conditional-rules-field-forged-by");
            const fieldIndex = element.getAttribute("data-conditional-rules-field-index");
            const blockIndex = element.getAttribute("data-conditional-rules-block-index");
            const blockName = element.getAttribute("data-conditional-rules-block-name");

            // if is checkbox
            if(element.type === 'checkbox' && id.includes("[]")){
                if(element.checked){
                    this.values.push({
                        id: id.replace("[]", ""),
                        formId: formId,
                        value: value,
                        forgedBy: forgedBy,
                        fieldIndex: fieldIndex,
                        blockIndex: blockIndex,
                        blockName: blockName
                    });
                }
            } else {
                this.values.push({
                    id: id,
                    formId: formId,
                    value: value,
                    forgedBy: forgedBy,
                    fieldIndex: fieldIndex,
                    blockIndex: blockIndex,
                    blockName: blockName
                });
            }
        });
    }

    /**
     * Save checks in browser's cache ONLY
     * when the form is saved
     *
     * @param event
     */
    #saveChecksInCache(event){
        let isSubmitted = false;

        switch (this.action){
            case "save-option-page":
                isSubmitted = event.target.id === 'save-option-page';
                break;

            case "add-tax":
            case "save-user":
                isSubmitted = event.target.id === 'submit';
                break;

            case "edit-tax":
                isSubmitted = event.target.classList.contains('button-primary');
                break;

            case "save-cpt":
                isSubmitted = event.target.classList.contains('editor-post-publish-button') || event.target.id === 'publish';
                break;
        }

        if(isSubmitted){
            this.#saveInCache(this.checks);
        }
    }

    /**
     * Change input value handler
     * @param event
     */
    #changeValueHandler(event){

        if(!this.#isValidKey(event)){
            return;
        }

        const element = event.target;
        const id = element.name;
        const formId = element.getAttribute("data-conditional-rules-id");
        const forgedBy = element.getAttribute("data-conditional-rules-field-forged-by");
        const fieldIndex = element.getAttribute("data-conditional-rules-field-index");
        const blockIndex = element.getAttribute("data-conditional-rules-block-index");
        const blockName = element.getAttribute("data-conditional-rules-block-name");

        const value = this.#getValue(element);

        let elementIndex = this.values.findIndex((el) => {
            return el.id === id && el.formId === formId;
        });

        // radio fields
        if(element.type === 'radio') {
            if (element.checked) {
                this.values[elementIndex] = {
                    id: id,
                    formId: formId,
                    value: value,
                    forgedBy: forgedBy,
                    fieldIndex: fieldIndex,
                    blockIndex: blockIndex,
                    blockName: blockName
                };
            }
        }

        // checkbox fields (only Back-end)
        else if(element.type === 'checkbox' && id.includes("[]")) {
            if (element.checked) {
                this.values.push({
                    id: id.replace("[]", ""),
                    formId: formId,
                    value: value,
                    forgedBy: forgedBy,
                    fieldIndex: fieldIndex,
                    blockIndex: blockIndex,
                    blockName: blockName
                });
            } else {

                // remove from this.values
                let newValues = [];

                this.values.map((el) => {
                    if(el.formId !== formId){
                        newValues.push(el);
                    } else {
                        if(value !== el.value){
                            newValues.push(el);
                        }
                    }
                });

                this.values = newValues;
            }
        } else {
            this.values[elementIndex] = {
                id: id,
                formId: formId,
                value: value,
                forgedBy: forgedBy,
                fieldIndex: fieldIndex,
                blockIndex: blockIndex,
                blockName: blockName
            };
        }

        this.#applyIsVisible();
    }

    /**
     *
     * @param event
     * @return {boolean}
     */
    #isValidKey(event) {

        if (event.ctrlKey) {
            return false;
        }

        if (event.shiftKey) {
            return false;
        }

        const key = event.key;

        const keysToSkip = [
            "ArrowUp",
            "ArrowDown",
            "Delete",
            "Enter",
        ];

        return !keysToSkip.includes(key);
    };

    /**
     * Run the checks
     */
    run()
    {
        // listen to those events in order to re-init props and values
        const events = [
            'acpt_flexible_element_added',
            'acpt_grouped_element_added',
            'acpt_grouped_element_removed',
            'acpt_media_added',
        ];

        events.map((event) => {
            document.addEventListener(event, (e) => {
                this.#init();
                this.#listenForChangeEvents();
            });
        });

        this.#listenForChangeEvents();

        document.addEventListener("click", (e) => this.#saveChecksInCache(e));
    }

    /**
     * Loop form elements and listen for change/keyup events
     */
    #listenForChangeEvents()
    {
        this.elements.forEach((element) => {
            ["change", "keyup"].map((ev)=>{
                element.addEventListener(ev, this.#debounce((e) => {
                    this.#changeValueHandler(e);
                }, 1000));
            });
        });
    }

    /**
     * Hide meta boxes if no element inside is visible
     */
    #hideBoxesIfNotVisible()
    {
        // hide meta boxes if no element is visible
        this.metaBoxes.forEach((metaBox) => {

            let visibleFieldsTotalCount = 0;
            const rows = metaBox.querySelectorAll(".acpt-admin-meta-row");

            rows.forEach((row) => {

                let visibleFieldsRowCount = 0;
                const fields = row.querySelectorAll(".acpt-admin-meta-wrapper");

                fields.forEach((field) => {
                    if(!field.classList.contains("hidden")){
                        visibleFieldsTotalCount++;
                        visibleFieldsRowCount++;
                    }
                });

                if(visibleFieldsRowCount === 0){
                    row.classList.add("hidden");
                } else {
                    row.classList.remove("hidden");
                }
            });

            if(visibleFieldsTotalCount === 0){

                // Attachments meta boxes
                const boxLabel = metaBox.getAttribute("data-box-label");

                if(boxLabel){
                    const tr = metaBox.closest(`.compat-field-${boxLabel}`);

                    if(tr){
                        tr.remove();
                    }
                }

                // Option pages
                const optionPageButtons = document.getElementById("acpt-option-page-buttons");

                if(optionPageButtons){
                    optionPageButtons.remove();
                }

                metaBox.remove();
            }
        });
    }

    /**
     *
     * @return {Promise<void>}
     */
    async #applyIsVisible()
    {
        let formData;
        const baseAjaxUrl = (typeof ajaxurl === 'string') ? ajaxurl : '/wp-admin/admin-ajax.php';

        formData = new FormData();
        formData.append('action', 'checkIsVisibleAction');
        formData.append('data', JSON.stringify({
            values: this.values,
            elementId: this.elementId,
            belongsTo: this.belongsTo,
        }));

        fetch(baseAjaxUrl, {
            method: 'POST',
            body: formData
        })
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            this.#applyConditions(data);
            this.checks = data;

            return data;
        });
    }

    /**
     *
     * @param func
     * @param timeout
     * @return {function(...[*]=)}
     */
    #debounce(func, timeout = 300){
        let timer;

        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }

    /**
     *
     * @param element
     * @return {number}
     */
    #getValue = (element) => {

        // is toggle element
        if(element.type === 'checkbox' && element.value === "1"){
            return element.checked ? 1 : 0;
        }

        return element.value;
    };

    /**
     * Apply conditions
     * @param data
     */
    #applyConditions = (data) => {
        for (const [key, value] of Object.entries(data)) {

            switch (typeof value) {
                case "boolean":

                    /**
                     *
                     * @return {null|*}
                     */
                    const el = () => {

                        // back-end elements
                        if(document.getElementById(key)){
                            return document.getElementById(key);
                        }

                        // form elements
                        if(document.querySelector(`[data-field-id="${key}"]`)){
                            return document.querySelector(`[data-field-id="${key}"]`);
                        }

                        return null;
                    };

                    if(el()){
                        if(value === false){
                            el().classList.add("hidden");
                        } else {
                            el().classList.remove("hidden");
                        }
                    }
                    break;

                case "object":

                    const elmts = () => {

                        // back-end elements
                        if(document.querySelectorAll(`[data-id="${key}"]`) && document.querySelectorAll(`[data-id="${key}"]`).length > 0){
                            return document.querySelectorAll(`[data-id="${key}"]`);
                        }

                        // form elements
                        if(document.querySelectorAll(`[data-field-id="${key}"]`) && document.querySelectorAll(`[data-field-id="${key}"]`).length > 0){
                            return document.querySelectorAll(`[data-field-id="${key}"]`);
                        }

                        return [];
                    };

                    if(elmts().length > 0){
                        elmts().forEach((element, index) => {

                            // repeater fields
                            if(typeof value[index] !== 'undefined'){
                                if(value[index] === false){
                                    element.classList.add("hidden");
                                } else {
                                    element.classList.remove("hidden");
                                }
                            }
                            // flexible fields
                            else {
                                const field = element.querySelector(".acpt-admin-meta-field-input");

                                if(field){
                                    const fieldIndex = field.getAttribute("data-conditional-rules-field-index");
                                    const blockIndex = field.getAttribute("data-conditional-rules-block-index");
                                    const blockName = field.getAttribute("data-conditional-rules-block-name");

                                    if(value[blockName] && typeof value[blockName][blockIndex][fieldIndex] !== 'undefined'){
                                        if(value[blockName][blockIndex][fieldIndex] === false){
                                            element.classList.add("hidden");
                                        } else {
                                            element.classList.remove("hidden");
                                        }
                                    }
                                }
                            }
                        });
                    }

                    break;
            }
        }

        this.#hideBoxesIfNotVisible();
    };

    /**
     * ========================================
     * CACHE SECTION
     * ========================================
     */

    /**
     *
     * @return {string}
     */
    #cacheKey = () => {
        return `acpt_conditional_rules_cache_${this.page}`;
    };

    /**
     * Flush the cache
     */
    #flushCache = () => {
        localStorage.removeItem(this.#cacheKey());
    };

    /**
     * Save elements in the browser's cache
     * only if the form is submitted
     * @param data
     */
    #saveInCache = (data) => {
        localStorage.setItem(this.#cacheKey(), JSON.stringify(data));
    };

    /**
     * retrieve elements from the browser's cache
     * @return {null|any}
     */
    #fromCache = () => {
        const retrievedObject = localStorage.getItem(this.#cacheKey());

        if(retrievedObject){
            return JSON.parse(retrievedObject)
        }

        return null;
    };
}

