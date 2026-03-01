/**
 * ANHQV Tech - Main JavaScript
 * Version: 2.1.0 - Performance Update
 */

(function () {
    'use strict';

    // ==================== //
    // Mobile Menu Toggle
    // ==================== //
    const initMobileMenu = () => {
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const nav = document.querySelector('.main-navigation');

        if (!menuToggle || !nav) return;

        menuToggle.addEventListener('click', () => {
            const isExpanded = menuToggle.getAttribute('aria-expanded') === 'true';
            menuToggle.setAttribute('aria-expanded', !isExpanded);
            nav.classList.toggle('is-open');
            document.body.style.overflow = !isExpanded ? 'hidden' : '';
        });

        document.addEventListener('click', (e) => {
            if (!nav.contains(e.target) && !menuToggle.contains(e.target)) {
                if (nav.classList.contains('is-open')) {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    nav.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && nav.classList.contains('is-open')) {
                menuToggle.setAttribute('aria-expanded', 'false');
                nav.classList.remove('is-open');
                document.body.style.overflow = '';
            }
        });
    };

    // ==================== //
    // Smooth Scroll
    // ==================== //
    const initSmoothScroll = () => {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href === '#') return;
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    };

    // ==========================================================================
    // FIX #8 — Sticky Header: se oculta al bajar solo en MÓVIL.
    // En escritorio el header permanece siempre visible (mejor UX).
    // ==========================================================================
    const initStickyHeader = () => {
        const header = document.querySelector('.site-header');
        if (!header) return;

        // Solo activar hide-on-scroll en pantallas pequeñas
        const shouldHide = () => window.innerWidth < 900;

        let lastScroll    = 0;
        let ticking       = false;
        const threshold   = 100;

        const handleScroll = () => {
            if (!shouldHide()) {
                header.style.transform = 'translateY(0)';
                return;
            }

            const currentScroll = window.pageYOffset;

            if (currentScroll > threshold) {
                if (currentScroll > lastScroll) {
                    header.style.transform = 'translateY(-100%)';
                } else {
                    header.style.transform = 'translateY(0)';
                }
            } else {
                header.style.transform = 'translateY(0)';
            }

            lastScroll = currentScroll;
        };

        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    handleScroll();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        // Re-evaluar al cambiar tamaño de ventana
        window.addEventListener('resize', () => {
            if (!shouldHide()) {
                header.style.transform = 'translateY(0)';
            }
        }, { passive: true });
    };

    // ==========================================================================
    // FIX #9 — Scroll Animations: las primeras tarjetas visibles (above-the-fold)
    // NO se ocultan para no penalizar el LCP de Google.
    // ==========================================================================
    const initScrollAnimations = () => {
        const observerOptions = {
            threshold:   0.1,
            rootMargin:  '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('anim-visible');
                    observer.unobserve(entry.target); // dejar de observar una vez animado
                }
            });
        }, observerOptions);

        // Calculamos cuántas tarjetas entran roughly above the fold
        // (aproximación: las 4 primeras no se animan)
        const ABOVE_FOLD_COUNT = 4;

        document.querySelectorAll('.post-card, .related-post-card').forEach((card, index) => {
            if (index < ABOVE_FOLD_COUNT) {
                // Ya visibles: no ocultar, no observar → LCP no se penaliza
                return;
            }
            card.classList.add('anim-hidden');
            observer.observe(card);
        });
    };

    // ==================== //
    // External Links
    // ==================== //
    const initExternalLinks = () => {
        const currentDomain = window.location.hostname;
        document.querySelectorAll('a[href^="http"]').forEach(link => {
            try {
                const linkDomain = new URL(link.href).hostname;
                if (linkDomain !== currentDomain) {
                    link.setAttribute('target', '_blank');
                    link.setAttribute('rel', 'noopener noreferrer');
                }
            } catch (e) { /* URL inválida, ignorar */ }
        });
    };

    // ==================== //
    // Reading Progress Bar
    // ==================== //
    const initReadingProgress = () => {
        if (!document.body.classList.contains('single-post')) return;

        const progressBar = document.createElement('div');
        progressBar.className = 'reading-progress';
        document.body.appendChild(progressBar);

        window.addEventListener('scroll', () => {
            const windowHeight   = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight - windowHeight;
            const scrolled       = window.pageYOffset;
            const progress       = Math.min((scrolled / documentHeight) * 100, 100);
            progressBar.style.width = `${progress}%`;
        }, { passive: true });
    };

    // ==================== //
    // Copy Code Blocks
    // ==================== //
    const initCodeCopy = () => {
        document.querySelectorAll('pre code').forEach(block => {
            const button = document.createElement('button');
            button.className    = 'copy-code-btn';
            button.textContent  = 'Copiar';
            button.type         = 'button';
            button.setAttribute('aria-label', 'Copiar código');

            const pre = block.parentElement;
            pre.style.position = 'relative';
            pre.appendChild(button);

            pre.addEventListener('mouseenter', () => { button.style.opacity = '1'; });
            pre.addEventListener('mouseleave', () => { button.style.opacity = '0'; });

            button.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(block.textContent);
                    button.textContent = '¡Copiado!';
                    button.classList.add('copied');
                    setTimeout(() => {
                        button.textContent = 'Copiar';
                        button.classList.remove('copied');
                    }, 2000);
                } catch (err) {
                    console.error('Error al copiar:', err);
                }
            });
        });
    };

    // ==================== //
    // Back to Top Button
    // ==================== //
    const initBackToTop = () => {
        const button = document.createElement('button');
        button.className = 'back-to-top';
        button.innerHTML = '↑';
        button.type      = 'button';
        button.setAttribute('aria-label', 'Volver arriba');
        document.body.appendChild(button);

        window.addEventListener('scroll', () => {
            button.classList.toggle('is-visible', window.pageYOffset > 500);
        }, { passive: true });

        button.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    };

    // ==================== //
    // Image Lightbox
    // ==================== //
    const initImageLightbox = () => {
        document.querySelectorAll('.entry-content img').forEach(img => {
            img.style.cursor = 'zoom-in';

            img.addEventListener('click', () => {
                const lightbox = document.createElement('div');
                lightbox.className = 'image-lightbox';
                lightbox.setAttribute('role', 'dialog');
                lightbox.setAttribute('aria-modal', 'true');
                lightbox.setAttribute('aria-label', img.alt || 'Imagen ampliada');

                const lightboxImg = document.createElement('img');
                lightboxImg.src   = img.src;
                lightboxImg.alt   = img.alt || '';

                const closeBtn = document.createElement('button');
                closeBtn.innerHTML        = '✕';
                closeBtn.className        = 'lightbox-close';
                closeBtn.type             = 'button';
                closeBtn.setAttribute('aria-label', 'Cerrar');

                lightbox.appendChild(closeBtn);
                lightbox.appendChild(lightboxImg);
                document.body.appendChild(lightbox);
                document.body.style.overflow = 'hidden';

                // Forzar reflow para la animación CSS
                requestAnimationFrame(() => lightbox.classList.add('is-open'));

                const closeLightbox = () => {
                    lightbox.classList.remove('is-open');
                    document.body.style.overflow = '';
                    setTimeout(() => lightbox.remove(), 300);
                };

                closeBtn.addEventListener('click', closeLightbox);
                lightbox.addEventListener('click', (e) => {
                    if (e.target === lightbox) closeLightbox();
                });
                document.addEventListener('keydown', function onKey(e) {
                    if (e.key === 'Escape') {
                        closeLightbox();
                        document.removeEventListener('keydown', onKey);
                    }
                });
            });
        });
    };

    // ==========================================================================
    // NUEVO — Tabla de Contenidos automática (TOC)
    // Se inserta al inicio del .entry-content si hay 3 o más headings h2/h3.
    // Mejora tiempo en página y reduce bounce rate.
    // ==========================================================================
    const initTableOfContents = () => {
        const content = document.querySelector('.entry-content');
        if (!content) return;

        const headings = content.querySelectorAll('h2, h3');
        if (headings.length < 3) return; // TOC solo si hay suficiente estructura

        // Añadir IDs automáticamente a los headings que no los tengan
        headings.forEach((heading, i) => {
            if (!heading.id) {
                const slug = heading.textContent
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .substring(0, 60);
                heading.id = slug || `section-${i}`;
            }
        });

        // Construir TOC
        const toc = document.createElement('nav');
        toc.className    = 'toc-widget';
        toc.setAttribute('aria-label', 'Tabla de contenidos');

        const tocTitle = document.createElement('p');
        tocTitle.className   = 'toc-title';
        tocTitle.textContent = '📋 Contenido';

        const tocList = document.createElement('ol');
        tocList.className = 'toc-list';

        headings.forEach(heading => {
            const li   = document.createElement('li');
            li.className = heading.tagName === 'H3' ? 'toc-item toc-h3' : 'toc-item toc-h2';

            const link       = document.createElement('a');
            link.href        = `#${heading.id}`;
            link.textContent = heading.textContent;
            link.addEventListener('click', (e) => {
                e.preventDefault();
                heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.pushState(null, null, `#${heading.id}`);
            });

            li.appendChild(link);
            tocList.appendChild(li);
        });

        // Toggle para móvil
        const toggleBtn       = document.createElement('button');
        toggleBtn.className   = 'toc-toggle';
        toggleBtn.type        = 'button';
        toggleBtn.textContent = 'Mostrar';
        toggleBtn.setAttribute('aria-expanded', 'false');

        tocTitle.appendChild(toggleBtn);

        toggleBtn.addEventListener('click', () => {
            const isOpen = toggleBtn.getAttribute('aria-expanded') === 'true';
            toggleBtn.setAttribute('aria-expanded', !isOpen);
            toggleBtn.textContent = isOpen ? 'Mostrar' : 'Ocultar';
            tocList.classList.toggle('is-hidden-mobile', isOpen);
        });

        toc.appendChild(tocTitle);
        toc.appendChild(tocList);

        // Insertar al inicio del contenido
        content.insertBefore(toc, content.firstChild);

        // Resaltar sección activa al hacer scroll
        const tocLinks = toc.querySelectorAll('a');

        const headingObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    tocLinks.forEach(link => link.classList.remove('toc-active'));
                    const activeLink = toc.querySelector(`a[href="#${entry.target.id}"]`);
                    if (activeLink) activeLink.classList.add('toc-active');
                }
            });
        }, { rootMargin: '0px 0px -60% 0px' });

        headings.forEach(h => headingObserver.observe(h));
    };

    // ==================== //
    // Initialize All
    // ==================== //
    const init = () => {
        initMobileMenu();
        initSmoothScroll();
        initStickyHeader();
        initScrollAnimations();
        initExternalLinks();
        initReadingProgress();
        initCodeCopy();
        initBackToTop();
        initImageLightbox();
        initTableOfContents();

        console.log('ANHQV Tech v2.1 initialized');
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
