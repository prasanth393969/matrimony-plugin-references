function eaelGlowingBoxes (options) {
  this.size         = options.size || 50;
  this.trailLength  = options.trailLength || 20;
  this.interval     = options.interval || 15;
  this.hueSpeed     = options.hueSpeed || 6;
  this.borderRadius = options.borderRadius || '0px';
  this.opacity      = options.opacity || 1;
  this.container    = typeof options.container === 'string' ? document.querySelector(options.container) : (options.container || document.body);
  this.id           = options.id || '';

  this.boxes = [];
  this.hue   = 0;
  this.mouse = { x: window.innerWidth / 2, y: window.innerHeight / 2 };
  this.running = false;
  this.intervalId = null;

  this.init = () => {
        this.destroy(); // Clean if re-init

        // Create wrapper
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'eael-glow-wrapper';
        this.wrapper.style.position = 'absolute';
        this.wrapper.style.top = 0;
        this.wrapper.style.left = 0;
        this.wrapper.style.width = '100%';
        this.wrapper.style.height = '100%';
        this.wrapper.style.pointerEvents = 'none';
        this.wrapper.style.opacity = this.opacity;
        if( this?.id ) {
            this.wrapper.id = this.id;
            this.wrapper.className += ' ' + this.id;
        }
        this.container.appendChild(this.wrapper);

        for (let i = 0; i < this.trailLength; i++) {
            let box = document.createElement('div');
            box.className = 'eael-glow-box';
            box.style.width = this.size + 'px';
            box.style.height = this.size + 'px';
            box.style.borderRadius = this.borderRadius;
            box.style.position = 'absolute';
            box.style.pointerEvents = 'none';
            this.wrapper.appendChild(box);
            this.boxes.push(box);
        }

        this.container.addEventListener('mousemove', this.handleMouseMove);
        this.start();
    };


  this.handleMouseMove = (event) => {
    const rect = this.container.getBoundingClientRect();
    this.mouse.x = event.clientX - rect.left;
    this.mouse.y = event.clientY - rect.top;
  };

  this.updateHue = () => {
    this.hue = (this.hue + this.hueSpeed) % 360;
  };

  this.updateBoxes = () => {
    for (let i = 0; i < this.boxes.length; i++) {
      if (i + 1 === this.boxes.length) {
        this.boxes[i].style.top = (this.mouse.y - this.size / 2) + 'px';
        this.boxes[i].style.left = (this.mouse.x - this.size / 2) + 'px';
        this.boxes[i].style.backgroundColor = `hsl(${this.hue}, 90%, 50%)`;
      } else {
        this.boxes[i].style.top = this.boxes[i + 1].style.top;
        this.boxes[i].style.left = this.boxes[i + 1].style.left;
        this.boxes[i].style.backgroundColor = this.boxes[i + 1].style.backgroundColor;
      }
    }
  };

  this.start = () => {
    if (this.running) return;
    this.running = true;
    this.intervalId = setInterval(() => {
      this.updateHue();
      this.updateBoxes();
    }, this.interval);
  };

  this.stop = () => {
    this.running = false;
    clearInterval(this.intervalId);
    this.intervalId = null;
  };

  this.destroy = () => {
        this.stop();
        this.boxes = [];
        if (this.wrapper) {
            this.wrapper.remove();
            this.wrapper = null;
        }
        // this.container.removeEventListener('mousemove', this.handleMouseMove);
    };

};
