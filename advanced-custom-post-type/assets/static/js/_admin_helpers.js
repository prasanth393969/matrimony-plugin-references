
export function gutenbergIsEnabled() {
    return typeof window.wpEditorL10n === "object";
}

export function escapeIdSelector(selector) {
    let escaped = selector.replaceAll("[", "\\\\[");
    escaped = escaped.replaceAll("]", "\\\\]");

    return escaped;
}

/**
 *
 * @param uuid
 * @return {boolean}
 */
export function isUUID ( uuid ) {
    let s = "" + uuid;

    s = s.match('^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$');
    if (s === null) {
        return false;
    }

    return true;
}

/**
 * Fetch all translations
 *
 * @returns {Promise<Response>}
 */
export const fetchLanguages = () => {

    const baseAjaxUrl = (typeof ajaxurl === 'string') ? ajaxurl : '/wp-admin/admin-ajax.php';

    let formData;
    formData = new FormData();
    formData.append('action', 'languagesAction');

    return fetch(baseAjaxUrl, {
        method: 'POST',
        body: formData
    });
};

/**
 * Get aspect ratio from svg string
 *
 * @param svg
 * @return {string|number}
 */
export const determineSVGAspectRatio = (svg) => {
    const viewBox = svg.match(/viewBox=\"(.*?)\"/);

    if(!viewBox){
        return 1;
    }

    const viewBoxProps = viewBox[1].split(" ");
    const width = viewBoxProps[2];
    const height = viewBoxProps[3];
    const ratio = Number(width / height);

    return parseFloat(ratio).toFixed(2);
};

/**
 * Determine the brightness of a color
 * @param color
 * @return {null|number}
 */
export const getPerceptualBrightness = (color) => {

    color = color.replace("#", "");

    const r = parseInt(color.substring(0,2),16);
    const g = parseInt(color.substring(2,4),16);
    const b = parseInt(color.substring(4,6),16);

    const brightness =  r*2 + g*3 + b;

    if(isNaN(brightness)){
        return null;
    }

    return brightness;
};

/**
 * Determine the darken color from an array of exadecimal colors.
 * Examples:
 *
 * ['none', 'none', 'none'] returns null
 * ['none', '#ffffff', '#dddddd', '#111111'] returns '#111111'
 *
 * @param colors
 * @return {null|*}
 */
export const theDarkerColorFromList = (colors) => {
    let colorsBrightness = [];
    let colorsValues = [];

    colors.map((color) => {
        const brightness = getPerceptualBrightness(color);

        if(brightness !== null){
            colorsBrightness.push(brightness);
            colorsValues.push(color);
        }
    });

    const darkenColor = Math.min(...colorsBrightness);
    const darkenColorIndex = colorsBrightness.indexOf(darkenColor);

    if(darkenColorIndex === -1){
        return null;
    }

    return colorsValues[darkenColorIndex];
};