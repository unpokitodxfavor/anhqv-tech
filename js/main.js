/**
 * ANHQV Tech - Main JavaScript
 * Version: 2.0.0
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

            // Prevent body scroll when menu is open
            document.body.style.overflow = !isExpanded ? 'hidden' : '';
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!nav.contains(e.target) && !menuToggle.contains(e.target)) {
                if (nav.classList.contains('is-open')) {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    nav.classList.remove('is-open');
                    document.body.style.overflow = '';
                }
            }
        });

        // Close menu on escape key
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
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    };

    // ==================== //
    // Sticky Header on Scroll
    // ==================== //
    const initStickyHeader = () => {
        const header = document.querySelector('.site-header');
        if (!header) return;

        let lastScroll = 0;
        const scrollThreshold = 100;

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;

            // Hide header when scrolling down, show when scrolling up
            if (currentScroll > scrollThreshold) {
                if (currentScroll > lastScroll) {
                    header.style.transform = 'translateY(-100%)';
                } else {
                    header.style.transform = 'translateY(0)';
                }
            } else {
                header.style.transform = 'translateY(0)';
            }

            lastScroll = currentScroll;
        });
    };

    // ==================== //
    // Intersection Observer for Animations
    // ==================== //
    const initScrollAnimations = () => {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe post cards
        document.querySelectorAll('.post-card, .related-post-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    };

    // ==================== //
    // External Links in New Tab
    // ==================== //
    const initExternalLinks = () => {
        const links = document.querySelectorAll('a[href^="http"]');
        const currentDomain = window.location.hostname;

        links.forEach(link => {
            const linkDomain = new URL(link.href).hostname;
            if (linkDomain !== currentDomain) {
                link.setAttribute('target', '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            }
        });
    };

    // ==================== //
    // Reading Progress Bar
    // ==================== //
    const initReadingProgress = () => {
        if (!document.body.classList.contains('single-post')) return;

        const progressBar = document.createElement('div');
        progressBar.className = 'reading-progress';
        progressBar.style.cssText = `
            position: fixed;
            top: 60px;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #a78bfa);
            z-index: 999;
            transition: width 0.1s ease;
        `;
        document.body.appendChild(progressBar);

        window.addEventListener('scroll', () => {
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight - windowHeight;
            const scrolled = window.pageYOffset;
            const progress = (scrolled / documentHeight) * 100;
            progressBar.style.width = `${progress}%`;
        });
    };

    // ==================== //
    // Copy Code Blocks
    // ==================== //
    const initCodeCopy = () => {
        const codeBlocks = document.querySelectorAll('pre code');

        codeBlocks.forEach(block => {
            const button = document.createElement('button');
            button.className = 'copy-code-btn';
            button.textContent = 'Copy';
            button.style.cssText = `
                position: absolute;
                top: 0.5rem;
                right: 0.5rem;
                padding: 0.25rem 0.75rem;
                background: rgba(59, 130, 246, 0.8);
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 0.75rem;
                opacity: 0;
                transition: opacity 0.2s;
            `;

            const pre = block.parentElement;
            pre.style.position = 'relative';
            pre.appendChild(button);

            pre.addEventListener('mouseenter', () => {
                button.style.opacity = '1';
            });

            pre.addEventListener('mouseleave', () => {
                button.style.opacity = '0';
            });

            button.addEventListener('click', async () => {
                const code = block.textContent;
                try {
                    await navigator.clipboard.writeText(code);
                    button.textContent = 'Copied!';
                    setTimeout(() => {
                        button.textContent = 'Copy';
                    }, 2000);
                } catch (err) {
                    console.error('Failed to copy code:', err);
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
        button.setAttribute('aria-label', 'Back to top');
        button.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #a78bfa);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.5rem;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 999;
        `;

        document.body.appendChild(button);

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 500) {
                button.style.opacity = '1';
                button.style.visibility = 'visible';
            } else {
                button.style.opacity = '0';
                button.style.visibility = 'hidden';
            }
        });

        button.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        button.addEventListener('mouseenter', () => {
            button.style.transform = 'scale(1.1)';
        });

        button.addEventListener('mouseleave', () => {
            button.style.transform = 'scale(1)';
        });
    };

    // ==================== //
    // Image Lightbox (Simple)
    // ==================== //
    const initImageLightbox = () => {
        const contentImages = document.querySelectorAll('.entry-content img');

        contentImages.forEach(img => {
            img.style.cursor = 'pointer';
            img.addEventListener('click', () => {
                const lightbox = document.createElement('div');
                lightbox.className = 'image-lightbox';
                lightbox.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.95);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 9999;
                    cursor: pointer;
                `;

                const lightboxImg = document.createElement('img');
                lightboxImg.src = img.src;
                lightboxImg.style.cssText = `
                    max-width: 90%;
                    max-height: 90%;
                    border-radius: 8px;
                `;

                lightbox.appendChild(lightboxImg);
                document.body.appendChild(lightbox);

                lightbox.addEventListener('click', () => {
                    lightbox.remove();
                });
            });
        });
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

        console.log('ANHQV Tech v2.0 initialized');
    };

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
