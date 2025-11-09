function eaelColorBalls(options = {}) {
	const target = typeof options.container === "string"
		? document.querySelector(options.container)
		: options.container;

	if (!target) return console.error("Target element not found.");

    const hexColors = options.colors || [
        "#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#f1c40f", "#e67e22", "#e74c3c"
    ];

	// Create and append canvas
	const canvas = document.createElement("canvas");
	canvas.id = options.id || "color-balls-canvas";
    canvas.className = options.id || "color-balls-canvas";
    canvas.style.height = '100%';    
    canvas.style.width = '100%';
    canvas.style.position = 'absolute';
    canvas.style.top = 0;
    canvas.style.left = 0;
    canvas.style.zIndex = options.zIndex || 1000;
    canvas.style.pointerEvents = 'none';
	target.appendChild(canvas);
	const ctx = canvas.getContext("2d");

	let w, h, balls = [];
	let mouse = { x: undefined, y: undefined };

	function resizeReset() {
		w = canvas.width = target.clientWidth;
		h = canvas.height = target.clientHeight;
	}

	let animationLoopId;
    function animationLoop() {
        ctx.clearRect(0, 0, w, h);
        if (mouse.x !== undefined && mouse.y !== undefined) {
            balls.push(new Ball());
        }
        if (balls.length > 200) balls = balls.slice(1);
        drawBalls();
        animationLoopId = requestAnimationFrame(animationLoop);
    }

	function drawBalls() {
		for (let i = 0; i < balls.length; i++) {
			balls[i].update();
			balls[i].draw();
		}
	}

	function mousemove(e) {
		const rect = canvas.getBoundingClientRect();
		mouse.x = e.clientX - rect.left;
		mouse.y = e.clientY - rect.top;
	}

	function mouseout() {
		mouse.x = undefined;
		mouse.y = undefined;
	}

	function getRandomInt(min, max) {
		return Math.round(Math.random() * (max - min)) + min;
	}

    function destroy() {
        window.removeEventListener("resize", resizeReset);
        target.removeEventListener("mousemove", mousemove);
        target.removeEventListener("mouseout", mouseout);
        if (canvas && canvas.parentNode) {
            canvas.parentNode.removeChild(canvas);
        }
        cancelAnimationFrame(animationLoopId);
    }

	class Ball {
		constructor() {
			this.x = mouse.x + getRandomInt(-20, 20);
			this.y = mouse.y + getRandomInt(-20, 20);
			this.size = getRandomInt(10, 20);
			this.hex = hexColors[getRandomInt(0, hexColors.length - 1)];
		}
		draw() {
			ctx.fillStyle = this.hex + "80"; // add transparency (50% alpha)
			ctx.beginPath();
			ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
			ctx.closePath();
			ctx.fill();
		}
		update() {
			this.size = Math.max(0, this.size - 0.3);
		}
	}

	resizeReset();
	animationLoop();

	target.addEventListener("mousemove", mousemove);
	target.addEventListener("mouseout", mouseout);
	window.addEventListener("resize", resizeReset);

    return {
        destroy: destroy,
        start: animationLoop,
        stop: () => cancelAnimationFrame(animationLoopId)
    };
}