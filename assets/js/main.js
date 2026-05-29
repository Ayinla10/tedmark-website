// Tedmark Digital Agency — Main JS

// ── Dropdown toggle (called from inline onclick) ──────
function toggleDrop() {
    const drop = document.getElementById('services-drop');
    const arrow = document.querySelector('.tm-drop-arrow');
    if (!drop) return;
    const open = drop.classList.toggle('open');
    if (arrow) arrow.style.transform = open ? 'rotate(180deg)' : '';
}

// Close dropdown when clicking outside
document.addEventListener('click', (e) => {
    const drop = document.getElementById('services-drop');
    if (drop && !drop.contains(e.target)) {
        const arrow = document.querySelector('.tm-drop-arrow');
        drop.classList.remove('open');
        if (arrow) arrow.style.transform = '';
    }
});

// ── Mobile menu toggle ────────────────────────────────
function toggleMobile() {
    const menu = document.getElementById('mobile-menu');
    const icon = document.getElementById('ham-icon');
    if (!menu) return;
    const open = menu.classList.toggle('open');
    if (icon) {
        icon.className = open ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
    }
}

function tmInit() {

    // ── AOS Init ──────────────────────────────────
    if (typeof AOS !== 'undefined') {
        AOS.init({ duration: 700, easing: 'ease-out-cubic', once: true, offset: 60 });
    }

    // ── Navbar Scroll ─────────────────────────────
    const navbar = document.getElementById('navbar');
    if (navbar) {
        const hasDarkHero = navbar.dataset.darkHero === '1';
        const onScroll = () => {
            if (!hasDarkHero || window.scrollY > 20) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    // ── Fade-in observer ─────────────────────────
    const fadeEls = document.querySelectorAll('.tm-fade');
    if (fadeEls.length) {
        // Fallback: immediately show anything already in/near viewport
        const showVisible = () => {
            fadeEls.forEach(el => {
                const rect = el.getBoundingClientRect();
                if (rect.top < window.innerHeight + 60) {
                    el.classList.add('visible');
                }
            });
        };
        showVisible();
        setTimeout(showVisible, 150);

        // Observer for scroll-triggered reveals
        if ('IntersectionObserver' in window) {
            const fadeObs = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        fadeObs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0, rootMargin: '0px 0px -40px 0px' });
            fadeEls.forEach(el => fadeObs.observe(el));
        }
    }

    // ── Back to top visibility ────────────────────
    const btt = document.getElementById('btt');
    if (btt) {
        window.addEventListener('scroll', () => {
            btt.classList.toggle('visible', window.scrollY > 400);
        }, { passive: true });
    }

    // ── Animated Counters ─────────────────────────
    const counters = document.querySelectorAll('[data-counter]');
    if (counters.length) {
        const animateCounter = (el) => {
            const target = parseFloat(el.dataset.counter);
            const suffix = el.dataset.suffix || '';
            const prefix = el.dataset.prefix || '';
            const duration = 2000;
            const start = performance.now();
            const isDecimal = target % 1 !== 0;

            const tick = (now) => {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const val = target * eased;
                el.textContent = prefix + (isDecimal ? val.toFixed(1) : Math.floor(val)) + suffix;
                if (progress < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !entry.target.dataset.animated) {
                    entry.target.dataset.animated = 'true';
                    animateCounter(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(c => observer.observe(c));
    }

    // ── Portfolio Filter ───────────────────────────
    const filterBtns = document.querySelectorAll('.tm-filter-btn, .filter-btn');
    const portfolioItems = document.querySelectorAll('.tm-port-card, .portfolio-item');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.dataset.filter;

            portfolioItems.forEach(item => {
                const show = filter === 'all' || item.dataset.category === filter;
                item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                if (show) {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.95)';
                    item.style.display = '';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 50);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.95)';
                    setTimeout(() => { item.style.display = 'none'; }, 300);
                }
            });
        });
    });

    // ── Newsletter Form ────────────────────────────
    const newsletterForm = document.getElementById('newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = newsletterForm.querySelector('[name=email]').value;
            const btn = newsletterForm.querySelector('button[type=submit]');
            btn.textContent = 'Subscribing...';
            btn.disabled = true;

            try {
                const res = await fetch('/tedmark-digital/api/subscribe.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email })
                });
                const data = await res.json();
                if (data.success) {
                    btn.textContent = '✓ Subscribed!';
                    btn.style.background = '#059669';
                    newsletterForm.reset();
                } else {
                    btn.textContent = 'Try Again';
                    btn.disabled = false;
                }
            } catch {
                btn.textContent = 'Try Again';
                btn.disabled = false;
            }
        });
    }

    // ── ROI Calculator (tools page) ───────────────
    const roiForm = document.getElementById('roi-form');
    if (roiForm) {
        roiForm.addEventListener('input', calculateROI);
    }

    function calculateROI() {
        const staff = parseFloat(document.getElementById('roi-staff')?.value) || 0;
        const hours = parseFloat(document.getElementById('roi-hours')?.value) || 0;
        const rate = parseFloat(document.getElementById('roi-rate')?.value) || 0;
        const inefficiency = parseFloat(document.getElementById('roi-inefficiency')?.value) || 0;

        const annualWaste = staff * hours * 52 * rate;
        const savings = annualWaste * (inefficiency / 100) * 0.75;
        const roiPercent = rate > 0 ? Math.round((savings / (staff * 500)) * 100) : 0;

        const savingsEl = document.getElementById('roi-savings');
        const roiEl = document.getElementById('roi-percent');
        const hoursEl = document.getElementById('roi-hours-saved');

        if (savingsEl) savingsEl.textContent = '$' + Math.round(savings).toLocaleString();
        if (roiEl) roiEl.textContent = roiPercent + '%';
        if (hoursEl) hoursEl.textContent = Math.round(staff * hours * 52 * (inefficiency / 100) * 0.75).toLocaleString();
    }

    // ── Business Health Checker ────────────────────
    const healthForm = document.getElementById('health-form');
    if (healthForm) {
        let currentStep = 0;
        const steps = healthForm.querySelectorAll('[data-step]');
        const progress = document.getElementById('health-progress');
        const stepLabel = document.getElementById('step-label');

        const showStep = (n) => {
            steps.forEach((s, i) => {
                s.style.display = i === n ? 'block' : 'none';
            });
            if (progress) progress.style.width = ((n + 1) / steps.length * 100) + '%';
            if (stepLabel) stepLabel.textContent = `Step ${n + 1} of ${steps.length}`;
        };

        showStep(0);

        healthForm.querySelectorAll('[data-next]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (currentStep < steps.length - 1) {
                    currentStep++;
                    showStep(currentStep);
                }
            });
        });

        healthForm.querySelectorAll('[data-back]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (currentStep > 0) {
                    currentStep--;
                    showStep(currentStep);
                }
            });
        });
    }

    // ── Smooth scroll for anchor links ────────────
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ── Auto-dismiss alerts ────────────────────────
    document.querySelectorAll('[data-auto-dismiss]').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s ease';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        }, 5000);
    });

    // ── Testimonial Slider (simple) ────────────────
    const testimonialTrack = document.getElementById('testimonial-track');
    if (testimonialTrack) {
        let current = 0;
        const cards = testimonialTrack.querySelectorAll('.testimonial-slide');
        const total = cards.length;

        const prev = document.getElementById('test-prev');
        const next = document.getElementById('test-next');
        const dots = document.querySelectorAll('[data-dot]');

        const go = (n) => {
            current = (n + total) % total;
            testimonialTrack.style.transform = `translateX(-${current * 100}%)`;
            dots.forEach((d, i) => d.classList.toggle('opacity-100', i === current));
            dots.forEach((d, i) => d.classList.toggle('opacity-30', i !== current));
        };

        if (prev) prev.addEventListener('click', () => go(current - 1));
        if (next) next.addEventListener('click', () => go(current + 1));
        dots.forEach((d, i) => d.addEventListener('click', () => go(i)));

        setInterval(() => go(current + 1), 6000);
    }

    // ── Service Recommender ────────────────────────
    const recommenderForm = document.getElementById('recommender-form');
    if (recommenderForm) {
        recommenderForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(recommenderForm);
            const answers = Object.fromEntries(formData);
            const services = getRecommendations(answers);
            displayRecommendations(services);
        });
    }

    function getRecommendations(answers) {
        const recs = [];
        if (answers.website === 'none' || answers.website === 'old') recs.push({ title: 'Website Development', icon: '🌐', desc: 'A modern, fast website that converts visitors into customers.' });
        if (answers.tracking === 'manual' || answers.tracking === 'none') recs.push({ title: 'Business Systems', icon: '⚙️', desc: 'Automated tracking, reporting, and operational systems.' });
        if (answers.communication === 'scattered') recs.push({ title: 'Communication Infrastructure', icon: '💬', desc: 'Unified communication tools for your team and clients.' });
        if (answers.customers === 'manual' || answers.customers === 'none') recs.push({ title: 'CRM System', icon: '👥', desc: 'Customer relationship management to grow repeat business.' });
        if (answers.sales === 'offline') recs.push({ title: 'E-Commerce Platform', icon: '🛒', desc: 'Sell your products online across Africa.' });
        if (answers.marketing === 'none' || answers.marketing === 'weak') recs.push({ title: 'Digital Marketing', icon: '📢', desc: 'Attract and convert more customers online.' });
        if (recs.length === 0) recs.push({ title: 'Business Automation', icon: '⚡', desc: 'Automate repetitive processes to save time and money.' });
        return recs;
    }

    function displayRecommendations(services) {
        const container = document.getElementById('recommendations');
        if (!container) return;
        container.innerHTML = services.map(s => `
            <div class="flex gap-4 p-5 bg-blue-50 border border-blue-100 rounded-xl">
                <div class="text-2xl">${s.icon}</div>
                <div>
                    <div class="font-semibold text-slate-800">${s.title}</div>
                    <div class="text-sm text-slate-500 mt-1">${s.desc}</div>
                </div>
            </div>
        `).join('');
        document.getElementById('recommendations-section')?.scrollIntoView({ behavior: 'smooth' });
    }

    // ── Lazy load images ───────────────────────────
    if ('IntersectionObserver' in window) {
        const imgObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imgObserver.unobserve(img);
                    }
                }
            });
        });
        document.querySelectorAll('img[data-src]').forEach(img => imgObserver.observe(img));
    }
}

// Run immediately if DOM already ready (script at bottom of body), else wait
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', tmInit);
} else {
    tmInit();
}
