import {$$, debounce, removeCookie, setCookie} from "./_admin_commons.js";

var $ = jQuery.noConflict();

/**
 * Run misc functions
 */
export const runMiscFunctions = () => {

    /**
     *
     * @param metaBox
     * @return {string}
     */
    const geyMetaBoxVisibilityKey = (metaBox) => {

        let key = '';
        const list = metaBox.classList;
        for (let x of list.values()) {
            if(x !== "closed"){
                key += x+"_";
            }
        }

        return `${key}closed`;
    };

    /**
     * Save meta box status in LS
     */
    $('.acpt-metabox .toggle-indicator').on('click', function(){

        const $this = $(this);

        if($this.closest(".acpt-metabox").length > 0){
            const metaBox = $this.closest(".acpt-metabox")[0];
            const metaBoxKey = geyMetaBoxVisibilityKey(metaBox);
            const domain = window.location.hostname;
            const path = "/";

            if(metaBox.classList.contains("closed")){
                removeCookie(metaBoxKey, path, domain);
            } else {
                setCookie(metaBoxKey, "1", 30, {
                    path: path,
                    domain: domain,
                    SameSite: "none",
                    Secure: true
                });
            }
        }
    });

    /**
     * Option pages
     */
    $('.acpt-toggle-indicator').on('click', function () {
        const target = $(this).data('target');
        $(`#${target}`).toggleClass('closed');
    });

    /**
     * Input range
     */
    $('.acpt-range').on('change', function () {
        const id = $(this).attr('id');
        const value = $(this).val();

        $(`#${id}_value`).text(value);
    });

    /**
     * Add 'multipart/form-data' type to comment form
     */
    if($('#commentform').length){
        $('#commentform')[0].encoding = 'multipart/form-data';
    }

    /**
     * Reset all extra fields on Add Tag forms
     */
    const resetAddTagFields = () => {

        // blank all preview divs
        const previewDivs = [
            'image-preview',
            'file-preview',
            'preview-file',
            'acpt_map_preview',
            'acpt_map_multi_preview',
            'embed-preview',
        ];

        // extra fields to be blanked
        const extraFields = [
            'attachment_id',
            'city',
            'country',
            'currency',
            'dial',
            'forged_by',
            'label',
            'lat',
            'length',
            'lng',
            'weight',
        ];

        previewDivs.forEach(function(div){
            $(`#addtag .${div}`).html("");
        });

        const inputs = $(`#addtag`).find("input");

        inputs.each(function(){

            const input = $(this);
            const inputName = input.attr("name");

            if(inputName){
                extraFields.forEach(function(extraField){
                    let result = inputName.endsWith(`_${extraField}`);

                    if(result == true){
                        input.val('');
                    }
                });
            }
        });
    };

    /**
     * Reset fields when saving a new tag
     */
    $('#addtag').on('mousedown', '#submit', function () {

        resetAddTagFields();

        //
        // This is a fix for creating a new term with editor fields associated
        //
        // This fix was taken from: https://github.com/sheabunge/visual-term-description-editor/blob/master/src/php/class-editor.php
        //
        if(typeof tinyMCE !== 'undefined'){
            tinyMCE.triggerSave();

            $(document).bind('ajaxSuccess.vtde_add_term', function () {
                if (tinyMCE.activeEditor) {
                    tinyMCE.activeEditor.setContent('');
                }

                $(document).unbind('ajaxSuccess.vtde_add_term', false);
            });
        }
    });

    /*========== TOGGLE INPUT ==========*/
    $('.wppd-ui-toggle').on( 'change', function () {
        const valId = $(this).attr('id');
        $('#'+valId).val(($(this).is(':checked')) ? 1 : 0 );
    });

    /*========== CURRENCY SELECTOR ==========*/
    $(".currency-selector").on("change", function () {

        const selected = $(this).find( "option:selected" );
        const amount = $(this).parent('div').prev();
        const symbol = amount.prev();

        symbol.text(selected.data("symbol"));
        amount.prop("placeholder", selected.data("placeholder"));
    });

    /*========== ADDRESS FIELDS COMMON FUNCTIONS ==========*/

    $('.acpt-reset-map').on('click', function (e) {

        e.preventDefault();

        const $this = $(this);
        const parentField = $this.parent();
        let fieldId;

        parentField.find('input').each(function(){
            const $this = $(this);
            const id = $this.attr('id');
            const type = $this.attr('type');

            if(id){
                if(type === 'text'){
                    fieldId = id;
                }

                $this.val('');
            }
        });

        if(fieldId){
            const event = new CustomEvent(
                "acpt-reset-map",
                {
                    detail: {
                        fieldId: fieldId+"_map"
                    }
                }
            );

            document.dispatchEvent(event);
        }
    });

    /*========== TABS, ACCORDIONS ==========*/

    $('.acpt-admin-horizontal-tab').on('click', function (e) {
        e.preventDefault();

        const $this = $(this);
        const parentTabs = $this.parent();
        const target = $this.data('target');
        const targetPanels = $(`#${target}`).parent();

        parentTabs.children().removeClass('active');
        targetPanels.children().removeClass('active');

        $(`#${target}`).addClass('active');
        $this.addClass('active');
    });

    $('.acpt-admin-vertical-tab').on('click', function (e) {
        e.preventDefault();

        const $this = $(this);
        const parentTabs = $this.parent();
        const target = $this.data('target');
        const targetPanels = $(`#${target}`).parent();

        parentTabs.children().removeClass('active');
        targetPanels.children().removeClass('active');

        $(`#${target}`).addClass('active');
        $this.addClass('active');
    });

    $('.acpt-admin-accordion-title').on('click', function (e) {
        e.preventDefault();

        const $this = $(this);
        const parent = $this.parent('div');
        const parentWrapper = parent.parent('div');
        const isAlreadyActive = parent.hasClass('active');

        parentWrapper.children().each(function () {
            $(this).removeClass('active');
        });

        if(isAlreadyActive){
            parent.removeClass('active');
        } else {
            parent.addClass('active');
        }
    });
}

