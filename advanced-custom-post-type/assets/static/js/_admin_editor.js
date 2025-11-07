import {tinyMCEWordpressDefaultSettings} from "./_admin_commons.js";
import {gutenbergIsEnabled} from "./_admin_helpers.js";

var $ = jQuery.noConflict();
const { __ } = wp.i18n;

/**
 * init TinyMCE on dynamic generated children fields
 *
 * @param id
 * @param rows
 * @param toolbar
 */
export const initTinyMCE = (id, rows = 8, toolbar = 'full') => {
    try {
        if(typeof tinyMCE === 'undefined'){
            console.error("tinyMCE is not defined. Include it here");
            return;
        }

        const textarea = $(`#${id}`);
        const media = textarea.data("media");
        const loading = $(`#loading_${id}`);
        const buttons = $(`#buttons_${id}`);
        const chCounter = $(`#ch_counter_${id}`);
        const max = parseInt(chCounter.data("max"));
        const min = parseInt(chCounter.data("min"));
        const tabs = buttons.data("tabs") ?? 'full';

        tinyMCE.init(tinyMCEWordpressDefaultSettings(id, rows, toolbar));
        tinyMCE.execCommand('mceToggleEditor', false, id);
        const ed = tinyMCE.activeEditor;

        ed.on('change', function(e) {
            const wcSaveVariationChangesButton = $(".save-variation-changes");

            if(wcSaveVariationChangesButton){
                wcSaveVariationChangesButton.removeAttr('disabled');
            }
        });

        loading.remove();
        textarea.removeClass("hidden");

        // Add media and editor tabs buttons
        let html = '';

        if(media !== 1){
            html += `<div id="wp-${id}-media-buttons" class="wp-media-buttons">
                    <button type="button" id="insert-media-button" class="button insert-media add_media" data-editor="${id}">
                        <span class="wp-media-buttons-icon"></span> 
                        ${__("Add Media")}
                    </button>
                </div>`;
        }

        // tabs
        html += `<div class="wp-editor-tabs">`;

        if(tabs === 'full' || tabs === 'visual'){
            html += `
                    <button type="button" id="${id}-tmce" aria-pressed="true" class="wp-switch-editor switch-tmce" data-wp-editor-id="${id}">
                        ${__("Visual")}
                    </button>
                `;
        }

        if(tabs === 'full' || tabs === 'text'){
            html += `
                    <button type="button" id="${id}-html" class="wp-switch-editor switch-html" data-wp-editor-id="${id}">
                        ${__("Text")}
                    </button>
                `;
        }

        html += `</div>`;
        buttons.html(html);

        // Add character counter if max or min are defined
        const initCharacterCounter = () => {
            if(max || min){

                /**
                 *
                 * @return {number}
                 */
                const charCount = () => {

                    const text = ed.contentDocument.body.innerText;

                    if(text.trim()  === ""){
                        return 0;
                    }

                    return text.length;
                };

                /**
                 *
                 * @return {string}
                 */
                const renderCharCounter = () => {

                    const value = charCount();
                    let css = '';

                    if(value < min || value >= max){
                        css += 'danger';
                    } else if(value >= max-5){
                        css += 'warning';
                    } else {
                        css = '';
                    }

                    const limit = max ? max : "∞";

                    return `<span class="count ${css}">${charCount()}</span>/<span class="max">${limit}</span> `;
                };

                const handleMinValue = () => {

                    let button;

                    if(gutenbergIsEnabled()){
                        button = $(".editor-post-publish-button__button"); // gutenberg
                    } else if($("#publish").length > 0) {
                        button = $("#publish"); // classic editor (post types)
                    } else if($("#save-option-page").length > 0) {
                        button = $("#save-option-page"); // option pages
                    } else if($("#submit").length > 0) {
                        button = $("#submit"); // create new taxonomy
                    } else if($(`input[type="submit"]`).length > 0) {
                        button = $(`input[type="submit"]`); // edit taxonomy
                    }

                    const value = charCount();
                    const errors = chCounter.next(".acpt-error-list");
                    const editor = chCounter.prev(".acpt-wp-editor-wrapper");

                    // min
                    if(value < min){
                        const error = `Min length ${min}`;
                        textarea[0].setCustomValidity(error);
                        textarea[0].reportValidity();
                        errors.html(`<li>${error}</li>`);
                        editor.addClass(`has-errors`);

                        if(button){
                            button.attr('disabled', 'disabled');
                        }
                    } else {
                        textarea[0].setCustomValidity("");
                        textarea[0].reportValidity();
                        errors.html("");
                        editor.removeClass(`has-errors`);

                        if(button){
                            button.removeAttr('disabled');
                        }
                    }
                };

                /**
                 * On load, render the character counter and listen for handling min value
                 */
                ed.on('load', function(e) {
                    chCounter.html(renderCharCounter());
                    handleMinValue();
                });

                /**
                 * Update counter
                 * Set validation error if MIN value is not reached
                 */
                ed.on('keyup', function(e) {

                    const value = charCount();
                    const count = chCounter.find(".count");

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
                    handleMinValue();
                });

                /**
                 * Return false is MAX value is exceeded
                 */
                ed.on('keydown', function(e) {

                    const allowedKeys = [
                        "Backspace",
                        "Delete",
                        "Shift",
                        "Control",
                        "ArrowLeft",
                        "ArrowRight",
                    ];

                    if(allowedKeys.includes(e.key)){
                        return true;
                    }

                    const value = charCount();

                    // max
                    if(value >= max){
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }

                    return true;
                });
            }
        };

        /**
         * If tabs is equals to only Textual, switch to HTML tab
         */
        const applyTabs = () => {
            if(tabs !== 'text'){
                return;
            }

            ed.on('load', function(e) {
                const switchEditors = window.switchEditors;
                switchEditors.go(id, 'html');
            });
        };

        initCharacterCounter();
        applyTabs();

        // restart tinyMCE if:
        // - tinyMCE ahs not properly started
        // - Gutenberg is enabled (fix slow connection issue)
        if(gutenbergIsEnabled()){
            const t = setTimeout(function (){

                const editor = textarea.prev(".mce-tinymce");
                const iframe = editor.find("iframe");
                const iframeBody = iframe.contents().find("body");

                if(!iframeBody.attr("contenteditable")){
                    console.log("tinyMCE was not properly initialized, I am trying to restart it once again...");
                    tinyMCE.execCommand('mceRemoveEditor', false, id);
                    tinyMCE.init(tinyMCEWordpressDefaultSettings(id, rows, toolbar));
                    tinyMCE.execCommand('mceToggleEditor', false, id);
                    initCharacterCounter();
                    applyTabs();
                } else {
                    clearTimeout(t);
                }
            }, 4000);
        }

    } catch (e) {
        console.error(e);
        buttons.remove();
    }
};

/**
 * Destry TinyMCE instance
 * @param id
 */
export const destroyTinyMCE = (id) => {
    try {
        if(typeof tinyMCE === 'undefined'){
            console.error("tinymce is not defined. Include it here");
            return;
        }

        tinyMCE.execCommand('mceRemoveEditor', false, id);
    } catch (e) {
        console.error(e);
    }
};

export function initEditorFields() {
    const editorFields = $("textarea.acpt-wp-editor");
    if(editorFields && editorFields.length > 0){
        editorFields.each(function(i, editorField) {
            initTinyMCE(editorField.id, editorField.getAttribute("rows"), editorField.dataset.toolbar);
        });
    }
}