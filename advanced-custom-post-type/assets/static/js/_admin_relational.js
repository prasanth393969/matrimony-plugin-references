import {$$, useTranslation} from "./_admin_commons.js";
import {isUUID} from "./_admin_helpers.js";
import {initSortable} from "./_admin_sortable.js";

var $ = jQuery.noConflict();

export const initRelationalFields = (idSelector = null) => {

    try {
        let selector = '.acpt-relation-field-selector';
        if (idSelector) {
            selector = `#${idSelector}`;
        }

        const relationalElement = $$(selector);

        if(relationalElement.length){
            relationalElement.each(function( index ) {

                const $this = $(this);
                const id = $this.attr('id');
                const fieldType = $this.data("field-type");
                const toType = $this.data("to-type");
                const toValue = $this.data("to-value");
                const postType = $this.data("post-type");
                const postStatus = $this.data("post-status");
                const postTaxonomy = $this.data("post-taxonomy");
                const termTaxonomy = $this.data("term-taxonomy");
                const userRole = $this.data("user-role");
                const defaultValues = $this.data("default-values");
                const layout = $this.data("layout");

                const options = $this.find(`#options_${id}`);
                const values = $this.find(`#selected_items_${id}`);
                const searchInput = $this.find(".search-input");
                const title = `title_${id}`;

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
                            "format": "html"
                        }),
                    },
                    success: function(data) {
                        searchInput.attr("disabled", false);
                        options.html(data.fields.options);
                        values.html(data.fields.selected);
                        displayDeleteAllLink($(`#${title}`), id);
                        initSortable();
                    },
                    dataType: 'json'
                });
            });
        }

    } catch (e) {
        console.error(e);
    }
};

export const handleRelationalFieldsEvents = () => {

    /**
     * Handle relationships sorting
     */
    document.addEventListener('acpt_grouped_element_sorted', (e) => {

        if(!e.detail.elementId){
            return;
        }

        const selectedElementsId = e.detail.elementId;
        const elementId = selectedElementsId.replace("selected_items_", "");
        const selector = $(`#${elementId}.acpt-relation-field-selector`);
        const value = selector.find("input");

        let newValues = [];

        $(`#${selectedElementsId}`).find("li").each(function(index) {
            const val = $(this).data("value");
            newValues.push(val);
        });

        value[0].value = newValues.join(",");
    });

    /**
     * Post relationships handling
     */
    $('body').on('change', '.post-relationship', function(e) {

        e.preventDefault();

        if($("#inversedBy").length === 0){
            return;
        }

        let $val = $( this ).val();

        if(Array.isArray($val)){
            $val = $val.join(',');
        }

        $("#inversedBy").val($val);
    });

    /**
     * Add an item
     */
    $('body').on('click', '.acpt-relation-field-selector .options .value', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if ($(this).hasClass('disabled')) {
            return;
        }

        const $this = $(this);
        const id = $this.attr('id');
        const parent = $this.parent();
        const parentId = parent.attr('id');
        const originalId = parentId.replace("options_", "");
        const minItems = parent.data('min');
        const maxItems = parent.data('max');
        const targetId = `selected_items_${originalId}`;
        const title = `title_${originalId}`;
        const valuesId = `values_${originalId}`;
        const value = $this.data('value');
        const html = $this.html();

        if(maxItems){
            if($(`#${targetId}`).children().length >= maxItems){
                return;
            }
        }

        let $saveValues = $(`#${valuesId}`).val() ? $(`#${valuesId}`).val().split(',') : [];
        $saveValues.push(value);
        $(`#${valuesId}`).val($saveValues.join(','));

        $(`#${targetId}`).append(`
            <li id="${originalId}" class="sortable-li sortable-li-${originalId} value" data-value="${value}">
                <div class="handle-placeholder">
                    <span class="handle">.<br/>.<br/>.</span> 
                    <span class="placeholder">${html}</span>
                </div> 
                <a class="delete" href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                        <path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path>
                    </svg>
                </a>
            </li>
        `);

        // dispatch change event on change
        document.getElementById(valuesId).dispatchEvent(new Event("change"));

        $this.addClass('hidden');
        $this.addClass('selected');
        displayDeleteAllLink($(`#${title}`), originalId);
        initSortable();

        if(maxItems <= $saveValues.length) {
            parent.addClass("not-allowed");
        }
    });

    /**
     * Deselect item
     */
    $('body').on('click', '.acpt-relation-field-selector .selected-items .delete', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const $this = $(this);
        const value = $this.parent().data('value');
        const parent = $this.parent().parent();
        const originalId = parent.attr('id').replace("selected_items_", "");
        const valuesId = `values_${originalId}`;
        const title = `title_${originalId}`;
        const options = $(`#options_${originalId}`);

        if(options){
            options.removeClass("not-allowed");
        }

        let $saveValues = $(`#${valuesId}`).val().split(',');

        $saveValues = $saveValues.filter(v => {
            if(isUUID(v)){
                return v !== value;
            }

            return parseInt(v) !== value;
        });

        $(`#${valuesId}`).val($saveValues.join(','));

        // dispatch change event on change
        document.getElementById(valuesId).dispatchEvent(new Event("change"));

        $(`#${$this.parent().attr('id')}`).removeClass('hidden');
        $(`#${$this.parent().attr('id')}`).removeClass('selected');
        $this.parent().remove();

        const dependentItem = $(`#options_${$this.parent().attr('id')}`).find(`#${value}`);

        if(dependentItem.length > 0){
            dependentItem.removeClass('hidden');
            dependentItem.removeClass('selected');
        }

        displayDeleteAllLink($(`#${title}`), originalId);
    });

    /**
     * Delete all items selected
     */
    $('body').on('click', '.acpt-relation-field-selector .delete-all', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const $this = $(this);
        const id = $this.attr('id');

        $(`#selected_items_${id}`).find('.value').each(function () {
            $(this).remove();
        });

        $(`#options_${id}`).find('.value').each(function () {
            $(this).removeClass("hidden")
            $(this).removeClass("selected")
        });

        $(`#values_${id}`).val('');

        // dispatch change event on change
        document.getElementById(`values_${id}`).dispatchEvent(new Event("change"));

        $this.remove();

        const options = $(`#options_${id}`);

        if(options){
            options.removeClass("not-allowed");
        }
    });

    /**
     * Search items
     */
    $('body').on('keyup', '.acpt-relation-field-selector .acpt-form-control', function() {
        const $this = $(this);
        const value = $this.val();
        const originalId = $this.attr('id').replace("search_", "");

        $(`#options_${originalId}`).find('.value').each(function () {
            const expression = `.*${value}.*`;
            const re = new RegExp(expression, 'gi');

            if(!$(this).hasClass('selected')){
                if(re.test($(this).text())){
                    $(this).removeClass("hidden")
                } else {
                    $(this).addClass("hidden")
                }
            }
        });
    });
};

/**
 * Display delete all link
 * @param element
 * @param id
 */
const displayDeleteAllLink = (element, id) => {
    if($(`#selected_items_${id}`).children().length > 0 && element.find(".delete-all").length === 0){
        element.append(`<a href="#" id="${id}" class="delete-all">${useTranslation("Delete all")}</a>`);
    }
};
