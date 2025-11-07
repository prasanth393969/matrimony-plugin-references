import {useTranslation} from "./_admin_commons.js";

/**
 * ACPT Tabulator
 */
export class ACPTTabulator
{
    /**
     *
     * @type {string}
     */
    defaultCellValue =  "Double click to edit";
    defaultCellObject = {value: this.defaultCellValue, settings: {}};

    /**
     *
     * @param selector
     */
    constructor(selector) {
        this.selector = selector;
        this.target = document.getElementById(selector);
        this.tableId = this.#randomId();
        this.json = {};
    }

    /**
     *
     * @return {*}
     */
    getId() {
        return this.selector;
    }

    /**
     *
     * @return {string}
     */
    #randomId()
    {
        return Math.random().toString(36).slice(2);
    }

    /**
     *
     * @param number
     * @return {string|null}
     */
    #numberToLetter(number)
    {
        const alphabet = ["a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"];

        if(alphabet[number]){
            return alphabet[number].toUpperCase();
        }

        return null;
    }

    /**
     *
     * @return {{}}
     */
    #dispatchChangeEvent()
    {
        const event = new CustomEvent(
            "acpt-tabulator-change",
            {
                detail: {
                    json: this.json,
                    id: this.selector,
                }
            }
        );

        document.dispatchEvent(event);
    }

    /**
     * Destroy the table
     */
    destroyTable()
    {
        const table = document.getElementById(this.tableId);

        if(table){
            table.remove();
            this.target.innerHTML = `<p class="update-nag notice notice-warning" style="margin: 0;">${useTranslation("No table already created.")}</p>`;
            this.json = {};
            this.#dispatchChangeEvent();
        }
    }

    /**
     * Create a table from a JSON object
     *
     * @param json
     */
    createTable(json)
    {
        if(!this.target){
            return;
        }

        if(!this.#validateJSON(json)){
            this.target.innerHTML = `<p class="update-nag notice notice-danger" style="margin: 0;">${useTranslation("JSON data is not valid.")}</p>`;
            throw new Error('json is not valid');
        }

        const layout = json.settings.layout;
        const css = json.settings.css;
        const header = json.settings.header;
        const footer = json.settings.footer;
        const columns = json.settings.columns;
        const rows = json.settings.rows;
        const data = json.data;

        if(Number(rows) === 0 || Number(columns) === 0){
            this.destroyTable();

            return;
        }

        let table;

        if(layout === 'vertical'){
            table = this.#createVerticalTable(columns, rows, data, css, footer);
        } else {
            table = this.#createHorizontalTable(columns, rows, data, css, header, footer);
        }

        if(typeof table === 'string'){
            this.target.innerHTML = table;
            this.json = json;
            this.#makeTableEditable(document.getElementById(this.tableId));
            //this.#makeTableResizableAndSortable(document.getElementById(this.tableId));
            //this.#createResizableTable(document.getElementById(this.tableId));
            this.#dispatchChangeEvent();
        }
    }

