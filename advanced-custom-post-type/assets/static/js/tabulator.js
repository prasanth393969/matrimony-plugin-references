import {useTranslation, wpAjaxRequest} from "./_admin_commons.js";
import {ACPTTabulator} from "./ACPTTabulator.js";

var $ = jQuery.noConflict();

jQuery(document).ready(function($){

    // load the table after the translations are available
    document.addEventListener("fetchLanguages", (e) => {

        /**
         * Run the tabulator
         */
        function runTabulator(){

            // loop all acpt-tabulator elements
            $('.acpt-tabulator').each(function () {

                let savedTemplates = [];
                const $this = $(this);
                const id = $this.attr('id');
                const targetId = $this.data("target-id");

                let input = $(`input[name='${id}']`);

                if(input.length === 0){
                    input = $(`input[name='${targetId}']`);
                }

                const modal = $(`#acpt-create-table-${id}`);
                const btnWrapper = $this.next('.btn-wrapper');
                const outcome = btnWrapper.next('.outcome');
                const btnModal = btnWrapper.find('.acpt-open-table-settings');
                const btnImportTemplate = btnWrapper.find('.acpt-open-import-template');
                const btnSaveTemplate = `<a class="acpt-open-save-template button button-secondary" href="#acpt-save-template-${id}" rel="modal:open" >${useTranslation("Save as template")}</a>`;

                if(!id){
                    return;
                }

                function destroyModal()
                {
                    $('.jquery-modal.blocker.current').remove();
                }

                /**
                 *
                 * @param message
                 * @param level
                 */
                function flashMessage(message, level = 'success')
                {
                    let color = '#02c39a';

                    if(level === 'danger'){
                        color = '#b02828';
                    }

                    outcome.css("color", color);
                    outcome.css("marginTop", "10px");
                    outcome.text(useTranslation(message));

                    setTimeout(function(){
                        outcome.css("color", "#777");
                        outcome.css("marginTop", "0");
                        outcome.text("");
                    }, 5000);
                }

                // init tabulator
                const tabulator = new ACPTTabulator(id);

                // create table from saved values
                const savedValue = input.val();

                if(typeof savedValue === "string" && savedValue !== "" && savedValue !== "{}"){
                    try {
                        const json = JSON.parse(savedValue);
                        tabulator.createTable(json);
                    } catch (e) {
                        console.error(e);
                    }
                }

                // listen for table changes
                document.addEventListener("acpt-tabulator-change", function(e){
                    const json = e.detail.json;
                    const tableSelector = e.detail.id;

                    // change modal values
                    if(json.data && json.data.length > 0){
                        modal.find("h3").text(useTranslation("Edit table settings"));
                        modal.find(".acpt-create-table").text(useTranslation("Edit"));

                        $(`#acpt-create-table-layout-${id}`).val(json.settings.layout);
                        $(`#acpt-create-table-alignment-${id}`).val(json.settings.alignment);
                        $(`#acpt-create-table-border-thickness-${id}`).val(json.settings.border.thickness);
                        $(`#acpt-create-table-border-style-${id}`).val(json.settings.border.style);
                        $(`#acpt-create-table-border-color-${id}`).val(json.settings.border.color);
                        $(`#acpt-create-table-background-color-${id}`).val(json.settings.background.color);
                        $(`#acpt-create-table-zebra-background-${id}`).val(json.settings.background.zebra);
                        $(`#acpt-create-table-color-${id}`).val(json.settings.color);
                        $(`#acpt-create-table-css-${id}`).val(json.settings.css);
                        $(`#acpt-create-table-header-${id}`).prop( "checked", json.settings.header);
                        $(`#acpt-create-table-footer-${id}`).prop( "checked", json.settings.footer);
                        $(`#acpt-create-table-columns-${id}`).val(json.settings.columns);
                        $(`#acpt-create-table-rows-${id}`).val(json.settings.rows);

                        btnModal.text("Edit table settings");

                        if(btnWrapper.find('.acpt-open-save-template').length === 0){
                            $(btnSaveTemplate).insertAfter(btnImportTemplate);
                        }

                    } else {
                        modal.find("h3").text(useTranslation("Create table"));
                        modal.find(".acpt-create-table").text(useTranslation("Create"));

                        $(`#acpt-create-table-layout-${id}`).val('horizontal');
                        $(`#acpt-create-table-alignment-${id}`).val('left');
                        $(`#acpt-create-table-border-thickness-${id}`).val(1);
                        $(`#acpt-create-table-border-style-${id}`).val('solid');
                        $(`#acpt-create-table-border-color-${id}`).val('#cccccc');
                        $(`#acpt-create-table-background-color-${id}`).val('#ffffff');
                        $(`#acpt-create-table-zebra-background-${id}`).val('#ffffff');
                        $(`#acpt-create-table-color-${id}`).val('#777777');
                        $(`#acpt-create-table-css-${id}`).val('');
                        $(`#acpt-create-table-header-${id}`).prop( "checked", true);
                        $(`#acpt-create-table-header-${id}`).show();
                        $(`label[for="acpt-create-table-header-${id}"]`).show();
                        $(`#acpt-create-table-footer-${id}`).prop( "checked", false);
                        $(`#acpt-create-table-columns-${id}`).val(2);
                        $(`#acpt-create-table-rows-${id}`).val(2);

                        btnModal.text(useTranslation("Create table"));
                        btnWrapper.find('.acpt-open-save-template').remove();
                    }

                    if(tableSelector === id){
                        input.val(JSON.stringify(json));
                    }
                });

                // import template modal
                $('body').on('click', `.acpt-open-import-template`, function(e){
                    wpAjaxRequest('fetchTableTemplatesAction', {})
                        .then((response) => {
                            return response.json();
                        })
                        .then((templates) => {

                            savedTemplates = templates;
                            const templateNameSelector = $(`#acpt-import-template-name-${id}`);

                            const templateElement = (template) => {
                                return `
                                    <li>
                                        <p class="label" title="${template.name}">
                                            ${template.name}
                                        </p>
                                        <span class="buttons">
                                            <a class="button button-secondary acpt-apply-template" id="acpt-apply-template-${tabulator.getId()}" data-target-id="${template.id}" title="${useTranslation("Apply")}" rel="modal:close" href="#">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1.2rem" height="1.2rem" viewBox="0 0 24 24"><path fill="#007cba" d="M11 15h2V9h3l-4-5l-4 5h3z"/><path fill="#007cba" d="M20 18H4v-7H2v7c0 1.103.897 2 2 2h16c1.103 0 2-.897 2-2v-7h-2z"/></svg>
                                            </a>
                                            <a class="button button-danger acpt-delete-template" id="acpt-apply-template-${tabulator.getId()}" data-target-id="${template.id}" title="${useTranslation("Delete")}" rel="modal:close" href="#">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1.2rem" height="1.2rem" viewBox="0 0 24 24"><path fill="#b02828" d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"/><path fill="#b02828" d="M9 10h2v8H9zm4 0h2v8h-2z"/></svg>
                                            </a>
                                        </span>
                                    </li>
                                `;
                            };

                            templateNameSelector
                                .find('li')
                                .remove()
                                .end()
                            ;

                            if(templates.length > 0){
                                templates.map((template) => {
                                    templateNameSelector.append($(templateElement(template)));
                                });
                            } else {
                                templateNameSelector.append($(`<li>${useTranslation("No templates found.")}</li>`));
                            }
                        });
                });

                // import template
                $('body').on('click', `#acpt-apply-template-${tabulator.getId()}`, function(e){

                    const selectedTemplateId = $(this).data("target-id");
                    const filterTemplate = savedTemplates.filter((template) => Number(template.id) === Number(selectedTemplateId));

                    if(filterTemplate.length === 1){
                        const selectedTemplate = filterTemplate[0];
                        tabulator.createTable(selectedTemplate.json);
                        destroyModal();
                        flashMessage("The template was successfully imported!");
                    } else {
                        $(`#acpt-import-template-${id}`).find('.errors').css("marginBottom", "10px");
                        $(`#acpt-import-template-${id}`).find('.errors').text("There was an error during the import of the template");
                    }
                });

                // delete template
                $('body').on('click', `.acpt-delete-template`, function(e){

                    const selectedTemplateId = $(this).data("target-id");

                    wpAjaxRequest('deleteTableTemplateAction', {id: selectedTemplateId})
                        .then((response) => {
                            return response.json();
                        })
                        .then((res) => {
                            if(res.success){
                                destroyModal();
                                flashMessage("The template was successfully deleted!");
                            } else if(res.error) {
                                destroyModal();
                                flashMessage(res.error, 'danger');
                            }
                        })
                    ;
                });

                // save template
                $('body').on('keyup', `#acpt-save-template-name-${id}`, function(e){
                    if(e.target.value !== ''){
                        $(`#acpt-save-template-${id}`).find('.acpt-save-template-name').removeClass("disabled");
                    } else {
                        $(`#acpt-save-template-${id}`).find('.acpt-save-template-name').addClass("disabled");
                    }
                });

                $('body').on('click', `.acpt-save-template-name`, function(e){
                    e.preventDefault();

                    wpAjaxRequest('saveTableTemplate', {
                        name: $(`#acpt-save-template-name-${id}`).val(),
                        json: tabulator.json
                    })
                        .then((response) => {
                            return response.json();
                        })
                        .then((response) => {
                            if(response.success === true){
                                $(`#acpt-save-template-name-${id}`).val('');
                                $(`#acpt-save-template-${id}`).find('.acpt-save-template-name').addClass("disabled");
                                destroyModal();
                                flashMessage("The template was successfully saved!");
                            } else {
                                $(`#acpt-save-template-${id}`).find('.errors').css("marginBottom", "10px");
                                $(`#acpt-save-template-${id}`).find('.errors').text(response.error);
                                console.error(response.error);
                            }
                        });
                });

                // create project modal
                $('body').on('click', `#acpt-create-table-layout-${id}`, function(e){
                    if(e.target.value === 'horizontal'){
                        $(`#acpt-create-table-header-${id}`).prop( "checked", true);
                        $(`#acpt-create-table-header-${id}`).show();
                        $(`label[for="acpt-create-table-header-${id}"]`).show();
                    } else if(e.target.value === 'vertical'){
                        $(`#acpt-create-table-header-${id}`).prop( "checked", false);
                        $(`#acpt-create-table-header-${id}`).hide();
                        $(`label[for="acpt-create-table-header-${id}"]`).hide();
                    }
                });

                // clear table
                $('body').on('click', `.acpt-clear-table[data-target-id=${id}]`, function(e){
                    e.preventDefault();
                    tabulator.destroyTable();
                });

                // create a new table or edit current settings
                $('body').on('click', `.acpt-create-table[data-target-id=${id}]`, function(e){

                    e.preventDefault();

                    // form values
                    const layout = $(`#acpt-create-table-layout-${id}`).val();
                    const alignment = $(`#acpt-create-table-alignment-${id}`).val();
                    const borderThickness = $(`#acpt-create-table-border-thickness-${id}`).val();
                    const borderStyle = $(`#acpt-create-table-border-style-${id}`).val();
                    const borderColor = $(`#acpt-create-table-border-color-${id}`).val();
                    const backgroundColor = $(`#acpt-create-table-background-color-${id}`).val();
                    const zebraBackgroundColor = $(`#acpt-create-table-zebra-background-${id}`).val();
                    const color = $(`#acpt-create-table-color-${id}`).val();
                    const css = $(`#acpt-create-table-css-${id}`).val();
                    const header = $(`#acpt-create-table-header-${id}`).is(':checked');
                    const footer = $(`#acpt-create-table-footer-${id}`).is(':checked');
                    const columns = $(`#acpt-create-table-columns-${id}`).val();
                    const rows = $(`#acpt-create-table-rows-${id}`).val();

                    /**
                     * @return {[]}
                     */
                    const obtainData = () => {

                        const defaultValue = tabulator.defaultCellValue;
                        let index = 0;
                        let data = [];

                        /**
                         *
                         * @param i
                         * @param k
                         */
                        const cellItem = (i, k) => {
                            return (
                                tabulator.json.data &&
                                tabulator.json.data.length > 0 &&
                                tabulator.json.data[i] &&
                                tabulator.json.data[i][k]
                            ) ? tabulator.json.data[i][k] : {value: defaultValue, settings: {}};
                        };

                        // header
                        if(header){
                            for (let k = 0; k < Number(columns); k++) {
                                if(!data[index]){
                                    data[index] = [];
                                }

                                data[index].push(cellItem(index, k));
                            }

                            index++;
                        }

                        // body
                        for (let i = 0; i < Number(rows); i++) {
                            for (let k = 0; k < Number(columns); k++) {
                                if(!data[index]){
                                    data[index] = [];
                                }

                                data[index].push(cellItem(index, k));
                            }

                            index++;
                        }

                        // footer
                        if(footer){
                            for (let k = 0; k < Number(columns); k++) {
                                if(!data[index]){
                                    data[index] = [];
                                }

                                data[index].push(cellItem(index, k));
                            }

                            index++;
                        }

                        return data;
                    };

                    const json = {
                        settings: {
                            layout: layout,
                            css: css,
                            alignment: alignment,
                            border: {
                                thickness: borderThickness,
                                style: borderStyle,
                                color: borderColor
                            },
                            background: {
                                color: backgroundColor,
                                zebra: zebraBackgroundColor,
                            },
                            color: color,
                            header: header,
                            footer: footer,
                            columns: columns,
                            rows: rows,
                        },
                        data: obtainData()
                    };

                    tabulator.createTable(json);
                });

                // add row
                $('body').on('click', `.acpt-editable-table .add-row`, function(e){
                    e.preventDefault();
                    const $this = $(this);
                    const rowId = $this.data("row-id");

                    if(typeof rowId === 'number'){
                        tabulator.addRow(rowId);
                    }
                });

                // delete row
                $('body').on('click', `.acpt-editable-table .delete-row`, function(e){
                    e.preventDefault();
                    const $this = $(this);
                    const rowId = $this.data("row-id");

                    if(typeof rowId === 'number'){
                        tabulator.deleteRow(rowId);
                    }
                });

                // add column
                $('body').on('click', `.acpt-editable-table .add-col`, function(e){
                    e.preventDefault();
                    const $this = $(this);
                    const colId = $this.data("col-id");

                    if(typeof colId === 'number'){
                        tabulator.addColumn(colId);
                    }
                });

                // delete column
                $('body').on('click', `.acpt-editable-table .delete-col`, function(e){
                    e.preventDefault();
                    const $this = $(this);
                    const colId = $this.data("col-id");

                    if(typeof colId === 'number'){
                        tabulator.deleteColumn(colId);
                    }
                });
            });
        }

        // run tabulator on init
        runTabulator();

        // run tabulator when a repeater element is added
        document.addEventListener("acpt_grouped_element_added", (e) => {
            runTabulator();
        });

        // run tabulator when a flexible element is added
        document.addEventListener("acpt_flexible_element_added", (e) => {
            runTabulator();
        });
    });
});
