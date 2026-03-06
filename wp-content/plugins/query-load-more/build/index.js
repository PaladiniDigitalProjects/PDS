/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/edit.js":
/*!*********************!*\
  !*** ./src/edit.js ***!
  \*********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Edit)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! lodash */ "lodash");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _editor_scss__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./editor.scss */ "./src/editor.scss");






function Edit(props) {
  var _ref, _borderProps$style, _typographyProps$styl, _spacingProps$style, _ref2, _ref3;
  const {
    attributes,
    setAttributes
  } = props;
  const {
    backgroundColor,
    backgroundGradient,
    autoTrigger
  } = attributes;
  const fluidTypographySettings = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useSetting)("typography.fluid");
  const borderProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.__experimentalUseBorderProps)(attributes);
  const spacingProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.__experimentalGetSpacingClassesAndStyles)(attributes);
  const typographyProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.getTypographyClassesAndStyles)(attributes, fluidTypographySettings);
  const justificationClass = (_ref = "justify-content-" + attributes?.layout?.justifyContent) !== null && _ref !== void 0 ? _ref : "left";
  let blockProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.useBlockProps)({
    className: "ghub-query-load-more " + justificationClass
  });
  const colorGradientSettings = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.__experimentalUseMultipleOriginColorsAndGradients)();
  const borderStyle = (_borderProps$style = borderProps?.style) !== null && _borderProps$style !== void 0 ? _borderProps$style : {};
  const typographyStyle = (_typographyProps$styl = typographyProps.style) !== null && _typographyProps$styl !== void 0 ? _typographyProps$styl : {};
  const spacingStyle = (_spacingProps$style = spacingProps?.style) !== null && _spacingProps$style !== void 0 ? _spacingProps$style : {};
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...(0,lodash__WEBPACK_IMPORTED_MODULE_4__.omit)({
      ...blockProps,
      className: blockProps?.className.split(" ").filter(className => !className.includes("border")).join(" ")
    }, "style")
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.RichText, {
    tagName: "a",
    value: attributes.label,
    style: (0,lodash__WEBPACK_IMPORTED_MODULE_4__.omitBy)({
      backgroundColor: backgroundColor,
      backgroundImage: backgroundGradient,
      ...borderStyle,
      ...spacingStyle,
      ...typographyStyle
    }, lodash__WEBPACK_IMPORTED_MODULE_4__.isEmpty),
    className: `ghub_query_load_more_link wp-element-button${(_ref2 = " " + typographyProps?.className) !== null && _ref2 !== void 0 ? _ref2 : ""}${(_ref3 = " " + borderProps?.className) !== null && _ref3 !== void 0 ? _ref3 : ""}`,
    onChange: newLabel => setAttributes({
      label: newLabel
    }),
    placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Load More", "query-load-more")
  })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InspectorControls, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.TextControl, {
    label: "Loading Text",
    value: attributes.loadingText,
    onChange: newText => {
      setAttributes({
        loadingText: newText
      });
    }
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.ToggleControl, {
    checked: autoTrigger,
    help: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Auto trigger when in viewport.", "query-load-more"),
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Auto Trigger", "query-load-more"),
    onChange: () => setAttributes({
      autoTrigger: !autoTrigger
    })
  }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.InspectorControls, {
    group: "color"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_3__.__experimentalColorGradientSettingsDropdown, {
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Color Settings", "gutenberghub-load-more"),
    panelId: props.clientId,
    settings: [{
      colorValue: backgroundColor,
      gradientValue: backgroundGradient,
      label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("Background", "gutenberghub-load-more"),
      onColorChange: newValue => {
        setAttributes({
          backgroundColor: newValue
        });
      },
      onGradientChange: newValue => {
        setAttributes({
          backgroundGradient: newValue
        });
      }
    }],
    ...colorGradientSettings
  })));
}

/***/ }),

/***/ "./src/extensions/index.js":
/*!*********************************!*\
  !*** ./src/extensions/index.js ***!
  \*********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _query_loop__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./query-loop */ "./src/extensions/query-loop.js");