    /**
     *
     * @param columns
     * @param rows
     * @param data
     * @param css
     * @param header
     * @param footer
     * @return {string}
     */
    #createHorizontalTable(columns, rows, data, css, header = true, footer = false)
    {
        let table = `<table id="`+this.tableId+`" class="acpt-editable-table ${css}">`;
        let rowIndex = 0;
        let index = 0;

        table += `<thead>`;

        // Letters
        table += `<tr>`;
        table += `<th class="white" width="10"></th>`;

        for (let k = 0; k < Number(columns); k++) {
            table += `<th style="text-align: center;" class="helper">`;
            table += `<a href="#" data-col-id="`+k+`" title="${useTranslation("Delete column")}" class="delete-col">-</a>`;
            table += `<a href="#" data-col-id="`+k+`" title="${useTranslation("Add column")}" class="add-col prev">+</a>`;
            table += `<span>${this.#numberToLetter(k)}</span>`;

            if(k === (Number(columns)-1)){
                table += `<a href="#" data-col-id="`+(k+1)+`" title="${useTranslation("Add column")}" class="add-col next">+</a>`;
            }

            table += `</td>`;
        }

        table += `<th class="white" width="10"></th>`;
        table += `</tr>`;

        // header
        if(header){
            table += `<tr data-row-id="`+rowIndex+`">`;
            table += `<th class="white" width="10"></th>`;

            for (let k = 0; k < Number(columns); k++) {
                table += `<th data-row-id="`+rowIndex+`" data-col-id="`+k+`" class="editable">`;
                table += `${data[index][k].value}`;
                table += `</th>`;
            }

            table += `<th class="white" width="10">
                <a href="#" data-row-id="`+rowIndex+`" title="${useTranslation("Delete row")}" class="delete-row">-</a>
            </th>`;
            table += `</tr>`;
            index++;
            rowIndex++;
        }

        table += `</thead>`;

        // body
        table += `<tbody>`;

        for (let i = 0; i < Number(rows); i++) {
            table += `<tr data-row-id="`+rowIndex+`">`;
            table += `<td class="no-resize helper">`;
            table += `<a href="#" data-row-id="`+rowIndex+`" title="${useTranslation("Add row")}" class="add-row prev">+</a>`;
            table += `<span >${rowIndex}</span>`;

            if(i === (Number(rows)-1)){
                table += `<a href="#" data-row-id="`+(rowIndex+1)+`" title="${useTranslation("Add row")}" class="add-row next">+</a>`;
            }

            table += `</td>`;

            for (let k = 0; k < Number(columns); k++) {
                table += `<td data-row-id="`+rowIndex+`" data-col-id="`+k+`" class="editable">`;
                table += `${data[index][k].value}`;
                table += `</td>`;
            }

            table += `<td class="no-resize white">
                <a href="#" data-row-id="`+rowIndex+`" title="${useTranslation("Delete row")}" class="delete-row">
                    -
                 </a>
            </td>`;
            table += `</tr>`;
            index++;
            rowIndex++;
        }

        table += `</tbody>`;

        // footer
        if(footer){
            table += `<tfoot>`;
            table += `<tr data-row-id="`+rowIndex+`">`;
            table += `<td></td>`;

            for (let k = 0; k < Number(columns); k++) {
                table += `<td data-row-id="`+rowIndex+`" data-col-id="`+k+`" class="editable">`;
                table += `${data[index][k].value}`;
                table += `</td>`;
            }

            table += `<td class="no-resize white">
                <a href="#" data-row-id="`+rowIndex+`" title="Delete row" class="delete-row">
                    -
                 </a>
            </td>`;
            table += `</tr>`;
            table += `</tfoot>`;
        }

        table += `</table>`;

        return table;
    }

