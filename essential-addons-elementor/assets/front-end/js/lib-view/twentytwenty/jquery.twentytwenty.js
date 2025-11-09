/**
 * eatwentytwenty.js
 * 
 * Extended version of the original "TwentyTwenty" jQuery plugin.
 * Use with caution when upgrading or modifying, since this file contains
 * custom modifications (e.g., external API access and auto-slide support).
 * 
 * Purpose:
 *   Provides a responsive before/after image comparison slider.
 * 
 * Features:
 * - Horizontal and vertical orientation support.
 * - Draggable or hover-based slider movement.
 * - Click-to-move option.
 * - Auto-slide animation support (extended).
 * - Labels and overlay support for "Before" and "After".
 * - Fully responsive – recalculates on window resize.
 * - External API access (added in this version) via jQuery .data():
 *      - animateSlider(state, startPct) → Animates slider to "before" or "after".
 *      - adjustSlider(pct) → Directly positions slider at percentage (0–1).
 *      - getSliderPercentage(x, y) → Calculates slider percentage from coordinates.
 * 
 * Usage:
 *   // Initialize
 *   $("#my-slider").eatwentytwenty({ orientation: "horizontal" });
 * 
 *   // Get API
 *   let api = $("#my-slider").data("eatwentytwenty");
 * 
 *   // Control externally
 *   api.animateSlider("after", 0.5);
 *   api.adjustSlider(0.3);
 * 
 * NOTE:
 * - This is NOT the vanilla TwentyTwenty plugin.
 * - If you update from the original TwentyTwenty source, carefully merge changes.
 * 
 */

