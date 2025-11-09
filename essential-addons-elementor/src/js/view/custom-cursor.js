/**
 * Render Circle Cursor
 *
 * @param {*} $scope
 * @param {*} $
 */
function renderCircleCursor( $scope, $){
    let cursorSettings = $scope.data('eael-cursor-settings');
    let elementId = $scope.data('id');
    let dotHtml = '';
    if( cursorSettings?.dot_show ) {
        dotHtml = '<div id="eael-cursor-dot-' + elementId + '"></div>';
    }
    let cursorHtml = '<div id="eael-cursor-' + elementId + '">' + dotHtml + '</div>';
    $($scope).append(DOMPurify.sanitize(cursorHtml));

    // Create cursor elements
    const $cursor = $scope.find('#eael-cursor-' + elementId);

    // Set initial cursor styles (normal state)
    $cursor.css({
        width: cursorSettings?.circle_size + 'px',
        height: cursorSettings?.circle_size + 'px',
        border: cursorSettings?.circle_thickness + 'px ' + cursorSettings?.circle_type + ' ' + cursorSettings?.circle_color,
        borderRadius: cursorSettings?.circle_radius + '%',
        position: 'fixed',
        pointerEvents: 'none',
        zIndex: '999999999999999',
        top: 0,
        left: 0,
        display: 'none',
        willChange: 'transform',
        transition: 'width 250ms cubic-bezier(0.25, 0.46, 0.45, 0.94), height 250ms cubic-bezier(0.25, 0.46, 0.45, 0.94), border 250ms cubic-bezier(0.25, 0.46, 0.45, 0.94), border-radius 250ms cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 250ms cubic-bezier(0.25, 0.46, 0.45, 0.94), background-color 250ms cubic-bezier(0.25, 0.46, 0.45, 0.94)'
    });

    if( cursorSettings?.dot_show ) {
        const $dot = $scope.find('#eael-cursor-dot-' + elementId);
        $dot.css({
            width: cursorSettings?.dot_size + 'px',
            height: cursorSettings?.dot_size + 'px',
            background: cursorSettings?.dot_color,
            borderRadius: cursorSettings?.dot_radius + '%',
            position: 'absolute',
            top: '50%',
            left: '50%',
            transform: 'translate(-50%, -50%)',
            willChange: 'transform',
            transition: 'width 250ms cubic-bezier(0.25, 0.46, 0.45, 0.94), height 250ms cubic-bezier(0.25, 0.46, 0.45, 0.94), background-color 250ms cubic-bezier(0.25, 0.46, 0.45, 0.94), border-radius 250ms cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 250ms cubic-bezier(0.25, 0.46, 0.45, 0.94)'
        });
    }

    if( 'circle' === cursorSettings?.cursor_type ) {
        $scope.css('cursor', 'none');
    }

    let isPointerState = false;

    // Default pointer selectors
    const defaultPointerSelectors = [
        'a[href]',
        'button',
        'input[type="button"]',
        'input[type="submit"]',
        'input[type="reset"]',
        'input[type="checkbox"]',
        'input[type="radio"]',
        'label',
        'select',
        'summary'
    ];

    // Function to apply cursor styles based on state
    function applyCursorStyles(isPointer = false) {
        const state = isPointer ? '_pointer' : '';
        const size = cursorSettings?.['circle_size' + state] || cursorSettings?.circle_size;
        const thickness = cursorSettings?.['circle_thickness' + state] || cursorSettings?.circle_thickness;
        const type = cursorSettings?.['circle_type' + state] || cursorSettings?.circle_type;
        const color = cursorSettings?.['circle_color' + state] || cursorSettings?.circle_color;
        const radius = cursorSettings?.['circle_radius' + state] || cursorSettings?.circle_radius;

        // Apply cursor styles while preserving transitions
        $cursor[0].style.width = size + 'px';
        $cursor[0].style.height = size + 'px';
        $cursor[0].style.border = thickness + 'px ' + type + ' ' + color;
        $cursor[0].style.borderRadius = radius + '%';

        // Apply dot styles if enabled
        if( cursorSettings?.dot_show || cursorSettings?.['dot_show' + state] ) {
            const $dot = $scope.find('#eael-cursor-dot-' + elementId);
            if( $dot.length ) {
                const dotSize = cursorSettings?.['dot_size' + state] || cursorSettings?.dot_size;
                const dotColor = cursorSettings?.['dot_color' + state] || cursorSettings?.dot_color;
                const dotRadius = cursorSettings?.['dot_radius' + state] || cursorSettings?.dot_radius;

                // Apply dot styles while preserving transitions
                $dot[0].style.width = dotSize + 'px';
                $dot[0].style.height = dotSize + 'px';
                $dot[0].style.backgroundColor = dotColor;
                $dot[0].style.borderRadius = dotRadius + '%';
            }
        }
    }

    $scope.on('mouseenter mousemove', function () {
        $cursor.show();
    }).on('mouseleave', function () {
        $cursor.hide();
        isPointerState = false;
        applyCursorStyles(false);
    }).on('mousemove', function (e) {
        // Use clientX/Y for viewport-relative positioning and update cursor position instantly
        const mouseX = e.clientX;
        const mouseY = e.clientY;

        // Update cursor position directly using CSS transform for instant tracking
        $cursor[0].style.transform = `translate3d(${mouseX}px, ${mouseY}px, 0) translate(-50%, -50%)`;

        // Update dot position if enabled (keep centered)
        if( cursorSettings?.dot_show ) {
            const $dot = $scope.find('#eael-cursor-dot-' + elementId);
            if( $dot.length ) {
                $dot[0].style.transform = 'translate(-50%, -50%)';
            }
        }

        // Check if hovering over pointer elements
        const target = e.target;
        const isOverPointerElement = defaultPointerSelectors.some(selector => {
            try {
                return target.matches(selector) || target.closest(selector);
            } catch (err) {
                return false;
            }
        });

        // Update cursor state if changed
        if (isOverPointerElement !== isPointerState ) {
            isPointerState = isOverPointerElement;
            applyCursorStyles(isPointerState);
        }
        if( isPointerState && 'circle' === cursorSettings?.cursor_type_pointer ) {
            $(e.target).css('cursor', 'none');
        }

        if( 'circle' !== cursorSettings?.cursor_type_pointer ) {
            if( isPointerState ) {
                $cursor.hide();
            }else {
                $cursor.show();
            }
        } else if( 'circle' !== cursorSettings?.cursor_type ) {
            if( isPointerState ) {
                $cursor.show();
            }else {
                $cursor.hide();
            }
        }
    });


}

