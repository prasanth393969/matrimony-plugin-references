var advancedTabs = function ($scope, $) {
   // Glassey tab bar style
   const $glasseyTabs = $scope.find(".eael-tabs-glassey");

   if ($glasseyTabs.length) {
      initGlasseyTabStyle($glasseyTabs);
   }

   /**
    * Initialize dynamic Glassey tab bar style
    */
   function initGlasseyTabStyle($glasseyTabs) {
      $glasseyTabs.each(function () {
         const $tabContainer = $(this);
         const $tabsList = $tabContainer.find("ul");
         const $tabs = $tabsList.find("li");
         const widgetId = $tabContainer
            .closest(".eael-advance-tabs")
            .attr("id");

         if (!widgetId || $tabs.length === 0) {
            return;
         }

         // Generate unique style ID for this widget instance
         const styleId = "eael-glassey-style-" + widgetId;

         // Remove existing style if it exists
         $("#" + styleId).remove();

         // Calculate tab widths and positions
         updateGlasseyTabPositions($tabContainer, $tabs, styleId);

         // Update positions on window resize
         $(window).on("resize.glassey-" + widgetId, function () {
            updateGlasseyTabPositions($tabContainer, $tabs, styleId);
         });

         // Update positions when tabs are clicked
         $tabs.on("click.glassey", function () {
            setTimeout(function () {
               updateGlasseyTabPositions($tabContainer, $tabs, styleId);
            }, 50);
         });
      });
   }

   /**
    * Update Glassey tab positions and generate dynamic CSS
    */
   function updateGlasseyTabPositions($tabContainer, $tabs, styleId) {
      const widgetId = $tabContainer.closest(".eael-advance-tabs").attr("id");
      let cssRules = "";

      $tabs.each(function () {
         const $tab = $(this);
         const tabNumber = $tab.data("tab");
         const tabPosition = $tab.position();
         const tabWidth = $tab.outerWidth();
         const tabHeight = $tab.outerHeight();

         if (
            tabPosition &&
            typeof tabPosition.left !== "undefined" &&
            tabWidth
         ) {
            const translateX = Math.round(tabPosition.left);
            const translateY = Math.round(tabPosition.top);
            const dynamicWidth = Math.round(tabWidth - 8);
            const dynamicHeight = Math.round(tabHeight - 8);
            cssRules += `
                        #${widgetId} .eael-tabs-glassey > ul:has(li.active[data-tab="${tabNumber}"])::after {
                           translate: ${translateX}px ${translateY}px;
                           width: ${dynamicWidth}px;
                           height: ${dynamicHeight}px;
                           transform-origin: right;
                           transition: background-color 400ms cubic-bezier(1, 0, 0.4, 1),
                                 box-shadow 400ms cubic-bezier(1, 0, 0.4, 1),
                                 translate 400ms cubic-bezier(1, 0, 0.4, 1),
                                 width 400ms cubic-bezier(1, 0, 0.4, 1);
                           animation: scaleToggle 440ms ease;
                        }
                     `;
         }
      });

      // Create or update the style element
      if (cssRules) {
         let $styleElement = $("#" + styleId);
         if ($styleElement.length === 0) {
            $styleElement = $("<style>", {
               id: styleId,
               type: "text/css",
            });
            $("head").append($styleElement);
         }
         $styleElement.html(cssRules);
      }
   }
}

jQuery(window).on("elementor/frontend/init", function () {
   if (eael.elementStatusCheck("advancedTabs")) {
      return false;
   }

   elementorFrontend.hooks.addAction(
      "frontend/element_ready/eael-adv-tabs.default",
      advancedTabs
   );
});
