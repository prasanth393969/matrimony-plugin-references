import {$$} from "./_admin_commons.js";
import {theDarkerColorFromList} from "./_admin_helpers.js";

var $ = jQuery.noConflict();

export const initColorPicker = (id = null) => {

    try {
        let selector = '.acpt-color-picker';
        if(id){
            selector = `#${id}`;
        }

        if($$(selector).length){
            if(typeof wp !== 'undefined'){
                $$(selector).wpColorPicker({
                    clear: function(event, ui){
                        const deleteButton = event.target;
                        const prev = deleteButton.previousElementSibling;
                        const element = prev.querySelector('input');

                        // SVG icon picker: remove stroke
                        if(element.classList.contains('acpt-icon-picker-stroke')){
                            const targetId = element.dataset.targetId;
                            const strokePattern = /stroke=\"(.*?)\"/gi;
                            let svg = $(`.acpt-icon-picker-preview[data-target-id=${targetId}]`).html();
                            svg = svg.replaceAll(strokePattern, "");
                            $(`.acpt-icon-picker-preview[data-target-id=${targetId}]`).html(svg);
                            $(`.acpt-icon-picker-value[data-target-id="${targetId}"]`).val(svg);
                        }

                        // SVG icon picker: remove fill
                        if(element.classList.contains('acpt-icon-picker-fill')){
                            const targetId = element.dataset.targetId;
                            const fillPattern = /fill=\"(.*?)\"/gi;
                            let svg = $(`.acpt-icon-picker-preview[data-target-id=${targetId}]`).html();
                            svg = svg.replaceAll(fillPattern, "");
                            $(`.acpt-icon-picker-preview[data-target-id=${targetId}]`).html(svg);
                            $(`.acpt-icon-picker-value[data-target-id="${targetId}"]`).val(svg);
                        }
                    },
                    change: function (event, ui) {
                        const element = event.target;
                        const color = ui.color.toString();

                        // Dispatch change Event
                        element.dispatchEvent(new CustomEvent(
                            "acpt-icon-picker",
                            {
                                detail: {
                                    color: color
                                }
                            }
                        ));

                        // SVG icon picker: modify stroke
                        if(element.classList.contains('acpt-icon-picker-stroke')){
                            const targetId = element.dataset.targetId;
                            const strokePattern = /stroke=\"(.*?)\"/gi;
                            let svg = $(`.acpt-icon-picker-preview[data-target-id=${targetId}]`).html();
                            const checkStrokeProps = [...svg.matchAll(strokePattern)];

                            if(checkStrokeProps.length > 0){

                                let colors = [];
                                checkStrokeProps.map((color)=>{
                                    colors.push(color[1]);
                                });

                                const theDarkerColor = theDarkerColorFromList(colors);

                                if(colors.includes("currentColor")){
                                    svg = svg.replace(strokePattern, `fill="${color}"`);
                                } else if(!colors.includes("none")){
                                    const regex = new RegExp("stroke=\"" + theDarkerColor + "\"", "gi");
                                    svg = svg.replace(regex, `stroke="${color}"`);
                                }
                            } else {
                                svg = svg.replace("<svg ", `<svg stroke="${color}" `);
                            }

                            $(`.acpt-icon-picker-preview[data-target-id=${targetId}]`).html(svg);
                            $(`.acpt-icon-picker-value[data-target-id="${targetId}"]`).val(svg);
                        }

                        // SVG icon picker: modify fill
                        if(element.classList.contains('acpt-icon-picker-fill')){
                            const targetId = element.dataset.targetId;
                            const fillPattern = /fill=\"(.*?)\"/gi;
                            let svg = $(`.acpt-icon-picker-preview[data-target-id=${targetId}]`).html();
                            const checkFillProps = [...svg.matchAll(fillPattern)];

                            if(checkFillProps.length > 0){

                                let colors = [];
                                checkFillProps.map((color)=>{
                                    colors.push(color[1]);
                                });

                                const theDarkerColor = theDarkerColorFromList(colors);

                                if(colors.includes("currentColor")){
                                    svg = svg.replace(fillPattern, `fill="${color}"`);
                                } else if(!colors.includes("none")){
                                    const regex = new RegExp("fill=\"" + theDarkerColor + "\"", "gi");
                                    svg = svg.replace(regex, `fill="${color}"`);
                                }
                            } else {
                                svg = svg.replace("<svg ", `<svg fill="${color}" `);
                            }

                            $(`.acpt-icon-picker-preview[data-target-id=${targetId}]`).html(svg);
                            $(`.acpt-icon-picker-value[data-target-id="${targetId}"]`).val(svg);
                        }
                    },
                });
            }
        }
    } catch (e) {
        console.error(e);
    }
};

export const handleColorFieldsEvents = () => {

    /**
     * Eye dropper
     */
    $('body').on('click', '.acpt-eye-dropper-button', function(e) {

        e.preventDefault();

        const $this = $(this);
        const targetId = $this.data('target-id');

        if(!targetId){
            return;
        }

        if (!window.EyeDropper) {
            alert("Your browser does not support the EyeDropper API");
            return;
        }

        const eyeDropper = new EyeDropper();

        eyeDropper
            .open()
            .then((result) => {

                /**
                 *
                 * @param rgba
                 * @param forceRemoveAlpha
                 * @return {string}
                 * @constructor
                 */
                const RGBAToHexA = (rgba, forceRemoveAlpha = false) => {
                    return "#" + rgba.replace(/^rgba?\(|\s+|\)$/g, '') // Get's rgba / rgb string values
                        .split(',') // splits them at ","
                        .filter((string, index) => !forceRemoveAlpha || index !== 3)
                        .map(string => parseFloat(string)) // Converts them to numbers
                        .map((number, index) => index === 3 ? Math.round(number * 255) : number) // Converts alpha to 255 number
                        .map(number => number.toString(16)) // Converts numbers to hex
                        .map(string => string.length === 1 ? "0" + string : string) // Adds 0 when length of one number is 1
                        .join("") // Puts the array to togehter to a string
                };

                /**
                 *
                 * @param strColor
                 * @return {boolean}
                 */
                const isColor = (strColor) => {
                    const s = new Option().style;
                    s.color = strColor;

                    return s.color !== '';
                };

                const hexColor = RGBAToHexA(result.sRGBHex, true);

                if(isColor(hexColor)){
                    $(`#${targetId}`).val(hexColor);
                    $this.prev(`.wp-picker-container`).find('.wp-color-result').css('background-color', hexColor);
                } else {
                    alert(`${hexColor} is not a valid CSS color`);
                }
            })
            .catch((e) => {
                console.error(e);
            });
    });
};