/**
 * Coremirror
 * @see https://codemirror.net/docs/
 */
export const initCodeMirror = (idSelector = null) => {

    try {
        let selector = 'textarea.acpt-codemirror';
        if(idSelector){
            selector = `#${idSelector}`;
        }

        if($$(selector).length){
            $$(selector).each(function() {
                const id = '#'+ $( this ).attr('id');

                if(typeof wp !== 'undefined'){

                    // override CodeMirror default settings
                    const editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};
                    editorSettings.codemirror = _.extend(
                        {},
                        editorSettings.codemirror,
                        {
                            lineNumbers: true,
                            indentUnit: 2,
                            tabSize: 2,
                            mode: 'htmlmixed',
                        }
                    );
                    const wpEditor = wp.codeEditor.initialize( $$(id), editorSettings );

                    $(document).on('keyup', '.CodeMirror-code', function(){
                        $(id).html(wpEditor.codemirror.getValue());
                        $(id).trigger('change');
                    });

                } else if(typeof CodeMirror === 'function') {

                    CodeMirror.fromTextArea(document.getElementById($( this ).attr('id')), {
                        lineNumbers: true,
                        indentUnit: 2,
                        tabSize: 2,
                        mode: 'htmlmixed',
                    });
                }
            });
        }
    } catch (e) {
        console.error(e);
    }
};

/**
 * selectize
 * @see https://selectize.dev/docs/api
 */