    #createVerticalTable(columns, rows, data, css, footer = false)
    {
        let table = `<table id="`+this.tableId+`" class="acpt-editable-table ${css}">`;
        let rowIndex = 0;
        let index = 0;

        // Letters
        table += `<thead>`;
        table += `<tr>`;
        table += `<th class="white" width="10"></th>`;

        for (let k = 0; k < Number(columns); k++) {
            table += `<th style="text-align: center;" class="helper">`;
            table += `<a href="#" data-col-id="`+k+`" title="${useTranslation("Delete column")}" class="delete-col">-</a>`;
            table += `<a href="#" data-col-id="`+k+`" title="${useTranslation("Add column")}" class="add-col prev">+</a>`;
            table += `<span>${this.#numberToLetter(k)}</span>`;

            if(k === (Number(columns)-1)){
                table += `<a href="#" data-col-id="`+(k+1)+`" title="${useTranslation("Add column")}" class="add-col next">+</a>`;
            }

            table += `</td>`;
        }

        table += `<th class="white" width="10"></th>`;
        table += `</tr>`;
        table += `</thead>`;

        // body
        table += `<tbody>`;

        for (let i = 0; i < Number(rows); i++) {
            table += `<tr data-row-id="`+rowIndex+`">`;
            table += `<td class="no-resize helper">`;
            table += `<a href="#" data-row-id="`+rowIndex+`" title="${useTranslation("Add row")}" class="add-row prev">+</a>`;
            table += `<span >${rowIndex}</span>`;

            if(i === (Number(rows)-1)){
                table += `<a href="#" data-row-id="`+(rowIndex+1)+`" title="${useTranslation("Add row")}" class="add-row next">+</a>`;
            }

            table += `</td>`;

            for (let k = 0; k < Number(columns); k++) {
                if(k === 0){
                    table += `<th data-row-id="`+rowIndex+`" data-col-id="`+k+`" class="editable">`;
                } else {
                    table += `<td data-row-id="`+rowIndex+`" data-col-id="`+k+`" class="editable">`;
                }

                table += `${data[index][k].value}`;

                if(k === 0){
                    table += `</th>`;
                } else {
                    table += `</td>`;
                }
            }

            table += `<td class="no-resize white">
                <a href="#" data-row-id="`+rowIndex+`" title="${useTranslation("Delete row")}" class="delete-row">
                    -
                 </a>
            </td>`;
            table += `</tr>`;
            index++;
            rowIndex++;
        }

        // footer
        if(footer){
            table += `<tfoot>`;
            table += `<tr data-row-id="`+rowIndex+`">`;
            table += `<td></td>`;

            for (let k = 0; k < Number(columns); k++) {
                if(k === 0){
                    table += `<th data-row-id="`+rowIndex+`" data-col-id="`+k+`" class="editable">`;
                } else {
                    table += `<td data-row-id="`+rowIndex+`" data-col-id="`+k+`" class="editable">`;
                }

                table += `${data[index][k].value}`;

                if(k === 0){
                    table += `</th>`;
                } else {
                    table += `</td>`;
                }
            }

            table += `<td class="no-resize white">
                <a href="#" data-row-id="`+rowIndex+`" title="${useTranslation("Delete row")}" class="delete-row">
                    -
                 </a>
            </td>`;
            table += `</tr>`;
            table += `</tfoot>`;
        }

        table += `</tbody>`;
        table += `</table>`;

        return table;
    }

    /**
     *
     * @param json
     * @return {boolean}
     */
    #validateJSON(json)
    {
        if(!json.settings){
            return false;
        }

        if(!json.settings.layout){
            return false;
        }

        if(!['vertical', 'horizontal'].includes(json.settings.layout)){
            return false;
        }

        if(typeof json.settings.header !== "boolean"){
            return false;
        }

        if(typeof json.settings.footer !== "boolean"){
            return false;
        }

        if(isNaN(json.settings.columns)){
            return false;
        }

        if(isNaN(json.settings.rows)){
            return false;
        }

        if(!json.data){
            return false;
        }

        if(typeof json.data !== "object"){
            return false;
        }

        return true;
    }

    /**
     *
     * @param table
     */
    #makeTableEditable(table)
    {
        const tableHeaders = table.querySelectorAll('th');
        const tableCells = table.querySelectorAll('td');

        /**
         *
         * @param cell
         */
        const makeCellEditable = (cell) => {
            if(cell.classList.contains('editable')){

                // add CSS to cells
                this.#applyCSSToCell(cell);

                // add a context menu
                this.#addContextMenu(cell);

                // make cell selected on single click
                this.#clickAndSelectCell(cell);

                const cellRowId = Number(cell.dataset.rowId);
                const cellColId = Number(cell.dataset.colId);
                const ctxMenu = document.getElementById(`context-menu-${cellRowId}-${cellColId}`);

                // click outside the cell
                this.#clickOutsideTheCell(cell, ctxMenu);

                // make cell editable on double click
                this.#doubleClickAndMakeCellEditable(cell, ctxMenu);

                // destroy contenteditable attribute
                this.#blurAndRemoveDestroyCellEditable(cell, ctxMenu);

                // detect any change in cell content
                this. #detectAnyChangeInCellContent(cell, ctxMenu,cellRowId, cellColId);

                // show/hide the contextmenu
                this.#toggleContextMenu(cell, ctxMenu);
            }
        };

        tableHeaders.forEach((cell) => {
            makeCellEditable(cell);
        });

        tableCells.forEach((cell) => {
            makeCellEditable(cell);
        });
    }

    /**
     *
     * @param cell
     */
    #selectCell(cell)
    {
        cell.classList.add("selected");
    }

    /**
     *
     * @param cell
     */
    #deselectCell(cell)
    {
        cell.classList.remove("selected");
    }

