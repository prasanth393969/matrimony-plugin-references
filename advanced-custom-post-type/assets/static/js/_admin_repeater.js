import {dateFields, useTranslation} from "./_admin_commons.js";
import {initSortable} from "./_admin_sortable.js";
import {initColorPicker} from "./_admin_colorpicker.js";
import {initDateRangePicker} from "./_admin_datepicker.js";
import {initRelationalFields} from "./_admin_relational.js";
import {initTinyMCE} from "./_admin_editor.js";
import {initBarcode, initCodeMirror, initCountrySelect, initEmbed, initIdGenerators, initImageSlider, initIntlTelInput, initQRCodeGenerator, initSelectize, initTextarea, initValidator} from "./_admin_misc.js";

var $ = jQuery.noConflict();

export const handleRepeaterFieldsEvents = () => {

    /**
     *
     * @param el
     */
    const scrollAndFocusTo = (el) => {
        if(el.length > 0){

            el[0].scrollIntoView({
                behavior: 'smooth'
            });

            el.addClass("sortable-li-active");
            el.find(".acpt-admin-meta-field-input").each(function (i, el) {
                const $this = $(this);
                const tagName = $this.prop("tagName");

                if(tagName === 'INPUT'){
                    const type = $this.attr("type");

                    if(type !== 'hidden'){
                        $this.focus();

                        return false;
                    }
                }
            });
        }
    };

    // Keyboard shortcut
    document.addEventListener('keydown', (event) => {

        // Navigate element with Ctrl+Up / Ctrl+Down
        if(
            event.ctrlKey &&
            event.key === "ArrowUp" &&
            event.target.classList.contains("acpt-admin-meta-field-input")
        ) {
            event.stopPropagation();
            event.preventDefault();

            const element = $(event.target);
            let parentRow = element.closest("li");
            let prevRow = parentRow.prev("li");

            if(prevRow.length === 0){
                parentRow = element.closest("tr");
                prevRow = parentRow.prev("tr");
            }

            if(prevRow.length > 0){
                parentRow.removeClass("sortable-li-active");
                scrollAndFocusTo(prevRow);
            }
        }

        if(
            event.ctrlKey &&
            event.key === "ArrowDown" &&
            event.target.classList.contains("acpt-admin-meta-field-input")
        ) {
            event.stopPropagation();
            event.preventDefault();

            const element = $(event.target);
            let parentRow = element.closest("li");
            let nextRow = parentRow.next("li");

            if(nextRow.length === 0){
                parentRow = element.closest("tr");
                nextRow = parentRow.next("tr");
            }

            if(nextRow.length > 0){
                parentRow.removeClass("sortable-li-active");
                scrollAndFocusTo(nextRow);
            }
        }

        // Delete element with Ctrl+Delete
        if(
            event.ctrlKey &&
            event.key === "Delete" &&
            event.target.classList.contains("acpt-admin-meta-field-input")
        ) {
            event.stopPropagation();
            event.preventDefault();

            const element = $(event.target);
            let parentRow = element.closest("tr");

            if(parentRow.length === 0){
                parentRow = element.closest("li");
            }

            if(parentRow.length > 0){

                const deleteButton = parentRow.find("a.remove-grouped-element");

                if(deleteButton.length > 0){
                    deleteButton.click();
                }
            }
        }

        // Add element with Ctrl+Enter
        if(
            event.ctrlKey &&
            event.key === "Enter" &&
            event.target.classList.contains("acpt-admin-meta-field-input")
        ) {
            event.stopPropagation();
            event.preventDefault();

            const element = $(event.target);
            let list = element.closest("ul");

            if(list.length === 0){
                list = element.closest("table");
            }

            if(list.length > 0){
                let addButton = list.next(".add-grouped-element");

                if(addButton.length === 0){
                    addButton = list.parent().next(".add-grouped-element");
                }

                if(addButton.length > 0){
                    addButton.click();
                }
            }
        }
    });

    $(document).on("click", function (event) {
        if ($(event.target).closest(".sortable-li-active").length === 0) {
            const activeSortableElements = $('.sortable-li-active');

            if(!activeSortableElements){
                return;
            }

            activeSortableElements.each(function() {
                $(this).removeClass('sortable-li-active');
            });
        }
    });

    /**
     * Remove all grouped elements
     */
    $('body').on('click', '.remove-all-grouped-elements', function(e) {
        e.preventDefault();

        const $this = $(this);
        const layout = $this.data('layout');
        const element = $this.data('element');
        const elements = $this.data('elements');
        const parentId = $this.data('groupId');
        const addButton = $(`.add-grouped-element[data-group-id=${parentId}]`);

        let list;
        if(layout === 'table'){
            list = $this.prev('a').prev('.acpt-table-responsive').find('.acpt-table').find('.acpt-sortable');
        } else {
            list = $this.prev('a').prev('.acpt-sortable');
        }

        const maxBlocks = list.data('max-blocks');
        const parentListId = list.attr('id');
        const parentGroupId = $this.data('group-id');
        const fieldsCount = list.find('tr').children.length;

        if(list){
            const warningMessage = useTranslation(`No fields saved, generate the first one clicking on "Add ${element}" button`);
            const warningElement = `<p data-message-id="${parentGroupId}" class="update-nag notice notice-warning inline no-records">${warningMessage}</p>`;

            if(layout === 'table'){
                list.children('tr').each(function(index, el){
                    if(index > 0){
                        el.remove();
                    }
                });

                const colspan = fieldsCount + 2;
                $('#'+parentListId).append(`<tr><td colspan="${colspan}">${warningElement}</td></tr>`);
            } else {
                list.empty();
                $('#'+parentListId).html('').append(warningElement);
            }

            if(maxBlocks && maxBlocks > 0){
                addButton.removeAttr('disabled')
            }
        }
    });

    /**
     * Add grouped element
     */
    $('body').on('click', '.add-grouped-element', function(e) {

        e.preventDefault();

        const $this = $(this);
        const id = $this.data('group-id');
        const layout = $this.data('layout');
        const mediaType = $this.data('media-type');
        const parentIndex = $this.data('parent-index');
        const parentName = $this.data('parent-name');
        const noRecordsMessageDiv = $('[data-message-id="'+id+'"]');

        let list;
        let index = 0;

        if(layout === 'table'){
            list = $this.prev('.acpt-table-responsive').find('.acpt-table').find('.acpt-sortable').first();
            index = list.find("tr.sortable-li").length;
        } else {
            list = $this.prev('ul.acpt-sortable');
            index = list.find("li").length;
        }

        const minBlocks = list.data('min-blocks');
        const maxBlocks = list.data('max-blocks');

        const newBlocksAllowed = () => {
            if(typeof maxBlocks === 'undefined'){
                return true;
            }

            return list.find(".sortable-li").length < maxBlocks;
        };

        const checkButton = () => {
            if(!newBlocksAllowed()){
                $this.attr('disabled', 'disabled');
            } else {
                $this.removeAttr('disabled');
            }
        };

        if(newBlocksAllowed()){
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    "action": "generateGroupedFieldsAction",
                    "data": JSON.stringify({
                        "id": id,
                        "mediaType": mediaType,
                        "index": index,
                        "parentName": parentName,
                        "parentIndex": parentIndex
                    }),
                },
                success: function(data) {

                    if(!list){
                        return;
                    }

                    list.append(data.fields);
                    initSortable();
                    initValidator();
                    checkButton();

                    // scroll to last element added
                    let lastElement = list.find("li").last();

                    if(lastElement.length === 0){
                        lastElement = list.find("tr").last();
                    }

                    scrollAndFocusTo(lastElement);

                    const evt = new Event("acpt_grouped_element_added");
                    document.dispatchEvent(evt);

                    if(noRecordsMessageDiv){
                        if(layout === 'table'){
                            noRecordsMessageDiv.parent("td").parent("tr").remove();
                        } else {
                            noRecordsMessageDiv.remove();
                        }
                    }

                    // init relational fields picker only on last list element
                    const relationalElements = list.last().find('.acpt-relation-field-selector');
                    relationalElements.each(function() {
                        initRelationalFields($(this).attr("id"));
                    });

                    // init date fields only on last list element
                    dateFields.map((settings)=>{
                        const dateRangePickerElements = list.last().find(settings.selector);
                        dateRangePickerElements.each(function() {
                            initDateRangePicker(settings.format, $(this).attr("id"));
                        });
                    });

                    // init codeMirror only on last list element
                    const codeMirrorElements = list.last().find('textarea.acpt-codemirror');
                    codeMirrorElements.each(function() {
                        initCodeMirror($(this).attr("id"));
                    });

                    // init colorpicker only on last list element
                    const colorPickerElements = list.last().find('.acpt-color-picker');
                    colorPickerElements.each(function() {
                        initColorPicker($(this).attr("id"));
                    });

                    // init selectize only on last list element
                    const selectizeElements = list.last().find('select.acpt-select2');
                    selectizeElements.each(function() {
                        initSelectize($(this).attr("id"));
                    });

                    // init TinyMCE on last list element
                    const wpEditors = list.last().find('textarea.acpt-wp-editor');
                    wpEditors.each(function() {
                        const id = $(this).attr("id");
                        const rows = $(this).attr("rows");
                        const toolbar = $(this).data("toolbar");

                        initTinyMCE(id, rows, toolbar);
                    });

                    // init initIdGenerators on last list element
                    const idElements = list.last().find('input.acpt-id-value');
                    idElements.each(function() {
                        initIdGenerators($(this).attr("id"), true);
                    });

                    // init intlTelInput on last list element
                    const phoneElements = list.last().find('input.acpt-phone');
                    phoneElements.each(function() {
                        initIntlTelInput($(this).attr("id"));
                    });

                    // init countrySelect on last list element
                    const countryElements = list.last().find('input.acpt-country');
                    countryElements.each(function() {
                        initCountrySelect($(this).attr("id"));
                    });

                    // init QRCodeElements on the last list element
                    const QRCodeElements = list.last().find('.acpt-qr-code-wrapper');
                    QRCodeElements.each(function() {
                        initQRCodeGenerator($(this).attr("id"));
                    });

                    // init BarCodeElements on the last list element
                    const BarCodeElements = list.last().find('.acpt-barcode-wrapper');
                    BarCodeElements.each(function() {
                        initBarcode($(this).attr("id"));
                    });

                    // init image slider on the last list element
                    const ImageSliderElements = list.last().find('.acpt-image-slider');
                    ImageSliderElements.each(function() {
                        initImageSlider($(this).attr("id"));
                    });

                    // init embed fields  on the last list element
                    const embedElements = list.last().find('.acpt-embed');
                    embedElements.each(function() {
                        initEmbed($(this).attr("id"));
                    });

                    // init textarea fields on the last list element
                    const textareaElements = list.last().find('.acpt-textarea');
                    textareaElements.each(function() {
                        initTextarea($(this).attr("id"));
                    });
                },
                dataType: 'json'
            });
        }
    });

    /**
     * Remove single grouped element
     */
    $('body').on('click', 'a.remove-grouped-element', function(e) {

        e.preventDefault();

        const $this = $(this);
        const parentId = $this.data('parent-id');
        const id = $this.data('target-id');
        const layout = $this.data('layout');
        const element = $this.data('element');
        const elements = $this.data('elements');
        const $index = $this.data('index');
        const $target = $('#'+id);
        const fieldsCount = $target.children.length;
        const parentList = $target.parent();
        const parentListId = parentList.attr('id');
        const minBlocks = parentList.data('min-blocks');
        const maxBlocks = parentList.data('max-blocks');
        const addButton = $(`.add-grouped-element[data-group-id=${parentId}]`);

        const newBlocksAllowed = () => {
            if(typeof maxBlocks === 'undefined'){
                return true;
            }

            return parentList.find(".sortable-li ").length < maxBlocks;
        };

        const checkButton = () => {
            if(!newBlocksAllowed()){
                addButton.attr('disabled', 'disabled');
            } else {
                addButton.removeAttr('disabled')
            }
        };

        $target.remove();
        checkButton();

        // scroll to last element
        if(parentList.length > 0){

            let element = parentList.find("li");

            if(element.length === 0){
                element = parentList.find("tr");
            }

            if(element.length > 0){
                const prevElement = element.get($index-1);
                const $prevElement = $(prevElement);
                scrollAndFocusTo($prevElement);
            }
        }

        let parentListElementCount;
        if(layout === 'table'){
            parentListElementCount = (parentList.find('tr').length - 1);
        } else {
            parentListElementCount = parentList.find('li').length;
        }

        if(parentListElementCount === 0){
            const warningMessage = useTranslation(`No fields saved, generate the first one clicking on "Add ${element}" button`);
            const warningElement = `<p data-message-id="${parentId}" class="update-nag notice notice-warning inline no-records">${warningMessage}</p>`;

            if(layout === 'table'){
                const colspan = fieldsCount + 2;
                $('#'+parentListId).append(`<tr><td colspan="${colspan}">${warningElement}</td></tr>`);
            } else {
                $('#'+parentListId).html('').append(warningElement);
            }
        }

        const evt = new Event("acpt_grouped_element_removed");
        document.dispatchEvent(evt);
    });

    /**
     * Toggle grouped element visibility
     */
    $('body').on('click', '.sortable-li_toggle_visibility', function(e){
        e.preventDefault();

        const $this = $(this);
        const elementId = $this.data('target-id');
        const element = $(`#${elementId}`);

        if($this.hasClass('reverse')){ $this.removeClass('reverse'); } else { $this.addClass('reverse'); }
        if(element.hasClass('hidden')){ element.removeClass('hidden'); } else { element.addClass('hidden'); }

        const evt = new Event("acpt_grouped_element_toggle_visibilty");
        document.dispatchEvent(evt);
    });

    /**
     * Sortable
     */
    $('body').on('mousedown', '.sortable-li', function(){

        const $this = $(this);
        const classes = $this.attr('class');

        // if the element is already active, don't do anything
        if(classes.includes('sortable-li-active')){
            return;
        }

        // check if there is nested sortable-li-active
        if($this.find('.sortable-li-active').length > 0){
            return;
        }

        $('.sortable-li-active').each(function() {
            $(this).removeClass('sortable-li-active');
        });

        $this.addClass('sortable-li-active');
    });

    /**
     * Leading fields on repeater contracted elements
     */
    $('body').on('change', '.acpt-leading-field', function(e) {

        const $this = $(this);
        const type = $this.attr('type');
        const value = (typeof $this.val() === 'object') ? $this.val().join(", ") : $this.val();

        $this.parents().each(function () {
            const $this = $(this);
            const className = $this.attr('class');

            if(className && className.includes('sortable-li')){

                if(type === 'checkbox'){
                    const checked = e.target.checked;
                    const placeholder = $this.find('.sortable-li_collapsed_placeholder').find('span.value');
                    const placeholderText = placeholder.text();
                    const placeholderTextArray = placeholderText.split(", ").filter(s => s !== '');

                    if(checked){
                        placeholderTextArray.push(value);
                        placeholder.text(placeholderTextArray.join(", "));
                    } else {
                        placeholder.text(placeholderTextArray.filter(s => s !== value).join(", "));
                    }

                } else {
                    $this.find('.sortable-li_collapsed_placeholder').find('span.value').text(value);
                }
            }
        });
    });
};