export const initSelectize = (id = null) => {
    try {
        if(jQuery().selectize) {

            const formatSelectizeItem = (item, escape) => {

                const relation_label_separator =  "<-------->";

                if(!item.text.includes(relation_label_separator)){

                    if(item.image){
                        return `<div class="selectize-item"><img src="${item.image}" alt="fdfd" width="50" /><div class="selectize-details"><span>${item.text}</span></div></div>`;
                    }

                    return `<div>${item.text}</div>`;
                }

                let explode = item.text.split(relation_label_separator);
                const thumbnail = explode[0];
                const cpt = explode[1];
                const label = explode[2];
                const thumbnailDiv = (thumbnail) ? `<div class="selectize-thumbnail"><img src="${thumbnail}" alt="${label}" width="40" /></div>` : `<div class="selectize-thumbnail"><span class="selectize-thumbnail-no-image"></span></div>`;

                return `<div class="selectize-item">${thumbnailDiv}<div class="selectize-details"><span class='acpt-badge'>${cpt}</span><span>${label}</span></div></div>`;
            };

            let selector = `.acpt-select2`;
            if(id){
                selector = `#${id}`;
            }

            const selectElements = $$(selector).get();

            selectElements.forEach((el) => {

                const element = $(el);

                // if the element is already selectized, skip
                if(element.hasClass("selectized")){
                    return;
                }

                /**
                 * init selectize element
                 */
                const initSelectizeElement = (max = null) => {

                    let options = {
                        plugins: ["restore_on_backspace", "clear_button", "remove_button"],
                        placeholder: '--Select--',
                        onChange: function() {
                            this.$input[0].dispatchEvent(new Event("change")) // dispatch change event on change
                        },
                        render: {
                            option: function(option, escape) {
                                return formatSelectizeItem(option, escape);
                            },
                            item: function(item, escape) {
                                return formatSelectizeItem(item, escape);
                            }
                        },
                    };

                    if(max){
                        options[maxItems] = max;
                    }

                    element.selectize(options);
                    element.siblings(".acpt-placeholder").remove();
                };

                // populate the <select> with AJAX
                if(element.hasClass("acpt-select2-ajax")){

                    const fieldType = element.data("field-type");
                    const toType = element.data("to-type");
                    const toValue = element.data("to-value");
                    const postType = element.data("post-type");
                    const postStatus = element.data("post-status");
                    const postTaxonomy = element.data("post-taxonomy");
                    const termTaxonomy = element.data("term-taxonomy");
                    const userRole = element.data("user-role");
                    const defaultValues = element.data("default-values");
                    const layout = element.data("layout");
                    const min = element.data("min");
                    const max = element.data("max");

                    $.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            "action": "generateRelationalFieldOptionsAction",
                            "data": JSON.stringify({
                                "id": id,
                                "fieldType": fieldType,
                                "toType": toType,
                                "toValue": toValue,
                                "postType": postType,
                                "postStatus": postStatus,
                                "postTaxonomy": postTaxonomy,
                                "termTaxonomy": termTaxonomy,
                                "userRole": userRole,
                                "defaultValues": defaultValues,
                                "layout": layout,
                                "format": "data"
                            }),
                        },
                        success: function(data) {

                            data.fields && data.fields.map((item) => {

                                const label = item.label;

                                if(item.value){
                                    element.append($('<option>', {
                                        value: item.value,
                                        text: item.label,
                                        selected: item.selected,
                                    }));
                                } else if(item.options){
                                    const optgroup = $('<optgroup>', {
                                        label: label
                                    });

                                    item.options.map((option) => {
                                        optgroup.append($('<option>', {
                                            value: option.value,
                                            text: option.label,
                                            selected: option.selected,
                                        }));
                                    });

                                    element.append(optgroup);
                                }
                            });

                            initSelectizeElement(max);
                        },
                        dataType: 'json'
                    });
                } else {
                    initSelectizeElement();
                }
            });


        }
    } catch (e) {
        console.error(e);
    }
};

/**
 * Init intlTelInput picker
 *
 * @param idSelector
 */
export const initIntlTelInput = (idSelector = null) => {
    try {
        let selector = '.acpt-phone';
        if(idSelector){
            selector = `#${idSelector}`;
        }

        const phoneElements = $$(selector).get();

        phoneElements.forEach((phoneElement) => {
            if(typeof intlTelInput !== 'undefined' && typeof phoneElement !== 'undefined'){

                const country = phoneElement.previousSibling;
                const dialCode = country.previousSibling;
                const utilsPath = dialCode.previousSibling;

                const iti = intlTelInput(phoneElement, {
                    initialCountry: country.value,
                    separateDialCode: true,
                    geoIpLookup: callback => {
                        fetch("https://ipapi.co/json")
                            .then(res => res.json())
                            .then(data => callback(data.country_code))
                            .catch(() => callback("us"));
                    },
                    loadUtils: () => import(utilsPath.value),
                });

                // on change country
                phoneElement.addEventListener("countrychange", function(e) {
                    const countryData = iti.getSelectedCountryData();

                    if(countryData.iso2 && countryData.dialCode){
                        country.value = countryData.iso2;
                        dialCode.value = countryData.dialCode;
                    }
                });
            }
        });
    } catch (e) {
        console.error(e);
    }
};

/**
 * Init Country select
 * @see https://github.com/mrmarkfrench/country-select-js
 *
 * @param idSelector
 */
export const initCountrySelect = (idSelector = null) => {

    try {
        let selector = '.acpt-country';
        if(idSelector){
            selector = `#${idSelector}`;
        }

        const countryElement = $$(selector);

        if(countryElement.length){
            const isoCodeElement = countryElement.prev();

            countryElement.countrySelect({
                defaultCountry: isoCodeElement.val(),
                preferredCountries: [],
            });

            countryElement.on("change",(e) => {
                const countryData = countryElement.countrySelect("getSelectedCountryData");
                isoCodeElement.val(countryData.iso2);
            });
        }
    } catch (e) {
        console.error(e);
    }
};

