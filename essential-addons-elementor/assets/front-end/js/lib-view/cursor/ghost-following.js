/**
 * Ghost Cursor Trail Library
 * A cute ghost that follows your cursor with smooth animations and configurable options
 * 
 * @version 1.0.0
 * @author Based on original work by supahfunk
 * @license MIT
 */

class GhostCursorTrail {
    constructor(options = {}) {
        // Default configuration
        this.config = {
            container: document.body,
            ghostColor: '#ffffff',
            eyeColor: '#161616',
            scale: 0.7,
            followSpeed: 10,
            velocityMultiplier: 8,
            zIndex: 9999,
            responsive: true,
            autoStart: false,
            ...options
        };

        // Internal state
        this.isActive = false;
        this.animationId = null;
        this.mouse = { 
            x: window.innerWidth / 2, 
            y: window.innerHeight / 2, 
            dir: '' 
        };
        this.clicked = false;
        this.pos = { x: 0, y: 0 };

        // DOM elements
        this.ghostElement = null;
        this.mouthElement = null;
        this.eyesElement = null;
        this.svgElement = null;

        // Bound methods for event listeners
        this.boundMouseMove = this.handleMouseMove.bind(this);
        this.boundMouseDown = this.handleMouseDown.bind(this);
        this.boundMouseUp = this.handleMouseUp.bind(this);
        this.boundTouchStart = this.handleTouchStart.bind(this);
        this.boundTouchMove = this.handleTouchMove.bind(this);

        // Initialize if autoStart is enabled
        if (this.config.autoStart) {
            this.start();
        }
    }

    /**
     * Create the ghost DOM structure
     */
    createGhostElement() {
        // Create main ghost container
        this.ghostElement = document.createElement('div');
        this.ghostElement.className = 'ghost-cursor-trail ghost-cursor-trail--hidden';
        this.ghostElement.style.zIndex = this.config.zIndex;

        // Set CSS custom properties for colors
        this.ghostElement.style.setProperty('--ghost-color', this.config.ghostColor);
        this.ghostElement.style.setProperty('--eye-color', this.config.eyeColor);

        // Create ghost head
        const head = document.createElement('div');
        head.className = 'ghost-cursor-trail__head';

        // Create eyes
        this.eyesElement = document.createElement('div');
        this.eyesElement.className = 'ghost-cursor-trail__eyes';

        // Create mouth
        this.mouthElement = document.createElement('div');
        this.mouthElement.className = 'ghost-cursor-trail__mouth';

        // Create tail
        const tail = document.createElement('div');
        tail.className = 'ghost-cursor-trail__tail';

        // Create rip effect
        const rip = document.createElement('div');
        rip.className = 'ghost-cursor-trail__rip';

        // Assemble the structure
        head.appendChild(this.eyesElement);
        head.appendChild(this.mouthElement);
        tail.appendChild(rip);
        this.ghostElement.appendChild(head);
        this.ghostElement.appendChild(tail);

        return this.ghostElement;
    }