/**
 * Initialize Following Dots Trail
 * 
 * @param {*} $scope 
 * @param {*} $ 
 */
function initFollowingDotsTrail($scope, settings, $) {
  const elementId = $scope.data('id');
  const trailCount = settings?.trail_count || 12;
  const trailSize = settings?.trail_size || 10;
  const trailOpacity = settings?.trail_opacity || 0.5;
  const trailRadius = settings?.trail_radius || 50;
  const trailSpeed = settings?.trail_speed || 0.08;
  const trailColors = settings?.trail_colors;

  const dots = [];
  const mouse = { x: 0, y: 0 };

  const $trailContainer = $('<div>', {
    id: 'eael-trails-' + elementId,
    css: {
      position: 'absolute',
      top: 0,
      left: 0,
      width: '100%',
      height: '100%',
      pointerEvents: 'none',
      zIndex: 9999999999,
      display: 'none'
    }
  }).appendTo($scope.css('position', 'relative'));

  // Create trail dots
  for (let i = 0; i < trailCount; i++) {
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
        zIndex: 9999999999
      }
    }).appendTo($trailContainer);
    dots.push({ $el: $dot, x: 0, y: 0 });
  }

  let idleTimer = null;
  const hideOnIdle = settings?.trail_hide_on_idle;
  const idleTimeout = settings?.trail_idle_timeout * 1000 || 3000; // 3 seconds of inactivity

  function resetIdleTimer() {
    clearTimeout(idleTimer);
    $trailContainer.show();
    
    idleTimer = setTimeout(() => {
      if( hideOnIdle ) {
        $trailContainer.fadeOut('slow');
      }
    }, idleTimeout);
  }

  $scope.on('mousemove', function (e) {
    const offset = $scope.offset();
    mouse.x = e.pageX - offset.left;
    mouse.y = e.pageY - offset.top;
    
    resetIdleTimer();
  }).on('mouseenter', function () {
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

/**
 * Initialize 90s Cursor Trail by using 90s Cursor Effects library
 *
 * @param {*} $scope
 * @param {*} options
 * @param {*} type
 */
function initialize90sCursorTrail( $scope, options, type, settings ) {
    let cursorTrail = false;
    let hideOnIdle = settings?.trail_hide_on_idle;
    let idleTimeout = settings?.trail_idle_timeout;
    let inactivityTimer = null;
    const inactivityTimeout = idleTimeout * 1000 || 3000; // 3 seconds of inactivity before destroying

    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            if( hideOnIdle ) {
                if (cursorTrail) {
                    $scope.find(' > canvas').fadeOut('slow', function(){
                        if (cursorTrail && typeof cursorTrail.destroy === 'function') {
                            cursorTrail.destroy();
                        }
                        cursorTrail = false;
                    });
                }
            }
        }, inactivityTimeout);
    }

    $scope.on('mouseenter mousemove', function () {
        if( type !== settings?.trail_effect ){
            return;
        }
      if (!cursorTrail) {
        if (typeof eaelCursorEffects !== 'undefined' && eaelCursorEffects[type]) {
            cursorTrail = new eaelCursorEffects[type](options);
        }
      }
      resetInactivityTimer();
    }).on('mouseleave', function () {
        clearTimeout(inactivityTimer);
        if (cursorTrail) {
            $scope.find(' > canvas').fadeOut('slow', function(){
                if (cursorTrail && typeof cursorTrail.destroy === 'function') {
                    cursorTrail.destroy();
                }
                cursorTrail = false;
            });
        }
    });
}

