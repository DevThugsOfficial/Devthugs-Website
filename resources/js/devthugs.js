function qs(root, sel) {
    return root ? root.querySelector(sel) : document.querySelector(sel);
}

function qsa(root, sel) {
    return Array.from((root || document).querySelectorAll(sel));
}

function scrollToSelector(sel) {
    const el = document.querySelector(sel);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
}

function initNavbar() {
    const nav = qs(null, '[data-navbar]');
    if (!nav) return;

    const toggle = qs(nav, '[data-mobile-menu-toggle]');
    const menu = qs(nav, '[data-mobile-menu]');
    const iconMenu = qs(nav, '[data-icon-menu]');
    const iconClose = qs(nav, '[data-icon-close]');

    const onScroll = () => {
        if (window.scrollY > 50) {
            nav.classList.add('bg-dark-base/80', 'backdrop-blur-xl', 'border-b', 'border-white/10');
            nav.classList.remove('bg-transparent', 'backdrop-blur-sm');
        } else {
            nav.classList.remove('bg-dark-base/80', 'backdrop-blur-xl', 'border-b', 'border-white/10');
            nav.classList.add('bg-transparent', 'backdrop-blur-sm');
        }
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            menu.classList.toggle('hidden');
            const isOpen = !menu.classList.contains('hidden');
            toggle.setAttribute('aria-expanded', String(isOpen));
            if (iconMenu && iconClose) {
                iconMenu.classList.toggle('hidden', isOpen);
                iconClose.classList.toggle('hidden', !isOpen);
            }
        });

        qsa(menu, '[data-mobile-menu-link]').forEach((link) => {
            link.addEventListener('click', () => {
                menu.classList.add('hidden');
                toggle.setAttribute('aria-expanded', 'false');
                if (iconMenu && iconClose) {
                    iconMenu.classList.remove('hidden');
                    iconClose.classList.add('hidden');
                }
            });
        });
    }
}

function initScrollButtons() {
    qsa(null, '[data-scroll-to]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const sel = btn.getAttribute('data-scroll-to');
            if (sel) scrollToSelector(sel);
        });
    });
}

function getFeaturedPosition(index, current, length) {
    if (index === current) return 'center';
    if (index === (current - 1 + length) % length) return 'left';
    if (index === (current + 1) % length) return 'right';
    if (index === (current - 2 + length) % length) return 'leftHidden';
    if (index === (current + 2 + length) % length) return 'rightHidden';
    return 'hidden';
}

function applyFeaturedStyles(cards, current, isDesktop) {
    const length = cards.length;
    const mapDesktop = {
        center: { x: 0, s: 1, o: 1, z: 30, cursor: 'default' },
        left: { x: -55, s: 0.8, o: 0.4, z: 20, cursor: 'pointer' },
        right: { x: 55, s: 0.8, o: 0.4, z: 20, cursor: 'pointer' },
        leftHidden: { x: -100, s: 0.6, o: 0, z: 10, cursor: 'pointer' },
        rightHidden: { x: 100, s: 0.6, o: 0, z: 10, cursor: 'pointer' },
        hidden: { x: 0, s: 0.6, o: 0, z: 10, cursor: 'pointer' },
    };
    const mapMobile = {
        center: { x: 0, s: 1, o: 1, z: 30, cursor: 'default' },
        left: { x: -110, s: 0.85, o: 0, z: 20, cursor: 'pointer' },
        right: { x: 110, s: 0.85, o: 0, z: 20, cursor: 'pointer' },
        leftHidden: { x: -200, s: 0.8, o: 0, z: 10, cursor: 'pointer' },
        rightHidden: { x: 200, s: 0.8, o: 0, z: 10, cursor: 'pointer' },
        hidden: { x: 0, s: 0.8, o: 0, z: 10, cursor: 'pointer' },
    };
    const map = isDesktop ? mapDesktop : mapMobile;

    cards.forEach((card, index) => {
        const pos = getFeaturedPosition(index, current, length);
        const st = map[pos];
        const hex = card.getAttribute('data-hex') || '#00f0ff';
        const isCenter = pos === 'center';

        card.style.transform = `translateX(${st.x}%) scale(${st.s})`;
        card.style.opacity = String(st.o);
        card.style.zIndex = String(st.z);
        card.style.cursor = st.cursor;
        card.style.boxShadow = isCenter
            ? `0 20px 50px -10px rgba(0,0,0,0.5), 0 0 40px ${hex}25`
            : '0 10px 30px -10px rgba(0,0,0,0.5)';
        card.style.border = isCenter ? `1px solid ${hex}50` : '1px solid rgba(255,255,255,0.05)';
        card.style.backgroundColor = 'rgba(255,255,255,0.02)';
        card.style.backdropFilter = 'blur(10px)';

        const content = card.querySelector('.featured-card-content');
        if (content) {
            content.classList.toggle('opacity-100', isCenter);
            content.classList.toggle('translate-y-0', isCenter);
            content.classList.toggle('opacity-40', !isCenter);
            content.classList.toggle('translate-y-4', !isCenter);
        }
    });
}