    #applyCSSToCell(cell)
    {
        const json = this.json;
        const colId = Number(cell.dataset.colId);
        const rowId = Number(cell.dataset.rowId);
        const cellSettings = (json.data[rowId] && json.data[rowId][colId]) ? json.data[rowId][colId].settings : {};

        const width = (cellSettings && cellSettings.width) ? cellSettings.width : '';
        const alignment = (cellSettings && cellSettings.alignment) ? cellSettings.alignment : json.settings.alignment;
        const color = (cellSettings && cellSettings.color) ? cellSettings.color : json.settings.color;
        const css = (cellSettings && cellSettings.css) ? cellSettings.css : '';
        const background = (cellSettings && cellSettings.backgroundColor) ? {
            color: cellSettings.backgroundColor,
            zebra: cellSettings.backgroundColor,
        } : json.settings.background;

        const border =  {
            style: (cellSettings && cellSettings.borderStyle) ? cellSettings.borderStyle : json.settings.border.style,
            color: (cellSettings && cellSettings.borderColor) ? cellSettings.borderColor : json.settings.border.color,
            thickness: (cellSettings && cellSettings.borderThickness) ? cellSettings.borderThickness : json.settings.border.thickness
        };

        if(css !== ''){
            cell.classList.add(css);
        }

        cell.style.width = width;
        cell.style.color = color;
        cell.style.textAlign = alignment;
        cell.style.borderStyle = border.style;
        cell.style.borderColor = border.color;
        cell.style.borderWidth = `${border.thickness}px`;
        cell.style.background = (rowId % 2) ? background.zebra : background.color;
    };