/**
 * Init textarea counter
 * @param idSelector
 */
export const initTextarea = (idSelector = null) => {
    try {
        let selector = '.acpt-textarea';
        if(idSelector){
            selector = `#${idSelector}`;
        }

        const textareaElement = $$(selector);
        const textareaCounter = textareaElement.next('.acpt-textarea-ch-counter');

        if(textareaElement.length && textareaCounter.length){
            textareaElement.on("keyup", function(e){

                const value = e.target.value.length;
                const count = textareaCounter.find(".count");
                const max = parseInt(textareaCounter.data("max"));
                const min = parseInt(textareaCounter.data("min"));

                if(value < min || value >= max){
                    count.removeClass("warning");
                    count.addClass('danger');
                } else if(value >= max-5){
                    count.removeClass("danger");
                    count.addClass('warning');
                } else {
                    count.removeClass("warning");
                    count.removeClass("danger");
                }

                count.text(value);
            });
        }

    } catch (e) {
        console.error(e);
    }
};

/**
 * Init image slider
 * @param idSelector
 */
export const initImageSlider = (idSelector = null) => {

    let selector = '.acpt-image-slider';
    if(idSelector){
        selector = `#${idSelector}`;
    }

    const sliderElement = $$(selector);

    if(sliderElement.length){
        sliderElement.each(function( index ) {
            const $this = $(this);
            const slider = $this.find(".slider");

            slider.on("input", function (e) {
                $this[0].style.setProperty('--position', `${e.target.value}%`);
            });
        });
    }
};

/**
 * Init embed fields
 * @param idSelector
 */
export const initEmbed = (idSelector = null) => {

    try {
        let selector = '.acpt-embed';
        if(idSelector){
            selector = `#${idSelector}`;
        }

        const embedElement = $$(selector);

        if(embedElement.length){

            const preview = embedElement.next(".embed-preview");

            embedElement.on("keyup", debounce((e) => {

                const value = e.target.value;

                if(value === ""){
                    preview.html("");
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        "action": "generateEmbedAction",
                        "data": JSON.stringify({
                            "value": value,
                        }),
                    },
                    success: function(data) {
                        if(preview){
                            preview.html(`
                                <div class="embed">
                                    ${data.embed}
                                </div>
                            `);
                        }
                    },
                    error: function(error){
                        if(preview){
                            preview.html("");
                        }
                    },
                    dataType: 'json'
                });
            }, 1000));
        }

    } catch (e) {
        console.error(e);
    }
};

/**
 * Init Barcode
 * @param idSelector
 */
export const initBarcode = (idSelector = null) => {
    try {
        let selector = '.acpt-barcode-wrapper';
        if(idSelector){
            selector = `#${idSelector}`;
        }

        const BarcodeElement = $$(selector);

        if(typeof JsBarcode === "function" && BarcodeElement.length){
            BarcodeElement.each(function( index ) {

                const $this = $(this);
                const value = $this.find(".value");
                const format = $this.find(".format");
                const color = $this.find(".color");
                const bgColor = $this.find(".bgColor");
                const clearButton = $this.find(".clear-barcode");
                const barcodeSvg = $this.find(".acpt-barcode-svg");
                const barcodeErrors = $this.find(".acpt-barcode-errors");
                const barcodeSvgId = barcodeSvg.attr('id');
                const barcodeValueInput = $(`#barcode_value_${barcodeSvgId.replace("acpt-barcode-", "")}`);

                /**
                 * Clear the UI
                 */
                const clearUI = () => {
                    barcodeSvg.html(`<svg class="acpt-barcode" id="${barcodeSvgId}"></svg>`);
                };

                const clearErrors = () => {
                    $(`#acpt-${barcodeSvgId}`).removeClass("has-errors");
                    barcodeErrors.html(``);
                };

                const addError = (err) => {
                    $(`#acpt-${barcodeSvgId}`).addClass("has-errors");
                    barcodeErrors.html(err);
                };

                /**
                 * Clear the form
                 */
                const clearForm = () => {
                    clearUI();
                    value.val('');
                    format.val("code128");
                    color.val('#000');
                    bgColor.val('#fff');
                    barcodeValueInput.val('');
                };

                /**
                 *
                 * @param text
                 * @return {*}
                 */
                function escapeHtml(text) {
                    var map = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    };

                    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
                }

                /**
                 * Generate Barcode and populate the form
                 */
                const generateBarcode = () => {

                    try {
                        clearErrors();

                        JsBarcode(`#${barcodeSvgId}`, value.val(), {
                            format: format.val() ? format.val() : "code128",
                            background: bgColor.val() ? bgColor.val() : "#ffffff",
                            lineColor: color.val() ? color.val() : "#000000",
                        });

                        const barcodeValue = {
                            svg: escapeHtml($(`#${barcodeSvgId}`)[0].outerHTML),
                            format: format.val() ? format.val() : "code128",
                            bgColor: bgColor.val() ? bgColor.val() : "#ffffff",
                            color: color.val() ? color.val() : "#000000",
                        };

                        barcodeValueInput.val(JSON.stringify(barcodeValue));
                    } catch (e) {
                        clearForm();
                        addError(`The value "${value.val()}" is not valid for ${format.val()} format`);
                    }
                };

                // clear the form
                clearButton.on("click", function(e){
                    e.preventDefault();
                    clearForm();
                });

                value.on("keyup", debounce((e) => {
                    generateBarcode();
                }, 1000));

                format.on("change", function(e){
                    generateBarcode();
                });

                color.on("acpt-icon-picker", debounce((e) => {
                    generateBarcode();
                }, 1000));

                bgColor.on("acpt-icon-picker", debounce((e) => {
                    generateBarcode();
                }, 1000));
            });
        }

    } catch (e) {
        console.error(e);
    }
};