/***/ }),

/***/ "./src/extensions/query-loop.js":
/*!**************************************!*\
  !*** ./src/extensions/query-loop.js ***!
  \**************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/hooks */ "@wordpress/hooks");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! lodash */ "lodash");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/compose */ "@wordpress/compose");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_compose__WEBPACK_IMPORTED_MODULE_5__);

/**
 * WordPress Dependencies
 */







/**
 * Add new attributes to the block
 */
const addAttributes = props => {
  if ((0,lodash__WEBPACK_IMPORTED_MODULE_4__.isEmpty)(props.attributes) || props.name !== "core/query") {
    return props;
  }
  props.attributes = (0,lodash__WEBPACK_IMPORTED_MODULE_4__.assign)(props.attributes, {
    ...props.attributes,
    ghubQueryId: {
      type: "string",
      default: ""
    }
  }, {});
  return props;
};
const withAdvanceControls = (0,_wordpress_compose__WEBPACK_IMPORTED_MODULE_5__.createHigherOrderComponent)(BlockEdit => {
  return props => {
    const {
      name
    } = props;
    if (name !== "core/query") {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockEdit, {
        ...props
      });
    }
    (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
      const blocks = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.select)('core/block-editor').getBlocks();
      blocks.forEach(block => {
        if (block.name === 'core/query') {
          const queryId = block.attributes.ghubQueryId;
          const shouldSetQueryId = props.attributes.ghubQueryId === queryId || (0,lodash__WEBPACK_IMPORTED_MODULE_4__.isEmpty)(props.attributes.ghubQueryId);
          if (shouldSetQueryId) {
            props.setAttributes({
              ghubQueryId: (0,lodash__WEBPACK_IMPORTED_MODULE_4__.uniqueId)()
            });
          }
        }
      });
    }, []);
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockEdit, {
      ...props
    });
  };
}, "withAdvanceControls");
const withBlockWrapper = (0,_wordpress_compose__WEBPACK_IMPORTED_MODULE_5__.createHigherOrderComponent)(BlockListBlock => {
  return props => {
    if (props.name !== "core/query") {
      return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockListBlock, {
        ...props
      });
    }
    props.wrapperProps = {
      ...props.wrapperProps,
      "data-ghubqueryid": props.attributes.ghubQueryId
    };
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(BlockListBlock, {
      ...props,
      ...props.wrapperProps
    });
  };
}, "withBlockWrapper");
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__.addFilter)("editor.BlockListBlock", "ghub-gallery-lightbox/with-block-wrapper", withBlockWrapper);
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__.addFilter)("editor.BlockEdit", "ghub-query-taxonomy/with-advance-controls", withAdvanceControls);
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__.addFilter)("blocks.registerBlockType", "ghub-query-taxonomy/add-attributes", addAttributes);

/***/ }),

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _style_scss__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./style.scss */ "./src/style.scss");
/* harmony import */ var _edit__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./edit */ "./src/edit.js");
/* harmony import */ var _block_json__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./block.json */ "./src/block.json");
/* harmony import */ var _save__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./save */ "./src/save.js");
/* harmony import */ var _extensions__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./extensions */ "./src/extensions/index.js");




/**
 * Internal dependencies
 */




