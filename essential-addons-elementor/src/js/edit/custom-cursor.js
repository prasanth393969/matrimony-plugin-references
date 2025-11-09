let CustomCursorHandler = function ($scope, $) {

    function handleCircleCursor ( model, element, state = '' ) {
        let settings = model?.attributes?.settings?.attributes;
        let elementId = model?.attributes?.id;

        // Store all cursor settings for both normal and pointer states
        let allCursorSettings = {
            // Normal state settings
            circle_type: settings?.['eael_custom_cursor_circle_type'] ?? 'solid',
            circle_thickness: settings?.['eael_custom_cursor_circle_thickness']?.size ?? 2,
            circle_color: settings?.['eael_custom_cursor_circle_color'] ?? '#9121fc',
            circle_size: settings?.['eael_custom_cursor_circle_size']?.size ?? 20,
            circle_radius: settings?.['eael_custom_cursor_circle_radius']?.size ?? 50,
            dot_show: 'yes' === settings?.['eael_custom_cursor_circle_dot_show'] ? true: false,
            dot_color: settings?.['eael_custom_cursor_circle_dot_color'] ?? '#fff',
            dot_size: settings?.['eael_custom_cursor_circle_dot_size']?.size ?? 8,
            dot_radius: settings?.['eael_custom_cursor_circle_dot_radius']?.size ?? 8,

            // Pointer state settings
            circle_type_pointer: settings?.['eael_custom_cursor_circle_type_pointer'] ?? settings?.['eael_custom_cursor_circle_type'] ?? 'solid',
            circle_thickness_pointer: settings?.['eael_custom_cursor_circle_thickness_pointer']?.size ?? settings?.['eael_custom_cursor_circle_thickness']?.size ?? 2,
            circle_color_pointer: settings?.['eael_custom_cursor_circle_color_pointer'] ?? settings?.['eael_custom_cursor_circle_color'] ?? '#9121fc',
            circle_size_pointer: settings?.['eael_custom_cursor_circle_size_pointer']?.size ?? settings?.['eael_custom_cursor_circle_size']?.size ?? 20,
            circle_radius_pointer: settings?.['eael_custom_cursor_circle_radius_pointer']?.size ?? settings?.['eael_custom_cursor_circle_radius']?.size ?? 50,
            dot_show_pointer: 'yes' === settings?.['eael_custom_cursor_circle_dot_show_pointer'] ? true: (settings?.['eael_custom_cursor_circle_dot_show_pointer'] === undefined ? ('yes' === settings?.['eael_custom_cursor_circle_dot_show'] ? true: false) : false),
            dot_color_pointer: settings?.['eael_custom_cursor_circle_dot_color_pointer'] ?? settings?.['eael_custom_cursor_circle_dot_color'] ?? '#fff',
            dot_size_pointer: settings?.['eael_custom_cursor_circle_dot_size_pointer']?.size ?? settings?.['eael_custom_cursor_circle_dot_size']?.size ?? 8,
            dot_radius_pointer: settings?.['eael_custom_cursor_circle_dot_radius_pointer']?.size ?? settings?.['eael_custom_cursor_circle_dot_radius']?.size ?? 8
        };

        // Get current state settings based on state parameter
        let currentState = state === '_pointer' ? 'pointer' : 'normal';
        let cursorSettings = {
            circle_type: currentState === 'pointer' ? allCursorSettings.circle_type_pointer : allCursorSettings.circle_type,
            circle_thickness: currentState === 'pointer' ? allCursorSettings.circle_thickness_pointer : allCursorSettings.circle_thickness,
            circle_color: currentState === 'pointer' ? allCursorSettings.circle_color_pointer : allCursorSettings.circle_color,
            circle_size: currentState === 'pointer' ? allCursorSettings.circle_size_pointer : allCursorSettings.circle_size,
            circle_radius: currentState === 'pointer' ? allCursorSettings.circle_radius_pointer : allCursorSettings.circle_radius,
            dot_show: currentState === 'pointer' ? allCursorSettings.dot_show_pointer : allCursorSettings.dot_show,
            dot_color: currentState === 'pointer' ? allCursorSettings.dot_color_pointer : allCursorSettings.dot_color,
            dot_size: currentState === 'pointer' ? allCursorSettings.dot_size_pointer : allCursorSettings.dot_size,
            dot_radius: currentState === 'pointer' ? allCursorSettings.dot_radius_pointer : allCursorSettings.dot_radius
        };

        let dotHtml = '';
        if( cursorSettings?.dot_show ) {
            dotHtml = '<div id="eael-cursor-dot-' + elementId + '"></div>';
        }
        let cursorHtml = '<div id="eael-cursor-' + elementId + '">' + dotHtml + '</div>';

        if( ! element.find('#eael-cursor-' + elementId).length ) {
            element.append(DOMPurify.sanitize(cursorHtml));
        }

        // Create cursor elements
        const $cursor = element.find('#eael-cursor-' + elementId);

        // Add smooth transitions for state changes
        $cursor.css({
            width: cursorSettings?.circle_size + 'px',
            height: cursorSettings?.circle_size + 'px',
            border: cursorSettings?.circle_thickness + 'px ' + cursorSettings?.circle_type + ' ' + cursorSettings?.circle_color,
            borderRadius: cursorSettings?.circle_radius + '%',
            position: 'fixed',
            transform: 'translate(-50%, -50%)',
            pointerEvents: 'none',
            zIndex: '9999999',
            top: 0,
            left: 0,
            display: 'none',
            transition: 'width 200ms ease-out, height 200ms ease-out, border 200ms ease-out, border-radius 200ms ease-out, opacity 200ms ease-out'
        });

        if( cursorSettings?.dot_show ) {
            const $dot = element.find('#eael-cursor-dot-' + elementId);
            $dot.css({
                width: cursorSettings?.dot_size + 'px',
                height: cursorSettings?.dot_size + 'px',
                background: cursorSettings?.dot_color,
                borderRadius: cursorSettings?.dot_radius + '%',
                position: 'absolute',
                top: '50%',
                left: '50%',
                transform: 'translate(-50%, -50%)',
                transition: 'width 200ms ease-out, height 200ms ease-out, background 200ms ease-out, border-radius 200ms ease-out'
            });
        }

        let isInArea = false;
        let isPointerState = false;

        // Default pointer selectors for detecting hover state changes
        const defaultPointerSelectors = [
            'a', 'button', 'input[type="button"]', 'input[type="submit"]',
            'input[type="reset"]', '[role="button"]', '.btn', '.button',
            'select', 'textarea', 'input[type="text"]', 'input[type="email"]',
            'input[type="password"]', 'input[type="search"]', 'input[type="url"]',
            'input[type="tel"]', 'input[type="number"]', '[contenteditable]',
            '[tabindex]:not([tabindex="-1"])'
        ];

        // Function to apply cursor styles based on current state
        function applyCursorStyles(isPointer = false) {
            const size = isPointer ? allCursorSettings.circle_size_pointer : allCursorSettings.circle_size;
            const thickness = isPointer ? allCursorSettings.circle_thickness_pointer : allCursorSettings.circle_thickness;
            const type = isPointer ? allCursorSettings.circle_type_pointer : allCursorSettings.circle_type;
            const color = isPointer ? allCursorSettings.circle_color_pointer : allCursorSettings.circle_color;
            const radius = isPointer ? allCursorSettings.circle_radius_pointer : allCursorSettings.circle_radius;
            const dotShow = isPointer ? allCursorSettings.dot_show_pointer : allCursorSettings.dot_show;

            // Apply cursor styles with direct CSS for instant visual feedback
            $cursor[0].style.width = size + 'px';
            $cursor[0].style.height = size + 'px';
            $cursor[0].style.border = thickness + 'px ' + type + ' ' + color;
            $cursor[0].style.borderRadius = radius + '%';

            // Apply dot styles if enabled
            if( dotShow ) {
                const $dot = element.find('#eael-cursor-dot-' + elementId);
                if( $dot.length ) {
                    const dotSize = isPointer ? allCursorSettings.dot_size_pointer : allCursorSettings.dot_size;
                    const dotColor = isPointer ? allCursorSettings.dot_color_pointer : allCursorSettings.dot_color;
                    const dotRadius = isPointer ? allCursorSettings.dot_radius_pointer : allCursorSettings.dot_radius;

                    $dot[0].style.width = dotSize + 'px';
                    $dot[0].style.height = dotSize + 'px';
                    $dot[0].style.backgroundColor = dotColor;
                    $dot[0].style.borderRadius = dotRadius + '%';
                }
            }
        }

        element.on('mouseenter', function () {
            if( ! window['eael_cursor_type_'+element.data('id')] || 'circle' !== window['eael_cursor_type_'+element.data('id')] ){
                return;
            }
            $cursor.show();
            isInArea = true;
        }).on('mouseleave', function () {
            $cursor.hide();
            isInArea = false;
            isPointerState = false;
            applyCursorStyles(false);
        }).on('mousemove', function (e) {
            if (!isInArea) return;

            // Use clientX/Y for viewport-relative positioning and update cursor position instantly
            const mouseX = e.clientX;
            const mouseY = e.clientY;

            // Update cursor position directly using CSS transform with translate3d for instantaneous tracking
            $cursor[0].style.transform = `translate3d(${mouseX}px, ${mouseY}px, 0) translate(-50%, -50%)`;

            // Check if hovering over pointer elements to determine state
            const target = e.target;
            const isOverPointerElement = defaultPointerSelectors.some(selector => {
                try {
                    return target.matches(selector) || target.closest(selector);
                } catch (err) {
                    return false;
                }
            });

            // Update cursor state if changed - render appearance based on current state
            if (isOverPointerElement !== isPointerState) {
                isPointerState = isOverPointerElement;
                applyCursorStyles(isPointerState);
            }

            // Control cursor visibility based on cursor type configuration to prevent double cursors
            // This logic matches the frontend implementation to ensure consistent behavior
            if( 'circle' !== settings?.['eael_custom_cursor_type_pointer'] ) {
                if( isPointerState ) {
                    $cursor.hide();
                }else {
                    $cursor.show();
                }
            } else if( 'circle' !== settings?.['eael_custom_cursor_type'] ) {
                if( isPointerState ) {
                    $cursor.show();
                }else {
                    $cursor.hide();
                }
            } else {
                $cursor.show();
            }
        });

        return 'none';
    }

    async function handleIconCursor( settings, element, suffix = '' ) {
        let iconSettings = {
            icon: settings?.['eael_custom_cursor_icon' + suffix],
            color: settings?.['eael_custom_cursor_icon_color' + suffix],
            size: settings?.['eael_custom_cursor_icon_size' + suffix]?.size,
            svgPath: settings?.eael_icon_to_svg_path
        };
    
        let svghtml = '';
        if ( 'svg' === iconSettings?.icon?.library ) {
            svghtml = iconSettings?.icon?.value?.url;
        } else {
            svghtml = 'data:image/svg+xml;base64,' + btoa( await get_svg_by_icon( iconSettings ) );
        }

        if( ! svghtml ) return '';
        return 'url("' + svghtml + '") 0 0, auto;';
    }

    function adjustItemAndColors( itemCount, assignedColors ) {
        // Get colors from settings
        let colors = assignedColors?.map(color => color.trim().replace(/['"]/g, ''))
            .filter(color => color !== '');
        
        // Assign colors based on count relationship
        let trailColors = [];
        const colorCount = colors.length;
        
        if (colorCount > 0) {
            // Assign colors to each trail segment using modulo for overflow
            for (let i = 0; i < itemCount; i++) {
                trailColors.push(colors[i % colorCount]);
            }
        }
        
        return trailColors;
    }

    function initFollowingDotsTrail( model, element ) {
        let settings = model?.attributes?.settings?.attributes;
        let elementId = model?.attributes?.id;
        const trailCount = settings?.eael_cursor_trail_count || 12;
        const trailSize = settings?.eael_cursor_trail_size?.size || 10;
        let trailOpacity = settings?.eael_cursor_trail_opacity?.size || 5;
        trailOpacity = trailOpacity / 10;
        const trailRadius = settings?.eael_cursor_trail_radius?.size || 50;
        const trailSpeed = ( settings?.eael_cursor_trail_speed?.size || 8 ) / 100;
        let trailColors = [];
    
        if ( 'single' === settings?.eael_cursor_trail_color_type && settings?.eael_cursor_trail_color ) {
            trailColors = [settings?.eael_cursor_trail_color];
        } else {
            for( let i = 1; i <= trailCount; i++ ) {
                if( settings?.['eael_cursor_trail_color_' + i] ) {
                    trailColors.push( settings['eael_cursor_trail_color_' + i] );
                }
            }
        }

        if( trailColors.length < 1 ) {
            trailColors = ['#9121fc'];
        }
        
        trailColors = adjustItemAndColors( trailCount, trailColors );

        const dots = [];
        const mouse = { x: 0, y: 0 };

        if( ! element.find('#eael-trails-' + elementId).length ) {
            $('<div>', {
                id: 'eael-trails-' + elementId,
                css: {
                position: 'absolute',
                top: 0,
                left: 0,
                width: '100%',
                height: '100%',
                pointerEvents: 'none',
                zIndex: 99999,
                display: 'none'
                }
            }).appendTo(element.css('position', 'relative'));
        }
        const $trailContainer = element.find('#eael-trails-' + elementId);
        // Create trail dots
        for (let i = 0; i < trailCount; i++) {
            if( ! element.find('#eael-trail-'+ elementId + '-' + i).length ) {
                const $dot = $('<div>', {
                id: 'eael-trail-'+ elementId + '-' + i,
                css: {
                    width: trailSize + 'px',
                    height: trailSize + 'px',
                    background: trailColors[i],
                    opacity: trailOpacity,
                    borderRadius: trailRadius + '%',
                    position: 'absolute',
                    pointerEvents: 'none',
                    zIndex: 99999
                }
                }).appendTo($trailContainer);
                dots.push({ $el: $dot, x: 0, y: 0 });
            }
        }

        let idleTimer = null;
        let hideOnIdle = settings?.eael_cursor_trail_hide_on_idle;
        const idleTimeout = settings?.eael_cursor_trail_idle_timeout?.size * 1000 || 3000; // 3 seconds of inactivity

        function resetIdleTimer() {
            clearTimeout(idleTimer);
            $trailContainer.show();
            
            idleTimer = setTimeout(() => {
                if( 'yes' === hideOnIdle ) {
                    $trailContainer.fadeOut('slow');
                }
            }, idleTimeout);
        }

        element.on('mousemove', function (e) {
            const offset = element.offset();
            mouse.x = e.pageX - offset.left;
            mouse.y = e.pageY - offset.top;

            $trailContainer.show();
            resetIdleTimer();
        }).on('mouseenter', function () {
            $trailContainer.show();
            resetIdleTimer();
        }).on('mouseleave', function () {
            clearTimeout(idleTimer);
            $trailContainer.fadeOut('slow');
        });

        function animate() {
            let x = mouse.x, y = mouse.y;

            dots.forEach((dot, i) => {
                const next = dots[i - 1] || { x, y };
                dot.x += (next.x - dot.x) * trailSpeed;
                dot.y += (next.y - dot.y) * trailSpeed;
                dot.$el.css('transform', `translate(${dot.x}px, ${dot.y}px)`);
            });

            requestAnimationFrame(animate);
        }

        animate();
    }

    function initialize90sCursorTrail( element, options, type, settings ){
        let cursorTrail = false;
        let inactivityTimer = null;
        let hideOnIdle = settings?.eael_cursor_trail_hide_on_idle;
        let idleTimeout = settings?.eael_cursor_trail_idle_timeout;
        const inactivityTimeout = idleTimeout * 1000 || 3000; // 3 seconds of inactivity before destroying

        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                if( 'yes' === hideOnIdle ) {
                    if (cursorTrail) {
                        element.find(' > canvas').fadeOut('slow', function(){
                            if( typeof cursorTrail.destroy === 'function' ){
                                cursorTrail?.destroy();
                            }
                            cursorTrail = false;
                        });
                    }
                }
            }, inactivityTimeout);
        }

        element.on('mouseenter mousemove', function () {
            if( !window['eael_cursor_trail_'+element.data('id')] ){
                return;
            }
            if( type !== settings?.eael_cursor_trail_effect  ){
                return;
            }
            if (!cursorTrail) {
                cursorTrail = new eaelCursorEffects[type](options);
            }

            // Remove all canvas elements except the last one
            const canvasElements = element.find('>canvas');
            if (canvasElements.length > 1) {
                canvasElements.not(':last').remove();
            }
            
            resetInactivityTimer();
        }).on('mouseleave', function () {
            clearTimeout(inactivityTimer);
            if (cursorTrail) {
                element.find(' > canvas').fadeOut('slow', function(){
                    if( typeof cursorTrail.destroy === 'function' ){
                        cursorTrail?.destroy();
                    }
                    cursorTrail = false;
                });
            }
        });
    }

    function initSmokyGhostCursor( model ) {
        let settings = model?.attributes?.settings?.attributes;
        let target = `.elementor-element-${model?.attributes?.id}`;
        let element = $(target);
        let options = {};
        if( settings?.eael_cursor_trail_ghost_size?.size ) {
            options.size = settings?.eael_cursor_trail_ghost_size?.size / 100;
        }
        if( settings?.eael_cursor_trail_ghost_color ) {
            options.mainColor = settings?.eael_cursor_trail_ghost_color;
        }
        let ghostTrail = false;
        let inactivityTimer = null;
        let hideOnIdle = settings?.eael_cursor_trail_hide_on_idle;
        let idleTimeout = settings?.eael_cursor_trail_idle_timeout?.size;
        const inactivityTimeout = idleTimeout * 1000 || 3000; // 3 seconds of inactivity before pausing

        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                if( 'yes' === hideOnIdle ) {
                    if (ghostTrail) {
                        element.find(' > canvas').fadeOut('slow', function(){
                            if( typeof ghostTrail.destroy === 'function' ){
                                ghostTrail.destroy();
                            }
                            ghostTrail = false;
                        });
                    }
                }
            }, inactivityTimeout);
        }

        element.on('mouseenter mousemove', function () {
            if( !window['eael_cursor_trail_'+element.data('id')] ){
                return;
            }
            if( 'phantomsmoke' !== settings?.eael_cursor_trail_effect  ){
                return;
            }
            if (!ghostTrail) {
                ghostTrail = new SmokyGhostTrail(target, options);
            } else{
                ghostTrail.start();
            }

            const canvasElements = element.find('>canvas');
            if (canvasElements.length > 1) {
                canvasElements.not(':last').remove();
            }

            resetInactivityTimer();
        }).on('mouseleave', function () {
            clearTimeout(inactivityTimer);
            if (ghostTrail) {
                element.find(' > canvas').fadeOut('slow', function(){
                    if( typeof ghostTrail.destroy === 'function' ){
                        ghostTrail?.destroy();
                    }
                    ghostTrail = false;
                });
            }
        });
    }

    function initspiritecho( model ) {
        let settings = model?.attributes?.settings?.attributes;
        let target = `.elementor-element-${model?.attributes?.id}`;
        let element = $(target);
        let options = {};

        // Map Elementor settings to GhostCursorTrail options
        if( settings?.eael_cursor_ghost_following_size?.size ) {
            options.scale = settings?.eael_cursor_ghost_following_size?.size / 10;
        }
        if( settings?.eael_cursor_ghost_following_color ) {
            options.ghostColor = settings?.eael_cursor_ghost_following_color;
        }
        if( settings?.eael_cursor_ghost_following_eye_color ) {
            options.eyeColor = settings?.eael_cursor_ghost_following_eye_color;
        }
        if( settings?.eael_cursor_ghost_following_appearance ) {
            options.appearanceMode = settings?.eael_cursor_ghost_following_appearance;
        }

        // Set container to the element
        options.container = element[0];

        let ghostTrail = false;
        let inactivityTimer = null;
        let hideOnIdle = settings?.eael_cursor_trail_hide_on_idle;
        let idleTimeout = settings?.eael_cursor_trail_idle_timeout?.size;
        const inactivityTimeout = idleTimeout * 1000 || 3000; // 3 seconds of inactivity before pausing

        function resetInactivityTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                if( 'yes' === hideOnIdle ) {
                    if (ghostTrail) {
                        element.find(' > .ghost-cursor-trail, > .ghost-cursor-trail-svg').fadeOut('slow', function(){
                            if( typeof ghostTrail.destroy === 'function' ){
                                ghostTrail.destroy();
                            }
                            ghostTrail = false;
                        });
                    }
                }
            }, inactivityTimeout);
        }

        element.on('mouseenter mousemove', function () {
            if( !window['eael_cursor_trail_'+element.data('id')] ){
                return;
            }
            if( 'spiritecho' !== settings?.eael_cursor_trail_effect  ){
                return;
            }
            if (!ghostTrail) {
                ghostTrail = new GhostCursorTrail(options);
                ghostTrail.start();
            } else {
                ghostTrail.start();
            }

            // Remove all ghost elements except the last one
            const ghostElements = element.find('.ghost-cursor-trail');
            const ghostSvgElements = element.find('.ghost-cursor-trail-svg');
            if (ghostElements.length > 1) {
                ghostElements.not(':last').remove();
            }
            if (ghostSvgElements.length > 1) {
                ghostSvgElements.not(':last').remove();
            }

            resetInactivityTimer();
        }).on('mouseleave', function () {
            clearTimeout(inactivityTimer);
            if (ghostTrail) {
                element.find(' > .ghost-cursor-trail, > .ghost-cursor-trail-svg').fadeOut('slow', function(){
                    if( typeof ghostTrail.destroy === 'function' ){
                        ghostTrail.destroy();
                    }
                    ghostTrail = false;
                });
            }
        });
    }

    function initfrostsparkles( model, element ) {
        let settings = model?.attributes?.settings?.attributes;

        const trailEmojis = settings?.eael_cursor_snowflake_emojis ? 
            settings.eael_cursor_snowflake_emojis.split(',').filter(emoji => emoji !== '') : 
            ['❄️'];
        const windyEffect = settings?.eael_cursor_snowflake_windy_effect || false;

        const options = {
            element: element[0],
            emojis: trailEmojis,
            windy: windyEffect
        };

        initialize90sCursorTrail(element, options, 'frostsparkles', settings);
    }

    function inittrailparticles( model, element ) {
        let settings = model?.attributes?.settings?.attributes;
        let ponterParticles = false;

        element.on('mouseenter mousemove', function () {
            if( !window['eael_cursor_trail_'+element.data('id')] ){
                return;
            }
            if( 'trailparticles' !== settings?.eael_cursor_trail_effect ) {
                return;
            }
            if (!ponterParticles) {
                ponterParticles = new PointerParticles(element[0]);
                ponterParticles.start();
            } else {
                ponterParticles.start();
            }
            const canvasElements = element.find('>canvas');
            if (canvasElements.length > 1) {
                canvasElements.not(':last').remove();
            }
        }).on('mouseleave', function () {
            if (ponterParticles) {
                element.find(' > canvas').fadeOut('slow', function(){
                    ponterParticles?.destroy();
                    ponterParticles = false;
                });
            }
        });
    }

    function initinktrail( model, element ) {
        let settings = model?.attributes?.settings?.attributes;
        let options = {
            container: '.elementor-element-' + model?.attributes?.id
        };

        if( settings?.eael_cursor_ink_line_color ) {
            options.color = settings?.eael_cursor_ink_line_color;
        }

        let inktrail = false;
        element.on('mouseenter mousemove', function () {
            if( !window['eael_cursor_trail_'+element.data('id')] ){
                return;
            }
            if( 'inktrail' !== settings?.eael_cursor_trail_effect ) {
                return;
            }
            if (!inktrail) {
                inktrail = eaelInkLine(options);
                inktrail.start();
            }
            
            const canvasElements = element.find('>canvas');
            if (canvasElements.length > 1) {
                canvasElements.not(':last').remove();
            }
        }).on('mouseleave', function () {
            if (inktrail) {
                element.find(' > canvas').fadeOut('slow', function(){
                    if( typeof inktrail.destroy === 'function' ){
                        inktrail?.destroy();
                    }
                    inktrail = false;
                });
            }
        });
    }

    function initGlowingBoxes( model, element ) {
        let settings = model?.attributes?.settings?.attributes;
        let id = 'eael-glowing-boxes-'+model?.attributes?.id;

        let options = {
            container: '.elementor-element-' + model?.attributes?.id,
            id: id
        };

        if( settings?.eael_cursor_glowing_boxes_opacity?.size ) {
            options.opacity = settings?.eael_cursor_glowing_boxes_opacity?.size / 10;
        }
        if( settings?.eael_cursor_glowing_boxes_size?.size ) {
            options.size = settings?.eael_cursor_glowing_boxes_size?.size;
        }
        if( settings?.eael_cursor_glowing_boxes_border_radius?.size ) {
            options.borderRadius = settings?.eael_cursor_glowing_boxes_border_radius?.size + 'px';
        }
        if( settings?.eael_cursor_glowing_boxes_trail_length?.size ) {
            options.trailLength = settings?.eael_cursor_glowing_boxes_trail_length?.size;
        }
        if( settings?.eael_cursor_glowing_boxes_interval?.size ) {
            options.interval = settings?.eael_cursor_glowing_boxes_interval?.size;
        }
        if( settings?.eael_cursor_glowing_boxes_hue_speed?.size ) {
            options.hueSpeed = settings?.eael_cursor_glowing_boxes_hue_speed?.size;
        }

        let glowingBoxes = false;
        
        element.on('mouseenter mousemove', function () {
            if( 'glowingBoxes' !== settings?.eael_cursor_trail_effect ) {
                return;
            }
            if (!glowingBoxes) {
                glowingBoxes = new eaelGlowingBoxes(options);
                glowingBoxes.init();
            }
            const canvasElements = element.find('.'+id);
            if (canvasElements.length > 1) {
                canvasElements.not(':last').remove();
            }
            
        }).on('mouseleave', function () {
            if (glowingBoxes) {
                element.find(' > .'+id).fadeOut('slow', function(){
                    if( typeof glowingBoxes.destroy === 'function' ){
                        glowingBoxes.destroy();
                    }
                    glowingBoxes = false;
                });
            }
        });
    }

    function initColorBalls( model, element ) {
        let settings = model?.attributes?.settings?.attributes;
        let id = 'eael-color-balls-'+model?.attributes?.id;

        let options = {
            container: '.elementor-element-' + model?.attributes?.id,
            id: id
        };

        let colors = [];
        for( let i = 1; i <= 7; i++ ) {
            if( settings?.['eael_cursor_color_balls_color_' + i] && settings?.['eael_cursor_color_balls_color_' + i] !== '' ) {
                colors.push( settings['eael_cursor_color_balls_color_' + i] );
            }
        }
        colors = colors.filter( color => color.trim() !== '' );
        if( colors.length > 0 ) {
            options.colors = colors;
        }

        let colorBalls = false;
        
        element.on('mouseenter mousemove', function () {
            if( 'colorBalls' !== settings?.eael_cursor_trail_effect ) {
                return;
            }
            if (!colorBalls) {
                colorBalls = eaelColorBalls(options);
            }
            const canvasElements = element.find('.'+id);
            if (canvasElements.length > 1) {
                canvasElements.not(':last').remove();
            }
        }).on('mouseleave', function () {
            if (colorBalls) {
                element.find(' > .'+id).fadeOut('slow', function(){
                    if( typeof colorBalls.destroy === 'function' ){
                        colorBalls.destroy();
                    }
                    colorBalls = false;
                });
            }
        });
    }

    async function get_svg_by_icon(settings) {
        if (!settings?.icon || !settings?.icon?.value || !settings?.icon?.library) return false;

        let icon = settings?.icon;
        let iconName = icon?.value?.replace(/(fas fa\-|fab fa\-|far fa\-)/, '');
        let library = icon?.library?.replace('fa-', '');
        let iClass = icon?.value?.replace(/\s+/g, '-');
        
        try {
            const response = await fetch(settings.svgPath + library + '.json');
            const svgObject = await response.json();
            
            if (!svgObject.icons || !svgObject.icons[iconName]) {
                return false;
            }
            
            const iconData = svgObject.icons[iconName];
            const viewBox = `0 0 ${iconData[0]} ${iconData[1]}`;
            let svgHtml = '<svg ';
            
            let color = '';
            if (settings?.color) {
                color = settings?.color;
            }
            
            if (settings?.size) {
                svgHtml += `width="${settings?.size}" `;
                svgHtml += `height="${settings?.size}" `;
            }
            
            svgHtml += `class="svg-inline--${iClass} eael-svg-icon" aria-hidden="true" data-icon="${iconName}" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="${viewBox}">`;
            svgHtml += `<path fill="${color}" d="${iconData[4]}"></path>`;
            svgHtml += `</svg>`;
            
            return svgHtml;
        } catch (error) {
            console.error('Error loading cursor icon JSON:', error);
            return false;
        }
    }

    async function renderCustomCursor (model) {
        let settings = model?.attributes?.settings?.attributes;
        let elementId = model?.attributes?.id, element = $(`.elementor-element-${elementId}`);

        window['eael_cursor_trail_'+elementId] = 'yes' === settings?.eael_cursor_trail_show;
        window['eael_cursor_type_'+elementId] = 'yes' === settings?.eael_enable_custom_cursor ? settings?.eael_custom_cursor_type : false;
        
        if( 'yes' === settings?.eael_enable_custom_cursor ) {
            let cursor = '';
            let cursor_css = '';
            if( 'image' === settings?.eael_custom_cursor_type ) {
                cursor = 'url("' + settings?.eael_custom_cursor_image?.url + '") 0 0, auto';
            } else if( 'icon' === settings?.eael_custom_cursor_type ) {
                 cursor = await handleIconCursor( settings, element );
            } else if( 'svg_code' === settings?.eael_custom_cursor_type ) {
                cursor = 'url("data:image/svg+xml;base64,' + btoa(settings?.eael_custom_cursor_svg_code) + '") 0 0, auto';
            } else if( 'regular' === settings?.eael_custom_cursor_type && settings?.eael_custom_cursor_regular ) {
                cursor = settings?.eael_custom_cursor_regular;                
            } else if( 'circle' === settings?.eael_custom_cursor_type ) {
                cursor = handleCircleCursor( model, element );
            }

            if( cursor ) {
                cursor_css += '.elementor-element-' + elementId + '{ cursor: ' + cursor + '; }';
            }

            let pointer_cursor = '';
            if( 'yes' === settings?.eael_enable_pointer_cursor ) {
                if( 'image' === settings?.eael_custom_cursor_type_pointer ) {
                pointer_cursor = 'url("' + settings?.eael_custom_cursor_image_pointer?.url + '") 0 0, auto';
                } else if( 'icon' === settings?.eael_custom_cursor_type_pointer ) {
                    pointer_cursor = await handleIconCursor( settings, element, '_pointer' );
                } else if( 'svg_code' === settings?.eael_custom_cursor_type_pointer ) {
                    pointer_cursor = 'url("data:image/svg+xml;base64,' + btoa(settings?.eael_custom_cursor_svg_code_pointer) + '") 0 0, auto';
                } else if( 'regular' === settings?.eael_custom_cursor_type_pointer && settings?.eael_custom_cursor_regular_pointer ) {
                    pointer_cursor = settings?.eael_custom_cursor_regular_pointer;                
                } else if( 'circle' === settings?.eael_custom_cursor_type_pointer ) {
                    pointer_cursor = handleCircleCursor( model, element, '_pointer' );
                }
            }

            if( pointer_cursor ) {
                cursor_css += '.elementor-element-' + elementId + ' ' + settings?.eael_pointer_selectors.join(', .elementor-element-' + elementId + ' ') + '{ cursor: ' + pointer_cursor + '; }';
            }

            if( element.find('#eael-cursor-style-' + elementId).length ) {
                element.find('#eael-cursor-style-' + elementId).remove();
            }
            
            if( cursor_css ) {
                element.append('<style id="eael-cursor-style-' + elementId + '">' + DOMPurify.sanitize( cursor_css ) + '</style>');
            }
        }

        if( 'yes' === settings?.eael_cursor_trail_show ) {
            if( 'following_dots' === settings?.eael_cursor_trail_effect ) {
                initFollowingDotsTrail( model, element );
            } else if( 'phantomsmoke' === settings?.eael_cursor_trail_effect ) {
                initSmokyGhostCursor( model, element );
            } else if( 'spiritecho' === settings?.eael_cursor_trail_effect ) {
                initspiritecho( model );
            } else if( 'frostsparkles' === settings?.eael_cursor_trail_effect ) {
                initfrostsparkles( model, element );
            } else if( 'trailparticles' === settings?.eael_cursor_trail_effect ) {
                inittrailparticles( model, element );
            } else if( 'inktrail' === settings?.eael_cursor_trail_effect ) {
                initinktrail( model, element );
            } else if( 'glowingBoxes' === settings?.eael_cursor_trail_effect ) {
                initGlowingBoxes( model, element );
            } else if( 'colorBalls' === settings?.eael_cursor_trail_effect ) {
                initColorBalls( model, element );
            }
        }
    }
    function getHoverEffectSettingsVal( models ) {
        if (!models || !Array.isArray(models)) {
            return;
        }

        $.each(models, function (i, model) {
            if (!model || !model.attributes) {
                return;
            }

            renderCustomCursor( model );

            if ( model.attributes.elType !== 'widget' &&
                model.attributes.elements &&
                model.attributes.elements.models
            ) {
                getHoverEffectSettingsVal( model.attributes?.elements?.models );
            }
        });
    }

    // Only run in edit mode and ensure elementor object exists and models are present
    if ( window.elementor?.elements?.models ) {
        getHoverEffectSettingsVal( window.elementor.elements.models );
    }
}

jQuery(window).on("elementor/frontend/init", function () {
    if (eael.elementStatusCheck('eaelCustomCursor')) {
        return false;
    }
    elementorFrontend.hooks.addAction( "frontend/element_ready/widget", CustomCursorHandler );
});

eael.hooks.addAction("editMode.init", "ea", () => {
    elementor.channels.editor.on("eael:custom_cursor:apply_changes", function () {
        elementor.saver.update.apply().then(function () {
            elementor.reloadPreview();
        });
    });

    elementor.settings.page.addChangeCallback(
		"eael_enable_custom_cursor",
		function (newValue) {
            if( 'yes' !== newValue ) {
                elementor.saver.update.apply().then(function () {
                    elementor.reloadPreview();
                });
            }
		}
	);
    
    elementor.settings.page.addChangeCallback(
		"eael_cursor_trail_show",
		function (newValue) {
            if( 'yes' !== newValue ) {
                elementor.saver.update.apply().then(function () {
                    elementor.reloadPreview();
                });
            }
		}
	);
});