/**
 * Init QR Code generator
 * @see https://github.com/davidshimjs/qrcodejs
 *
 * @param idSelector
 */
export const initQRCodeGenerator = (idSelector = null) => {
    try {
        let selector = '.acpt-qr-code-wrapper';
        if(idSelector){
            selector = `#${idSelector}`;
        }

        const QRCodeGeneratorElement = $$(selector);

        if(typeof QRCode === "function" && QRCodeGeneratorElement.length){

            QRCodeGeneratorElement.each(function( index ) {

                const $this = $(this);
                const url = $this.find(".url");
                const resolution = $this.find(".resolution");
                const colorDark = $this.find(".color-dark");
                const colorLight = $this.find(".color-light");
                const clearButton = $this.find(".clear-qr-code");
                const QRCodeImage = $this.find(".acpt-qr-code");
                const QRCodeImageId = QRCodeImage.attr('id');
                const QRCodeValueInput = $(`#qr_code_value_${QRCodeImageId.replace("acpt-qr-code-", "")}`);

                /**
                 * Clear the UI
                 */
                const clearUI = () => {
                    QRCodeImage.html("");
                };

                /**
                 * Clear the form
                 */
                const clearForm = () => {
                    clearUI();
                    url.val('');
                    resolution.val(100);
                    colorDark.val('#000');
                    colorLight.val('#fff');
                    QRCodeValueInput.val('');
                };

                /**
                 *
                 * @return {jQuery|*|boolean}
                 */
                const isURLValid = () => {
                    return url.is(':valid') && url.val() !== "";
                };

                /**
                 * Generate QR Code and populate the form
                 */
                const generateQRCode = () => {

                    if (!isURLValid()) {
                        clearForm();
                        return;
                    }

                    clearUI();
                    const element = document.getElementById(QRCodeImageId);

                    const qrCode = new QRCode(element, {
                        text: url.val(),
                        width: resolution.val(),
                        height: resolution.val(),
                        colorDark : colorDark.val() ? colorDark.val() :"#000000",
                        colorLight : colorLight.val() ? colorLight.val() : "#ffffff",
                        correctLevel : QRCode.CorrectLevel.H
                    });

                    const QRCodeImageSrc = element.children[0].toDataURL("image/png");

                    const QRCodeValue = {
                        img: QRCodeImageSrc,
                        resolution: resolution.val(),
                        colorDark : colorDark.val() ? colorDark.val() :"#000000",
                        colorLight : colorLight.val() ? colorLight.val() : "#ffffff"
                    };

                    QRCodeValueInput.val(JSON.stringify(QRCodeValue));
                };

                // clear the form
                clearButton.on("click", function(e){
                    e.preventDefault();
                    clearForm();
                });

                url.on("keyup", debounce((e) => {
                    generateQRCode();
                }, 1000));

                resolution.on("change", function(e){
                    generateQRCode();
                });

                colorDark.on("acpt-icon-picker", debounce((e) => {
                    generateQRCode();
                }, 1000));

                colorLight.on("acpt-icon-picker", debounce((e) => {
                    generateQRCode();
                }, 1000));
            });
        }

    } catch (e) {
        console.error(e);
    }
};