function initPhantomSmoke($scope, $settings, $ ) {
    let target = $scope[0];
    let options = {};
    if( $settings?.trail_ghost_size ) {
        options.size = $settings?.trail_ghost_size;
    }
    if( $settings?.trail_ghost_color ) {
        options.mainColor = $settings?.trail_ghost_color;
    }
    let ghostTrail = false;
    let inactivityTimer = null;
    let hideOnIdle = $settings?.trail_hide_on_idle;
    let idleTimeout = $settings?.trail_idle_timeout;
    const inactivityTimeout = idleTimeout * 1000 || 3000; // 3 seconds of inactivity before pausing

    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            if( hideOnIdle ) {
                if (ghostTrail) {
                    $scope.find(' > canvas').fadeOut('slow', function(){
                        if (ghostTrail && typeof ghostTrail.destroy === 'function') {
                            ghostTrail.destroy();
                        }
                        ghostTrail = false;
                    });
                }
            }
        }, inactivityTimeout);
    }

    $scope.on('mouseenter mousemove', function () {
      if (!ghostTrail) {
        if (typeof SmokyGhostTrail !== 'undefined') {
            ghostTrail = new SmokyGhostTrail(target, options);
        }
      } else{
        if (ghostTrail && typeof ghostTrail.start === 'function') {
            ghostTrail.start();
        }
      }
      resetInactivityTimer();
    }).on('mouseleave', function () {
        clearTimeout(inactivityTimer);
        if (ghostTrail) {
            $scope.find(' > canvas').fadeOut('slow', function(){
                if (ghostTrail && typeof ghostTrail.destroy === 'function') {
                    ghostTrail.destroy();
                }
                ghostTrail = false;
            });
        }
    });
}

/**
 * Initialize spiritecho
 *
 * @param {*} $scope
 * @param {*} $
 */
function initspiritecho($scope, settings, $) {
    let target = $scope[0];
    let options = {};

    // Map settings to GhostCursorTrail options
    if( settings?.ghost_following_size ) {
        options.scale = settings?.ghost_following_size;
    }
    if( settings?.ghost_following_color ) {
        options.ghostColor = settings?.ghost_following_color;
    }
    if( settings?.ghost_following_eye_color ) {
        options.eyeColor = settings?.ghost_following_eye_color;
    }

    // Set container to the element
    options.container = target;

    let ghostTrail = false;
    let inactivityTimer = null;
    let hideOnIdle = settings?.trail_hide_on_idle;
    let idleTimeout = settings?.trail_idle_timeout;
    const inactivityTimeout = idleTimeout * 1000 || 3000; // 3 seconds of inactivity before pausing

    function resetInactivityTimer() {
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(() => {
            if( hideOnIdle ) {
                if (ghostTrail) {
                    $scope.find(' > .ghost-cursor-trail, > .ghost-cursor-trail-svg').fadeOut('slow', function(){
                        if( ghostTrail ) {
                            ghostTrail?.destroy();
                        }
                        ghostTrail = false;
                    });
                }
            }
        }, inactivityTimeout);
    }

    $scope.on('mouseenter mousemove', function () {
        if (!ghostTrail) {
            if (typeof GhostCursorTrail !== 'undefined') {
                ghostTrail = new GhostCursorTrail(options);
                if (ghostTrail && typeof ghostTrail.start === 'function') {
                    ghostTrail.start();
                }
            }
        } else {
            if (ghostTrail && typeof ghostTrail.start === 'function') {
                ghostTrail.start();
            }
        }
        resetInactivityTimer();
    }).on('mouseleave', function () {
        clearTimeout(inactivityTimer);
        if (ghostTrail) {
            $scope.find(' > .ghost-cursor-trail, > .ghost-cursor-trail-svg').fadeOut('slow', function(){
                if( ghostTrail ) {
                    ghostTrail?.destroy();
                }
                ghostTrail = false;
            });
        }
    });
}