(function($){

  $.fn.eatwentytwenty = function(options) {
    var options = $.extend({
      default_offset_pct: 0.5,
      orientation: 'horizontal',
      before_label: 'Before',
      after_label: 'After',
      no_overlay: false,
      move_slider_on_hover: false,
      move_with_handle_only: true,
      click_to_move: false,
      autoSlide: false,
      autoSlideSpeed: 1000,
      autoSlidePause: 500,
    }, options);

    return this.each(function() {

      var sliderPct = options.default_offset_pct;
      var container = $(this);
      var sliderOrientation = options.orientation;
      var beforeDirection = (sliderOrientation === 'vertical') ? 'down' : 'left';
      var afterDirection = (sliderOrientation === 'vertical') ? 'up' : 'right';

      container.wrap("<div class='twentytwenty-wrapper twentytwenty-" + sliderOrientation + "'></div>");
      if(!options.no_overlay) {
        container.append("<div class='twentytwenty-overlay'></div>");
      }
      var beforeImg = container.find("img:first");
      var afterImg = container.find("img:last");
      container.append("<div class='twentytwenty-handle'></div>");
      var slider = container.find(".twentytwenty-handle");
      slider.append("<span class='twentytwenty-" + beforeDirection + "-arrow'></span>");
      slider.append("<span class='twentytwenty-" + afterDirection + "-arrow'></span>");
      container.addClass("twentytwenty-container");
      beforeImg.addClass("twentytwenty-before");
      afterImg.addClass("twentytwenty-after");

      var overlay = container.find(".twentytwenty-overlay");
      $("<div>", {
        "class": "twentytwenty-before-label",
        "data-content": options.before_label
      }).appendTo(overlay);

      $("<div>", {
        "class": "twentytwenty-after-label",
        "data-content": options.after_label
      }).appendTo(overlay);

      var calcOffset = function(dimensionPct) {
        var w = beforeImg.width();
        var h = beforeImg.height();
        return {
          w: w+"px",
          h: h+"px",
          cw: (dimensionPct*w)+"px",
          ch: (dimensionPct*h)+"px"
        };
      };

      var adjustContainer = function(offset) {
        if (sliderOrientation === 'vertical') {
          beforeImg.css("clip", "rect(0,"+offset.w+","+offset.ch+",0)");
          afterImg.css("clip", "rect("+offset.ch+","+offset.w+","+offset.h+",0)");
        }
        else {
          beforeImg.css("clip", "rect(0,"+offset.cw+","+offset.h+",0)");
          afterImg.css("clip", "rect(0,"+offset.w+","+offset.h+","+offset.cw+")");
        }
        container.css("height", offset.h);
      };

      var adjustSlider = function(pct) {
        var offset = calcOffset(pct);
        slider.css((sliderOrientation==="vertical") ? "top" : "left", (sliderOrientation==="vertical") ? offset.ch : offset.cw);
        adjustContainer(offset);
      };

      var minMaxNumber = function(num, min, max) {
        return Math.max(min, Math.min(max, num));
      };

      var getSliderPercentage = function(positionX, positionY) {
        // Ensure offset and dimension variables are initialized
        if (offsetX === 0 && offsetY === 0 && imgWidth === 0 && imgHeight === 0) {
          offsetX = container.offset().left;
          offsetY = container.offset().top;
          imgWidth = beforeImg.width();
          imgHeight = beforeImg.height();
        }

        var sliderPercentage = (sliderOrientation === 'vertical') ?
          (positionY-offsetY)/imgHeight :
          (positionX-offsetX)/imgWidth;

        return minMaxNumber(sliderPercentage, 0, 1);
      };

      $(window).on("resize.eatwentytwenty", function(e) {
        // Update offset and dimensions on resize
        offsetX = container.offset().left;
        offsetY = container.offset().top;
        imgWidth = beforeImg.width();
        imgHeight = beforeImg.height();
        adjustSlider(sliderPct);
      });

      var offsetX = 0;
      var offsetY = 0;
      var imgWidth = 0;
      var imgHeight = 0;
      var slideHandle;

      var onMoveStart = function(e) {
        container.addClass("active");
        offsetX = container.offset().left;
        offsetY = container.offset().top;
        imgWidth = beforeImg.width(); 
        imgHeight = beforeImg.height();          
      };
      var onMove = function(e) {
        if (container.hasClass("active")) {
          sliderPct = getSliderPercentage(e.pageX, e.pageY);
          adjustSlider(sliderPct);
        }
      };
      var onMoveEnd = function() {
        container.removeClass("active");
      };

      var moveTarget = options.move_with_handle_only ? slider : container;
      moveTarget.on("movestart",onMoveStart);
      moveTarget.on("move",onMove);
      moveTarget.on("moveend",onMoveEnd);

      if (options.move_slider_on_hover) {
        container.on("mouseenter", onMoveStart);
        container.on("mousemove", onMove);
        container.on("mouseleave", onMoveEnd);
      }

      slider.on("touchmove", function(e) {
        e.preventDefault();
      });

      container.find("img").on("mousedown", function(event) {
        event.preventDefault();
      });

      if (options.click_to_move) {
        container.on('click', function(e) {
          offsetX = container.offset().left;
          offsetY = container.offset().top;
          imgWidth = beforeImg.width();
          imgHeight = beforeImg.height();

          sliderPct = getSliderPercentage(e.pageX, e.pageY);
          adjustSlider(sliderPct);
        });
      }

      // --- Exposed method ---
      function animateSlider(state, startPct, step) {
        clearInterval(slideHandle);
        var pct = startPct;
        var step = step/10000 || 0.001;
        var interval = 1;
        
        if (state === 'before') {
          slideHandle = setInterval(function() {
            pct -= step;
            adjustSlider(pct);
            if (pct <= 0) clearInterval(slideHandle);
          }, interval);
        } else {
          slideHandle = setInterval(function() {
            pct += step;
            adjustSlider(pct);
            if (pct >= 1) clearInterval(slideHandle);
          }, interval);
        }
      }

      // Initialize offset and dimension variables
      var initializeOffsetAndDimensions = function() {
        offsetX = container.offset().left;
        offsetY = container.offset().top;
        imgWidth = beforeImg.width();
        imgHeight = beforeImg.height();
      };

      // --- Store API for external use ---
      container.data("eatwentytwenty", {
        animateSlider: animateSlider,
        adjustSlider: adjustSlider,
        getSliderPercentage: getSliderPercentage,
        initializeOffsetAndDimensions: initializeOffsetAndDimensions
      });

      // Initialize dimensions after images are loaded
      beforeImg.on('load', function() {
        initializeOffsetAndDimensions();
      });

      // If images are already loaded, initialize immediately
      if (beforeImg[0].complete) {
        initializeOffsetAndDimensions();
      }

      $(window).trigger("resize.eatwentytwenty");
    });
  };

})(jQuery);