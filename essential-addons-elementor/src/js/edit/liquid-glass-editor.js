/**
 * Liquid Glass Effect - Elementor Editor Support
 * Liquid Glass Editor Controller
 */

class LiquidGlassEditor {
      constructor() {
         this.svgContainer = null;
         this.supportedEffects = ['effect4', 'effect5', 'effect6'];
         this.effectDefaults = {
               effect4: { freq: 0.008, strength: 77 },
               effect5: { freq: 1.008, strength: 77 },
               effect6: { freq: 0.02, strength: 30 }
         };

         this.init();
      }
      
      init() {
         this.bindEvents();
      }

      /**
      * Bind Elementor editor events
      */
      bindEvents() {
         // Panel editor events
         elementor.hooks.addAction('panel/open_editor/widget', () => this.handlePanelOpen());
         elementor.hooks.addAction('panel/open_editor/section', () => this.handlePanelOpen());
         elementor.hooks.addAction('panel/open_editor/container', () => this.handlePanelOpen());

         // Preview events
         elementor.on('preview:loaded', () => this.handlePreviewLoaded());

         // Settings change events
         elementor.channels.editor.on('change', (controlView, elementView) => {
               this.handleSettingsChange(controlView, elementView);
         });
      }

      /**
      * Handle panel open events
      */
      handlePanelOpen() {
         setTimeout(() => this.updateAllSVGFilters(), 100);
      }

      /**
      * Handle preview loaded events
      */
      handlePreviewLoaded() {
         setTimeout(() => {
               this.initSVGContainer();
               this.updateAllSVGFilters();
         }, 500);
      }

      /**
      * Handle settings change events
      * @param {Object} controlView - Elementor control view
      * @param {Object} elementView - Elementor element view
      */
      handleSettingsChange(controlView, elementView) {
         try {
               if (!controlView?.model || !elementView?.model) {
                  return;
               }

               const controlName = controlView.model.get('name');

               if (controlName?.includes('eael_liquid_glass_effect')) {
                  setTimeout(() => this.updateAllSVGFilters(), 150);
               }
         } catch (error) {
               // Silently handle errors
         }
      }

      /**
      * Check if preview iframe is ready
      * @returns {boolean} Preview readiness status
      */
      isPreviewReady() {
         return !!(elementor.$preview?.[0]?.contentWindow);
      }

      /**
      * Get preview document
      * @returns {Document} Preview document
      */
      getPreviewDocument() {
         return elementor.$preview[0].contentWindow.document;
      }

      /**
      * Remove previous SVG container
      * @param {Document} previewDocument - Preview document
      */
      removePreviousContainer(previewDocument) {
         const existing = previewDocument.getElementById('eael-liquid-glass-editor-svg');
         existing?.remove();
      }

      /**
      * Create SVG container in preview document
      * @param {Document} previewDocument - Preview document
      */
      createSVGContainer(previewDocument) {
         this.svgContainer = previewDocument.createElement('div');
         this.svgContainer.id = 'eael-liquid-glass-editor-svg';
         this.svgContainer.style.display = 'none';
         previewDocument.body.appendChild(this.svgContainer);
      }

      /**
      * Initialize SVG container in the preview iframe
      * @returns {boolean} Success status
      */
      initSVGContainer() {
         try {
               if (!this.isPreviewReady()) {
                  return false;
               }

               const previewDocument = this.getPreviewDocument();
               this.removePreviousContainer(previewDocument);
               this.createSVGContainer(previewDocument);

               return true;
         } catch (error) {
               return false;
         }
      }

      /**
      * Get effect settings from element settings
      * @param {string} effect - Effect type (effect4, effect5, effect6)
      * @param {Object} settings - Element settings
      * @returns {Object} Effect configuration
      */
      getEffectSettings(effect, settings) {
         const defaults = this.effectDefaults[effect];
         const freqKey = `eael_liquid_glass_effect_noise_freq_${effect}`;
         const strengthKey = `eael_liquid_glass_effect_noise_strength_${effect}`;

         return {
               freq: settings[freqKey]?.size || defaults.freq,
               strength: settings[strengthKey]?.size || defaults.strength
         };
      }

