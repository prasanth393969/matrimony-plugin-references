import {dateFields, useTranslation} from "./_admin_commons.js";
import {initSortable} from "./_admin_sortable.js";
import {initColorPicker} from "./_admin_colorpicker.js";
import {initDateRangePicker} from "./_admin_datepicker.js";
import {initRelationalFields} from "./_admin_relational.js";
import {initTinyMCE} from "./_admin_editor.js";
import {initBarcode, initCodeMirror, initCountrySelect, initEmbed, initIdGenerators, initImageSlider, initIntlTelInput, initQRCodeGenerator, initSelectize, initTextarea, initValidator} from "./_admin_misc.js";

var $ = jQuery.noConflict();

export const handleFlexibleFieldsEvents = () => {

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

    /**
     * Add block button
     */
    $('body').on('click', '.acpt_add_flexible_btn', function(e) {

        e.preventDefault();

        const $this = $(this);
        const list = $this.next('.acpt_flexible_block_items');

        ($this.hasClass('active')) ? $this.removeClass('active') : $this.addClass('active');
        (list.hasClass('active')) ? list.removeClass('active') : list.addClass('active');
    });

    /**
     * Hide add block menu
     */
    document.addEventListener("click", function(evt) {

        const targetEl = evt.target;
        const showAddBlockMenu =
            targetEl.classList.contains('acpt_flexible_block_item') ||
            targetEl.classList.contains('acpt_add_flexible_btn') ||
            targetEl.classList.contains('acpt_add_flexible_btn_label') ||
            targetEl.classList.contains('acpt_add_flexible_btn_icon') ||
            targetEl.classList.contains('acpt_add_flexible_btn_icon') ||
            targetEl.parentNode.classList.contains('acpt_add_flexible_btn_icon')
        ;

        if(showAddBlockMenu === false){
            $('.acpt_flexible_block_items').removeClass('active');
            $('.acpt_add_flexible_btn').removeClass('active');
        }
    });

    /**
     * Delete all blocks
     */
    $('body').on('click', '.remove-all-blocks', function(e){

        e.preventDefault();

        const $this = $(this);
        const blockListId = $this.data('block-list-id');
        const blockList = $("ul#acpt-sortable-"+blockListId);
        const minBlocks = $this.data('min-blocks');
        const maxBlocks = $this.data('max-blocks');
        const addBlockButton = $this.prev().prev();

        if(blockList){
            blockList.empty();

            const warningMessage = useTranslation(`No blocks saved, generate the first one clicking on "Add block" button`);
            const warningElement = `<p data-message-id="${blockListId}" class="update-nag notice notice-warning inline no-records">${warningMessage}</p>`;

            blockList.append(warningElement);

            if(maxBlocks && maxBlocks > 0){
                addBlockButton.attr("disabled", false);
            }
        }
    });

    /**
     * Add block from context menu
     */
    $('body').on('click', '.acpt_flexible_block_items > li', function(e) {

        e.preventDefault();

        const $this = $(this);
        const dropdownList = $this.parent();
        const layout = $this.data('layout');
        const blockId = $this.data('value');
        const blockListId = $this.data('block-list-id');
        const parentName = $this.data('parent-name');
        const blockIndex = $this.data('block-index');
        const mediaType = $this.data('media-type');
        const fieldId = $this.data('field-id');
        const minBlocks = $this.data('min-blocks');
        const maxBlocks = $this.data('max-blocks');
        const blockList = $("ul#acpt-sortable-"+blockListId);
        const blockListLength = blockList.find("li.acpt_blocks_list_item").length;
        const button = blockList.next(".acpt_add_flexible_block").find("button");
        const noRecordsMessageDiv = $('[data-message-id="'+fieldId+'"]');

        const newBlocksAllowed = () => {
            if(typeof maxBlocks === 'undefined' || maxBlocks === ''){
                return true;
            }

            return blockListLength < maxBlocks;
        };

        if(newBlocksAllowed()){
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    "action": "generateFlexibleBlockAction",
                    "data": JSON.stringify({
                        "layout": layout,
                        "blockId": blockId,
                        "mediaType": mediaType,
                        "parentName": parentName,
                        "index": blockListLength,
                        "blockListId": blockListId,
                        "minBlocks": minBlocks,
                        "maxBlocks": maxBlocks
                    }),
                },
                success: function(data) {

                    if(!blockList){
                        return;
                    }

                    blockList.append(data.block);

                    // scroll to last element added
                    const lastElement = blockList.find("li").last();

                    if(lastElement.length > 0){
                        lastElement[0].scrollIntoView({
                            behavior: 'smooth'
                        });
                    }

                    const newBlocksAllowed = () => {
                        if(typeof maxBlocks === 'undefined' || maxBlocks === ''){
                            return true;
                        }

                        return (blockListLength+1) >= maxBlocks;
                    };

                    if(!newBlocksAllowed()){
                        button.attr("disabled", true);
                    }

                    initSortable();
                    initValidator();

                    if(noRecordsMessageDiv){
                        noRecordsMessageDiv.remove();
                    }

                    // init relational fields picker only on last list element
                    const relationalElements = blockList.last().find('.acpt-relation-field-selector');
                    relationalElements.each(function() {
                        initRelationalFields($(this).attr("id"));
                    });

                    // init date fields only on last list element
                    dateFields.map((settings)=>{
                        const dateRangePickerElements = blockList.last().find(settings.selector);
                        dateRangePickerElements.each(function() {
                            initDateRangePicker(settings.format, $(this).attr("id"));
                        });
                    });

                    // init codeMirror only on last list element
                    const codeMirrorElements = blockList.last().find('textarea.acpt-codemirror');
                    codeMirrorElements.each(function() {
                        initCodeMirror($(this).attr("id"));
                    });

                    // init colorpicker only on last list element
                    const colorPickerElements = blockList.last().find('.acpt-color-picker');
                    colorPickerElements.each(function() {
                        initColorPicker($(this).attr("id"));
                    });

                    // init selectize only on last list element
                    const selectizeElements = blockList.last().find('select.acpt-select2');
                    selectizeElements.each(function() {
                        initSelectize($(this).attr("id"));
                    });

                    // init TinyMCE on last list element
                    const wpEditors = blockList.last().find('textarea.acpt-wp-editor');
                    wpEditors.each(function() {
                        const id = $(this).attr("id");
                        const rows = $(this).attr("rows");
                        const toolbar = $(this).data("toolbar");

                        initTinyMCE(id, rows, toolbar);
                    });

                    // init initIdGenerators on last list element
                    const idElements = blockList.last().find('input.acpt-id-value');
                    idElements.each(function() {
                        initIdGenerators($(this).attr("id"), true);
                    });

                    // init intlTelInput on last list element
                    const phoneElements = blockList.last().find('input.acpt-phone');
                    phoneElements.each(function() {
                        initIntlTelInput($(this).attr("id"));
                    });

                    // init countrySelect on last list element
                    const countryElements = blockList.last().find('input.acpt-country');
                    countryElements.each(function() {
                        initCountrySelect($(this).attr("id"));
                    });

                    // init QRCodeElements on the last list element
                    const QRCodeElements = blockList.last().find('.acpt-qr-code-wrapper');
                    QRCodeElements.each(function() {
                        initQRCodeGenerator($(this).attr("id"));
                    });

                    // init BarCodeElements on the last list element
                    const BarCodeElements = blockList.last().find('.acpt-barcode-wrapper');
                    BarCodeElements.each(function() {
                        initBarcode($(this).attr("id"));
                    });

                    // init image slider on the last list element
                    const ImageSliderElements = blockList.last().find('.acpt-image-slider');
                    ImageSliderElements.each(function() {
                        initImageSlider($(this).attr("id"));
                    });

                    // init embed fields  on the last list element
                    const embedElements = blockList.last().find('.acpt-embed');
                    embedElements.each(function() {
                        initEmbed($(this).attr("id"));
                    });

                    // init textarea fields on the last list element
                    const textareaElements = blockList.last().find('.acpt-textarea');
                    textareaElements.each(function() {
                        initTextarea($(this).attr("id"));
                    });
                },
                dataType: 'json'
            });
        }

        dropdownList.removeClass('active');
    });

    /**
     * Delete all elements inside a block
     */
    $('body').on('click', '.acpt_delete_all_flexible_element_btn', function(e){
        e.preventDefault();

        const $this = $(this);
        const generatedBlockId = $this.data('block-id');
        const blockId = $this.data('group-id');
        const element = $this.data('element');
        const layout = $this.data('layout');
        const index = $this.data('index');
        const parentBlockList = $('[data-parent-id="'+generatedBlockId+'"]');
        const list = $(`#block-elements-${blockId}-${index}`);
        const parentListId = `block-elements-${blockId}-${index}`;

        if(list){
            const warningMessage = useTranslation(`No fields saved, generate the first one clicking on "Add ${element}" button`);
            const warningElement = `<p data-message-id="${parentListId}" class="update-nag notice notice-warning inline no-records">${warningMessage}</p>`;

            if(layout === 'table'){

                const fieldsCount = list.find('tr').children.length;

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
        }
    });

    // Keyboard shortcut
    document.addEventListener('keydown', (event) => {

        // Add element with Ctrl+Enter
        if(
            event.ctrlKey &&
            event.key === "Enter" &&
            event.target.classList.contains("acpt-admin-meta-field-input")
        ) {
            event.stopPropagation();
            event.preventDefault();

            const element = $(event.target);
            let list = element.closest("table");

            if(list.length === 0){
                list = element.closest("ul");
            }

            if(list.length > 0){

                // Table layout
                let addButton = list.parent().next(".acpt_add_flexible_element_btn");

                // Row layout
                if(addButton.length === 0){
                    addButton = list.parent().parent().next(".acpt_add_flexible_element_btn");
                }

                // Block layout
                if(addButton.length === 0){
                    addButton = list.parent().parent().parent().parent().parent().next(".acpt_add_flexible_element_btn");
                }

                if(addButton.length > 0){
                    addButton.click();
                }
            }
        }
    });

    /**
     * Add element inside a block
     */
    $('body').on('click', '.acpt_add_flexible_element_btn', function(e){
        e.preventDefault();

        const $this = $(this);
        const generatedBlockId = $this.data('block-id');
        const layout = $this.data('layout');
        const blockId = $this.data('group-id');
        const mediaType = $this.data('media-type');
        const parentName = $this.data('parent-name');
        const index = $this.data('index');
        const minBlocks = $this.data('min-blocks');
        const maxBlocks = $this.data('max-blocks');
        const parentBlockList = $('[data-parent-id="'+generatedBlockId+'"]');
        const list = parentBlockList.find(`#block-elements-${blockId}-${index}`);
        const noRecordsMessageDiv = parentBlockList.find('[data-message-id="block-elements-'+blockId+ '-' + index+'"]');

        /**
         *
         * @param layout
         * @param list
         * @return {number|*}
         */
        const elementCount = (layout, list) => {

            if(layout === 'table'){

                const count = (list.find('tr').length - 1);

                // check if table is empty
                if(count === 1 && list.find('tr').find('td').length === 1){
                    return 0;
                }

                return count;
            }

            return list.find('li').length;
        };

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                "action": "generateFlexibleGroupedFieldsAction",
                "data": JSON.stringify({
                    "blockId": blockId,
                    "mediaType": mediaType,
                    "elementIndex": elementCount(layout, list),
                    "blockIndex": index,
                    "layout": layout,
                    "parentName": parentName,
                    "minBlocks": minBlocks,
                    "maxBlocks": maxBlocks
                }),
            },
            success: function(data) {

                if(list){
                    list.append(data.fields);
                    initSortable();
                    initValidator();

                    // scroll to last element added
                    let lastElement = list.find("li").last();

                    if(lastElement.length === 0){
                        lastElement = list.find("tr").last();
                    }

                    scrollAndFocusTo(lastElement);

                    const evt = new Event("acpt_flexible_element_added");
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
                }
            },
            dataType: 'json'
        });
    });

    /**
     * Toggle block visibility
     */
    $('body').on('click', '.acpt_blocks_list_item_toggle_visibility', function(e){
        e.preventDefault();

        const $this = $(this);
        const blockId = $this.data('target-id');
        const block = $(`#${blockId}`);
        const targetList = $('*[data-parent-id='+blockId+']');
        const addElementButton = $('*[data-block-id='+blockId+']');
        const parentTitleElement = $this.parent().parent();

        if($this.hasClass('reverse')){ $this.removeClass('reverse'); } else { $this.addClass('reverse'); }
        // if(parentTitleElement.hasClass('no-margin')){ parentTitleElement.removeClass('no-margin'); } else { parentTitleElement.addClass('no-margin'); }
        // if(targetList.hasClass('hidden')){ targetList.removeClass('hidden'); } else { targetList.addClass('hidden'); }
        // if(addElementButton.hasClass('hidden')){ addElementButton.removeClass('hidden'); } else { addElementButton.addClass('hidden'); }
        if(block.hasClass('collapsed')){ block.removeClass('collapsed'); } else { block.addClass('collapsed'); }

        const evt = new Event("acpt_grouped_element_toggle_visibilty");
        document.dispatchEvent(evt);
    });

    /**
     * Delete block
     */
    $('body').on('click', '.acpt_blocks_list_item_delete', function(e){
        e.preventDefault();

        const $this = $(this);
        const blockId = $this.data('target-id');
        const block = $('#'+blockId);
        const blockList = block.parent();
        const blockListId = blockList.attr('id');
        const blockListLength = blockList.find("li.acpt_blocks_list_item").length;
        const minBlocks = blockList.data('min-blocks');
        const maxBlocks = blockList.data('max-blocks');
        const button = blockList.next(".acpt_add_flexible_block").find("button");

        const newBlocksAllowed = () => {
            if(typeof maxBlocks === 'undefined' || maxBlocks === ''){
                return true;
            }

            return blockListLength >= maxBlocks;
        };

        if(newBlocksAllowed()){
            button.attr("disabled", false);
        }

        block.remove();

        if(blockListLength === 1){
            const warningMessage = useTranslation(`No blocks saved, generate the first one clicking on "Add block" button`);
            const warningElement = `<p data-message-id="${blockListId}" class="update-nag notice notice-warning inline no-records">${warningMessage}</p>`;

            blockList.append(warningElement);
        }
    });
};