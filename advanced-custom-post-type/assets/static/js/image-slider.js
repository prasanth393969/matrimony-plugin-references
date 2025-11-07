var $ = jQuery.noConflict();

window.onload = function () {

    const sliderElement = $('.acpt-image-slider');

    if(sliderElement.length){
        sliderElement.each(function( index ) {
            const $this = $(this);
            const slider = $this.find(".slider");

            $('body').on('input', slider, function(e){
                $this[0].style.setProperty('--position', `${e.target.value}%`);
            });
        });
    }
};