function initFeaturedCarousel() {
    const root = qs(null, '[data-featured-carousel]');
    if (!root) return;

    const stage = qs(root, '[data-featured-stage]');
    const cards = qsa(root, '.featured-card');
    const dots = qsa(root, '[data-featured-dot]');
    const total = cards.length;
    if (!total) return;

    let current = 0;
    let hovered = false;

    const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

    const render = () => {
        applyFeaturedStyles(cards, current, isDesktop());
        dots.forEach((dot, i) => {
            const hex = cards[i]?.getAttribute('data-hex') || '#00f0ff';
            if (i === current) {
                dot.style.width = '2.5rem';
                dot.style.backgroundColor = hex;
                dot.style.boxShadow = `0 0 15px ${hex}80`;
            } else {
                dot.style.width = '';
                dot.style.backgroundColor = '';
                dot.style.boxShadow = 'none';
                dot.classList.add('bg-white/20');
            }
        });
    };

    const setIndex = (i) => {
        current = (i + total) % total;
        render();
    };

    qsa(root, '[data-featured-prev]').forEach((b) => b.addEventListener('click', () => setIndex(current - 1)));
    qsa(root, '[data-featured-next]').forEach((b) => b.addEventListener('click', () => setIndex(current + 1)));

    dots.forEach((dot) => {
        dot.addEventListener('click', () => setIndex(Number(dot.getAttribute('data-featured-dot'))));
    });

    cards.forEach((card, index) => {
        card.addEventListener('click', () => {
            if (index !== current) setIndex(index);
        });
    });

    const hoverHost = qs(root, '[data-featured-hover]');
    if (hoverHost) {
        hoverHost.addEventListener('mouseenter', () => {
            hovered = true;
        });
        hoverHost.addEventListener('mouseleave', () => {
            hovered = false;
        });
    }

    window.addEventListener(
        'resize',
        () => {
            render();
        },
        { passive: true },
    );

    setInterval(() => {
        if (!hovered) setIndex(current + 1);
    }, 5000);

    render();
}

function initWhyCarousel() {
    const root = qs(null, '[data-why-carousel]');
    if (!root) return;

    const slides = qsa(root, '[data-why-slide]');
    const dots = qsa(root, '[data-why-dot]');
    let idx = 0;

    const show = (i) => {
        idx = (i + slides.length) % slides.length;
        slides.forEach((el, j) => {
            const on = j === idx;
            el.classList.toggle('hidden', !on);
            el.classList.toggle('flex', on);
        });
        dots.forEach((d, j) => {
            d.classList.toggle('bg-white', j === idx);
            d.classList.toggle('w-8', j === idx);
            d.classList.toggle('bg-white/20', j !== idx);
            d.classList.toggle('w-2', j !== idx);
        });
    };

    qs(root, '[data-why-prev]')?.addEventListener('click', () => show(idx - 1));
    qs(root, '[data-why-next]')?.addEventListener('click', () => show(idx + 1));
    dots.forEach((d) => d.addEventListener('click', () => show(Number(d.getAttribute('data-why-dot')))));

    setInterval(() => show(idx + 1), 4000);
    show(0);
}