/**
 * re-init form validator
 */
export const initValidator = () => {
    if(typeof ACPTFormValidator === 'function'){

        let action = null;
        switch (adminpage) {
            case "user-edit-php":
            case "edit-tags-php":
                action = "add-tax";
                break;

            case "post-php":
                action = "save-cpt";
                break;

            case "term-php":
                action = "edit-tax";
                break;

            case "admin-php":
                action = "save-option-page";
                break;
        }

        const validator = new ACPTFormValidator(action);
        validator.run();
    }
};

/**
 * This function applies the visibility conditions to nested fields
 */
export function applyVisibilityFromLocalStorage() {
    const vis = localStorage.getItem("acpt_fields_visibility");

    if(vis){
        const visibility = JSON.parse(vis);

        visibility.map((v) => {
            const list = $(`ul.acpt-sortable[data-conditional-rules-id="${v.id}"]`);

            v.vis.map((value, index) => {
                const el = list.find("li")[index];

                if(!el){
                    return;
                }

                const isBlock = el.id.includes("block-");
                const className = isBlock ? "collapsed" : "hidden";
                const btn1 = el.querySelector(".li_toggle_visibility");
                const btn2 = el.querySelector(".sortable-li_toggle_visibility");

                if(value === true){

                    // remove reverse
                    if(btn1){
                        btn1.classList.remove("reverse");
                    }

                    if(btn2){
                        btn2.classList.remove("reverse");
                    }

                    el.classList.remove(className);
                } else {

                    // add reverse
                    if(btn1){
                        btn1.classList.add("reverse");
                    }

                    if(btn2){
                        btn2.classList.add("reverse");
                    }

                    el.classList.add(className);
                }
            });
        });
    }
}

export function handleGroupedElementEvents() {

    const events = [
        "acpt_grouped_element_removed",
        "acpt_grouped_element_added",
        "acpt_grouped_element_sorted",
        "acpt_grouped_element_toggle_visibilty",
    ];

    events.map(event => {
        document.addEventListener(event, () => {
            const sortableFields = $(".acpt-sortable");
            let visibility = [];

            if(sortableFields && sortableFields.length > 0){

                // regenerate IDs
                if(event === "acpt_grouped_element_sorted" || event === "acpt_grouped_element_removed"){
                    const ids = sortableFields.find(".acpt-id-value");

                    if(ids.length > 0){
                        ids.each(function(i){
                            const id = $(this).attr("id");
                            const index = i;
                            const generateButton = $(`.acpt-id-generate[data-target-id=${id}]`);
                            const strategy = generateButton.data("id-strategy");

                            if(strategy === 'auto_inc'){
                                initIdGenerators(id, true, index);
                            }
                        });
                    }
                }

                // visibility checks
                sortableFields.each(function(i, sortableField) {
                    const id = sortableField.dataset.conditionalRulesId;
                    const li = sortableField.querySelectorAll("li");
                    const vis = [];

                    for (i = 0; i < li.length; ++i) {
                        const el = li[i];
                        const isHidden = el.classList.contains("hidden") || el.classList.contains("collapsed");

                        vis.push(!isHidden);
                    }

                    visibility.push({
                        id: id,
                        vis: vis
                    });
                });
            }

            localStorage.setItem("acpt_fields_visibility", JSON.stringify(visibility));
        });
    });
}

export const initIdGenerators = (idSelector = null, generate = false, index = null) => {

    let selector = '.acpt-id-value';
    if (idSelector) {
        selector = `#${idSelector}`;
    }

    const idElements = $$(selector);

    if(idElements.length){
        idElements.each(function( i ) {

            const target = $(this);
            const targetId = target.attr('id');
            const generateButton = $(`.acpt-id-generate[data-target-id=${targetId}]`);
            const strategy = generateButton.data("id-strategy");

            if(index === null){
                index = generateButton.data("index");
            }

            const generateId = () => {
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        "action": "generateIdAction",
                        "data": JSON.stringify({
                            "strategy": strategy,
                            "index": index
                        }),
                    },
                    success: function(data) {
                        if(data.id){
                            target.val(data.id);
                        }
                    },
                    dataType: 'json'
                });
            };

            generateButton.on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                generateId();
            });

            if(generate){
                generateId();
            }
        });
    }
};