/**
 * Initialize Snowflake Cursor
 * 
 * @param {*} $scope 
 * @param {*} settings 
 * @param {*} $ 
 */
function initfrostsparkles($scope, settings, $) {
    const trailEmojis = settings?.trail_emojis ? settings.trail_emojis.split(',').filter(emoji => emoji !== '') : ['❄️'];
    const windyEffect = settings?.trail_windy_efect || false;

    const options = {
        element: $scope[0],
        emojis: trailEmojis,
        windy: windyEffect
    };

    initialize90sCursorTrail($scope, options, 'frostsparkles', settings);
}

/**
 * Initialize Pointer Particles
 * 
 * @param {*} $scope 
 * @param {*} settings 
 * @param {*} $ 
 */
function inittrailparticles($scope, settings, $) {
    const particleCount = settings?.particle_count || 30;
    const particleSize = settings?.particle_size || 5;
    const particleColor = settings?.particle_color || '#ff00ff';

    const options = {
        element: $scope[0],
        particleCount: particleCount,
        pointSize: particleSize,
        colors: [particleColor]
    };

    let ponterParticles = false;

    $scope.on('mouseenter mousemove', function () {
        if (!ponterParticles) {
            if (typeof PointerParticles !== 'undefined') {
                ponterParticles = new PointerParticles($scope[0]);
                if (ponterParticles && typeof ponterParticles.start === 'function') {
                    ponterParticles.start();
                }
            }
        } else {
            if (ponterParticles && typeof ponterParticles.start === 'function') {
                ponterParticles.start();
            }
        }
    }).on('mouseleave', function () {
        if (ponterParticles) {
            $scope.find(' > canvas').fadeOut('slow', function(){
                if (ponterParticles && typeof ponterParticles.destroy === 'function') {
                    ponterParticles.destroy();
                }
                ponterParticles = false;
            });
        }
    });
}

/**
 * Initialize Ink Line
 * 
 * @param {*} $scope 
 * @param {*} settings 
 * @param {*} $ 
 */
function initinktrail($scope, settings, $) {
    let options = {
        container: $scope[0]
    };

    if( settings?.ink_line_color ) {
        options.color = settings?.ink_line_color;
    }

    let inktrail = false;
    $scope.on('mouseenter mousemove', function () {
        if (!inktrail) {
            if (typeof eaelInkLine !== 'undefined') {
                inktrail = eaelInkLine(options);
            }
        }
    }).on('mouseleave', function () {
        if (inktrail) {
            $scope.find(' > canvas').fadeOut('slow', function(){
                if (inktrail && typeof inktrail.destroy === 'function') {
                    inktrail.destroy();
                }
                inktrail = false;
            });
        }
    });
}

/**
 * Initialize Glowing Boxes
 * 
 * @param {*} $scope 
 * @param {*} settings 
 * @param {*} $ 
 */
function initGlowingBoxes($scope, settings, $) {
    let opacity = settings?.glowing_boxes_opacity || 1;
    opacity = opacity / 10;
    let id = 'eael-glowing-boxes-'+$scope.data('id');
    let trailLength = settings?.glowing_boxes_trail_length || 20;
    let size = settings?.glowing_boxes_size || 50;
    let borderRadius = settings?.glowing_boxes_border_radius || '1px';
    let interval = settings?.glowing_boxes_interval || 10;
    let hueSpeed = settings?.glowing_boxes_hue_speed || 2;

    var options = {
        container: $scope[0], // any CSS selector or HTMLElement
        trailLength: trailLength,
        size: size,
        interval: interval,
        hueSpeed: hueSpeed,
        borderRadius: borderRadius,
        id: id,
        opacity: opacity
    };


    let glowingBoxes = false;
    $scope.on('mouseenter mousemove', function () {
        if (!glowingBoxes) {
            if (typeof eaelGlowingBoxes !== 'undefined') {
                glowingBoxes = new eaelGlowingBoxes(options);
                if (glowingBoxes && typeof glowingBoxes.init === 'function') {
                    glowingBoxes.init();
                }
            }
        }
    }).on('mouseleave', function () {
        if (glowingBoxes) {
            $scope.find(' > .'+id).fadeOut('slow', function(){
                if (glowingBoxes && typeof glowingBoxes.destroy === 'function') {
                    glowingBoxes.destroy();
                }
                glowingBoxes = false;
            });
        }
    });
}

