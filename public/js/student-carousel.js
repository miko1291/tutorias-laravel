(() => {
    document.querySelectorAll('[data-student-carousel]').forEach(carousel => {
        const slides = [...carousel.querySelectorAll('[data-carousel-slide]')];
        const dots = [...carousel.querySelectorAll('[data-carousel-dot]')];
        const play = carousel.querySelector('[data-carousel-play]');
        const stage = carousel.querySelector('.student-carousel-slides');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let current = 0;
        let playing = !reducedMotion.matches;
        let timer;


        const schedule = () => {
            window.clearInterval(timer);
            if (playing && !document.hidden) {
                timer = window.setInterval(() => show(current + 1), 6500);
            }
        };
        const show = index => {
            current = (index + slides.length) % slides.length;
            slides.forEach((slide, i) => { slide.classList.toggle('is-active', i === current); slide.setAttribute('aria-hidden', String(i !== current)); slide.inert = i !== current; });
            dots.forEach((dot, i) => {
                if (i === current) dot.setAttribute('aria-current', 'true');
                else dot.removeAttribute('aria-current');
            });
        };
        const updatePlayback = () => {
            play.setAttribute('aria-pressed', String(playing));
            const label = playing ? 'Pausar presentación' : 'Reanudar presentación';
            play.setAttribute('aria-label', label);
            play.setAttribute('title', label);
            play.querySelector('i').className = `bi bi-${playing ? 'pause' : 'arrow-clockwise'}`;
            stage.setAttribute('aria-live', playing ? 'off' : 'polite');
            schedule();
        };
        const navigate = index => { playing = false; show(index); updatePlayback(); };
        dots.forEach((dot, i) => dot.addEventListener('click', () => navigate(i)));
        play.addEventListener('click', () => { playing = !playing; updatePlayback(); });
        carousel.addEventListener('keydown', event => {
            if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
            event.preventDefault();
            navigate(current + (event.key === 'ArrowRight' ? 1 : -1));
        });
        carousel.addEventListener('focusin', () => { playing = false; updatePlayback(); });
        document.addEventListener('visibilitychange', schedule);
        reducedMotion.addEventListener('change', () => { if (reducedMotion.matches) { playing = false; updatePlayback(); } });
        carousel.querySelector('[data-carousel-controls]').hidden = false;
        carousel.classList.add('is-enhanced');
        slides.forEach(slide => { slide.hidden = false; });
        show(0);
        updatePlayback();
    });
})();