    /**
     *
     * @param cell
     */
    #addContextMenu(cell)
    {
        const $this = this;
        const colId = Number(cell.dataset.colId);
        const rowId = Number(cell.dataset.rowId);
        const cellSettings = (this.json.data[rowId] && this.json.data[rowId][colId]) ? this.json.data[rowId][colId].settings : {};
        const settings = this.json.settings;
        const id = `context-menu-${rowId}-${colId}`;

        const defaultWidth = (cellSettings && cellSettings.width) ? cellSettings.width : '';
        const defaultAlignment = (cellSettings && cellSettings.alignment) ? cellSettings.alignment : settings.alignment;
        const defaultColor = (cellSettings && cellSettings.color) ? cellSettings.color : settings.color;
        const defaultCss = (cellSettings && cellSettings.css) ? cellSettings.css : '';
        const defaultBackground = (cellSettings && cellSettings.backgroundColor) ? {
            color: cellSettings.backgroundColor,
            zebra: cellSettings.backgroundColor,
        } : settings.background;
        const defaultBorder = {
            style: (cellSettings && cellSettings.borderStyle) ? cellSettings.borderStyle : settings.border.style,
            color: (cellSettings && cellSettings.borderColor) ? cellSettings.borderColor : settings.border.color,
            thickness: (cellSettings && cellSettings.borderThickness) ? cellSettings.borderThickness : settings.border.thickness
        };

        const ctxMenu = document.createElement("div");
        ctxMenu.id = id;
        ctxMenu.classList.add("acpt-context-menu");
        ctxMenu.innerHTML = `
            <h3>Cell #${this.#numberToLetter(colId)}${rowId} settings</h3>
            <div class="input-wrapper">
                <label for="${id}-alignment">${useTranslation("Alignment")}</label>
                <select id="${id}-alignment">
                    <option ${defaultAlignment === 'left' ? "selected" : ""} value="left">${useTranslation("left")}</option>
                    <option ${defaultAlignment === 'center' ? "selected" : ""} value="center">${useTranslation("center")}</option>
                    <option ${defaultAlignment === 'right' ? "selected" : ""} value="right">${useTranslation("right")}</option>
                </select>
            </div> 
            <div class="input-wrapper">
                <label for="${id}-width">${useTranslation("Width")}</label>
                <input id="${id}-width" type="text" value="${defaultWidth}" />
            </div> 
            <div class="input-wrapper">
                <label for="${id}-border">${useTranslation("Border")}</label>
                <div class="inputs">
                    <select id="${id}-border-thickness">
                        <option ${Number(defaultBorder.thickness) === 1 ? "selected" : ""} value="1">1px</option>
                        <option ${Number(defaultBorder.thickness) === 2 ? "selected" : ""} value="2">2px</option>
                        <option ${Number(defaultBorder.thickness) === 3 ? "selected" : ""} value="3">3px</option>
                        <option ${Number(defaultBorder.thickness) === 4 ? "selected" : ""} value="4">4px</option>
                        <option ${Number(defaultBorder.thickness) === 5 ? "selected" : ""} value="5">5px</option>
                        <option ${Number(defaultBorder.thickness) === 6 ? "selected" : ""} value="6">6px</option>
                        <option ${Number(defaultBorder.thickness) === 7 ? "selected" : ""} value="7">7px</option>
                        <option ${Number(defaultBorder.thickness) === 8 ? "selected" : ""} value="8">8px</option>
                        <option ${Number(defaultBorder.thickness) === 9 ? "selected" : ""} value="9">9px</option>
                        <option ${Number(defaultBorder.thickness) === 10 ? "selected" : ""} value="10">10px</option>
                        <option ${Number(defaultBorder.thickness) === 11 ? "selected" : ""} value="11">11px</option>
                        <option ${Number(defaultBorder.thickness) === 12 ? "selected" : ""} value="12">12px</option>
                        <option ${Number(defaultBorder.thickness) === 13 ? "selected" : ""} value="13">13px</option>
                        <option ${Number(defaultBorder.thickness) === 14 ? "selected" : ""} value="14">14px</option>
                        <option ${Number(defaultBorder.thickness) === 15 ? "selected" : ""} value="15">15px</option>
                        <option ${Number(defaultBorder.thickness) === 16 ? "selected" : ""} value="16">16px</option>
                        <option ${Number(defaultBorder.thickness) === 17 ? "selected" : ""} value="17">17px</option>
                        <option ${Number(defaultBorder.thickness) === 18 ? "selected" : ""} value="18">18px</option>
                        <option ${Number(defaultBorder.thickness) === 19 ? "selected" : ""} value="19">19px</option>
                        <option ${Number(defaultBorder.thickness) === 20 ? "selected" : ""} value="20">20px</option>
                    </select>
                    <select id="${id}-border-style">
                        <option ${defaultBorder.style === "solid" ? "selected" : ""} value="solid">Solid</option>
                        <option ${defaultBorder.style === "dotted" ? "selected" : ""} value="dotted">Dotted</option>
                        <option ${defaultBorder.style === "dashed" ? "selected" : ""} value="dashed">Dashed</option>
                        <option ${defaultBorder.style === "double" ? "selected" : ""} value="double">Double</option>
                        <option ${defaultBorder.style === "groove" ? "selected" : ""} value="groove">Groove</option>
                        <option ${defaultBorder.style === "ridge" ? "selected" : ""} value="ridge">Ridge</option>
                        <option ${defaultBorder.style === "inset" ? "selected" : ""} value="inset">Inset</option>
                        <option ${defaultBorder.style === "outset" ? "selected" : ""} value="outset">Outset</option>
                        <option ${defaultBorder.style === "none" ? "selected" : ""} value="none">None</option>
                        <option ${defaultBorder.style === "hidden" ? "selected" : ""} value="hidden">Hidden</option>
                    </select>
                    <input id="${id}-border-color" type="text" value="${defaultBorder.color}" />
                </div>
            </div> 
            <div class="input-wrapper">
                <label for="${id}-color">${useTranslation("Text color")}</label>
                <input id="${id}-color" type="text" value="${defaultColor}" />
            </div> 
            <div class="input-wrapper">
                <label for="${id}-background-color">${useTranslation("Background")}</label>
                <input id="${id}-background-color" type="text" value="${defaultBackground.color}" />
            </div> 
            <div class="input-wrapper">
                <label for="${id}-css">${useTranslation("CSS classes")}</label>
                <input id="${id}-css" type="text" value="${defaultCss}" />
            </div> 
        `;

        cell.appendChild(ctxMenu);

        const width = ctxMenu.querySelector(`#${id}-width`);
        const alignment = ctxMenu.querySelector(`#${id}-alignment`);
        const borderThickness = ctxMenu.querySelector(`#${id}-border-thickness`);
        const borderStyle = ctxMenu.querySelector(`#${id}-border-style`);
        const borderColor = ctxMenu.querySelector(`#${id}-border-color`);
        const color = ctxMenu.querySelector(`#${id}-color`);
        const backgroundColor = ctxMenu.querySelector(`#${id}-background-color`);
        const css = ctxMenu.querySelector(`#${id}-css`);

        width.addEventListener('keyup', function (e) {
            const width = e.target.value;
            cell.style.width = width;
            $this.json.data[rowId][colId].settings = {...$this.json.data[rowId][colId].settings, ...{width: width}};
            $this.#dispatchChangeEvent();
        });

        alignment.addEventListener('change', function (e) {
            const alignment = e.target.value;
            cell.style.textAlign = alignment;
            $this.json.data[rowId][colId].settings = {...$this.json.data[rowId][colId].settings, ...{alignment: alignment}};
            $this.#dispatchChangeEvent();
        });

        borderThickness.addEventListener('change', function (e) {
            const borderThickness = e.target.value;
            cell.style.borderWidth = `${borderThickness}px`;
            $this.json.data[rowId][colId].settings = {...$this.json.data[rowId][colId].settings, ...{borderThickness: borderThickness}};
            $this.#dispatchChangeEvent();
        });

        borderStyle.addEventListener('change', function (e) {
            const borderStyle = e.target.value;
            cell.style.borderStyle = borderStyle;
            $this.json.data[rowId][colId].settings = {...$this.json.data[rowId][colId].settings, ...{borderStyle: borderStyle}};
            $this.#dispatchChangeEvent();
        });

        borderColor.addEventListener('keyup', function (e) {
            const borderColor = e.target.value;
            cell.style.borderColor = borderColor;
            $this.json.data[rowId][colId].settings = {...$this.json.data[rowId][colId].settings, ...{borderColor: borderColor}};
            this.#dispatchChangeEvent();
        });

        color.addEventListener('keyup', function (e) {
            const color = e.target.value;
            cell.style.color = color;
            $this.json.data[rowId][colId].settings = {...$this.json.data[rowId][colId].settings, ...{color: color}};
            $this.#dispatchChangeEvent();
        });

        backgroundColor.addEventListener('keyup', function (e) {
            const backgroundColor = e.target.value;
            cell.style.background = backgroundColor;
            $this.json.data[rowId][colId].settings = {...$this.json.data[rowId][colId].settings, ...{backgroundColor: backgroundColor}};
            $this.#dispatchChangeEvent();
        });

        css.addEventListener('keyup', function (e) {
            const css = e.target.value;
            cell.classList.add(css);
            $this.json.data[rowId][colId].settings = {...$this.json.data[rowId][colId].settings, ...{css: css}};
            $this.#dispatchChangeEvent();
        });
    }

