import {fetchLanguages} from "./_admin_helpers.js";
import {handleRelationalFieldsEvents, initRelationalFields} from "./_admin_relational.js";
import {initSortable} from "./_admin_sortable.js";
import {handleRepeaterFieldsEvents} from "./_admin_repeater.js";
import {handleFlexibleFieldsEvents} from "./_admin_flexible.js";
import {handleListFieldsEvents} from "./_admin_list.js";
import {handleFileFieldsEvents} from "./_admin_file.js";
import {handleIconFieldsEvents} from "./_admin_iconpicker.js";
import {handleColorFieldsEvents, initColorPicker} from "./_admin_colorpicker.js";
import {handleDateFieldsEvents, initDateRangePicker} from "./_admin_datepicker.js";
import {runWooCommerceFixes} from "./_admin_woocommerce.js";
import {initEditorFields} from "./_admin_editor.js";
import {applyVisibilityFromLocalStorage, handleGroupedElementEvents, initBarcode, initCodeMirror, initCountrySelect, initEmbed, initIdGenerators, initImageSlider, initIntlTelInput, initQRCodeGenerator, initSelectize, initTextarea, runMiscFunctions} from "./_admin_misc.js";

var $ = jQuery.noConflict();

/**
 * Main admin JS
 */
jQuery(function ($) {

    /**
     * ===================================================================
     * FETCH LANGUAGES
     * ===================================================================
     */

    fetchLanguages()
        .then((response) => response.json())
        .then((translations) => {
            document.adminjs = {
                translations: translations
            };
            document.dispatchEvent(new Event("fetchLanguages"));
        })
        .catch((err) => {
            console.error("Something went wrong!", err);
        });

    /**
     * ===================================================================
     * RELATION SELECTOR SECTION
     * ===================================================================
     */

    handleRelationalFieldsEvents();

    /**
     * ===================================================================
     * REPEATER ELEMENTS HANDLING
     * ===================================================================
     */

    handleRepeaterFieldsEvents();

    /**
     * ===================================================================
     * FLEXIBLE ELEMENTS HANDLING
     * ===================================================================
     */

    handleFlexibleFieldsEvents();

    /**
     * ===================================================================
     * LIST ELEMENTS HANDLING
     * ===================================================================
     */

    handleListFieldsEvents();

    /**
     * ===================================================================
     * FILE FIELD HANDLING
     * ===================================================================
     */

    handleFileFieldsEvents();

    /**
     * ===================================================================
     * COLOR PICKER
     * ===================================================================
     */

    handleColorFieldsEvents();

    /**
     * ===================================================================
     * ICON PICKER
     * ===================================================================
     */

    handleIconFieldsEvents();

    /**
     * ===================================================================
     * DATE PICKER
     * ===================================================================
     */

    handleDateFieldsEvents();

    /**
     * ===================================================================
     * MISCELLANEA
     * ===================================================================
     */

    runMiscFunctions();
    runWooCommerceFixes();

    /**
     * ===================================================================
     * INIT
     * ===================================================================
     */

    // Init the dependencies
    function init() {
        initEditorFields();
        initSelectize();
        initCodeMirror();
        initColorPicker();
        initSortable();
        initDateRangePicker("daterange");
        initDateRangePicker("date");
        initDateRangePicker("datetime");
        initDateRangePicker("time");
        initIntlTelInput();
        initCountrySelect();
        initQRCodeGenerator();
        initBarcode();
        initEmbed();
        initTextarea();
        initImageSlider();
        initRelationalFields();
        initIdGenerators();
        applyVisibilityFromLocalStorage();
        handleGroupedElementEvents();
    }

    init();
});