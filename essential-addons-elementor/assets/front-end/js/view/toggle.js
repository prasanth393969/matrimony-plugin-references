/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/js/view/toggle.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/js/view/toggle.js":
/*!*******************************!*\
  !*** ./src/js/view/toggle.js ***!
  \*******************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("eael.hooks.addAction(\"init\", \"ea\", function () {\n  elementorFrontend.hooks.addAction(\"frontend/element_ready/eael-toggle.default\", function ($scope, $) {\n    var context = $scope[0];\n\n    // make primary active on init\n    context.querySelector(\".eael-primary-toggle-label\").classList.add(\"active\");\n\n    // Function to toggle between states\n    var toggleState = function toggleState(targetOption) {\n      var current = context.querySelector(\".eael-toggle-content-wrap\").classList.contains(\"primary\") ? \"primary\" : \"secondary\";\n      var newState;\n      if (targetOption) {\n        // If specific option is targeted\n        newState = targetOption === \"1\" ? \"primary\" : \"secondary\";\n      } else {\n        // Toggle to opposite state\n        newState = current === \"primary\" ? \"secondary\" : \"primary\";\n      }\n      if (newState === \"secondary\") {\n        context.querySelector(\".eael-toggle-content-wrap\").classList.remove(\"primary\");\n        context.querySelector(\".eael-toggle-content-wrap\").classList.add(\"secondary\");\n        context.querySelector(\".eael-toggle-switch-container\").classList.add(\"eael-toggle-switch-on\");\n        context.querySelector(\".eael-primary-toggle-label\").classList.remove(\"active\");\n        context.querySelector(\".eael-secondary-toggle-label\").classList.add(\"active\");\n\n        // Set the correct radio button as checked\n        var option2Radio = context.querySelector('input[c-option=\"2\"]');\n        if (option2Radio) option2Radio.checked = true;\n      } else {\n        context.querySelector(\".eael-toggle-content-wrap\").classList.add(\"primary\");\n        context.querySelector(\".eael-toggle-content-wrap\").classList.remove(\"secondary\");\n        context.querySelector(\".eael-toggle-switch-container\").classList.remove(\"eael-toggle-switch-on\");\n        context.querySelector(\".eael-primary-toggle-label\").classList.add(\"active\");\n        context.querySelector(\".eael-secondary-toggle-label\").classList.remove(\"active\");\n\n        // Set the correct radio button as checked\n        var option1Radio = context.querySelector('input[c-option=\"1\"]');\n        if (option1Radio) option1Radio.checked = true;\n      }\n\n      // Custom onclick event with current state information\n      var currentLabel = newState === \"primary\" ? context.querySelector(\".eael-primary-toggle-label\").textContent.trim() : context.querySelector(\".eael-secondary-toggle-label\").textContent.trim();\n\n      // console.log(`Toggle switched to: ${currentLabel} (${newState})`);\n\n      // Trigger custom event\n      eael.hooks.doAction(\"ea-toggle-triggered\", context, {\n        state: newState,\n        label: currentLabel,\n        option: newState === \"primary\" ? \"1\" : \"2\"\n      });\n    };\n\n    //Support for Keyboard accessibility\n    var toggleSwitchRound = context.querySelector(\".eael-toggle-switch-round\");\n    $scope.on(\"keyup\", toggleSwitchRound, function (e) {\n      if (e.key === \"Enter\" || e.keyCode === 13) {\n        e.preventDefault();\n        toggleState();\n      }\n    });\n\n    // Handle clicks on all switcher options\n    var switcherOptions = context.querySelectorAll(\".eael-toggle-switch\");\n    switcherOptions.forEach(function (option) {\n      option.onclick = function (e) {\n        e.preventDefault();\n        var targetOption = option.getAttribute(\"data-option\");\n        toggleState(targetOption);\n      };\n    });\n\n    // Grasshopper Handle radio button changes (for CSS-driven animations)\n    var allRadioInputs = context.querySelectorAll(\".eael-flip-switcher__input\");\n    allRadioInputs.forEach(function (radio) {\n      radio.addEventListener(\"change\", function (e) {\n        if (e.target.checked && context.contains(e.target)) {\n          var option = e.target.getAttribute(\"c-option\");\n          toggleState(option);\n        }\n      });\n    });\n  });\n});\n\n//# sourceURL=webpack:///./src/js/view/toggle.js?");

/***/ })

/******/ });