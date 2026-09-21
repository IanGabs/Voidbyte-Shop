(function () {
    const canvas = document.getElementById('hero-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const COLORS = ['124, 58, 237', '6, 182, 212', '168, 85, 247'];
    const LINK_DIST = 130;

    let width = 0, height = 0, dpr = 1;
    let particles = [];
    let bytes = [];
    let running = true;
    let rafId = null;
    const mouse = { x: null, y: null };

    function resize() {
        const rect = canvas.getBoundingClientRect();
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        width = rect.width;
        height = rect.height;
        canvas.width = width * dpr;
        canvas.height = height * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        build();
    }

    function build() {
        const count = Math.min(90, Math.floor((width * height) / 14000));
        particles = [];
        for (let i = 0; i < count; i++) {
            particles.push({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.35,
                vy: (Math.random() - 0.5) * 0.35,
                r: Math.random() * 1.8 + 0.6,
                color: COLORS[Math.floor(Math.random() * COLORS.length)],
                pulse: Math.random() * Math.PI * 2
            });
        }

        const byteCount = Math.min(14, Math.floor(width / 90));
        bytes = [];
        for (let i = 0; i < byteCount; i++) {
            bytes.push(makeByte(true));
        }
    }

    function makeByte(initial) {
        return {
            x: Math.random() * width,
            y: initial ? Math.random() * height : -20,
            speed: Math.random() * 0.5 + 0.25,
            text: Math.random() > 0.5 ? '1' : '0',
            alpha: Math.random() * 0.25 + 0.06,
            size: Math.random() * 6 + 9
        };
    }

    function draw() {
        ctx.clearRect(0, 0, width, height);

        // bytes caindo devagar ao fundo
        ctx.font = '600 12px Rajdhani, monospace';
        for (const b of bytes) {
            ctx.fillStyle = `rgba(6, 182, 212, ${b.alpha})`;
            ctx.font = `600 ${b.size}px Rajdhani, monospace`;
            ctx.fillText(b.text, b.x, b.y);
            b.y += b.speed;
            if (b.y > height + 20) Object.assign(b, makeByte(false));
        }

        // linhas entre partículas próximas
        for (let i = 0; i < particles.length; i++) {
            const p = particles[i];
            for (let j = i + 1; j < particles.length; j++) {
                const q = particles[j];
                const dx = p.x - q.x, dy = p.y - q.y;
                const dist = Math.hypot(dx, dy);
                if (dist < LINK_DIST) {
                    const a = (1 - dist / LINK_DIST) * 0.28;
                    ctx.strokeStyle = `rgba(${p.color}, ${a})`;
                    ctx.lineWidth = 0.7;
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                    ctx.lineTo(q.x, q.y);
                    ctx.stroke();
                }
            }
        }

        // partículas
        for (const p of particles) {
            p.pulse += 0.02;
            const glow = 0.55 + Math.sin(p.pulse) * 0.35;

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(${p.color}, ${glow})`;
            ctx.shadowBlur = 10;
            ctx.shadowColor = `rgba(${p.color}, 0.8)`;
            ctx.fill();
            ctx.shadowBlur = 0;

            p.x += p.vx;
            p.y += p.vy;

            // leve atração pelo cursor
            if (mouse.x !== null) {
                const dx = mouse.x - p.x, dy = mouse.y - p.y;
                const d = Math.hypot(dx, dy);
                if (d < 160 && d > 1) {
                    p.x += (dx / d) * 0.25;
                    p.y += (dy / d) * 0.25;
                }
            }

            if (p.x < -20) p.x = width + 20;
            if (p.x > width + 20) p.x = -20;
            if (p.y < -20) p.y = height + 20;
            if (p.y > height + 20) p.y = -20;
        }
    }

    function loop() {
        if (running) draw();
        rafId = requestAnimationFrame(loop);
    }

    // pausa quando o hero sai da tela (economiza CPU)
    if ('IntersectionObserver' in window) {
        new IntersectionObserver((entries) => {
            running = entries[0].isIntersecting;
        }, { threshold: 0 }).observe(canvas);
    }

    canvas.parentElement.addEventListener('mousemove', (e) => {
        const rect = canvas.getBoundingClientRect();
        mouse.x = e.clientX - rect.left;
        mouse.y = e.clientY - rect.top;
    });
    canvas.parentElement.addEventListener('mouseleave', () => {
        mouse.x = mouse.y = null;
    });

    window.addEventListener('resize', () => {
        clearTimeout(window.__heroResize);
        window.__heroResize = setTimeout(resize, 150);
    });

    resize();
    if (reduceMotion) {
        draw();
    } else {
        loop();
    }
})();
