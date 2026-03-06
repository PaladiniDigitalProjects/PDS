jQuery(document).ready(function($) {

    if (typeof pds_params === 'undefined') return;

    let contentElements = null;
    let originalContents = new Map();
    let isTranslated = false;
    let errorTimeout = null;
    let isProcessing = false;
    let vSliderTimelines = new Map();

    const uiContainer = $('#pds-translate-ui-container');
    const vSliderContainers = $('.pds-v-slider-container');
    const errorMessageDiv = $('<div id="pds-error-message"></div>').hide().appendTo('body');

    const selectorString = pds_params.content_selector.split(',').map(s => s.trim()).filter(s => s).join(', ');
    if (selectorString) {
         contentElements = $(selectorString);
    }

    if (!contentElements || contentElements.length === 0) {
        console.warn('PDS Translator: Content element(s) not found using selector:', pds_params.content_selector);
        uiContainer.hide(); vSliderContainers.hide(); return;
    }

    let languageSelect, translateButton, showOriginalButton;
    if (uiContainer.length > 0) {
        uiContainer.empty();
        languageSelect = $('<select id="pds-language-select" title="' + pds_params.text_select_language + '"></select>');
        let languagesAvailable = false;
        if (pds_params.available_languages && Object.keys(pds_params.available_languages).length > 0) { languagesAvailable = true; $.each(pds_params.available_languages, function(key, label) { const option = $('<option></option>').val(key).text(label); if (key === pds_params.default_language) option.prop('selected', true); languageSelect.append(option); }); }
        else { languageSelect.append('<option value="">--</option>').prop('disabled', true); }
        translateButton = $('<button id="pds-do-translate-button"></button>').text(pds_params.text_translate_button);
        showOriginalButton = $('<button id="pds-show-original-button"></button>').text(pds_params.text_show_original).hide();
        uiContainer.append(languageSelect); uiContainer.append(translateButton); uiContainer.append(showOriginalButton);
        if (pds_params.is_dev_mode) { uiContainer.append(`<span class="pds-dev-mode-notice-ui">${pds_params.text_dev_mode_notice}</span>`); }
        translateButton.on('click', function() { const selectedLanguage = languageSelect.val(); if (selectedLanguage) { triggerTranslation(selectedLanguage); } });
        showOriginalButton.on('click', function() { if (isProcessing) return; hideError(); revertToOriginalState(true); });
    }

    if (vSliderContainers.length > 0 && typeof gsap !== 'undefined') {
        vSliderContainers.each(function(index) {
            const sliderId = `pds-v-slider-${index}`; const $sliderContainer = $(this).attr('id', sliderId);
            const vsOpts = { $slides: $sliderContainer.find('.pds-v-slide'), $list: $sliderContainer.find('.pds-v-slides'), duration: 14, lineHeight: 100 };
            if (vsOpts.$slides.length > 1) {
                gsap.set(vsOpts.$list, { y: 0 }); const vSlideTimeline = gsap.timeline({ paused: true, repeat: -1 });
                vsOpts.$slides.each(function(i) { vSlideTimeline.to(vsOpts.$list, { duration: vsOpts.duration / vsOpts.$slides.length, y: i * -1 * vsOpts.lineHeight, ease: "elastic.out(1, 0.4)" }); });
                vSlideTimeline.to(vsOpts.$list, { duration: vsOpts.duration / vsOpts.$slides.length, y: vsOpts.$slides.length * -1 * vsOpts.lineHeight, ease: "elastic.out(1, 0.4)" }).set(vsOpts.$list, { y: 0 });
                vSlideTimeline.play(); vSliderTimelines.set(sliderId, vSlideTimeline);
                vsOpts.$list.on('click', '.pds-v-slide-trigger', function(e) { e.preventDefault(); if (isProcessing) return; const targetLanguage = $(this).data('target-language'); const currentTimeline = vSliderTimelines.get(sliderId); if (targetLanguage) { if (currentTimeline) { currentTimeline.pause(); } triggerTranslation(targetLanguage, currentTimeline); } });
            } else if (vsOpts.$slides.length === 1) {
                 gsap.set(vsOpts.$list, { y: 0 });
                 vsOpts.$list.on('click', '.pds-v-slide-trigger', function(e) { e.preventDefault(); if (isProcessing) return; const targetLanguage = $(this).data('target-language'); if(targetLanguage) { triggerTranslation(targetLanguage); } });
            }
        });
    } else if (vSliderContainers.length > 0 && typeof gsap === 'undefined') {
        console.warn('PDS Translator: GSAP library not loaded. Slider animation disabled.');
        vSliderContainers.find('.pds-v-slide-trigger').on('click', function(e) { e.preventDefault(); if (isProcessing) return; const targetLanguage = $(this).data('target-language'); if(targetLanguage) { triggerTranslation(targetLanguage); } });
    }

    function triggerTranslation(targetLanguage, timelineToResume = null) {
        if (isProcessing) return;
        hideError();
        if (!pds_params.is_dev_mode && pds_params.api_key_missing) { showError(pds_params.is_admin ? pds_params.text_error_no_key_admin : pds_params.text_error_no_key_user); return; }
        if (isTranslated) { showError("Please click 'Show Original' before translating again."); return; }

        if (originalContents.size === 0) {
             contentElements.each(function() { const $el = $(this); const elementHTML = $el.html(); if (elementHTML && elementHTML.trim()) { originalContents.set($el[0], elementHTML); } });
             if (originalContents.size === 0) { showError(pds_params.text_error_no_content); return; }
        }

        isProcessing = true;
        contentElements.removeClass('pds-translation-failed');
        updateUILoadingState(true, targetLanguage, true);

        const ajaxPromises = [];
        let overallSuccess = true;
        let elementsToProcessCount = 0;
        let elementsProcessedCount = 0;

        contentElements.each(function() {
             const $el = $(this); const elementDOM = $el[0];
             if (originalContents.has(elementDOM)) { elementsToProcessCount++; }
        });

        if (elementsToProcessCount === 0) {
             console.warn("PDS Translator: No elements found with content to translate (or content not stored).");
             isProcessing = false; updateUILoadingState(false, '', false); originalContents.clear(); return;
        }

        updateProgressButton(0, elementsToProcessCount);

        contentElements.each(function() {
            const $el = $(this); const elementDOM = $el[0]; const originalHTML = originalContents.get(elementDOM);
            if (!originalHTML) { return; }
            applyPlaceholders($el);
            const promise = $.ajax({ url: pds_params.ajax_url, type: 'POST', dataType: 'json', data: { action: 'pds_translate_page', nonce: pds_params.nonce, content: originalHTML, target_language: targetLanguage }, timeout: 70000 })
            .done(function(response) { if (!(response.success && response.data?.translated_html)) { overallSuccess = false; console.error("PDS Translator: Translation failed for one element.", response.data?.message || 'Unknown API error'); $el.addClass('pds-translation-failed'); $el.html(originalContents.get(elementDOM)); } else { $el.html(response.data.translated_html); } })
            .fail(function(jqXHR, textStatus) { overallSuccess = false; console.error('PDS Translator: AJAX Error for one element:', textStatus, jqXHR.status); $el.addClass('pds-translation-failed'); $el.html(originalContents.get(elementDOM)); })
            .always(function() { elementsProcessedCount++; updateProgressButton(elementsProcessedCount, elementsToProcessCount); removePlaceholders($el); });
            ajaxPromises.push(promise);
        });

        $.when.apply($, ajaxPromises).then(
            function() { isTranslated = true; updateUIVisibility(true); },
            function() { console.error("PDS Translator: One or more translation requests failed."); showError(pds_params.text_error_partial_fail); isTranslated = true; updateUIVisibility(true); }
        ).always(function() {
            isProcessing = false; updateUILoadingState(false, '', false);
            if (timelineToResume) { timelineToResume.play(); }
        });
    }

    function applyPlaceholders(element) {
        if (!element.hasClass('pds-content-processing')) { 
            element.addClass('pds-content-processing'); 
        }
        element.find('p, h1, h2, h3, h4, h5, h6, li, td, th, blockquote, figcaption, label, img').each(function() {
            const $childEl = $(this);
            if ($childEl.closest('.pds-placeholder-active', element[0]).length === 0 && !$childEl.hasClass('pds-placeholder-active')) {
                if ($childEl.is('img')) { 
                    $childEl.addClass('pds-placeholder-active'); 
                } else if ($childEl.text().trim().length > 0) { 
                    $childEl.addClass('pds-placeholder-active'); 
                }
            }
        });
        // Check if click-lang elements exist and then add the classes
        const $clickLang = $('.click-lang');
        if ($clickLang.length) {
            $clickLang.removeClass('click-lang');
            $clickLang.addClass('click-lang-active');
        }
    }
    
    function removePlaceholders(element) {
        element.removeClass('pds-content-processing');
        element.find('.pds-placeholder-active').removeClass('pds-placeholder-active');
        // Check if click-lang elements exist and then remove the classes
      /*  const $clickLang = $('.click-lang-active');
        if ($clickLang.length) {
            $clickLang.removeClass('click-lang-active');
            $clickLang.addClass('click-lang');
        }*/
    }
    

    function updateUIVisibility(showOriginalVisible) {
        if (!uiContainer.length) return;
        if (showOriginalVisible) { languageSelect.hide(); translateButton.hide(); showOriginalButton.show(); }
        else { languageSelect.show(); translateButton.show(); translateButton.text(pds_params.text_translate_button).removeClass('has-progress'); showOriginalButton.hide(); }
    }

    function updateUILoadingState(isLoading, targetLanguage = '', sliderLoading = false) {
        if (translateButton && translateButton.length) {
             if (isLoading) { translateButton.addClass('loading'); }
             else { translateButton.text(pds_params.text_translate_button).removeClass('loading has-progress'); }
        }
        if (languageSelect && languageSelect.length) { languageSelect.prop('disabled', isLoading); }
        if (sliderLoading) { vSliderContainers.addClass('pds-slider-loading'); }
        else { vSliderContainers.removeClass('pds-slider-loading'); }
    }

    function updateProgressButton(processed, total) {
         if (translateButton && translateButton.length && isProcessing) {
             const progressTextFormat = pds_params.text_translating_progress || 'Translating %d/%d...';
             const safeFormat = progressTextFormat.replace(/%[sd]/g, '%d');
             const formatString = (str, args) => { let i = 0; return str.replace(/%d/g, () => args[i++]); };
             const progressText = formatString(safeFormat, [processed, total]);
             translateButton.text(progressText).addClass('has-progress');
         }
    }

    function revertToOriginalState(restoreContent) {
        if (restoreContent && originalContents.size > 0) { originalContents.forEach((html, element) => { $(element).html(html).removeClass('pds-translation-failed'); removePlaceholders($(element)); }); originalContents.clear(); }
        else if (!restoreContent) { contentElements.removeClass('pds-translation-failed'); contentElements.each(function() { removePlaceholders($(this)); }); }
        isTranslated = false; isProcessing = false;
        updateUIVisibility(false); updateUILoadingState(false, '', false);
        vSliderTimelines.forEach(timeline => { if(timeline) timeline.play(); });
    }

    function showError(message) { errorMessageDiv.text(message).fadeIn(); clearTimeout(errorTimeout); errorTimeout = setTimeout(hideError, 7000); }
    function hideError() { clearTimeout(errorTimeout); errorMessageDiv.fadeOut(); }

});