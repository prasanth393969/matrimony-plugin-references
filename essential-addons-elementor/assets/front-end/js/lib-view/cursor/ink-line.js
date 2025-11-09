(function (global) {
  function eaelInkLine(options = {}) {
    if( !options.container ) return; // Return if no container is provided

    const {
      color = "#000000",
      pointsNumber = 40,
      widthFactor = 0.3,
      spring = 0.4,
      friction = 0.5
    } = options;
    
    let container = typeof options.container === "string" ? document.querySelector(options.container) : options.container;

    if( !container ) return; // Return if no container is provided

    let canvas, ctx, rafId;
    let mouseMoved = false;
    let pointer = { x: 0, y: 0 };
    let trail = [];

    const events = {
      mousemove: e => {
        mouseMoved = true;
        updateMousePosition(e.pageX, e.pageY);
      },
      touchmove: e => {
        mouseMoved = true;
        updateMousePosition(e.touches[0].pageX, e.touches[0].pageY);
      },
      click: e => updateMousePosition(e.pageX, e.pageY),
      resize: setupCanvas
    };

    function getOffset(el) {
      const rect = el.getBoundingClientRect();
      return {
        left: rect.left + window.scrollX,
        top: rect.top + window.scrollY
      };
    }

    function updateMousePosition(x, y) {
      const offset = getOffset(container);
      pointer.x = x - offset.left;
      pointer.y = y - offset.top;
    }

    function setupCanvas() {
      canvas.width = container.clientWidth;
      canvas.height = container.clientHeight;
    }

    function initTrail() {
      pointer.x = container.clientWidth / 2;
      pointer.y = container.clientHeight / 2;
      trail = new Array(pointsNumber).fill().map(() => ({
        x: pointer.x,
        y: pointer.y,
        dx: 0,
        dy: 0
      }));
    }

    function draw(t) {
      if (!mouseMoved) return (rafId = requestAnimationFrame(draw));

      ctx.clearRect(0, 0, canvas.width, canvas.height);

      for (let i = 0; i < trail.length; i++) {
        if (i === 0) {
          trail[i].x = pointer.x;
          trail[i].y = pointer.y;
        } else {
          const prev = trail[i - 1];
          trail[i].dx += (prev.x - trail[i].x) * spring;
          trail[i].dy += (prev.y - trail[i].y) * spring;
          trail[i].dx *= friction;
          trail[i].dy *= friction;
          trail[i].x += trail[i].dx;
          trail[i].y += trail[i].dy;
        }
      }

      ctx.lineCap = "round";
      ctx.strokeStyle = color;
      ctx.beginPath();
      ctx.moveTo(trail[0].x, trail[0].y);

      for (let i = 1; i < trail.length - 1; i++) {
        const xc = 0.5 * (trail[i].x + trail[i + 1].x);
        const yc = 0.5 * (trail[i].y + trail[i + 1].y);
        ctx.quadraticCurveTo(trail[i].x, trail[i].y, xc, yc);
        ctx.lineWidth = widthFactor * (pointsNumber - i);
        ctx.stroke();
      }

      ctx.lineTo(trail[trail.length - 1].x, trail[trail.length - 1].y);
      ctx.stroke();

      rafId = requestAnimationFrame(draw);
    }

    function attachEvents() {
      container.addEventListener("mousemove", events.mousemove);
      container.addEventListener("touchmove", events.touchmove);
      container.addEventListener("click", events.click);
      container.addEventListener("resize", events.resize);
    }

    function detachEvents() {
      container.removeEventListener("mousemove", events.mousemove);
      container.removeEventListener("touchmove", events.touchmove);
      container.removeEventListener("click", events.click);
      container.removeEventListener("resize", events.resize);
    }

    function start() {
      if (!canvas) {
        canvas = document.createElement("canvas");
        canvas.style.position = "absolute";
        canvas.style.top = 0;
        canvas.style.left = 0;
        canvas.style.pointerEvents = "none";
        canvas.style.zIndex = 9999;
        canvas.style.width = "100%";
        canvas.style.height = "100%";
        container.appendChild(canvas);
        ctx = canvas.getContext("2d");
      }

      setupCanvas();
      initTrail();
      attachEvents();
      draw(0);
    }

    function destroy() {
      if (rafId) cancelAnimationFrame(rafId);
      detachEvents();
      if (canvas && canvas.parentNode) {
        canvas.parentNode.removeChild(canvas);
        canvas = null;
      }
    }

    // Auto-start if container is in DOM
    if (container && container instanceof HTMLElement) {
      start();
    }

    return { start, destroy };
  }

  global.eaelInkLine = eaelInkLine;
})(window);
