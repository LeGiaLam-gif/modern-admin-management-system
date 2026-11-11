(function () {
    "use strict";

    const config = {
        density: { dust: 190, stars: 95, sparks: 35 },
        reducedMotion: window.matchMedia("(prefers-reduced-motion: reduce)").matches,
        parallaxDepth: [14, 34, 68],
        influenceRadius: 175,
        auraRadius: 270,
        constellationRadius: 155,
        constellationMaxLines: 18
    };

    const nebulaCanvas = document.getElementById("loginNebulaCanvas");
    const cosmosCanvas = document.getElementById("loginCosmosCanvas");
    if (!nebulaCanvas || !cosmosCanvas) return;

    const nebulaContext = nebulaCanvas.getContext("2d");
    const cosmosContext = cosmosCanvas.getContext("2d");
    if (!nebulaContext || !cosmosContext) return;

    const pointer = {
        x: window.innerWidth / 2,
        y: window.innerHeight / 2,
        targetX: window.innerWidth / 2,
        targetY: window.innerHeight / 2,
        normX: 0,
        normY: 0,
        energy: 0,
        targetEnergy: 0,
        idleTimer: 0
    };
    const clouds = [
        { x: .14, y: .18, rx: 460, ry: 340, inner: "rgba(105,48,155,.24)", mid: "rgba(50,22,75,.12)", speed: .00045, phase: .2, parallax: .45, morph: .18 },
        { x: .24, y: .28, rx: 350, ry: 240, inner: "rgba(166,107,209,.12)", mid: "rgba(105,48,155,.06)", speed: .00062, phase: 1.7, parallax: .75, morph: .22 },
        { x: .86, y: .78, rx: 480, ry: 360, inner: "rgba(105,48,155,.19)", mid: "rgba(50,22,75,.10)", speed: .00038, phase: 3.1, parallax: .5, morph: .16 },
        { x: .16, y: .76, rx: 390, ry: 280, inner: "rgba(50,22,75,.22)", mid: "rgba(22,13,37,.10)", speed: .00051, phase: 4.5, parallax: .35, morph: .2 },
        { x: .82, y: .22, rx: 370, ry: 270, inner: "rgba(166,107,209,.09)", mid: "rgba(50,22,75,.06)", speed: .00055, phase: 5.4, parallax: .65, morph: .19 }
    ];
    const particles = [];
    let width = window.innerWidth;
    let height = window.innerHeight;
    let pixelRatio = 1;

    function resize() {
        width = window.innerWidth;
        height = window.innerHeight;
        pixelRatio = Math.min(window.devicePixelRatio || 1, 2);

        cosmosCanvas.width = Math.floor(width * pixelRatio);
        cosmosCanvas.height = Math.floor(height * pixelRatio);
        cosmosContext.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);

        nebulaCanvas.width = Math.max(1, Math.floor(width * .5));
        nebulaCanvas.height = Math.max(1, Math.floor(height * .5));
    }

    function createParticle(type) {
        const depthRoll = Math.random();
        const depth = depthRoll > .72 ? 2 : depthRoll > .35 ? 1 : 0;
        const depthScale = depth === 0 ? .65 : depth === 1 ? 1 : 1.38;
        let radius;
        let alpha;
        let color;
        let pulseSpeed;
        let speedMultiplier;

        if (type === "dust") {
            radius = (.45 + Math.random() * .75) * depthScale;
            alpha = .16 + Math.random() * .28;
            color = Math.random() > .35 ? "#A66BD1" : "#D9B978";
            pulseSpeed = .0008 + Math.random() * .0015;
            speedMultiplier = .12;
        } else if (type === "star") {
            radius = (.85 + Math.random() * 1.35) * depthScale;
            alpha = .32 + Math.random() * .48;
            color = ["#F6F2FF", "#A66BD1", "#E2D4F0"][Math.floor(Math.random() * 3)];
            pulseSpeed = .0014 + Math.random() * .0028;
            speedMultiplier = .18;
        } else {
            radius = (1.35 + Math.random() * 1.65) * depthScale;
            alpha = .48 + Math.random() * .42;
            color = Math.random() > .42 ? "#D9B978" : "#A66BD1";
            pulseSpeed = .0018 + Math.random() * .0032;
            speedMultiplier = .24;
        }

        const angle = Math.random() * Math.PI * 2;
        const velocity = (.08 + Math.random() * .22) * speedMultiplier * depthScale;
        return {
            type, depth, x: Math.random() * width, y: Math.random() * height,
            vx: Math.cos(angle) * velocity, vy: Math.sin(angle) * velocity - .03,
            radius, alpha, color, pulseSpeed, phase: Math.random() * Math.PI * 2,
            pushX: 0, pushY: 0
        };
    }

    for (let i = 0; i < config.density.dust; i++) particles.push(createParticle("dust"));
    for (let i = 0; i < config.density.stars; i++) particles.push(createParticle("star"));
    for (let i = 0; i < config.density.sparks; i++) particles.push(createParticle("spark"));

    function renderNebula(time) {
        const ctx = nebulaContext;
        const scale = .5;
        const canvasWidth = nebulaCanvas.width;
        const canvasHeight = nebulaCanvas.height;
        ctx.clearRect(0, 0, canvasWidth, canvasHeight);

        for (const cloud of clouds) {
            const t = config.reducedMotion ? cloud.phase : time * cloud.speed + cloud.phase;
            const driftX = Math.sin(t) * 45 + Math.cos(t * .7) * 25;
            const driftY = Math.cos(t * .85) * 35 + Math.sin(t * 1.2) * 20;
            const offsetX = -pointer.normX * 55 * cloud.parallax;
            const offsetY = -pointer.normY * 55 * cloud.parallax;
            const cx = (cloud.x * width + driftX + offsetX) * scale;
            const cy = (cloud.y * height + driftY + offsetY) * scale;
            const rx = cloud.rx * scale * (1 + Math.sin(t * 1.3) * cloud.morph);
            const ry = cloud.ry * scale * (1 + Math.cos(t * 1.1) * cloud.morph);

            ctx.save();
            ctx.translate(cx, cy);
            ctx.rotate(Math.sin(t * .4) * .45);
            ctx.scale(1, ry / Math.max(1, rx));
            const gradient = ctx.createRadialGradient(0, 0, 0, 0, 0, rx);
            gradient.addColorStop(0, cloud.inner);
            gradient.addColorStop(.52, cloud.mid);
            gradient.addColorStop(1, "rgba(8,6,17,0)");
            ctx.fillStyle = gradient;
            ctx.beginPath();
            ctx.arc(0, 0, rx, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        }
    }

    function renderParticles(time) {
        const ctx = cosmosContext;
        const energy = pointer.energy;
        ctx.clearRect(0, 0, width, height);

        if (energy > .005) {
            const gradient = ctx.createRadialGradient(pointer.x, pointer.y, 0, pointer.x, pointer.y, config.auraRadius);
            gradient.addColorStop(0, `rgba(166,107,209,${(.15 * energy).toFixed(3)})`);
            gradient.addColorStop(.38, `rgba(105,48,155,${(.08 * energy).toFixed(3)})`);
            gradient.addColorStop(.72, `rgba(217,185,120,${(.025 * energy).toFixed(3)})`);
            gradient.addColorStop(1, "rgba(8,6,17,0)");
            ctx.fillStyle = gradient;
            ctx.beginPath();
            ctx.arc(pointer.x, pointer.y, config.auraRadius, 0, Math.PI * 2);
            ctx.fill();
        }

        const candidates = [];
        for (const particle of particles) {
            if (!config.reducedMotion) {
                particle.x += particle.vx;
                particle.y += particle.vy;
                if (particle.x < -30) particle.x = width + 30;
                if (particle.x > width + 30) particle.x = -30;
                if (particle.y < -30) particle.y = height + 30;
                if (particle.y > height + 30) particle.y = -30;
            }

            const depth = config.parallaxDepth[particle.depth];
            const baseX = particle.x - pointer.normX * depth;
            const baseY = particle.y - pointer.normY * depth;
            const dx = baseX - pointer.x;
            const dy = baseY - pointer.y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            let boost = 0;

            if (distance < config.influenceRadius && energy > .02) {
                boost = (1 - distance / config.influenceRadius) * energy;
                if (particle.depth > 0 && distance > 1) {
                    const angle = Math.atan2(dy, dx) + .35;
                    const force = particle.depth === 2 ? 14 : 7;
                    particle.pushX += (Math.cos(angle) * boost * force - particle.pushX) * .08;
                    particle.pushY += (Math.sin(angle) * boost * force - particle.pushY) * .08;
                }
            } else {
                particle.pushX *= .94;
                particle.pushY *= .94;
            }

            const x = baseX + particle.pushX;
            const y = baseY + particle.pushY;
            if (particle.type !== "dust" && distance < config.constellationRadius && energy > .05) {
                candidates.push({ x, y, distance, color: particle.color });
            }

            const pulse = config.reducedMotion ? .85 : .68 + .32 * Math.sin(time * particle.pulseSpeed + particle.phase);
            const alpha = Math.min(1, particle.alpha * pulse + boost * .52);
            const radius = particle.radius * (1 + boost * .45);

            if (particle.type === "spark" || (particle.type === "star" && (particle.depth === 2 || boost > .2))) {
                const glowRadius = radius * (particle.type === "spark" ? 4.2 : 3);
                const glow = ctx.createRadialGradient(x, y, 0, x, y, glowRadius);
                glow.addColorStop(0, particle.color === "#D9B978"
                    ? `rgba(217,185,120,${(alpha * .55).toFixed(3)})`
                    : `rgba(166,107,209,${(alpha * .55).toFixed(3)})`);
                glow.addColorStop(1, "rgba(8,6,17,0)");
                ctx.fillStyle = glow;
                ctx.beginPath();
                ctx.arc(x, y, glowRadius, 0, Math.PI * 2);
                ctx.fill();
            }

            ctx.globalAlpha = alpha;
            ctx.fillStyle = particle.color;
            ctx.beginPath();
            ctx.arc(x, y, radius, 0, Math.PI * 2);
            ctx.fill();
        }
        ctx.globalAlpha = 1;

        let lineCount = 0;
        ctx.lineWidth = .65;
        for (let i = 0; i < candidates.length && lineCount < config.constellationMaxLines; i++) {
            for (let j = i + 1; j < candidates.length && lineCount < config.constellationMaxLines; j++) {
                const a = candidates[i];
                const b = candidates[j];
                const dx = a.x - b.x;
                const dy = a.y - b.y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                if (distance >= 110) continue;

                const opacity = (1 - Math.max(a.distance, b.distance) / config.constellationRadius)
                    * (1 - distance / 110) * energy * .34;
                if (opacity <= .015) continue;
                ctx.strokeStyle = a.color === "#D9B978" || b.color === "#D9B978"
                    ? `rgba(217,185,120,${opacity.toFixed(3)})`
                    : `rgba(166,107,209,${opacity.toFixed(3)})`;
                ctx.beginPath();
                ctx.moveTo(a.x, a.y);
                ctx.lineTo(b.x, b.y);
                ctx.stroke();
                lineCount++;
            }
        }
    }

    window.addEventListener("pointermove", function (event) {
        pointer.targetX = event.clientX;
        pointer.targetY = event.clientY;
        pointer.targetEnergy = 1;
        window.clearTimeout(pointer.idleTimer);
        pointer.idleTimer = window.setTimeout(function () {
            pointer.targetEnergy = 0;
        }, 1200);
    }, { passive: true });

    window.addEventListener("pointerleave", function () {
        pointer.targetEnergy = 0;
    });

    window.addEventListener("resize", resize, { passive: true });
    resize();

    function animate(time) {
        const ease = .065;
        pointer.x += (pointer.targetX - pointer.x) * ease;
        pointer.y += (pointer.targetY - pointer.y) * ease;
        pointer.normX += (pointer.targetX / Math.max(1, width) - .5 - pointer.normX) * (ease * .85);
        pointer.normY += (pointer.targetY / Math.max(1, height) - .5 - pointer.normY) * (ease * .85);
        const energyEase = pointer.targetEnergy > pointer.energy ? .06 : .018;
        pointer.energy += (pointer.targetEnergy - pointer.energy) * energyEase;

        renderNebula(time);
        renderParticles(time);
        window.requestAnimationFrame(animate);
    }

    window.requestAnimationFrame(animate);
}());