/**
 * Initialize Color Balls
 *
 * @param {*} $scope
 * @param {*} settings
 * @param {*} $
 */
function initColorBalls($scope, settings, $) {
    let id = 'eael-color-balls-'+$scope.data('id');
    let colors = settings?.color_balls_colors || [];
    let options = {
        container: $scope[0],
        id: id,
        colors: colors
    };

    let colorBalls = false;
    $scope.on('mouseenter mousemove', function () {
        if (!colorBalls) {
            if (typeof eaelColorBalls !== 'undefined') {
                colorBalls = eaelColorBalls(options);
            }
        }
    }).on('mouseleave', function () {
        if (colorBalls) {
            $scope.find(' > .'+id).fadeOut('slow', function(){
                if (colorBalls && typeof colorBalls.destroy === 'function') {
                    colorBalls.destroy();
                }
                colorBalls = false;
            });
        }
    });
}

var CustomCursor = function ($scope, $) {
    let cursorType = $scope.data('eael-cursor');
    let cursorSettings = $scope.data('eael-cursor-settings');

    if( 'circle' === cursorType || 'circle' === cursorSettings?.cursor_type || 'circle' === cursorSettings?.cursor_type_pointer ) {
        renderCircleCursor( $scope, $ );
    }

    if( 'yes' === cursorSettings?.trail ) {
        if( 'following_dots' === cursorSettings?.trail_effect ) {
            initFollowingDotsTrail($scope, cursorSettings, $);
        } else if( 'colorSwipe' === cursorSettings?.trail_effect ) {
            initRainbowTrail($scope, cursorSettings, $);
        } else if( 'phantomsmoke' === cursorSettings?.trail_effect ) {
            initPhantomSmoke($scope, cursorSettings, $);
        } else if( 'spiritecho' === cursorSettings?.trail_effect ) {
            initspiritecho($scope, cursorSettings, $);
        } else if( 'textFlag' === cursorSettings?.trail_effect ) {
            initTextFlag($scope, cursorSettings, $);
        } else if( 'frostsparkles' === cursorSettings?.trail_effect ) {
            initfrostsparkles($scope, cursorSettings, $);
        } else if( 'trailparticles' === cursorSettings?.trail_effect ) {
            inittrailparticles($scope, cursorSettings, $);
        } else if( 'inktrail' === cursorSettings?.trail_effect ) {
            initinktrail($scope, cursorSettings, $);
        } else if( 'glowingBoxes' === cursorSettings?.trail_effect ) {
            initGlowingBoxes($scope, cursorSettings, $);
        } else if( 'colorBalls' === cursorSettings?.trail_effect ) {
            initColorBalls($scope, cursorSettings, $);
        }
    }
}

jQuery(window).on("elementor/frontend/init", function () {
    
    if (eael.elementStatusCheck('eaelCustomCursor2') || window.isEditMode) {
        return false;
    }
    
    elementorFrontend.hooks.addAction( "frontend/element_ready/widget", CustomCursor );
    elementorFrontend.hooks.addAction( "frontend/element_ready/container", CustomCursor );
    elementorFrontend.hooks.addAction( "frontend/element_ready/section", CustomCursor );
});

jQuery(window).on("elementor/frontend/init", function () {
    if (eael.elementStatusCheck('eaelCustomCursor3')) {
        return false;
    }
    let pageId = jQuery('body').data('page-id');
    if( ! pageId ) {
        return;
    }
    let rawData = jQuery('body').find('#eael-cursor-trail-settings-'+pageId).text();
    if( ! rawData ) {
        return;
    }

    let parsedData;
    try {
        parsedData = JSON.parse(rawData);
    } catch (error) {
        console.warn('Failed to parse cursor settings JSON', error);
        return;
    }

    if (!parsedData) {
        return;
    }

    jQuery('body').data('eael-cursor-settings', parsedData);
    CustomCursor( jQuery('body'), jQuery );
});