function initStats() {
    const stats = qsa(null, '[data-stat]');
    if (!stats.length) return;

    const animate = (el) => {
        const end = Number(el.getAttribute('data-end')) || 0;
        const suffix = el.getAttribute('data-suffix') || '';
        const display = qs(el, '[data-stat-display]');
        let start = 0;
        const duration = 2000;
        const step = end / (duration / 16);
        const t = setInterval(() => {
            start += step;
            if (start >= end) {
                if (display) display.textContent = String(end) + suffix;
                clearInterval(t);
            } else if (display) {
                display.textContent = String(Math.floor(start)) + suffix;
            }
        }, 16);
    };

    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((e) => {
                if (!e.isIntersecting) return;
                const el = e.target;
                if (el.dataset.animated) return;
                el.dataset.animated = '1';
                animate(el);
            });
        },
        { threshold: 0.3 },
    );

    stats.forEach((s) => io.observe(s));
}

function initShowcaseSlider() {
    const root = qs(null, '[data-showcase-slider]');
    if (!root) return;

    const slides = qsa(root, '.showcase-slide');
    const dots = qsa(root, '[data-showcase-dot]');
    let idx = 0;
    let timer;

    const show = (i) => {
        idx = (i + slides.length) % slides.length;
        slides.forEach((img, j) => {
            const on = j === idx;
            img.classList.toggle('opacity-100', on);
            img.classList.toggle('z-[1]', on);
            img.classList.toggle('opacity-0', !on);
            img.classList.toggle('z-0', !on);
        });
        dots.forEach((d, j) => {
            const on = j === idx;
            d.className = on
                ? 'rounded-full transition-all duration-300 w-6 h-1.5 bg-neon-cyan shadow-[0_0_8px_rgba(0,240,255,0.6)]'
                : 'rounded-full transition-all duration-300 w-1.5 h-1.5 bg-white/30 hover:bg-white/60';
        });
    };

    const resetTimer = () => {
        clearInterval(timer);
        timer = setInterval(() => show(idx + 1), 4000);
    };

    root.addEventListener('mouseenter', () => clearInterval(timer));
    root.addEventListener('mouseleave', resetTimer);

    dots.forEach((d) =>
        d.addEventListener('click', () => {
            show(Number(d.getAttribute('data-showcase-dot')));
            resetTimer();
        }),
    );

    resetTimer();
}

function initMilestoneCarousel() {
    const root = qs(null, '[data-milestone-carousel]');
    if (!root) return;

    const panels = qsa(root, '.milestone-panel');
    const total = panels.length;
    let idx = 0;

    const show = (i) => {
        idx = Math.max(0, Math.min(total - 1, i));
        panels.forEach((p, j) => p.classList.toggle('hidden', j !== idx));
        qsa(root, '[data-milestone-dot]').forEach((d, j) => {
            const on = j === idx;
            const inner = d.querySelector('div');
            if (inner) {
                inner.className = on
                    ? 'w-2.5 h-2.5 rounded-full transition-all duration-300 bg-neon-cyan scale-125 shadow-[0_0_10px_rgba(0,240,255,0.5)]'
                    : 'w-2.5 h-2.5 rounded-full transition-all duration-300 bg-white/20 hover:bg-white/40';
            }
        });
        const prev = qs(root, '[data-milestone-prev]');
        const next = qs(root, '[data-milestone-next]');
        if (prev) prev.disabled = idx === 0;
        if (next) next.disabled = idx === total - 1;
    };

    qs(root, '[data-milestone-prev]')?.addEventListener('click', () => show(idx - 1));
    qs(root, '[data-milestone-next]')?.addEventListener('click', () => show(idx + 1));
    qsa(root, '[data-milestone-dot]').forEach((d) =>
        d.addEventListener('click', () => show(Number(d.getAttribute('data-milestone-dot')))),
    );

    show(0);
}

function initWhyDevthugs() {
    const root = qs(null, '[data-why-devthugs]');
    if (!root) return;

    const rows = qsa(root, '[data-why-feature-row]');
    const imgs = qsa(root, '.why-feature-image');
    if (!rows.length) return;

    const setActive = (active) => {
        rows.forEach((row, i) => {
            const on = i === active;
            const b = row.getAttribute('data-border') || 'border-neon-cyan';
            row.className =
                'why-feature-row flex items-center gap-6 p-6 rounded-xl cursor-pointer transition-all duration-300 border-l-4 ' +
                (on ? `bg-white/10 ${b} shadow-lg` : 'border-transparent bg-transparent hover:bg-white/5');

            const iconWrap = row.children[0];
            if (iconWrap) {
                iconWrap.classList.toggle('scale-110', on);
                iconWrap.classList.toggle('shadow-[0_0_20px_rgba(255,255,255,0.1)]', on);
            }

            const title = row.querySelector('h3');
            const desc = row.querySelector('p');
            if (title) {
                title.classList.toggle('text-white', on);
                title.classList.toggle('text-white/70', !on);
            }
            if (desc) {
                desc.classList.toggle('text-white/80', on);
                desc.classList.toggle('text-white/40', !on);
            }
        });
        imgs.forEach((img, i) => {
            const on = i === active;
            img.classList.toggle('opacity-100', on);
            img.classList.toggle('z-[1]', on);
            img.classList.toggle('opacity-0', !on);
            img.classList.toggle('z-0', !on);
        });
    };

    rows.forEach((row, i) => {
        row.addEventListener('mouseenter', () => setActive(i));
    });

    setActive(0);
}