(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_1__.registerBlockType)(_block_json__WEBPACK_IMPORTED_MODULE_4__.name, {
  icon: (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("svg", {
    width: "24",
    height: "24",
    viewBox: "0 0 24 24",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("path", {
    "fill-rule": "evenodd",
    "clip-rule": "evenodd",
    d: "M3.2 8H20.8C21.2243 8 21.6313 8.16857 21.9314 8.46863C22.2314 8.76869 22.4 9.17566 22.4 9.6V14.4C22.4 14.8243 22.2314 15.2313 21.9314 15.5314C21.6313 15.8314 21.2243 16 20.8 16H3.2C2.77565 16 2.36869 15.8314 2.06863 15.5314C1.76857 15.2313 1.6 14.8243 1.6 14.4V9.6C1.6 9.17566 1.76857 8.76869 2.06863 8.46863C2.36869 8.16857 2.77565 8 3.2 8ZM0 9.6C0 8.75131 0.337142 7.93738 0.937258 7.33726C1.53737 6.73714 2.35131 6.4 3.2 6.4H20.8C21.6487 6.4 22.4626 6.73714 23.0627 7.33726C23.6629 7.93738 24 8.75131 24 9.6V14.4C24 15.2487 23.6629 16.0626 23.0627 16.6627C22.4626 17.2629 21.6487 17.6 20.8 17.6H3.2C2.35131 17.6 1.53737 17.2629 0.937258 16.6627C0.337142 16.0626 0 15.2487 0 14.4V9.6ZM7.2 10.8C6.88174 10.8 6.57652 10.9264 6.35147 11.1515C6.12643 11.3765 6 11.6817 6 12C6 12.3183 6.12643 12.6235 6.35147 12.8485C6.57652 13.0736 6.88174 13.2 7.2 13.2C7.51826 13.2 7.82348 13.0736 8.04853 12.8485C8.27357 12.6235 8.4 12.3183 8.4 12C8.4 11.6817 8.27357 11.3765 8.04853 11.1515C7.82348 10.9264 7.51826 10.8 7.2 10.8ZM10.8 12C10.8 11.6817 10.9264 11.3765 11.1515 11.1515C11.3765 10.9264 11.6817 10.8 12 10.8C12.3183 10.8 12.6235 10.9264 12.8485 11.1515C13.0736 11.3765 13.2 11.6817 13.2 12C13.2 12.3183 13.0736 12.6235 12.8485 12.8485C12.6235 13.0736 12.3183 13.2 12 13.2C11.6817 13.2 11.3765 13.0736 11.1515 12.8485C10.9264 12.6235 10.8 12.3183 10.8 12ZM16.8 10.8C16.4817 10.8 16.1765 10.9264 15.9515 11.1515C15.7264 11.3765 15.6 11.6817 15.6 12C15.6 12.3183 15.7264 12.6235 15.9515 12.8485C16.1765 13.0736 16.4817 13.2 16.8 13.2C17.1183 13.2 17.4235 13.0736 17.6485 12.8485C17.8736 12.6235 18 12.3183 18 12C18 11.6817 17.8736 11.3765 17.6485 11.1515C17.4235 10.9264 17.1183 10.8 16.8 10.8Z",
    fill: "currentColor"
  })),
  /**
   * @see ./edit.js
   */
  edit: _edit__WEBPACK_IMPORTED_MODULE_3__["default"],
  /**
   * @see ./save.js
   */
  save: _save__WEBPACK_IMPORTED_MODULE_5__["default"]
});

/***/ }),

/***/ "./src/save.js":
/*!*********************!*\
  !*** ./src/save.js ***!
  \*********************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! lodash */ "lodash");
/* harmony import */ var lodash__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(lodash__WEBPACK_IMPORTED_MODULE_2__);

/**
 * Wordpress Dependencies
 */


