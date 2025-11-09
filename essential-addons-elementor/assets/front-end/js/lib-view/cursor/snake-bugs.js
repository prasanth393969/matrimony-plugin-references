function eaelSnakeBugs(options) {
  const {
    container,
    chainCount = 30,
    entityCount = 8,
    activeColor = '',
    idleColor = '',
    opacity = 1,
    disappearAfter = 0
  } = options;

  const canvas = document.createElement('canvas');
  canvas.style.position = 'absolute';
  canvas.style.top = '0';
  canvas.style.left = '0';
  canvas.style.zIndex = '999999';
  canvas.style.pointerEvents = 'none';
  canvas.style.opacity = opacity;
  canvas.style.transition = 'opacity 0.5s ease';

  container.style.position = 'relative';
  container.appendChild(canvas);
  const ctx = canvas.getContext('2d');

  let w, h, cx, cy, mx, my, md = 0, tick = 0;
  let chains = [];
  let mouseMoveTimer;
  let visible = true;
  let hideTimeout;

  function rand(min, max) {
    return Math.random() * (max - min) + min;
  }

  function resize() {
    w = container.clientWidth;
    h = container.clientHeight;
    canvas.width = w * devicePixelRatio;
    canvas.height = h * devicePixelRatio;
    canvas.style.width = `${w}px`;
    canvas.style.height = `${h}px`;
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.scale(devicePixelRatio, devicePixelRatio);
    cx = w / 2;
    cy = h / 2;
  }

  function fadeOutCanvas() {
    canvas.style.opacity = 0;
    hideTimeout = setTimeout(() => {
      if (canvas.parentNode) container.removeChild(canvas);
      visible = false;
    }, 500);
  }

  function fadeInCanvas() {
    clearTimeout(hideTimeout);
    if (!canvas.parentNode) container.appendChild(canvas);
    canvas.style.display = 'block';
    requestAnimationFrame(() => {
      canvas.style.opacity = opacity;
    });
    visible = true;
  }

  container.addEventListener('mouseleave', () => {
    if (disappearAfter > 0 && visible) fadeOutCanvas();
  });

  container.addEventListener('mousemove', function(e) {
    if (disappearAfter > 0 && !visible) fadeInCanvas();
    const rect = canvas.getBoundingClientRect();
    mx = e.clientX - rect.left;
    my = e.clientY - rect.top;
    md = 1;
    clearTimeout(mouseMoveTimer);
    mouseMoveTimer = setTimeout(() => md = 0, 150);

    if (disappearAfter > 0) {
      clearTimeout(hideTimeout);
      hideTimeout = setTimeout(fadeOutCanvas, disappearAfter);
    }
  });

  function Impulse() {
    this.x = cx;
    this.y = cy;
    this.ax = 0;
    this.ay = 0;
    this.vx = 0;
    this.vy = 0;
    this.r = 1;
  }

  Impulse.prototype.step = function () {
    this.x += this.vx;
    this.y += this.vy;
    if (this.x + this.r >= w || this.x <= this.r) { this.vx = 0; this.ax = 0; }
    if (this.y + this.r >= h || this.y <= this.r) { this.vy = 0; this.ay = 0; }
    if (this.x + this.r >= w) { this.x = w - this.r; }
    if (this.x <= this.r) { this.x = this.r; }
    if (this.y + this.r >= h) { this.y = h - this.r; }
    if (this.y <= this.r) { this.y = this.r; }

    if (md) {
      let dx = this.x - mx;
      let dy = this.y - my;
      let dist = rand(50, 150);
      let angle = Math.atan2(dy, dx) - Math.PI / 2;
      let frac = 0.02;
      this.vx -= Math.cos(angle) * dist * frac;
      this.vy -= Math.sin(angle) * dist * frac;

      let dx2 = this.x - mx;
      let dy2 = this.y - my;
      let dist2 = Math.sqrt(dx2 * dx2 + dy2 * dy2);
      let angle2 = Math.atan2(dy2, dx2);
      let frac2 = 0.01;
      this.vx -= Math.cos(angle2) * dist2 * frac2;
      this.vy -= Math.sin(angle2) * dist2 * frac2;
    }

    let angle = rand(0, 1) * Math.PI;
    let magnitude = rand(-0.4, 0.4);
    this.ax += Math.cos(angle) * magnitude;
    this.ay += Math.sin(angle) * magnitude;

    this.vx += this.ax;
    this.vy += this.ay;
    this.ax *= Math.abs(this.ax) > 2 ? 0.75 : 1;
    this.ay *= Math.abs(this.ay) > 2 ? 0.75 : 1;
    this.vx *= Math.abs(this.vx) > 1 ? 0.75 : 1;
    this.vy *= Math.abs(this.vy) > 1 ? 0.75 : 1;
  };

  function Entity(opt) {
    this.branch = opt.branch;
    this.i = opt.i;
    this.x = opt.x;
    this.y = opt.y;
    this.vx = 0;
    this.vy = 0;
    this.radius = opt.radius;
    this.attractor = opt.attractor;
    this.damp = opt.damp;
  }

  Entity.prototype.step = function () {
    this.vx = (this.attractor.x - this.x) * this.damp;
    this.vy = (this.attractor.y - this.y) * this.damp;
    this.x += this.vx;
    this.y += this.vy;
    this.av = (Math.abs(this.vx) + Math.abs(this.vy)) / 2;
    let dx = this.attractor.x - this.x,
        dy = this.attractor.y - this.y;
    this.rot = Math.atan2(dy, dx);
  };

  function Branch(opt) {
    this.entities = [];
    this.chain = opt.chain;
    this.avoiding = 0;
    for (let i = 0; i < entityCount; i++) {
      let entity = new Entity({
        branch: this,
        i: i,
        x: cx,
        y: cy,
        radius: 1 + (entityCount - i) / entityCount * 5,
        damp: 0.2,
        attractor: i === 0 ? opt.attractor : this.entities[i - 1]
      });
      this.entities.push(entity);
    }
  }

  Branch.prototype.step = function () {
    this.entities.forEach(entity => entity.step());
  };

  Branch.prototype.draw = function (ctx) {
    ctx.beginPath();
    ctx.moveTo(this.entities[0].x, this.entities[0].y);
    this.entities.forEach((entity, i) => {
      if (i > 0) ctx.lineTo(entity.x, entity.y);
    });
    ctx.strokeStyle = md ? (activeColor || `hsla(120, 70%, 60%, ${opacity * 0.3})`) : (idleColor || `hsla(200, 70%, 60%, ${opacity * 0.3})`);
    ctx.stroke();

    this.entities.forEach(entity => {
      ctx.save();
      ctx.translate(entity.x, entity.y);
      ctx.beginPath();
      ctx.rotate(entity.rot);
      ctx.fillStyle = md ? (activeColor || `hsla(120, 70%, 60%, ${opacity * 0.5})`) : (idleColor || `hsla(200, 70%, 60%, ${opacity * 0.5})`);
      ctx.fillRect(-entity.radius, -entity.radius, entity.radius * 2, entity.radius * 2);
      ctx.restore();
    });
  };

  function Chain() {
    this.branches = [];
    this.impulse = new Impulse();
    this.branches.push(new Branch({ chain: this, attractor: this.impulse }));
  }

  Chain.prototype.step = function () {
    this.impulse.step();
    this.branches.forEach(branch => {
      branch.step();
      branch.draw(ctx);
    });
  };

  function loop() {
    requestAnimationFrame(loop);
    ctx.globalCompositeOperation = 'destination-out';
    ctx.fillStyle = 'rgba(0, 0, 0, 1)';
    ctx.fillRect(0, 0, w, h);
    ctx.globalCompositeOperation = 'lighter';
    if (visible) chains.forEach(chain => chain.step());
    tick++;
  }

  resize();
  window.addEventListener('resize', resize);

  for (let i = 0; i < chainCount; i++) {
    chains.push(new Chain());
  }

  loop();

  if (disappearAfter > 0) {
    hideTimeout = setTimeout(fadeOutCanvas, disappearAfter);
  }
}

// Example usage:
// eaelSnakeBugs({ container: document.getElementById('snake-box1'), chainCount: 40, activeColor: '#ff00ff', idleColor: '#00ffff', opacity: 0.6, disappearAfter: 2000 });