function initTeamSection() {
    const root = qs(null, '[data-team-section]');
    if (!root) return;

    const track = qs(root, '[data-team-track]');
    const cards = qsa(root, '[data-team-card]');
    const dotsHost = qs(root, '[data-team-dots]');
    const modal = qs(root, '[data-team-modal]');
    const modalBody = qs(root, '[data-team-modal-body]');
    const jsonEl = qs(root, '#team-json-data');

    let team = [];
    try {
        team = JSON.parse(jsonEl?.textContent || '[]');
    } catch {
        team = [];
    }

    const total = team.length;
    let current = 0;
    let itemsToShow = 4;

    const updateItemsToShow = () => {
        const w = window.innerWidth;
        if (w < 640) itemsToShow = 1;
        else if (w < 1024) itemsToShow = 2;
        else itemsToShow = 4;
    };

    const maxIndex = () => Math.max(0, total - itemsToShow);

    const renderDots = () => {
        if (!dotsHost) return;
        dotsHost.innerHTML = '';
        const m = maxIndex();
        for (let i = 0; i <= m; i += 1) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = `h-1.5 rounded-full transition-all duration-300 ${i === current ? 'bg-neon-cyan w-8' : 'bg-white/15 w-2 hover:bg-white/30'}`;
            b.addEventListener('click', () => {
                current = i;
                renderTrack();
            });
            dotsHost.appendChild(b);
        }
    };

    const renderTrack = () => {
        updateItemsToShow();
        if (track) {
            track.style.transform = `translateX(calc(-${current * (100 / itemsToShow)}% - ${(current * 1.5) / itemsToShow}rem))`;
        }
        renderDots();
    };

    const openModal = (index) => {
        const m = team[index];
        if (!m || !modal || !modalBody) return;
        const seed = String(m.name).replace(/\s+/g, '');
        const avatar =
            m.photo_url ||
            `https://api.dicebear.com/7.x/avataaars/svg?seed=${encodeURIComponent(seed)}`;

        const skills = (m.skills || []).map((s) => `<span class="px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/80 text-sm font-inter">${escapeHtml(s)}</span>`).join('');

        const projects = (m.projects || [])
            .map(
                (p) => `
            <div class="p-4 rounded-xl bg-dark-base border border-white/5">
              <h5 class="font-orbitron font-medium text-white mb-2">${escapeHtml(p.title)}</h5>
              <p class="font-inter text-sm text-white/50 leading-relaxed">${escapeHtml(p.description)}</p>
            </div>`,
            )
            .join('');

        const portfolioUrl = typeof m.portfolio_url === 'string' ? m.portfolio_url.trim() : '';
        const portfolioCtaClass =
            'w-full py-4 rounded-xl border border-neon-cyan/50 text-neon-cyan font-orbitron font-bold flex items-center justify-center gap-2 hover:bg-neon-cyan/10 hover:shadow-[0_0_20px_rgba(0,240,255,0.2)] transition-all duration-300';
        const portfolioIcon =
            '<svg class="w-[18px] h-[18px]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" /></svg>';
        const portfolioCta = portfolioUrl
            ? `<a href="${escapeHtml(portfolioUrl)}" target="_blank" rel="noopener noreferrer" class="${portfolioCtaClass}">View Full Portfolio ${portfolioIcon}</a>`
            : `<button type="button" class="${portfolioCtaClass}" data-team-modal-close-inner>View Full Portfolio ${portfolioIcon}</button>`;

        modalBody.innerHTML = `
      <div class="flex flex-col sm:flex-row gap-8 items-start mb-10">
        <div class="w-28 h-28 shrink-0 rounded-full overflow-hidden bg-dark-base border-2 border-neon-cyan shadow-[0_0_20px_rgba(0,240,255,0.2)]">
          <img src="${avatar}" alt="${escapeHtml(m.name)}" class="w-full h-full object-cover" />
        </div>
        <div>
          <h3 class="font-orbitron text-3xl font-bold text-white mb-2">${escapeHtml(m.name)}</h3>
          <p class="font-inter text-neon-cyan font-medium text-lg mb-3">${escapeHtml(m.role)}</p>
          <p class="font-inter text-white/60 italic leading-relaxed">"${escapeHtml(m.tagline)}"</p>
        </div>
      </div>
      <div class="space-y-8">
        <div>
          <h4 class="font-orbitron text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-neon-purple"></span> Core Skills
          </h4>
          <div class="flex flex-wrap gap-2">${skills}</div>
        </div>
        <div>
          <h4 class="font-orbitron text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-neon-cyan"></span> Featured Projects
          </h4>
          <div class="grid gap-4">${projects}</div>
        </div>
        <div class="pt-4">
          ${portfolioCta}
        </div>
      </div>`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        qs(modalBody, '[data-team-modal-close-inner]')?.addEventListener('click', closeModal);
    };

    const closeModal = () => {
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    qsa(root, '[data-team-open]').forEach((btn) => {
        btn.addEventListener('click', () => openModal(Number(btn.getAttribute('data-team-open'))));
    });

    qs(root, '[data-team-modal-backdrop]')?.addEventListener('click', closeModal);
    qs(root, '[data-team-modal-close]')?.addEventListener('click', closeModal);

    qs(root, '[data-team-prev]')?.addEventListener('click', () => {
        current = current === 0 ? maxIndex() : current - 1;
        renderTrack();
    });
    qs(root, '[data-team-next]')?.addEventListener('click', () => {
        current = current >= maxIndex() ? 0 : current + 1;
        renderTrack();
    });

    let auto = setInterval(() => {
        if (modal && !modal.classList.contains('hidden')) return;
        current = current >= maxIndex() ? 0 : current + 1;
        renderTrack();
    }, 4000);

    root.addEventListener('mouseenter', () => clearInterval(auto));
    root.addEventListener('mouseleave', () => {
        clearInterval(auto);
        auto = setInterval(() => {
            if (modal && !modal.classList.contains('hidden')) return;
            current = current >= maxIndex() ? 0 : current + 1;
            renderTrack();
        }, 4000);
    });

    window.addEventListener('resize', renderTrack, { passive: true });
    renderTrack();
}

function escapeHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function initOurWorks() {
    const root = qs(null, '[data-our-works]');
    if (!root) return;

    const panels = qsa(root, '.our-work-panel');
    const total = panels.length;
    let idx = 0;

    const show = (i) => {
        idx = Math.max(0, Math.min(total - 1, i));
        panels.forEach((p, j) => p.classList.toggle('hidden', j !== idx));
        qsa(root, '[data-our-work-dot]').forEach((d, j) => {
            const inner = d.querySelector('div');
            if (inner) {
                inner.className =
                    j === idx
                        ? 'h-1.5 rounded-full transition-all duration-500 w-10 bg-neon-cyan shadow-[0_0_15px_rgba(0,240,255,0.6)]'
                        : 'h-1.5 rounded-full transition-all duration-500 w-2 bg-white/20 group-hover:bg-white/50';
            }
        });

        panels.forEach((p, j) => {
            const v = p.querySelector('video');
            if (!v) return;
            if (j === idx) v.play().catch(() => {});
            else {
                v.pause();
                v.currentTime = 0;
            }
        });
    };

    qs(root, '[data-our-work-prev]')?.addEventListener('click', () => show(idx - 1));
    qs(root, '[data-our-work-next]')?.addEventListener('click', () => show(idx + 1));
    qsa(root, '[data-our-work-dot]').forEach((d) =>
        d.addEventListener('click', () => show(Number(d.getAttribute('data-our-work-dot')))),
    );

    show(0);
}

export function initDevthugsSite() {
    initNavbar();
    initScrollButtons();
    initFeaturedCarousel();
    initWhyCarousel();
    initStats();
    initShowcaseSlider();
    initMilestoneCarousel();
    initWhyDevthugs();
    initTeamSection();
    initOurWorks();
}
