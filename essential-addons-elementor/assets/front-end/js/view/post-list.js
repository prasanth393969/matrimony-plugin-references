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
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/js/view/post-list.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/js/view/post-list.js":
/*!**********************************!*\
  !*** ./src/js/view/post-list.js ***!
  \**********************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("function _typeof(o) { \"@babel/helpers - typeof\"; return _typeof = \"function\" == typeof Symbol && \"symbol\" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && \"function\" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? \"symbol\" : typeof o; }, _typeof(o); }\nfunction ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }\nfunction _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }\nfunction _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }\nfunction _toPropertyKey(t) { var i = _toPrimitive(t, \"string\"); return \"symbol\" == _typeof(i) ? i : i + \"\"; }\nfunction _toPrimitive(t, r) { if (\"object\" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || \"default\"); if (\"object\" != _typeof(i)) return i; throw new TypeError(\"@@toPrimitive must return a primitive value.\"); } return (\"string\" === r ? String : Number)(t); }\nvar postListHandler = function postListHandler($scope, $) {\n  // category\n  eael.getToken();\n  var $post_cat_wrap = $('.post-categories', $scope),\n    $scroll_on_pagination = parseInt($post_cat_wrap.data('scroll-on-pagination')),\n    $scroll_on_pagination_offset = parseInt($post_cat_wrap.data('scroll-offset'));\n  $post_cat_wrap.on('click', 'a', function (e) {\n    e.preventDefault();\n    var $this = $(this);\n    // tab class\n    $('.post-categories a', $scope).removeClass('active');\n    $this.addClass('active');\n    // collect props\n    var $class = $post_cat_wrap.data('class'),\n      $widget_id = $post_cat_wrap.data(\"widget\"),\n      $page_id = $post_cat_wrap.data(\"page-id\"),\n      $nonce = $post_cat_wrap.data(\"nonce\"),\n      $args = $post_cat_wrap.data('args'),\n      $settings = $post_cat_wrap.data('settings'),\n      $page = 1,\n      $template_info = $post_cat_wrap.data('template'),\n      $taxonomy = {\n        taxonomy: $('.post-categories a.active', $scope).data('taxonomy'),\n        field: 'term_id',\n        terms: $('.post-categories a.active', $scope).data('id')\n      };\n\n    // ajax\n    $.ajax({\n      url: localize.ajaxurl,\n      type: 'POST',\n      data: {\n        action: 'load_more',\n        \"class\": $class,\n        args: $args,\n        taxonomy: $taxonomy,\n        settings: $settings,\n        template_info: $template_info,\n        page: $page,\n        page_id: $page_id,\n        widget_id: $widget_id,\n        nonce: localize.nonce\n      },\n      success: function success(response) {\n        var $content = $(response);\n        if ($content.hasClass('no-posts-found') || $content.length == 0) {\n          $('.eael-post-appender', $scope).empty().append($content);\n\n          // update nav\n          $('.btn-prev-post', $scope).prop('disabled', true);\n          $('.btn-next-post', $scope).prop('disabled', true);\n        } else {\n          $('.eael-post-appender', $scope).empty().append($content);\n\n          // update page\n          $('.post-list-pagination', $scope).data('page', 1);\n\n          // update nav\n          $('.btn-prev-post', $scope).prop('disabled', true);\n          $('.btn-next-post', $scope).prop('disabled', false);\n        }\n      },\n      error: function error(response) {\n        console.log(response);\n      }\n    });\n  });\n\n  // load more\n  var $pagination_wrap = $('.post-list-pagination', $scope);\n  $pagination_wrap.on('click', 'button', function (e) {\n    e.preventDefault();\n    e.stopPropagation();\n    e.stopImmediatePropagation();\n    // collect props\n    var $this = $(this),\n      $widget_id = $pagination_wrap.data(\"widget\"),\n      $page_id = $pagination_wrap.data(\"page-id\"),\n      $nonce = $pagination_wrap.data(\"nonce\"),\n      $class = $pagination_wrap.data('class'),\n      $args = $pagination_wrap.data('args'),\n      $settings = $pagination_wrap.data('settings'),\n      $page = $this.hasClass('btn-prev-post') ? parseInt($pagination_wrap.data('page')) - 1 : parseInt($pagination_wrap.data('page')) + 1,\n      $template_info = $pagination_wrap.data('template'),\n      $taxonomy = {\n        taxonomy: $('.post-categories a.active', $scope).data('taxonomy'),\n        field: 'term_id',\n        terms: $('.post-categories a.active', $scope).data('id')\n      };\n    if ($taxonomy.taxonomy === '' || $taxonomy.taxonomy === 'all' || $taxonomy.taxonomy === 'undefined') {\n      $taxonomy.taxonomy = 'all';\n    }\n    if ($page == 1 && $this.hasClass(\"btn-prev-post\")) {\n      $this.prop('disabled', true);\n    }\n    $this.prop('disabled', true);\n    if ($page <= 0) {\n      return;\n    }\n    $.ajax({\n      url: localize.ajaxurl,\n      type: 'post',\n      data: {\n        action: 'load_more',\n        \"class\": $class,\n        args: $args,\n        taxonomy: $taxonomy,\n        settings: $settings,\n        page: $page,\n        template_info: $template_info,\n        page_id: $page_id,\n        widget_id: $widget_id,\n        nonce: localize.nonce\n      },\n      success: function success(response) {\n        var $content = $(response);\n        if ($content.hasClass('no-posts-found') || $content.length == 0) {\n          // do nothing\n        } else {\n          $('.eael-post-appender', $scope).empty().append($content);\n          if ($page == 1 && $this.hasClass(\"btn-prev-post\")) {\n            $this.prop('disabled', true);\n          } else {\n            $('.post-list-pagination button', $scope).prop('disabled', false);\n          }\n          $pagination_wrap.data('page', $page);\n        }\n        if ($scroll_on_pagination && $('.eael-post-appender', $scope).length > 0) {\n          var $post_list_container = $('.eael-post-list-container', $scope);\n          if (!isElementInViewport($post_list_container)) {\n            $('html, body').animate({\n              scrollTop: $post_list_container.offset().top - $scroll_on_pagination_offset\n            }, 500);\n          }\n        }\n      },\n      error: function error(response) {\n        console.log(response);\n      }\n    });\n  });\n  function isElementInViewport(el) {\n    if (typeof jQuery === \"function\" && el instanceof jQuery) {\n      el = el[0];\n    }\n    var rect = el.getBoundingClientRect();\n    return rect.top >= 0 && rect.left >= 0 && rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /* or $(window).height() */\n    rect.right <= (window.innerWidth || document.documentElement.clientWidth) /* or $(window).width() */;\n  }\n\n  // Responsive layout handler for dynamic breakpoint support\n  function handleResponsiveLayout() {\n    var $postCategories = $('.post-categories', $scope);\n    if (!$postCategories.length) return;\n    var breakpointData = $postCategories.data('breakpoints');\n    var layoutSettings = $postCategories.data('layout-settings');\n    if (!breakpointData || !layoutSettings) {\n      return;\n    }\n    var currentWidth = window.innerWidth;\n    var activeLayout = layoutSettings.desktop || 'horizontal'; // default to desktop layout\n\n    // Separate min-width and max-width breakpoints for proper handling\n    var minWidthBreakpoints = [];\n    var maxWidthBreakpoints = [];\n    Object.keys(breakpointData).forEach(function (key) {\n      var breakpoint = breakpointData[key];\n      if (breakpoint.direction === 'min') {\n        minWidthBreakpoints.push(_objectSpread({\n          key: key\n        }, breakpoint));\n      } else {\n        maxWidthBreakpoints.push(_objectSpread({\n          key: key\n        }, breakpoint));\n      }\n    });\n\n    // Sort min-width breakpoints in descending order (largest first)\n    minWidthBreakpoints.sort(function (a, b) {\n      return b.value - a.value;\n    });\n\n    // Sort max-width breakpoints in ascending order (smallest first)\n    maxWidthBreakpoints.sort(function (a, b) {\n      return a.value - b.value;\n    });\n\n    // Check min-width breakpoints first (desktop, widescreen)\n    for (var _i = 0, _minWidthBreakpoints = minWidthBreakpoints; _i < _minWidthBreakpoints.length; _i++) {\n      var breakpoint = _minWidthBreakpoints[_i];\n      if (currentWidth >= breakpoint.value && layoutSettings[breakpoint.key]) {\n        activeLayout = layoutSettings[breakpoint.key];\n        break;\n      }\n    }\n\n    // Check max-width breakpoints (tablet, mobile) - these override min-width if they match\n    for (var _i2 = 0, _maxWidthBreakpoints = maxWidthBreakpoints; _i2 < _maxWidthBreakpoints.length; _i2++) {\n      var _breakpoint = _maxWidthBreakpoints[_i2];\n      if (currentWidth <= _breakpoint.value && layoutSettings[_breakpoint.key]) {\n        activeLayout = layoutSettings[_breakpoint.key];\n        break;\n      }\n    }\n\n    // Remove all layout classes and add the active one\n    $postCategories.removeClass('eael-categories-layout-horizontal eael-categories-layout-vertical');\n    $postCategories.addClass('eael-categories-layout-' + activeLayout);\n  }\n\n  // Initialize responsive layout on load\n  handleResponsiveLayout();\n\n  // Handle window resize for real-time responsiveness\n  $(window).on('resize.eael-post-list-' + $scope.data('id'), function () {\n    handleResponsiveLayout();\n  });\n\n  // Cleanup on scope destroy\n  $scope.on('remove', function () {\n    $(window).off('resize.eael-post-list-' + $scope.data('id'));\n  });\n};\njQuery(window).on('elementor/frontend/init', function () {\n  elementorFrontend.hooks.addAction('frontend/element_ready/eael-post-list.default', postListHandler);\n});\n\n//# sourceURL=webpack:///./src/js/view/post-list.js?");

/***/ })

/******/ });