    /**
     *
     * @param cell
     */
    #clickAndSelectCell(cell)
    {
        const $this = this;

        cell.addEventListener("click", function(e){
            e.preventDefault();
            $this.#selectCell(cell);
        });
    }

    /**
     *
     * @param cell
     * @param ctxMenu
     */
    #clickOutsideTheCell(cell, ctxMenu)
    {
        const $this = this;

        window.addEventListener('click', function(e){
            if (!cell.contains(e.target)){
                ctxMenu.style.display = "none";
                $this.#deselectCell(cell);
            }
        });
    }

    /**
     *
     * @param cell
     * @param ctxMenu
     */
    #doubleClickAndMakeCellEditable(cell, ctxMenu)
    {
        const $this = this;

        cell.addEventListener("dblclick", function(e){
            $this.#deselectCell(cell);
            $this.#editACell(cell);
        });
    }

    /**
     *
     * @param cell
     * @param ctxMenu
     */
    #blurAndRemoveDestroyCellEditable(cell, ctxMenu)
    {
        const $this = this;

        cell.addEventListener('blur', function () {
            $this.#deselectCell(cell);
            cell.removeAttribute("contenteditable", "true");
            cell.removeAttribute("spellcheck", "false");
        }, true);
    }

    /**
     *
     * @param cell
     * @param ctxMenu
     * @param cellRowId
     * @param cellColId
     */
    #detectAnyChangeInCellContent(cell, ctxMenu,cellRowId, cellColId)
    {
        const $this = this;

        // handle TAB keypress to navigate the table cells
        cell.addEventListener("keydown", function(e) {
            if(event.which === 9){
                e.preventDefault();

                /**
                 *
                 * @return {null|*}
                 */
                const findNextCell = () => {
                    if($this.json.data[cellRowId] && $this.json.data[cellRowId][cellColId+1]){
                        return document.querySelector(`[data-col-id="${(cellColId+1)}"][data-row-id="${cellRowId}"]`);
                    } else if($this.json.data[cellRowId+1] && $this.json.data[cellRowId+1][0]){
                        return document.querySelector(`[data-col-id="0"][data-row-id="${cellRowId+1}"]`);
                    } else {
                        return document.querySelector(`[data-col-id="0"][data-row-id="0"]`);
                    }
                };

                const nextCell = findNextCell();

                if(nextCell !== null){
                    $this.#deselectCell(cell);
                    $this.#editACell(nextCell);
                }
            }
        });

        cell.addEventListener('input', function(e){

            if(e.target.classList.contains("editable") && $this.json.data[cellRowId] && $this.json.data[cellRowId][cellColId]){

                const cellContent = cell.innerText;
                const row = $this.json.data[cellRowId];

                row.map((cell, index) => {
                    if(index === cellColId){
                        $this.json.data[cellRowId][cellColId] = {
                            value: $this.#sanitizeStringForJSON(cellContent),
                            settings: cell.settings
                        };
                    }
                });

                $this.#dispatchChangeEvent();
            }
        });
    }

