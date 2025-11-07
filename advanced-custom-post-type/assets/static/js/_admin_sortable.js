import {destroyTinyMCE, initTinyMCE} from "./_admin_editor.js";

/**
 * Sortable functions
 * @see https://github.com/lukasoppermann/html5sortable
 */

var $ = jQuery.noConflict();

export const initSortable = () => {

    try {
        if(typeof sortable !== 'undefined'){

            // playlist
            if($('.playlist-preview').length){
                sortable('.playlist-preview', {
                    acceptFrom: '.playlist-preview',
                    forcePlaceholderSize: true,
                    items: '.audio',
                    hoverClass: 'hover',
                    copy: false
                });

                // sortable playlist items feature
                $('.playlist-preview').each(function(index) {

                    let i = index;

                    if(sortable('.playlist-preview')[index]){
                        sortable('.playlist-preview')[index].addEventListener('sortupdate', function(e) {

                            const sortedItems = e.detail.destination.items;
                            let sortedIndexArray = [];

                            sortedItems.map((sortedItem)=>{
                                sortedIndexArray.push($(sortedItem).data('index'));
                            });

                            const $imageWrapper = $(this);
                            const $target = $imageWrapper.data('target');
                            const $placeholder = $('#'+$target+'_copy');
                            let $placeholderIds;

                            if($('#'+$target+'_attachment_id').length > 0){
                                $placeholderIds = $('#'+$target+'_attachment_id');
                            } else {
                                $placeholderIds = $("#"+$target+"\\[attachment_id\\]\\["+i+"\\]");
                            }

                            const $inputWrapper = $placeholder.next( '.inputs-wrapper' );

                            // update input readonly && update input hidden
                            const $savedIds = $placeholderIds.val().split(',');
                            const $savedValues = $placeholder.val().split(',');
                            const $savedInputs = $inputWrapper.children('input');

                            let $sortedIds = [];
                            let $sortedValues = [];
                            let $sortedInputs = [];

                            sortedItems.map((sortedItem)=>{

                                const sortedIndex = $(sortedItem).data('index');
                                const audio = $(sortedItem).find("audio");
                                const audioURL = audio.attr('src');
                                const audioId = audio.data('id');

                                $sortedIds.push(audioId);
                                $sortedValues.push(audioURL);
                                $savedInputs.each(function () {
                                    if($(this).data('index') === sortedIndex){
                                        $sortedInputs.push($(this));
                                    }
                                });
                            });

                            $placeholderIds.val($sortedIds.join(','));
                            $placeholder.val($sortedValues.join(','));

                            if($placeholder.length > 0){
                                $placeholder[0].dispatchEvent(new Event("change")); // dispatch change Event for ACPTConditionalRules
                            }

                            $inputWrapper.html($sortedInputs);
                        });
                    }
                });

                sortable('.playlist-preview', 'reload');
            }

            // gallery
            if($('.gallery-preview').length){

                sortable('.gallery-preview', {
                    acceptFrom: '.gallery-preview',
                    forcePlaceholderSize: true,
                    items: '.image',
                    hoverClass: 'hover',
                    copy: false
                });

                // sortable gallery items feature
                $('.gallery-preview').each(function(index) {

                    let i = index;

                    if(sortable('.gallery-preview')[index]){
                        sortable('.gallery-preview')[index].addEventListener('sortupdate', function(e) {

                            const sortedItems = e.detail.destination.items;
                            let sortedIndexArray = [];

                            const $imageWrapper = $(this);
                            const $target = $imageWrapper.data('target');
                            const $placeholder = $('#'+$target+'_copy');
                            let $placeholderIds;

                            if($('#'+$target+'_attachment_id').length > 0){
                                $placeholderIds = $('#'+$target+'_attachment_id');
                            } else {
                                $placeholderIds = $("#"+$target+"\\[attachment_id\\]\\["+i+"\\]");
                            }

                            const $inputWrapper = $placeholder.next( '.inputs-wrapper' );

                            // update input readonly && update input hidden
                            const $savedIds = $placeholderIds.val().split(',');
                            const $savedValues = $placeholder.val().split(',');
                            const $savedInputs = $inputWrapper.children('input');

                            let $sortedIds = [];
                            let $sortedValues = [];
                            let $sortedInputs = [];

                            sortedItems.map((sortedItem)=>{

                                const sortedIndex = $(sortedItem).data('index');
                                const img = $(sortedItem).find("img");
                                const imgURL = img.attr('src');
                                const imgId = img.data('id');

                                $sortedIds.push(imgId);
                                $sortedValues.push(imgURL);
                                $savedInputs.each(function () {
                                    if($(this).data('index') === sortedIndex){
                                        $sortedInputs.push($(this));
                                    }
                                });
                            });

                            $placeholderIds.val($sortedIds.join(','));
                            $placeholder.val($sortedValues.join(','));

                            if($placeholder.length > 0){
                                $placeholder[0].dispatchEvent(new Event("change")); // dispatch change Event for ACPTConditionalRules
                            }

                            $inputWrapper.html($sortedInputs);
                        });
                    }
                });

                sortable('.gallery-preview', 'reload');
            }

            // repeater fields
            if($('.acpt-sortable').length > 0){

                $('.acpt-sortable').each(function(index) {

                    const elementId = $(this).attr('id');
                    let id = elementId.replace("acpt-sortable-", "");
                    
                    // relational fields
                    if(elementId.includes("selected_items_")){
                        id = elementId.replace("selected_items_", "");
                    }

                    sortable(`#${elementId}`, {
                        acceptFrom: `#${elementId}`,
                        forcePlaceholderSize: true,
                        items: `.sortable-li-${id}`,
                        handle: '.handle',
                        hoverClass: 'hover',
                        copy: false
                    })[0].addEventListener('sortupdate', function(e) {
                        const evt = new CustomEvent("acpt_grouped_element_sorted", { detail: { 'elementId': elementId }});
                        document.dispatchEvent(evt);
                    });

                    sortable(`#${elementId}`, 'reload');

                    // handle tinyMCE fields
                    if(sortable(`#${elementId}`)[index]){
                        sortable(`#${elementId}`)[index].addEventListener('sortupdate', function(e) {
                            if(e.detail.item.id){
                                const editorField = $(`#${e.detail.item.id}`).find("textarea.acpt-wp-editor");

                                if(editorField){
                                    destroyTinyMCE(editorField.attr("id"));
                                    initTinyMCE(editorField.attr("id"), editorField.attr("rows"), editorField.data("toolbar"));
                                }
                            }
                        });
                    }
                });
            }

            // nested flexible fields
            if($('.acpt-nested-sortable').length > 0){

                $('.acpt-nested-sortable').each(function(index) {
                    const elementId = $(this).attr('id');
                    const id = elementId.replace("block-elements-", "");

                    sortable('#'+elementId, {
                        acceptFrom: '#'+elementId,
                        forcePlaceholderSize: true,
                        items: `.sortable-li-${id}`,
                        handle: '.handle',
                        hoverClass: 'hover',
                        copy: false
                    })[0].addEventListener('sortupdate', function(e) {
                        const evt = new Event("acpt_grouped_element_sorted");
                        document.dispatchEvent(evt);
                    });

                    sortable('#'+elementId, 'reload');

                    // handle tinyMCE fields
                    if(sortable(`#${elementId}`)[index]){
                        sortable(`#${elementId}`)[index].addEventListener('sortupdate', function(e) {
                            if(e.detail.item.id){
                                const editorField = $(`#${e.detail.item.id}`).find("textarea.acpt-wp-editor");

                                if(editorField){
                                    destroyTinyMCE(editorField.attr("id"));
                                    initTinyMCE(editorField.attr("id", editorField.attr("rows"), editorField.data("toolbar")));
                                }
                            }
                        });
                    }
                });
            }
        }
    } catch (e) {
        console.error(e);
    }
};