function Save(props) {
  var _ref, _borderProps$style, _spacingProps$style, _typographyProps$styl, _ref2, _ref3;
  const {
    attributes
  } = props;
  const {
    backgroundColor,
    backgroundGradient
  } = attributes;
  const borderProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.__experimentalGetBorderClassesAndStyles)(attributes);
  const spacingProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.__experimentalGetSpacingClassesAndStyles)(attributes);
  const typographyProps = (0,_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.getTypographyClassesAndStyles)(attributes);
  const justificationClass = (_ref = "justify-content-" + attributes?.layout?.justifyContent) !== null && _ref !== void 0 ? _ref : "left";
  let blockProps = _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.useBlockProps.save({
    className: "ghub-query-load-more " + justificationClass
  });
  const borderStyle = (_borderProps$style = borderProps?.style) !== null && _borderProps$style !== void 0 ? _borderProps$style : {};
  const spacingStyle = (_spacingProps$style = spacingProps?.style) !== null && _spacingProps$style !== void 0 ? _spacingProps$style : {};
  const typographyStyle = (_typographyProps$styl = typographyProps.style) !== null && _typographyProps$styl !== void 0 ? _typographyProps$styl : {};
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    ...(0,lodash__WEBPACK_IMPORTED_MODULE_2__.omit)({
      ...blockProps,
      className: blockProps?.className.split(" ").filter(className => !className.includes("border")).join(" ")
    }, "style")
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_1__.RichText.Content, {
    tagName: "a",
    value: attributes.label,
    style: (0,lodash__WEBPACK_IMPORTED_MODULE_2__.omitBy)({
      backgroundColor: backgroundColor,
      backgroundImage: backgroundGradient,
      ...borderStyle,
      ...spacingStyle,
      ...typographyStyle
    }, lodash__WEBPACK_IMPORTED_MODULE_2__.isEmpty),
    className: `ghub_query_load_more_link wp-element-button${(_ref2 = " " + typographyProps?.className) !== null && _ref2 !== void 0 ? _ref2 : ""}${(_ref3 = " " + borderProps?.className) !== null && _ref3 !== void 0 ? _ref3 : ""}`
  }));
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Save);

/***/ }),

/***/ "./src/editor.scss":
/*!*************************!*\
  !*** ./src/editor.scss ***!
  \*************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./src/style.scss":
/*!************************!*\
  !*** ./src/style.scss ***!
  \************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "lodash":
/*!*************************!*\
  !*** external "lodash" ***!
  \*************************/
/***/ ((module) => {

module.exports = window["lodash"];

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/compose":
/*!*********************************!*\
  !*** external ["wp","compose"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["compose"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/hooks":
/*!*******************************!*\
  !*** external ["wp","hooks"] ***!
  \*******************************/
/***/ ((module) => {

module.exports = window["wp"]["hooks"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ }),

/***/ "./src/block.json":
/*!************************!*\
  !*** ./src/block.json ***!
  \************************/
/***/ ((module) => {

module.exports = JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"ghub/query-load-more","version":"1.0.0","title":"Query Load More","category":"ghub-products","description":"The Load More block in WordPress Gutenberg is a game-changer! It allows users to display a set number of posts initially and then load more with a single click.","attributes":{"autoTrigger":{"type":"boolean","default":false},"backgroundColor":{"type":"string","default":""},"backgroundGradient":{"type":"string","default":""},"loadingText":{"type":"string","default":"Loading..."},"label":{"type":"string","default":"Load More"}},"supports":{"anchor":true,"className":false,"__experimentalBorder":{"color":true,"radius":true,"style":true,"width":true,"__experimentalDefaultControls":{"color":true,"radius":true,"style":true,"width":true}},"__experimentalLayout":{"allowSwitching":false,"allowInheriting":false,"allowOrientation":false,"allowSpaceBetween":false,"allowVerticalAlignment":false,"default":{"type":"flex"}},"color":{"gradients":false,"background":false,"link":true,"text":false},"spacing":{"margin":true,"padding":true},"typography":{"fontSize":true,"lineHeight":true,"__experimentalFontFamily":true,"__experimentalTextDecoration":true,"__experimentalFontStyle":true,"__experimentalFontWeight":true,"__experimentalLetterSpacing":true,"__experimentalTextTransform":true,"__experimentalDefaultControls":{"fontSize":true}},"__unstablePasteTextInline":true},"ancestor":["core/query"],"usesContext":["query","queryId","ghubQueryId"],"textdomain":"query-load-more","editorScript":"file:./index.js","editorStyle":"file:./index.css","style":"file:./style-index.css","viewScript":"gutenberghub-query-load-more-plugin-frontend-script"}');

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"index": 0,
/******/ 			"./style-index": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = globalThis["webpackChunkquery_load_more"] = globalThis["webpackChunkquery_load_more"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["./style-index"], () => (__webpack_require__("./src/index.js")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;
//# sourceMappingURL=index.js.map