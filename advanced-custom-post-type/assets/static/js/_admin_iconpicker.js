import {useTranslation} from "./_admin_commons.js";
import {determineSVGAspectRatio} from "./_admin_helpers.js";

var $ = jQuery.noConflict();
const ICONIFY_API_ROOT = 'https://api.iconify.design/';

export const handleIconFieldsEvents = () => {

    // open the modal
    $('body').on('click', '.acpt-icon-picker-button', function(e) {
        e.preventDefault();

        const $this = $(this);
        const targetId = $this.data('target-id');
        const targetModalId = targetId+'_modal';
        const targetModal = $('#'+targetModalId);

        (targetModal.hasClass('hidden')) ? targetModal.removeClass('hidden') : targetModal.addClass('hidden');
    });

    // choose between upload or browse icons
    $('body').on('click', '.acpt-icon-picker-selector > li', function(e){
        e.preventDefault();

        const $this = $(this);
        const selector = $this.parent('ul');
        const value = $this.data('value');

        if(value === 'upload'){
            selector.siblings(".acpt-icon-picker-upload").removeClass('hidden');
            selector.addClass("hidden");
        } else if(value === 'browse'){
            selector.siblings(".acpt-icon-picker-browse").removeClass('hidden');
            selector.addClass("hidden");
        }

        selector.prev(".acpt-icon-picker-back").removeClass('hidden');
    });

    // back to selector
    $('body').on('click', '.acpt-icon-picker-back', function(e){
        e.preventDefault();

        const $this = $(this);
        const selector = $this.next('ul');
        $this.addClass('hidden');
        selector.siblings(".acpt-icon-picker-upload").addClass('hidden');
        selector.siblings(".acpt-icon-picker-browse").addClass('hidden');
        selector.removeClass("hidden");
    });

    // change icon size
    $('body').on('change', '.acpt-icon-picker-size > input', function(e) {
        e.preventDefault();

        const $this = $(this);
        const targetId = $this.data('target-id');
        let svg = $(`.acpt-icon-picker-preview[data-target-id=${targetId}]`).html();
        const aspectRatio = determineSVGAspectRatio(svg);
        const width = $this.val();
        const height = Math.ceil(width * aspectRatio);
        const widthPattern = /width=\"(.*?)\"/;
        const heightPattern = /height=\"(.*?)\"/;
        const checkWidthProp = widthPattern.test(svg);
        const checkHeightProp = heightPattern.test(svg);

        if(checkWidthProp && checkHeightProp){
            svg = svg.replace(widthPattern, `width="${width}px"`);
            svg = svg.replace(heightPattern, `height="${height}px"`);
        } else {
            svg = svg.replace("<svg ", `<svg width="${width}px" `);
            svg = svg.replace("<svg ", `<svg height="${height}px" `);
        }

        $(`.acpt-icon-picker-preview[data-target-id=${targetId}]`).html(svg);
        $(`.acpt-icon-picker-value[data-target-id="${targetId}"]`).val(svg);
    });

    // clear icon field
    $('body').on('click', '.acpt-icon-picker-delete', function(e) {
        e.preventDefault();

        const $this = $(this);
        const targetId = $this.data('target-id');

        $(`.acpt-icon-picker-preview[data-target-id=${targetId}]`).html('');
        $(`#${targetId}`).val('');
        $this.addClass('hidden');
        $(`.acpt-icon-picker-preview-wrapper[data-target-id=${targetId}]`).addClass('hidden');
        $(`.acpt-icon-picker-size[data-target-id=${targetId}]`).addClass('hidden');
        $(`.acpt-icon-picker-stroke[data-target-id=${targetId}]`).addClass('hidden');
        $(`.acpt-icon-picker-fill[data-target-id=${targetId}]`).addClass('hidden');
    });

    // upload a custom SVG icon
    $('body').on('change', '.acpt-icon-picker-upload > input', function(e) {
        e.preventDefault();

        const $this = $(this);
        const targetId = $this.data('target-id');
        const file =  $this.prop('files')[0];
        const read = new FileReader();

        read.readAsBinaryString(file);
        read.onloadend = function(){
            const svgString = read.result;
            const parser = new DOMParser();
            const doc = parser.parseFromString(svgString, "image/svg+xml");
            const svg = doc.querySelector("svg").outerHTML;

            $(`.acpt-icon-picker-value[data-target-id="${targetId}"]`).val(svg);
            const targetModal = $('#'+targetId+'_modal');
            $('.acpt-icon-picker-preview[data-target-id="'+targetId+'"]').html(svg);
            $(`.acpt-icon-picker-preview-wrapper[data-target-id=${targetId}]`).removeClass('hidden');
            $(`.acpt-icon-picker-size[data-target-id=${targetId}]`).removeClass('hidden');
            $(`.acpt-icon-picker-stroke[data-target-id=${targetId}]`).removeClass('hidden');
            $(`.acpt-icon-picker-fill[data-target-id=${targetId}]`).removeClass('hidden');
            $(`.acpt-icon-picker-size[data-target-id=${targetId}] > input`).val('24');
            $(`input.acpt-icon-picker-stroke[data-target-id=${targetId}]`).val('#777777');
            $(`input.acpt-icon-picker-fill[data-target-id=${targetId}]`).val('#777777');
            (targetModal.hasClass('hidden')) ? targetModal.removeClass('hidden') : targetModal.addClass('hidden');
        }
    });

    // browse icons
    $('body').on('click', '.acpt-icon-picker-provider', function (e) {

        const $this = $(this);
        ($this.hasClass('active')) ? $this.removeClass('active') : $this.addClass('active');

        let visibleProviders = [];
        $('.acpt-icon-picker-provider.active').each(function() {
            const provider =  $(this).data('value');
            visibleProviders.push(provider);
        });

        $('.acpt-icon-picker-icon').each(function () {
            const provider =  $(this).data('prefix');
            const $this = $(this);

            if(visibleProviders.length > 0){
                (visibleProviders.includes(provider)) ? $this.removeClass('hidden') : $this.addClass('hidden');
            } else {
                $this.removeClass('hidden');
            }
        });
    });

    // search icons
    $('body').on('input', '.acpt-icon-picker-search', function(e) {

        const $this = $(this);
        const search = e.target.value;
        const results = $this.next('.acpt-icon-picker-results');
        const targetId = results.data('target-id');

        if(search.length >= 3){
            $.ajax({
                type: 'GET',
                url: `${ICONIFY_API_ROOT}search?query=${search}&limit=96`,
                success: function(data) {
                    results.html('');

                    // create the filter by provider
                    if(data.collections){

                        const providers = Object.keys(data.collections).sort();
                        let providerFilter = `<div class="acpt-icon-picker-providers">`;

                        providers.forEach((provider) => {
                            if(data.collections[provider] && data.collections[provider]?.name){
                                providerFilter += `<div data-target-id="${targetId}" data-value="${provider}" class="acpt-icon-picker-provider">${data.collections[provider]?.name}</div>`;
                            }
                        });

                        providerFilter += `</div>`;

                        results.append(providerFilter);
                    }

                    // append icons
                    if(data.icons.length > 0){
                        data.icons.forEach((icon)=>{
                            const iconSplitted = icon.split(':');
                            const prefix = iconSplitted[0];
                            const iconName = iconSplitted[1];
                            const svgUrl = `${ICONIFY_API_ROOT}${prefix}/${iconName}.svg`;
                            results.append(`<div data-target-id="${targetId}" data-value="${icon}" data-prefix="${prefix}" class="acpt-icon-picker-icon" title="${icon}"><img src="${svgUrl}" width="32" height="32"></div>`);
                        });
                    } else {
                        results.append(`<div>${useTranslation("Sorry, no result match.")}</div>`);
                    }

                    const deleteButton = $(`.acpt-icon-picker-delete[data-target-id="${targetId}"]`);
                    deleteButton.removeClass('hidden');
                },
                error: function(error) {
                    console.error(error);
                    results.append(useTranslation("There was an error fetching icons, retry later."));
                },
            });
        }
    });

    // pick ad icon from iconify
    $('body').on('click', '.acpt-icon-picker-icon', function(e) {
        e.preventDefault();

        const $this = $(this);
        const value = $this.data('value');
        const targetId = $this.data('target-id');
        const iconSplitted = value.split(':');
        const prefix = iconSplitted[0];
        const iconName = iconSplitted[1];
        const svgUrl = `${ICONIFY_API_ROOT}${prefix}/${iconName}.svg`;

        $.ajax({
            type: 'GET',
            url: svgUrl,
            success: function(data) {
                let svg = data.children[0].outerHTML;
                const aspectRatio = determineSVGAspectRatio(svg);
                const width = 24;
                const height = Math.ceil(width * aspectRatio);

                svg = svg.replace(/width=\"(.*?)\"/, `width="${width}px"`);
                svg = svg.replace(/height=\"(.*?)\"/, `height="${height}px"`);
                $(`.acpt-icon-picker-value[data-target-id="${targetId}"]`).val(svg);
                const targetModal = $('#'+targetId+'_modal');
                $('.acpt-icon-picker-preview[data-target-id="'+targetId+'"]').html(svg);
                $(`.acpt-icon-picker-preview-wrapper[data-target-id=${targetId}]`).removeClass('hidden');
                $(`.acpt-icon-picker-size[data-target-id=${targetId}]`).removeClass('hidden');
                $(`.acpt-icon-picker-stroke[data-target-id=${targetId}]`).removeClass('hidden');
                $(`.acpt-icon-picker-fill[data-target-id=${targetId}]`).removeClass('hidden');
                $(`.acpt-icon-picker-size[data-target-id=${targetId}] > input`).val('24');
                $(`input.acpt-icon-picker-stroke[data-target-id=${targetId}]`).val('#777777');
                $(`input.acpt-icon-picker-fill[data-target-id=${targetId}]`).val('#777777');
                (targetModal.hasClass('hidden')) ? targetModal.removeClass('hidden') : targetModal.addClass('hidden');
            },
            error: function(error) {
                console.error(error);

                results.append(useTranslation("There was an error fetching icons, retry later."));
            },
        });
    });

    // close icon picker
    $('body').on('click', '.close-acpt-icon-picker', function(e) {
        e.preventDefault();

        const $this = $(this);
        const targetModalId = $this.data('target-id');
        const targetModal = $('#'+targetModalId);

        (targetModal.hasClass('hidden')) ? targetModal.removeClass('hidden') : targetModal.addClass('hidden');
    });
};