      /**
      * Generate SVG filter content based on effect type
      * @param {string} effect - Effect type
      * @param {number} freq - Frequency value
      * @param {number} strength - Strength value
      * @returns {string} SVG filter content
      */
      generateFilterContent(effect, freq, strength) {
         const filterConfigs = {
               effect4: `
                  <feTurbulence type="fractalNoise" baseFrequency="${freq} ${freq}" numOctaves="2" seed="92" result="noise" />
                  <feGaussianBlur in="noise" stdDeviation="0.02" result="blur" />
                  <feDisplacementMap in="SourceGraphic" in2="blur" scale="${strength}" xChannelSelector="R" yChannelSelector="G" />
               `,
               effect5: `
                  <feTurbulence type="fractalNoise" baseFrequency="${freq} ${freq}" numOctaves="1" seed="9000" result="noise" />
                  <feGaussianBlur in="noise" stdDeviation="0.1" result="blurred" />
                  <feDisplacementMap in="SourceGraphic" in2="blurred" scale="${strength}" xChannelSelector="R" yChannelSelector="G" />
               `,
               effect6: `
                  <feTurbulence type="turbulence" baseFrequency="${freq}" numOctaves="3" result="turbulence"/>
                  <feDisplacementMap in2="turbulence" in="SourceGraphic" scale="${strength}" xChannelSelector="R" yChannelSelector="G"/>
               `
         };

         return filterConfigs[effect] || '';
      }

      /**
      * Generate complete SVG filter for a specific element and effect
      * @param {string} elementId - Element ID
      * @param {string} effect - Effect type
      * @param {Object} settings - Element settings
      * @returns {string} Complete SVG markup
      */
      generateSVGFilter(elementId, effect, settings) {
         if (!this.supportedEffects.includes(effect)) {
               return '';
         }

         const { freq, strength } = this.getEffectSettings(effect, settings);
         const filterContent = this.generateFilterContent(effect, freq, strength);
         const effectNumber = effect.slice(-1);

         return `
               <svg>
                  <defs>
                     <filter id="eael-glass-distortion${effectNumber}-${elementId}" x="0%" y="0%" width="100%" height="100%">
                           ${filterContent}
                     </filter>
                  </defs>
                  <style>
                     [data-id="${elementId}"].eael_liquid_glass-${effect}::before {
                           filter: url(#eael-glass-distortion${effectNumber}-${elementId});
                     }
                  </style>
               </svg>
         `;
      }

      /**
      * Check if element has liquid glass effect enabled
      * @param {Object} settings - Element settings
      * @returns {boolean} Whether element has liquid glass effect
      */
      hasLiquidGlassEffect(settings) {
         return settings.eael_liquid_glass_effect_switch === 'yes' &&
                  settings.eael_liquid_glass_effect &&
                  this.supportedEffects.includes(settings.eael_liquid_glass_effect);
      }

      /**
      * Process single element for liquid glass effects
      * @param {Object} element - Elementor element
      * @returns {string} Generated SVG HTML or empty string
      */
      processElement(element) {
         const settings = element.get('settings').attributes;

         if (!this.hasLiquidGlassEffect(settings)) {
               return '';
         }

         const elementId = element.get('id');
         const effect = settings.eael_liquid_glass_effect;

         return this.generateSVGFilter(elementId, effect, settings);
      }

      /**
      * Recursively scan elements for liquid glass effects
      * @param {Array} elementsList - List of elements to scan
      * @returns {Array} Array of generated SVG HTML strings
      */
      scanElements(elementsList) {
         const svgFilters = [];

         elementsList.forEach(element => {
               // Process current element
               const svgHTML = this.processElement(element);
               if (svgHTML) {
                  svgFilters.push(svgHTML);
               }

               // Process nested elements
               const children = element.get('elements');
               if (children?.models?.length > 0) {
                  svgFilters.push(...this.scanElements(children.models));
               }
         });

         return svgFilters;
      }

      /**
      * Update SVG filters for all elements with liquid glass effects
      */
      updateAllSVGFilters() {
         try {
               // Initialize SVG container if needed
               if (!this.svgContainer && !this.initSVGContainer()) {
                  return;
               }

               // Clear existing filters
               if (this.svgContainer) {
                  this.svgContainer.innerHTML = '';
               }

               // Check if elementor and preview are available
               if (!window.elementor?.getPreviewView) {
                  return;
               }

               // Get all elements in the current document
               const previewView = elementor.getPreviewView();
               if (!previewView?.collection?.models) {
                  return;
               }

               // Scan all elements and generate SVG filters
               const svgFilters = this.scanElements(previewView.collection.models);

               // Inject all SVG filters
               if (this.svgContainer && svgFilters.length > 0) {
                  this.svgContainer.innerHTML = svgFilters.join('');
               }

         } catch (error) {
            console.error(error);
         }
      }
   }

   /**
    * Initialize Liquid Glass Editor using EAEL hooks system
    */
eael.hooks.addAction("editMode.init", "ea", () => {
   new LiquidGlassEditor();
});
