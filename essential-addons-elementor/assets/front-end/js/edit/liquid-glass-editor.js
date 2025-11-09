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
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/js/edit/liquid-glass-editor.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/js/edit/liquid-glass-editor.js":
/*!********************************************!*\
  !*** ./src/js/edit/liquid-glass-editor.js ***!
  \********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("function _typeof(o) { \"@babel/helpers - typeof\"; return _typeof = \"function\" == typeof Symbol && \"symbol\" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && \"function\" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? \"symbol\" : typeof o; }, _typeof(o); }\nfunction _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }\nfunction _nonIterableSpread() { throw new TypeError(\"Invalid attempt to spread non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); }\nfunction _unsupportedIterableToArray(r, a) { if (r) { if (\"string\" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return \"Object\" === t && r.constructor && (t = r.constructor.name), \"Map\" === t || \"Set\" === t ? Array.from(r) : \"Arguments\" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }\nfunction _iterableToArray(r) { if (\"undefined\" != typeof Symbol && null != r[Symbol.iterator] || null != r[\"@@iterator\"]) return Array.from(r); }\nfunction _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }\nfunction _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }\nfunction _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError(\"Cannot call a class as a function\"); }\nfunction _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, \"value\" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }\nfunction _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, \"prototype\", { writable: !1 }), e; }\nfunction _toPropertyKey(t) { var i = _toPrimitive(t, \"string\"); return \"symbol\" == _typeof(i) ? i : i + \"\"; }\nfunction _toPrimitive(t, r) { if (\"object\" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || \"default\"); if (\"object\" != _typeof(i)) return i; throw new TypeError(\"@@toPrimitive must return a primitive value.\"); } return (\"string\" === r ? String : Number)(t); }\n/**\n * Liquid Glass Effect - Elementor Editor Support\n * Liquid Glass Editor Controller\n */\nvar LiquidGlassEditor = /*#__PURE__*/function () {\n  function LiquidGlassEditor() {\n    _classCallCheck(this, LiquidGlassEditor);\n    this.svgContainer = null;\n    this.supportedEffects = ['effect4', 'effect5', 'effect6'];\n    this.effectDefaults = {\n      effect4: {\n        freq: 0.008,\n        strength: 77\n      },\n      effect5: {\n        freq: 1.008,\n        strength: 77\n      },\n      effect6: {\n        freq: 0.02,\n        strength: 30\n      }\n    };\n    this.init();\n  }\n  return _createClass(LiquidGlassEditor, [{\n    key: \"init\",\n    value: function init() {\n      this.bindEvents();\n    }\n\n    /**\n    * Bind Elementor editor events\n    */\n  }, {\n    key: \"bindEvents\",\n    value: function bindEvents() {\n      var _this = this;\n      // Panel editor events\n      elementor.hooks.addAction('panel/open_editor/widget', function () {\n        return _this.handlePanelOpen();\n      });\n      elementor.hooks.addAction('panel/open_editor/section', function () {\n        return _this.handlePanelOpen();\n      });\n      elementor.hooks.addAction('panel/open_editor/container', function () {\n        return _this.handlePanelOpen();\n      });\n\n      // Preview events\n      elementor.on('preview:loaded', function () {\n        return _this.handlePreviewLoaded();\n      });\n\n      // Settings change events\n      elementor.channels.editor.on('change', function (controlView, elementView) {\n        _this.handleSettingsChange(controlView, elementView);\n      });\n    }\n\n    /**\n    * Handle panel open events\n    */\n  }, {\n    key: \"handlePanelOpen\",\n    value: function handlePanelOpen() {\n      var _this2 = this;\n      setTimeout(function () {\n        return _this2.updateAllSVGFilters();\n      }, 100);\n    }\n\n    /**\n    * Handle preview loaded events\n    */\n  }, {\n    key: \"handlePreviewLoaded\",\n    value: function handlePreviewLoaded() {\n      var _this3 = this;\n      setTimeout(function () {\n        _this3.initSVGContainer();\n        _this3.updateAllSVGFilters();\n      }, 500);\n    }\n\n    /**\n    * Handle settings change events\n    * @param {Object} controlView - Elementor control view\n    * @param {Object} elementView - Elementor element view\n    */\n  }, {\n    key: \"handleSettingsChange\",\n    value: function handleSettingsChange(controlView, elementView) {\n      var _this4 = this;\n      try {\n        if (!(controlView !== null && controlView !== void 0 && controlView.model) || !(elementView !== null && elementView !== void 0 && elementView.model)) {\n          return;\n        }\n        var controlName = controlView.model.get('name');\n        if (controlName !== null && controlName !== void 0 && controlName.includes('eael_liquid_glass_effect')) {\n          setTimeout(function () {\n            return _this4.updateAllSVGFilters();\n          }, 150);\n        }\n      } catch (error) {\n        // Silently handle errors\n      }\n    }\n\n    /**\n    * Check if preview iframe is ready\n    * @returns {boolean} Preview readiness status\n    */\n  }, {\n    key: \"isPreviewReady\",\n    value: function isPreviewReady() {\n      var _elementor$$preview;\n      return !!((_elementor$$preview = elementor.$preview) !== null && _elementor$$preview !== void 0 && (_elementor$$preview = _elementor$$preview[0]) !== null && _elementor$$preview !== void 0 && _elementor$$preview.contentWindow);\n    }\n\n    /**\n    * Get preview document\n    * @returns {Document} Preview document\n    */\n  }, {\n    key: \"getPreviewDocument\",\n    value: function getPreviewDocument() {\n      return elementor.$preview[0].contentWindow.document;\n    }\n\n    /**\n    * Remove previous SVG container\n    * @param {Document} previewDocument - Preview document\n    */\n  }, {\n    key: \"removePreviousContainer\",\n    value: function removePreviousContainer(previewDocument) {\n      var existing = previewDocument.getElementById('eael-liquid-glass-editor-svg');\n      existing === null || existing === void 0 || existing.remove();\n    }\n\n    /**\n    * Create SVG container in preview document\n    * @param {Document} previewDocument - Preview document\n    */\n  }, {\n    key: \"createSVGContainer\",\n    value: function createSVGContainer(previewDocument) {\n      this.svgContainer = previewDocument.createElement('div');\n      this.svgContainer.id = 'eael-liquid-glass-editor-svg';\n      this.svgContainer.style.display = 'none';\n      previewDocument.body.appendChild(this.svgContainer);\n    }\n\n    /**\n    * Initialize SVG container in the preview iframe\n    * @returns {boolean} Success status\n    */\n  }, {\n    key: \"initSVGContainer\",\n    value: function initSVGContainer() {\n      try {\n        if (!this.isPreviewReady()) {\n          return false;\n        }\n        var previewDocument = this.getPreviewDocument();\n        this.removePreviousContainer(previewDocument);\n        this.createSVGContainer(previewDocument);\n        return true;\n      } catch (error) {\n        return false;\n      }\n    }\n\n    /**\n    * Get effect settings from element settings\n    * @param {string} effect - Effect type (effect4, effect5, effect6)\n    * @param {Object} settings - Element settings\n    * @returns {Object} Effect configuration\n    */\n  }, {\n    key: \"getEffectSettings\",\n    value: function getEffectSettings(effect, settings) {\n      var _settings$freqKey, _settings$strengthKey;\n      var defaults = this.effectDefaults[effect];\n      var freqKey = \"eael_liquid_glass_effect_noise_freq_\".concat(effect);\n      var strengthKey = \"eael_liquid_glass_effect_noise_strength_\".concat(effect);\n      return {\n        freq: ((_settings$freqKey = settings[freqKey]) === null || _settings$freqKey === void 0 ? void 0 : _settings$freqKey.size) || defaults.freq,\n        strength: ((_settings$strengthKey = settings[strengthKey]) === null || _settings$strengthKey === void 0 ? void 0 : _settings$strengthKey.size) || defaults.strength\n      };\n    }\n\n    /**\n    * Generate SVG filter content based on effect type\n    * @param {string} effect - Effect type\n    * @param {number} freq - Frequency value\n    * @param {number} strength - Strength value\n    * @returns {string} SVG filter content\n    */\n  }, {\n    key: \"generateFilterContent\",\n    value: function generateFilterContent(effect, freq, strength) {\n      var filterConfigs = {\n        effect4: \"\\n                  <feTurbulence type=\\\"fractalNoise\\\" baseFrequency=\\\"\".concat(freq, \" \").concat(freq, \"\\\" numOctaves=\\\"2\\\" seed=\\\"92\\\" result=\\\"noise\\\" />\\n                  <feGaussianBlur in=\\\"noise\\\" stdDeviation=\\\"0.02\\\" result=\\\"blur\\\" />\\n                  <feDisplacementMap in=\\\"SourceGraphic\\\" in2=\\\"blur\\\" scale=\\\"\").concat(strength, \"\\\" xChannelSelector=\\\"R\\\" yChannelSelector=\\\"G\\\" />\\n               \"),\n        effect5: \"\\n                  <feTurbulence type=\\\"fractalNoise\\\" baseFrequency=\\\"\".concat(freq, \" \").concat(freq, \"\\\" numOctaves=\\\"1\\\" seed=\\\"9000\\\" result=\\\"noise\\\" />\\n                  <feGaussianBlur in=\\\"noise\\\" stdDeviation=\\\"0.1\\\" result=\\\"blurred\\\" />\\n                  <feDisplacementMap in=\\\"SourceGraphic\\\" in2=\\\"blurred\\\" scale=\\\"\").concat(strength, \"\\\" xChannelSelector=\\\"R\\\" yChannelSelector=\\\"G\\\" />\\n               \"),\n        effect6: \"\\n                  <feTurbulence type=\\\"turbulence\\\" baseFrequency=\\\"\".concat(freq, \"\\\" numOctaves=\\\"3\\\" result=\\\"turbulence\\\"/>\\n                  <feDisplacementMap in2=\\\"turbulence\\\" in=\\\"SourceGraphic\\\" scale=\\\"\").concat(strength, \"\\\" xChannelSelector=\\\"R\\\" yChannelSelector=\\\"G\\\"/>\\n               \")\n      };\n      return filterConfigs[effect] || '';\n    }\n\n    /**\n    * Generate complete SVG filter for a specific element and effect\n    * @param {string} elementId - Element ID\n    * @param {string} effect - Effect type\n    * @param {Object} settings - Element settings\n    * @returns {string} Complete SVG markup\n    */\n  }, {\n    key: \"generateSVGFilter\",\n    value: function generateSVGFilter(elementId, effect, settings) {\n      if (!this.supportedEffects.includes(effect)) {\n        return '';\n      }\n      var _this$getEffectSettin = this.getEffectSettings(effect, settings),\n        freq = _this$getEffectSettin.freq,\n        strength = _this$getEffectSettin.strength;\n      var filterContent = this.generateFilterContent(effect, freq, strength);\n      var effectNumber = effect.slice(-1);\n      return \"\\n               <svg>\\n                  <defs>\\n                     <filter id=\\\"eael-glass-distortion\".concat(effectNumber, \"-\").concat(elementId, \"\\\" x=\\\"0%\\\" y=\\\"0%\\\" width=\\\"100%\\\" height=\\\"100%\\\">\\n                           \").concat(filterContent, \"\\n                     </filter>\\n                  </defs>\\n                  <style>\\n                     [data-id=\\\"\").concat(elementId, \"\\\"].eael_liquid_glass-\").concat(effect, \"::before {\\n                           filter: url(#eael-glass-distortion\").concat(effectNumber, \"-\").concat(elementId, \");\\n                     }\\n                  </style>\\n               </svg>\\n         \");\n    }\n\n    /**\n    * Check if element has liquid glass effect enabled\n    * @param {Object} settings - Element settings\n    * @returns {boolean} Whether element has liquid glass effect\n    */\n  }, {\n    key: \"hasLiquidGlassEffect\",\n    value: function hasLiquidGlassEffect(settings) {\n      return settings.eael_liquid_glass_effect_switch === 'yes' && settings.eael_liquid_glass_effect && this.supportedEffects.includes(settings.eael_liquid_glass_effect);\n    }\n\n    /**\n    * Process single element for liquid glass effects\n    * @param {Object} element - Elementor element\n    * @returns {string} Generated SVG HTML or empty string\n    */\n  }, {\n    key: \"processElement\",\n    value: function processElement(element) {\n      var settings = element.get('settings').attributes;\n      if (!this.hasLiquidGlassEffect(settings)) {\n        return '';\n      }\n      var elementId = element.get('id');\n      var effect = settings.eael_liquid_glass_effect;\n      return this.generateSVGFilter(elementId, effect, settings);\n    }\n\n    /**\n    * Recursively scan elements for liquid glass effects\n    * @param {Array} elementsList - List of elements to scan\n    * @returns {Array} Array of generated SVG HTML strings\n    */\n  }, {\n    key: \"scanElements\",\n    value: function scanElements(elementsList) {\n      var _this5 = this;\n      var svgFilters = [];\n      elementsList.forEach(function (element) {\n        var _children$models;\n        // Process current element\n        var svgHTML = _this5.processElement(element);\n        if (svgHTML) {\n          svgFilters.push(svgHTML);\n        }\n\n        // Process nested elements\n        var children = element.get('elements');\n        if ((children === null || children === void 0 || (_children$models = children.models) === null || _children$models === void 0 ? void 0 : _children$models.length) > 0) {\n          svgFilters.push.apply(svgFilters, _toConsumableArray(_this5.scanElements(children.models)));\n        }\n      });\n      return svgFilters;\n    }\n\n    /**\n    * Update SVG filters for all elements with liquid glass effects\n    */\n  }, {\n    key: \"updateAllSVGFilters\",\n    value: function updateAllSVGFilters() {\n      try {\n        var _window$elementor, _previewView$collecti;\n        // Initialize SVG container if needed\n        if (!this.svgContainer && !this.initSVGContainer()) {\n          return;\n        }\n\n        // Clear existing filters\n        if (this.svgContainer) {\n          this.svgContainer.innerHTML = '';\n        }\n\n        // Check if elementor and preview are available\n        if (!((_window$elementor = window.elementor) !== null && _window$elementor !== void 0 && _window$elementor.getPreviewView)) {\n          return;\n        }\n\n        // Get all elements in the current document\n        var previewView = elementor.getPreviewView();\n        if (!(previewView !== null && previewView !== void 0 && (_previewView$collecti = previewView.collection) !== null && _previewView$collecti !== void 0 && _previewView$collecti.models)) {\n          return;\n        }\n\n        // Scan all elements and generate SVG filters\n        var svgFilters = this.scanElements(previewView.collection.models);\n\n        // Inject all SVG filters\n        if (this.svgContainer && svgFilters.length > 0) {\n          this.svgContainer.innerHTML = svgFilters.join('');\n        }\n      } catch (error) {\n        console.error(error);\n      }\n    }\n  }]);\n}();\n/**\n * Initialize Liquid Glass Editor using EAEL hooks system\n */\neael.hooks.addAction(\"editMode.init\", \"ea\", function () {\n  new LiquidGlassEditor();\n});\n\n//# sourceURL=webpack:///./src/js/edit/liquid-glass-editor.js?");

/***/ })

/******/ });