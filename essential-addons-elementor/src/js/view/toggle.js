eael.hooks.addAction("init", "ea", () => {
   elementorFrontend.hooks.addAction(
      "frontend/element_ready/eael-toggle.default",
      ($scope, $) => {
         let context = $scope[0];

         // make primary active on init
         context
            .querySelector(".eael-primary-toggle-label")
            .classList.add("active");

         // Function to toggle between states
         const toggleState = (targetOption) => {
            const current = context
               .querySelector(".eael-toggle-content-wrap")
               .classList.contains("primary")
               ? "primary"
               : "secondary";

            let newState;
            if (targetOption) {
               // If specific option is targeted
               newState = targetOption === "1" ? "primary" : "secondary";
            } else {
               // Toggle to opposite state
               newState = current === "primary" ? "secondary" : "primary";
            }

            if (newState === "secondary") {
               context
                  .querySelector(".eael-toggle-content-wrap")
                  .classList.remove("primary");
               context
                  .querySelector(".eael-toggle-content-wrap")
                  .classList.add("secondary");
               context
                  .querySelector(".eael-toggle-switch-container")
                  .classList.add("eael-toggle-switch-on");
               context
                  .querySelector(".eael-primary-toggle-label")
                  .classList.remove("active");
               context
                  .querySelector(".eael-secondary-toggle-label")
                  .classList.add("active");

               // Set the correct radio button as checked
               const option2Radio = context.querySelector(
                  'input[c-option="2"]'
               );
               if (option2Radio) option2Radio.checked = true;
            } else {
               context
                  .querySelector(".eael-toggle-content-wrap")
                  .classList.add("primary");
               context
                  .querySelector(".eael-toggle-content-wrap")
                  .classList.remove("secondary");
               context
                  .querySelector(".eael-toggle-switch-container")
                  .classList.remove("eael-toggle-switch-on");
               context
                  .querySelector(".eael-primary-toggle-label")
                  .classList.add("active");
               context
                  .querySelector(".eael-secondary-toggle-label")
                  .classList.remove("active");

               // Set the correct radio button as checked
               const option1Radio = context.querySelector(
                  'input[c-option="1"]'
               );
               if (option1Radio) option1Radio.checked = true;
            }

            // Custom onclick event with current state information
            const currentLabel =
               newState === "primary"
                  ? context
                       .querySelector(".eael-primary-toggle-label")
                       .textContent.trim()
                  : context
                       .querySelector(".eael-secondary-toggle-label")
                       .textContent.trim();

            // console.log(`Toggle switched to: ${currentLabel} (${newState})`);

            // Trigger custom event
            eael.hooks.doAction("ea-toggle-triggered", context, {
               state: newState,
               label: currentLabel,
               option: newState === "primary" ? "1" : "2",
            });
         };

         //Support for Keyboard accessibility
         let toggleSwitchRound = context.querySelector(
            ".eael-toggle-switch-round"
         );
         $scope.on("keyup", toggleSwitchRound, (e) => {
            if (e.key === "Enter" || e.keyCode === 13) {
               e.preventDefault();
               toggleState();
            }
         });

         // Handle clicks on all switcher options
         const switcherOptions = context.querySelectorAll(
            ".eael-toggle-switch"
         );
         switcherOptions.forEach((option) => {
            option.onclick = (e) => {
               e.preventDefault();
               const targetOption = option.getAttribute("data-option");
               toggleState(targetOption);
            };
         });

         // Grasshopper Handle radio button changes (for CSS-driven animations)
         const allRadioInputs = context.querySelectorAll(
            ".eael-flip-switcher__input"
         );
         allRadioInputs.forEach((radio) => {
            radio.addEventListener("change", (e) => {
               if (e.target.checked && context.contains(e.target)) {
                  const option = e.target.getAttribute("c-option");
                  toggleState(option);
               }
            });
         });
      }
   );
});
