/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./packages/frontend/src/accordion.js":
/*!********************************************!*\
  !*** ./packages/frontend/src/accordion.js ***!
  \********************************************/
/***/ (() => {

class GhubAccordion {
  constructor(accordionAccordion) {
    this.accordion = accordionAccordion;
    this.accordionItemAccordion = this.accordion.querySelectorAll('.ghub-accordion-item');
    this.accordionTitles = this.accordion.querySelectorAll('.ghub-accordion-title');
    this.accordionContents = this.accordion.querySelectorAll('.ghub-accordion-content');
    this.accordionContentInnerAccordions = this.accordion.querySelectorAll('.ghub-accordion-content-inner-accordion');
    this.searchElements = this.accordion.querySelectorAll('.wp-block-search');

    // saving accordion content heights.
    this.accordionContentsHeights = Array.from(this.accordionContents).map(contentElem => {
      let computedStyle = getComputedStyle(contentElem);
      let elementHeight = contentElem.clientHeight;
      elementHeight -= parseFloat(computedStyle.paddingTop) + parseFloat(computedStyle.paddingBottom);
      return elementHeight;
    });
    this.accordionContentsPadding = Array.from(this.accordionContents).map(contentElem => getComputedStyle(contentElem).getPropertyValue('padding'));

    // Datasets
    this.defaultFirstItemOpen = this.accordion.dataset.open_first;
    this.openOneAtATime = this.accordion.dataset.open_one;

    //initialization
    this.initialize();
  }
  initialize() {
    this.attachEventHandler();
    this.showInitialAccordion();
    this.removeInitializeClass();
    if (this.searchElements.length > 0) {
      this.attachSearchEvents();
    }
  }
  searchAccordion(value) {
    this.accordionItemAccordion.forEach((item, index) => {
      const searchedAccordion = item.innerText.toLowerCase().includes(value.toLowerCase().trim());
      if (!searchedAccordion) {
        item.classList.add('ghub-accordion-hide');
      } else {
        item.classList.remove('ghub-accordion-hide');
      }
    });
  }
  attachSearchEvents() {
    this.searchElements?.forEach(element => {
      element.addEventListener('submit', e => {
        e.preventDefault();
      });
      const searchInput = element.querySelector('.wp-block-search__input');
      const searchButton = element.querySelector('.wp-block-search__button');
      searchInput.required = false;
      searchButton.addEventListener('click', () => {
        this.searchAccordion(searchInput.value);
      });
      searchInput.addEventListener('keydown', e => {
        if (e.keyCode !== 13) {
          return;
        }
        this.searchAccordion(e.target.value);
      });
    });
  }
  removeInitializeClass() {
    this.accordionContents.forEach(content => {
      if (content.classList.contains('ghub-accordion-content-initialized')) {
        content.classList.remove('ghub-accordion-content-initialized');
      }
    });
  }
  showInitialAccordion() {
    let isInitialAccordionOpen = this.defaultFirstItemOpen === 'true' ? true : false;
    if (isInitialAccordionOpen && this.accordionItemAccordion.length > 0) {
      this.accordionItemAccordion[0].classList.add('ghub-active-accordion');
      this.accordionContents[0].style.height = this.accordionContentsHeights[0] + 'px';
      this.accordionContents[0].style.padding = this.accordionContentsPadding[0];
    }
  }
  setActiveAccordion(idx) {
    let openOneAtATime = this.openOneAtATime === 'true' ? true : false;

    // Accordion
    if (!openOneAtATime && this.accordionItemAccordion.length > 0) {
      this.accordionItemAccordion[idx].classList.toggle('ghub-active-accordion');
    } else {
      this.accordionItemAccordion.forEach((accordion, accordionIndex) => {
        if (idx === accordionIndex && this.accordionItemAccordion.length > 0) {
          accordion.classList.toggle('ghub-active-accordion');
          this.accordionContents[accordionIndex].style.height = this.accordionContentsHeights[accordionIndex] + 'px';
          this.accordionContents[accordionIndex].style.padding = this.accordionContentsPadding[accordionIndex];
        } else {
          accordion.classList.remove('ghub-active-accordion');
          this.accordionContents[accordionIndex].style.height = 0;
          this.accordionContents[accordionIndex].style.paddingTop = 0;
          this.accordionContents[accordionIndex].style.paddingBottom = 0;
        }
      });
    }
    // Accordion
    if (this.accordionItemAccordion[idx].classList.contains('ghub-active-accordion')) {
      this.accordionContents[idx].style.height = this.accordionContentsHeights[idx] + 'px';
      this.accordionContents[idx].style.padding = this.accordionContentsPadding[idx];
    } else {
      this.accordionContents[idx].style.height = 0;
      this.accordionContents[idx].style.paddingTop = 0;
      this.accordionContents[idx].style.paddingBottom = 0;
    }
  }
  attachEventHandler() {
    this.accordionTitles.forEach((title, idx) => {
      this.accordionContents[idx].style.height = 0;
      this.accordionContents[idx].style.paddingTop = 0;
      this.accordionContents[idx].style.paddingBottom = 0;
      setTimeout(() => {
        this.accordionContents[idx].classList.add('ghub-accordion-content-animation');
      }, 250);
      title.addEventListener('click', event => {
        event.stopPropagation();
        this.setActiveAccordion(idx);
      });
    });
  }
}
window.addEventListener('load', () => {
  const accordionAccordions = document.querySelectorAll('.ghub-accordion');
  accordionAccordions.forEach(accordionAccordion => new GhubAccordion(accordionAccordion));
});

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
/************************************************************************/
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
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
/*!****************************************!*\
  !*** ./packages/frontend/src/index.js ***!
  \****************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _accordion__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./accordion */ "./packages/frontend/src/accordion.js");
/* harmony import */ var _accordion__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_accordion__WEBPACK_IMPORTED_MODULE_0__);

})();

/******/ })()
;
//# sourceMappingURL=frontend.js.map