    /**
     * Create the SVG filter for gooey effect
     */
    createSVGFilter() {
        this.svgElement = document.createElement('div');
        this.svgElement.className = 'ghost-cursor-trail-svg';
        this.svgElement.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1">
                <defs>
                    <filter id="ghost-cursor-goo">
                        <feGaussianBlur
                            in="SourceGraphic"
                            stdDeviation="10"
                            result="ghost-blur" />
                        <feColorMatrix
                            in="ghost-blur"
                            mode="matrix"
                            values="
                                1 0 0 0 0
                                0 1 0 0 0
                                0 0 1 0 0
                                0 0 0 16 -7"
                            result="ghost-gooey" />
                    </filter>
                </defs>
            </svg>
        `;
        return this.svgElement;
    }

    /**
     * Handle mouse movement
     */
    handleMouseMove(e) {
        this.mouse = {
            x: e.clientX || e.pageX || window.innerWidth / 2,
            y: e.clientY || e.pageY || window.innerHeight / 2,
            dir: (this.mouse.x > e.clientX) ? 'left' : 'right'
        };
    }

    /**
     * Handle touch movement
     */
    handleTouchMove(e) {
        if (e.touches && e.touches[0]) {
            this.mouse = {
                x: e.touches[0].pageX || window.innerWidth / 2,
                y: e.touches[0].pageY || window.innerHeight / 2,
                dir: (this.mouse.x > e.touches[0].pageX) ? 'left' : 'right'
            };
        }
    }

    /**
     * Handle touch start
     */
    handleTouchStart(e) {
        this.handleTouchMove(e);
    }

    /**
     * Handle mouse down
     */
    handleMouseDown(e) {
        e.preventDefault();
        this.clicked = true;
    }

    /**
     * Handle mouse up
     */
    handleMouseUp() {
        this.clicked = false;
    }

    /**
     * Utility function to map values from one range to another
     */
    map(num, inMin, inMax, outMin, outMax) {
        return (num - inMin) * (outMax - outMin) / (inMax - inMin) + outMin;
    }

    /**
     * Update ghost position and animations
     */
    updateGhost() {
        if (!this.ghostElement || !this.isActive) return;

        // Calculate distance and velocity
        const distX = this.mouse.x - this.pos.x;
        const distY = this.mouse.y - this.pos.y;

        const velX = distX / this.config.velocityMultiplier;
        const velY = distY / this.config.velocityMultiplier;

        // Update position with smooth following
        this.pos.x += distX / this.config.followSpeed;
        this.pos.y += distY / this.config.followSpeed;

        // Calculate transformations
        const skewX = this.map(velX, 0, 100, 0, -50);
        const scaleY = this.map(velY, 0, 100, 1, 2.0);
        const scaleEyeX = this.map(Math.abs(velX), 0, 100, 1, 1.2);
        let scaleEyeY = this.map(Math.abs(velX * 2), 0, 100, 1, 0.1);
        let scaleMouth = Math.min(
            Math.max(
                this.map(Math.abs(velX * 1.5), 0, 100, 0, 10),
                this.map(Math.abs(velY * 1.2), 0, 100, 0, 5)
            ),
            2
        );

        // Handle click state
        if (this.clicked) {
            scaleEyeY = 0.4;
            scaleMouth = -scaleMouth;
        }

        // Apply transformations
        this.ghostElement.style.transform = `
            translate(${this.pos.x}px, ${this.pos.y}px) 
            scale(${this.config.scale}) 
            skew(${skewX}deg) 
            rotate(${-skewX}deg) 
            scaleY(${scaleY})
        `;

        this.eyesElement.style.transform = `
            translateX(-50%) 
            scale(${scaleEyeX}, ${scaleEyeY})
        `;

        this.mouthElement.style.transform = `
            translate(${(-skewX * 0.5 - 10)}px) 
            scale(${scaleMouth})
        `;
    }

    /**
     * Animation loop
     */
    animate() {
        if (!this.isActive) return;

        this.updateGhost();
        this.animationId = requestAnimationFrame(() => this.animate());
    }

    /**
     * Add event listeners
     */
    addEventListeners() {
        const events = [
            ['mousemove', this.boundMouseMove],
            ['touchstart', this.boundTouchStart],
            ['touchmove', this.boundTouchMove],
            ['mousedown', this.boundMouseDown],
            ['mouseup', this.boundMouseUp]
        ];

        events.forEach(([event, handler]) => {
            window.addEventListener(event, handler, { passive: false });
        });
    }

    /**
     * Remove event listeners
     */
    removeEventListeners() {
        const events = [
            ['mousemove', this.boundMouseMove],
            ['touchstart', this.boundTouchStart],
            ['touchmove', this.boundTouchMove],
            ['mousedown', this.boundMouseDown],
            ['mouseup', this.boundMouseUp]
        ];

        events.forEach(([event, handler]) => {
            window.removeEventListener(event, handler);
        });
    }

    /**
     * Start the ghost cursor trail
     */
    start() {
        if (this.isActive) return this;

        // Create elements if they don't exist
        if (!this.ghostElement) {
            this.createGhostElement();
            this.createSVGFilter();
            this.config.container.appendChild(this.ghostElement);
            this.config.container.appendChild(this.svgElement);
        }

        // Show the ghost
        this.ghostElement.classList.remove('ghost-cursor-trail--hidden');
        this.ghostElement.classList.add('ghost-cursor-trail--visible');

        // Start tracking
        this.isActive = true;
        this.addEventListeners();
        this.animate();

        return this;
    }

    /**
     * Stop the ghost cursor trail
     */
    stop() {
        if (!this.isActive) return this;

        this.isActive = false;
        this.removeEventListeners();

        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
            this.animationId = null;
        }

        // Hide the ghost
        if (this.ghostElement) {
            this.ghostElement.classList.remove('ghost-cursor-trail--visible');
            this.ghostElement.classList.add('ghost-cursor-trail--hidden');
        }

        return this;
    }

    /**
     * Destroy the ghost cursor trail and clean up
     */
    destroy() {
        this.stop();

        // Remove DOM elements
        if (this.ghostElement && this.ghostElement.parentNode) {
            this.ghostElement.parentNode.removeChild(this.ghostElement);
        }
        if (this.svgElement && this.svgElement.parentNode) {
            this.svgElement.parentNode.removeChild(this.svgElement);
        }

        // Reset references
        this.ghostElement = null;
        this.mouthElement = null;
        this.eyesElement = null;
        this.svgElement = null;

        return this;
    }

    /**
     * Update configuration
     */
    updateConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };

        // Update colors if ghost exists
        if (this.ghostElement) {
            this.ghostElement.style.setProperty('--ghost-color', this.config.ghostColor);
            this.ghostElement.style.setProperty('--eye-color', this.config.eyeColor);
            this.ghostElement.style.zIndex = this.config.zIndex;
        }

        return this;
    }

    /**
     * Get current configuration
     */
    getConfig() {
        return { ...this.config };
    }

    /**
     * Check if the ghost is currently active
     */
    isRunning() {
        return this.isActive;
    }
}

// Export for different module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GhostCursorTrail;
} else if (typeof define === 'function' && define.amd) {
    define([], function() {
        return GhostCursorTrail;
    });
} else {
    window.GhostCursorTrail = GhostCursorTrail;
}