    /**
     *
     * @param string
     * @return {*}
     */
    #sanitizeStringForJSON(string)
    {
        return string
            .replace(/[\"]/g, "'")
            .replace(/[\\]/g, '\\\\')
            .replace(/[\/]/g, '\\/')
            .replace(/[\b]/g, '\\b')
            .replace(/[\f]/g, '\\f')
            .replace(/[\n]/g, '\\n')
            .replace(/[\r]/g, '\\r')
            .replace(/[\t]/g, '\\t')
        ;
    }

    /**
     *
     * @param cell
     * @param ctxMenu
     */
    #toggleContextMenu(cell, ctxMenu)
    {
        const $this = this;

        cell.addEventListener("contextmenu",function(e){
            e.preventDefault();
            $this.#selectCell(cell);

            const ctxMenus = document.querySelectorAll('.acpt-context-menu');
            ctxMenus.forEach(function(ctx) {
                ctx.style.display = "none";
            });

            ctxMenu.style.display = "block";
        },false);
    }

    /**
     * Make the table sortable
     * @param table
     */
    #makeTableResizableAndSortable(table)
    {
        const tableBody = table.querySelector('tbody');

        // Make rows sortable
        const rowsSortable = new Sortable(tableBody, {
            animation: 150,
        });

        // Make columns and table header cells draggable using interact.js
        const headers = table.querySelectorAll('th');
        interact(headers).draggable({
            // Enable both left and right edges for dragging
            edges: { left: true, right: true, bottom: false, top: false },
            listeners: {
                start(event) {
                    const target = event.target;
                    target.classList.add('dragging');
                },
                move(event) {
                    const target = event.target;
                    const dx = event.dx;
                    target.style.transform = `translate(${dx}px)`;
                },
                end(event) {
                    const target = event.target;
                    target.style.transform = '';
                    target.classList.remove('dragging');
                },
            },
        }).resizable({
            // Enable right edge for resizing
            edges: { right: true },
            restrictEdges: {
                outer: 'parent',
            },
            restrictSize: {
                min: { width: 50 },
            },
            listeners: {
                move(event) {
                    const target = event.target;
                    const width = parseFloat(target.style.width) || 0;
                    target.style.width = width + event.dx + 'px';
                },
            },
        });
    }

    /**
     * Create a resizable table
     * @param table
     */
    #createResizableTable(table)
    {
        const cols = table.querySelectorAll('th');

        const createResizableColumn = (col, resizer) => {
            let x = 0;
            let w = 0;

            const mouseDownHandler = function (e) {
                x = e.clientX;


                const styles = window.getComputedStyle(col);
                w = parseInt(styles.width, 10);


                document.addEventListener('mousemove', mouseMoveHandler);
                document.addEventListener('mouseup', mouseUpHandler);


                resizer.classList.add('resizing');
            };


            const mouseMoveHandler = function (e) {
                const dx = e.clientX - x;
                col.style.width = `${w + dx}px`;
            };


            const mouseUpHandler = function () {
                resizer.classList.remove('resizing');
                document.removeEventListener('mousemove', mouseMoveHandler);
                document.removeEventListener('mouseup', mouseUpHandler);
            };


            resizer.addEventListener('mousedown', mouseDownHandler);
        };

        [].forEach.call(cols, function (col) {
            // Add a resizer element to the column
            const resizer = document.createElement('div');
            resizer.classList.add('resizer');

            // Set the height
            resizer.style.height = `${table.offsetHeight}px`;
            col.appendChild(resizer);

            createResizableColumn(col, resizer);
        });
    };

    /**
     *
     * @param id
     */
    addRow(id)
    {
        const table = document.getElementById(this.tableId);

        if(table){
            const $this = this;
            const rows = table.querySelectorAll("tr");

            // clone data
            let json = {...this.json};
            let aRowWasAdded = false;

            rows.forEach(function(row) {
                const rowId = row.dataset.rowId;

                if(typeof rowId !== 'undefined' && Number(rowId) === Number(id)){

                    let updatedData = [];

                    json.data.map((el, index) => {
                        if(Number(rowId) === Number(index)){
                            aRowWasAdded = true;
                            updatedData.push($this.#newRowElement());
                        }

                        updatedData.push(el);
                    });

                    json.data = updatedData;
                }
            });

            // we are adding a row at the bottom
            if(aRowWasAdded === false){
                json.data.push(this.#newRowElement());
            }

            json.settings.rows = Number(json.settings.rows) + 1;
            this.createTable(json);
        }
    }

    /**
     *
     * @param cell
     */
    #editACell(cell) {
        cell.setAttribute("contenteditable", "true");
        cell.setAttribute("spellcheck", "false");
        cell.focus();

        if(cell.innerText.includes(this.defaultCellValue)){
            cell.innerText = '';
        }
    }

    /**
     *
     * @return {[]}
     */
    #newRowElement() {
        let newRow = [];

        for (let i = 0; i < Number(this.json.settings.columns); i++) {
            newRow.push(this.defaultCellObject);
        }

        return newRow;
    };

    /**
     *
     * @param id
     */
    deleteRow(id)
    {
        const table = document.getElementById(this.tableId);

        if(table){
            const $this = this;
            const rows = table.querySelectorAll("tr");

            // we are removing tfoot row
            if(Number($this.json.settings.rows) < Number(id)){
                $this.json.settings.footer = false;
                $this.json.data = $this.json.data.filter((el, index) => index !== ($this.json.data.length-1));
            } else {
                rows.forEach(function(row) {
                    const rowId = row.dataset.rowId;
                    if(Number(rowId) === Number(id)){

                        if($this.json.settings.header === true && Number(rowId) === 0){
                            $this.json.settings.header = false;
                        } else {
                            $this.json.settings.rows = $this.json.settings.rows -1;
                        }

                        let updatedData = [];

                        $this.json.data.map((el, index) => {
                            if(Number(rowId) !== Number(index)){
                                updatedData.push(el);
                            }
                        });

                        $this.json.data = updatedData;
                    }
                });
            }

            this.createTable($this.json);
        }
    }

    /**
     *
     * @param id
     */
    addColumn(id)
    {
        const table = document.getElementById(this.tableId);

        if(table){
            // clone data
            let json = {...this.json};

            json.data.map((row) => {
                row.splice(id, 0, this.defaultCellObject);
            });

            json.settings.columns = Number(json.settings.columns) + 1;
            this.createTable(json);
        }
    }

    /**
     *
     * @param id
     */
    deleteColumn(id)
    {
        const table = document.getElementById(this.tableId);

        if(table){

            // clone data
            let json = {...this.json};

            json.data.map((row) => {
                row.splice(id, 1);
            });

            json.settings.columns = Number(json.settings.columns) - 1;
            this.createTable(json);
        }
    }
}