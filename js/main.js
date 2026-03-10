/**
 * ANHQV Tech - Main JavaScript
 * Version: 2.5.0
 *
 * Nuevas funcionalidades:
 *  7.  Sistema de series (CSS + PHP, JS para animación de barra de progreso)
 *  8.  Dashboard de estadísticas (PHP puro en admin)
 *  9.  Skeleton Loading
 */
(function () {
    'use strict';

    const DATA = () => window.anhqvData || {};

    // ================================================================
    // Dark / Light Mode Toggle
    // ================================================================
    const initThemeToggle = () => {
        const btn  = document.getElementById('theme-toggle');
        const html = document.documentElement;
        const meta = document.getElementById('theme-color-meta');
        if (!btn) { console.warn('ANHQV: #theme-toggle no encontrado'); return; }
        const applyTheme = (theme) => {
            html.classList.toggle('light-mode', theme === 'light');
            if (meta) meta.setAttribute('content', theme === 'light' ? '#f1f5f9' : '#0f172a');
            btn.setAttribute('aria-label', theme === 'light' ? 'Cambiar a modo oscuro' : 'Cambiar a modo claro');
        };
        applyTheme(localStorage.getItem('anhqv-theme') || 'dark');
        btn.addEventListener('click', () => {
            const next = html.classList.contains('light-mode') ? 'dark' : 'light';
            applyTheme(next); localStorage.setItem('anhqv-theme', next);
        });
    };

    // ================================================================
    // Mobile Menu
    // ================================================================
    const initMobileMenu = () => {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const nav    = document.querySelector('.main-navigation');
        if (!toggle || !nav) return;
        toggle.addEventListener('click', () => {
            const open = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', !open);
            nav.classList.toggle('is-open');
            document.body.style.overflow = !open ? 'hidden' : '';
        });
        document.addEventListener('click', (e) => {
            if (!nav.contains(e.target) && !toggle.contains(e.target) && nav.classList.contains('is-open')) {
                toggle.setAttribute('aria-expanded', 'false'); nav.classList.remove('is-open'); document.body.style.overflow = '';
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && nav.classList.contains('is-open')) {
                toggle.setAttribute('aria-expanded', 'false'); nav.classList.remove('is-open'); document.body.style.overflow = '';
            }
        });
    };

    // ================================================================
    // Smooth Scroll
    // ================================================================
    const initSmoothScroll = () => {
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href === '#') return;
                const target = document.querySelector(href);
                if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
            });
        });
    };

    // ================================================================
    // Sticky Header
    // ================================================================
    const initStickyHeader = () => {
        const header = document.querySelector('.site-header');
        if (!header) return;
        let last = 0, ticking = false;
        const isMobile = () => window.innerWidth < 900;
        const onScroll = () => {
            if (!isMobile()) { header.style.transform = ''; return; }
            const cur = window.pageYOffset;
            header.style.transform = cur > 100 ? (cur > last ? 'translateY(-100%)' : 'translateY(0)') : 'translateY(0)';
            last = cur;
        };
        window.addEventListener('scroll', () => { if (!ticking) { requestAnimationFrame(() => { onScroll(); ticking = false; }); ticking = true; } }, { passive: true });
        window.addEventListener('resize', () => { if (!isMobile()) header.style.transform = ''; }, { passive: true });
    };

    // ================================================================
    // Scroll Animations
    // ================================================================
    const initScrollAnimations = () => {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('anim-visible'); obs.unobserve(e.target); } });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        document.querySelectorAll('.post-card, .related-post-card').forEach((card, i) => {
            if (i < 4) return; card.classList.add('anim-hidden'); obs.observe(card);
        });
    };

    // ================================================================
    // External Links
    // ================================================================
    const initExternalLinks = () => {
        const domain = window.location.hostname;
        document.querySelectorAll('a[href^="http"]').forEach(link => {
            try { if (new URL(link.href).hostname !== domain) { link.target = '_blank'; link.rel = 'noopener noreferrer'; } } catch (e) {}
        });
    };

    // ================================================================
    // Reading Progress
    // ================================================================
    const initReadingProgress = () => {
        if (!document.body.classList.contains('single-post')) return;
        const bar = document.createElement('div'); bar.className = 'reading-progress';
        document.body.appendChild(bar);
        window.addEventListener('scroll', () => {
            const total = document.documentElement.scrollHeight - window.innerHeight;
            bar.style.width = Math.min((window.pageYOffset / total) * 100, 100) + '%';
        }, { passive: true });
    };

    // ================================================================
    // Copy Code
    // ================================================================
    const initCodeCopy = () => {
        document.querySelectorAll('pre code').forEach(block => {
            const btn = document.createElement('button');
            btn.className = 'copy-code-btn'; btn.textContent = 'Copiar'; btn.type = 'button';
            const pre = block.parentElement;
            pre.style.position = 'relative'; pre.appendChild(btn);
            pre.addEventListener('mouseenter', () => btn.style.opacity = '1');
            pre.addEventListener('mouseleave', () => btn.style.opacity = '0');
            btn.addEventListener('click', async () => {
                try { await navigator.clipboard.writeText(block.textContent); btn.textContent = '¡Copiado!'; btn.classList.add('copied'); setTimeout(() => { btn.textContent = 'Copiar'; btn.classList.remove('copied'); }, 2000); } catch (e) {}
            });
        });
    };

    // ================================================================
    // Back to Top
    // ================================================================
    const initBackToTop = () => {
        const btn = document.createElement('button');
        btn.className = 'back-to-top'; btn.innerHTML = '↑'; btn.type = 'button'; btn.setAttribute('aria-label', 'Volver arriba');
        document.body.appendChild(btn);
        window.addEventListener('scroll', () => btn.classList.toggle('is-visible', window.pageYOffset > 500), { passive: true });
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    };

    // ================================================================
    // Image Lightbox
    // ================================================================
    const initImageLightbox = () => {
        document.querySelectorAll('.entry-content img').forEach(img => {
            img.style.cursor = 'zoom-in';
            img.addEventListener('click', () => {
                const lb = document.createElement('div'); lb.className = 'image-lightbox'; lb.setAttribute('role', 'dialog'); lb.setAttribute('aria-modal', 'true');
                const lbImg = document.createElement('img'); lbImg.src = img.src; lbImg.alt = img.alt || '';
                const cls = document.createElement('button'); cls.innerHTML = '✕'; cls.className = 'lightbox-close'; cls.type = 'button';
                lb.appendChild(cls); lb.appendChild(lbImg); document.body.appendChild(lb); document.body.style.overflow = 'hidden';
                requestAnimationFrame(() => lb.classList.add('is-open'));
                const close = () => { lb.classList.remove('is-open'); document.body.style.overflow = ''; setTimeout(() => lb.remove(), 300); };
                cls.addEventListener('click', close); lb.addEventListener('click', e => { if (e.target === lb) close(); });
                document.addEventListener('keydown', function esc(e) { if (e.key === 'Escape') { close(); document.removeEventListener('keydown', esc); } });
            });
        });
    };

    // ================================================================
    // TOC
    // ================================================================
    const initTableOfContents = () => {
        const content = document.querySelector('.entry-content'); if (!content) return;
        const headings = content.querySelectorAll('h2, h3'); if (headings.length < 3) return;
        headings.forEach((h, i) => { if (!h.id) h.id = (h.textContent.toLowerCase().trim().replace(/[^\w\s-]/g,'').replace(/\s+/g,'-').substring(0,60))||'section-'+i; });
        const toc = document.createElement('nav'); toc.className = 'toc-widget'; toc.setAttribute('aria-label','Tabla de contenidos');
        const title = document.createElement('p'); title.className = 'toc-title'; title.textContent = '📋 Contenido';
        const toggle = document.createElement('button'); toggle.className = 'toc-toggle'; toggle.type = 'button'; toggle.textContent = 'Mostrar'; toggle.setAttribute('aria-expanded','false'); title.appendChild(toggle);
        const list = document.createElement('ol'); list.className = 'toc-list';
        headings.forEach(h => {
            const li = document.createElement('li'); li.className = 'toc-item '+(h.tagName==='H3'?'toc-h3':'toc-h2');
            const a = document.createElement('a'); a.href = '#'+h.id; a.textContent = h.textContent;
            a.addEventListener('click', e => { e.preventDefault(); h.scrollIntoView({behavior:'smooth',block:'start'}); history.pushState(null,null,'#'+h.id); });
            li.appendChild(a); list.appendChild(li);
        });
        toggle.addEventListener('click', () => { const open=toggle.getAttribute('aria-expanded')==='true'; toggle.setAttribute('aria-expanded',!open); toggle.textContent=open?'Mostrar':'Ocultar'; list.classList.toggle('is-hidden-mobile',open); });
        toc.appendChild(title); toc.appendChild(list); content.insertBefore(toc, content.firstChild);
        const links = toc.querySelectorAll('a');
        headings.forEach(h => {
            new IntersectionObserver((entries) => { entries.forEach(entry => { if (entry.isIntersecting) { links.forEach(l=>l.classList.remove('toc-active')); const a=toc.querySelector('a[href="#'+entry.target.id+'"]'); if(a)a.classList.add('toc-active'); } }); }, { rootMargin:'0px 0px -60% 0px' }).observe(h);
        });
    };

    // ================================================================
    // Búsqueda AJAX
    // ================================================================
    const initAjaxSearch = () => {
        const toggleBtn = document.getElementById('search-toggle');
        const panel     = document.getElementById('ajax-search-panel');
        const input     = document.getElementById('ajax-search-input');
        const results   = document.getElementById('ajax-search-results');
        const overlay   = document.querySelector('.ajax-search-overlay');
        if (!toggleBtn || !panel || !input) return;
        let timer = null, lastQuery = '';
        const open  = () => { panel.classList.add('is-open'); panel.setAttribute('aria-hidden','false'); overlay.classList.add('is-visible'); document.body.classList.add('ajax-search-open'); toggleBtn.setAttribute('aria-expanded','true'); document.body.style.overflow='hidden'; setTimeout(()=>input.focus(),50); };
        const close = () => { panel.classList.remove('is-open'); panel.setAttribute('aria-hidden','true'); overlay.classList.remove('is-visible'); document.body.classList.remove('ajax-search-open'); toggleBtn.setAttribute('aria-expanded','false'); document.body.style.overflow=''; input.value=''; results.innerHTML=''; lastQuery=''; };
        toggleBtn.addEventListener('click', () => panel.classList.contains('is-open')?close():open());
        overlay.addEventListener('click', close);
        document.addEventListener('keydown', (e) => { if (e.key==='Escape'&&panel.classList.contains('is-open')) close(); });
        const render = (json) => {
            if (!json.success) { results.innerHTML='<p class="ajax-result-empty">Error al buscar.</p>'; return; }
            const {results:items,total,query}=json.data;
            if (!items.length) { results.innerHTML=`<p class="ajax-result-empty">Sin resultados para "<strong>${query}</strong>"</p>`; return; }
            let html=`<p class="ajax-results-header"><strong>${total}</strong> resultado${total!==1?'s':''} para "<strong>${query}</strong>"</p>`;
            items.forEach(p=>{const thumb=p.thumb?`<div class="ajax-result-thumb"><img src="${p.thumb}" alt="${p.title}" loading="lazy"></div>`:'<div class="ajax-result-thumb"></div>';html+=`<a href="${p.url}" class="ajax-result-item">${thumb}<div class="ajax-result-info">${p.category?`<span class="ajax-result-cat">${p.category}</span>`:''}<p class="ajax-result-title">${p.title}</p><span class="ajax-result-meta">${p.date} · ${p.reading}</span></div></a>`;});
            results.innerHTML=html;
        };
        const search = async (q) => {
            if(q===lastQuery)return;lastQuery=q;
            if(q.length<2){results.innerHTML='';return;}
            results.innerHTML='<div class="ajax-result-loading">Buscando...</div>';
            try { const d=DATA(); const res=await fetch(`${d.ajaxurl}?action=anhqv_search&query=${encodeURIComponent(q)}&nonce=${d.searchNonce}`); const json=await res.json(); if(input.value.trim()===q)render(json); }
            catch{results.innerHTML='<p class="ajax-result-empty">Error de conexión.</p>';}
        };
        input.addEventListener('input',()=>{clearTimeout(timer);const q=input.value.trim();if(q.length<2){results.innerHTML='';lastQuery='';return;}timer=setTimeout(()=>search(q),350);});
        input.addEventListener('keydown',(e)=>{if(e.key==='ArrowDown'){e.preventDefault();results.querySelector('.ajax-result-item')?.focus();}});
        results.addEventListener('keydown',(e)=>{const items=[...results.querySelectorAll('.ajax-result-item')],idx=items.indexOf(document.activeElement);if(e.key==='ArrowDown'&&idx<items.length-1){e.preventDefault();items[idx+1].focus();}if(e.key==='ArrowUp'){e.preventDefault();idx>0?items[idx-1].focus():input.focus();}});
    };

    // ================================================================
    // Valoración con estrellas
    // ================================================================
    const initStarRating = () => {
        const widget=document.querySelector('.rating-widget'); if(!widget)return;
        const d=DATA(),postId=widget.dataset.postId;
        const input=widget.querySelector('.star-rating-input');
        const btns=widget.querySelectorAll('.star-btn');
        const resultDiv=widget.querySelector('.rating-result');
        const feedback=widget.querySelector('.rating-feedback');
        const fill=widget.querySelector('.stars-fill');
        const summary=widget.querySelector('.rating-summary');
        if(!input||!postId)return;
        btns.forEach(btn=>{btn.addEventListener('mouseenter',()=>{if(input.classList.contains('is-rated'))return;const v=parseInt(btn.dataset.value);btns.forEach(b=>b.style.color=parseInt(b.dataset.value)<=v?'#f59e0b':'');});btn.addEventListener('mouseleave',()=>{if(input.classList.contains('is-rated'))return;btns.forEach(b=>b.style.color='');});});
        const showResult=(avg,count,pct)=>{if(fill)fill.style.width=pct+'%';if(summary)summary.innerHTML=`<strong>${avg.toFixed(1)}</strong>/5 <span class="rating-count">(${count} valoraciones)</span>`;resultDiv.removeAttribute('hidden');};
        btns.forEach(btn=>{btn.addEventListener('click',async()=>{if(input.classList.contains('is-rated'))return;const rating=parseInt(btn.dataset.value);input.classList.add('is-rated');btns.forEach(b=>{b.style.color=parseInt(b.dataset.value)<=rating?'#f59e0b':'';b.disabled=true;});feedback.textContent='Guardando...';try{const fd=new FormData();fd.append('action','anhqv_save_rating');fd.append('nonce',d.ratingNonce);fd.append('post_id',postId);fd.append('rating',rating);const res=await fetch(d.ajaxurl,{method:'POST',body:fd});const json=await res.json();if(json.success){feedback.textContent='¡Gracias por tu valoración! ⭐';showResult(json.data.average,json.data.count,json.data.percent);}else if(json.data?.message==='already_rated'){feedback.textContent=d.alreadyRated||'Ya has valorado';showResult(json.data.average,json.data.count,json.data.percent);}else{feedback.textContent='Error al guardar.';input.classList.remove('is-rated');btns.forEach(b=>{b.style.color='';b.disabled=false;});}}catch{feedback.textContent='Error de conexión.';input.classList.remove('is-rated');btns.forEach(b=>{b.style.color='';b.disabled=false;});}});});
    };

    // ================================================================
    // Hero partículas + parallax
    // ================================================================
    const initHeroParticles = () => {
        const section = document.querySelector('.hero-section'); if (!section) return;
        section.querySelectorAll('.hero-particle').forEach(p => {
            p.style.cssText=`left:${Math.random()*100}%;width:${2+Math.random()*4}px;height:${2+Math.random()*4}px;animation-duration:${8+Math.random()*15}s;animation-delay:-${Math.random()*10}s`;
        });
        if (window.innerWidth > 900) {
            window.addEventListener('scroll', () => { section.style.backgroundPositionY=`calc(50% + ${window.pageYOffset*0.4}px)`; }, { passive: true });
        }
    };

    // ================================================================
    // Toggle Revista / Lista
    // ================================================================
    const initViewToggle = () => {
        const grid=document.getElementById('posts-grid'); const btns=document.querySelectorAll('.view-toggle-btn');
        if(!grid||!btns.length)return;
        const applyView=(view)=>{grid.classList.toggle('view-list',view==='list');btns.forEach(b=>{const a=b.dataset.view===view;b.classList.toggle('is-active',a);b.setAttribute('aria-pressed',a);});};
        applyView(localStorage.getItem('anhqv-view')||'grid');
        btns.forEach(btn=>{btn.addEventListener('click',()=>{const v=btn.dataset.view;applyView(v);localStorage.setItem('anhqv-view',v);});});
    };

    // ================================================================
    // Favoritos
    // ================================================================
    const initFavorites = () => {
        const KEY = 'anhqv-favorites';
        const get  = () => JSON.parse(localStorage.getItem(KEY)||'[]');
        const save = (ids) => localStorage.setItem(KEY, JSON.stringify(ids));
        const isSaved = (id) => get().includes(String(id));
        const toggle  = (id) => { const f=get(),i=f.indexOf(String(id)); if(i>=0)f.splice(i,1); else f.push(String(id)); save(f); updateCounter(); return i<0; };
        const updateCounter = () => { const c=get().length,b=document.getElementById('favorites-count'); if(!b)return; if(c>0){b.textContent=c;b.style.display='flex';}else{b.style.display='none';} };
        const syncBtns = () => { document.querySelectorAll('.save-article-btn').forEach(btn=>{ const saved=isSaved(btn.dataset.id); btn.classList.toggle('is-saved',saved); const sp=btn.querySelector('.save-btn-text'); if(sp)sp.textContent=saved?'Guardado':'Guardar artículo'; }); };
        updateCounter(); syncBtns();
        document.addEventListener('click',(e)=>{ const btn=e.target.closest('.save-article-btn'); if(!btn)return; const saved=toggle(btn.dataset.id); btn.classList.toggle('is-saved',saved); const sp=btn.querySelector('.save-btn-text'); if(sp)sp.textContent=saved?'Guardado':'Guardar artículo'; btn.style.transform='scale(1.2)'; setTimeout(()=>{btn.style.transform='';},200); });
    };

    // ================================================================
    // Newsletter
    // ================================================================
    const initNewsletter = () => {
        document.querySelectorAll('.newsletter-form').forEach(form=>{
            const input=form.querySelector('.newsletter-email'),btn=form.querySelector('.newsletter-submit'),feedback=form.closest('.newsletter-widget')?.querySelector('.newsletter-feedback'),nonce=form.dataset.nonce;
            if(!input||!btn)return;
            form.addEventListener('submit',async(e)=>{e.preventDefault();const email=input.value.trim();if(!email)return;btn.disabled=true;btn.textContent='Enviando...';try{const d=DATA(),fd=new FormData();fd.append('action','anhqv_newsletter');fd.append('nonce',nonce||d.newsletterNonce);fd.append('email',email);const res=await fetch(d.ajaxurl,{method:'POST',body:fd});const json=await res.json();if(json.success){if(feedback)feedback.textContent=json.data.message;input.value='';form.style.display='none';}else{if(feedback)feedback.textContent=json.data?.message||'Error.';btn.disabled=false;btn.textContent='Suscribirme';}}catch{if(feedback)feedback.textContent='Error de conexión.';btn.disabled=false;btn.textContent='Suscribirme';}});
        });
    };

    // ================================================================
    // Prefetch
    // ================================================================
    const initPrefetch = () => {
        if(!('IntersectionObserver' in window))return;
        const prefetched=new Set(),domain=window.location.hostname;
        const prefetch=(url)=>{ if(prefetched.has(url))return; prefetched.add(url); const l=document.createElement('link');l.rel='prefetch';l.href=url;l.as='document';document.head.appendChild(l); };
        let timer=null;
        document.addEventListener('mouseover',(e)=>{ const link=e.target.closest('a[href]'); if(!link)return; try{const u=new URL(link.href);if(u.hostname!==domain||u.hash||u.pathname===window.location.pathname||link.href.includes('wp-admin'))return;timer=setTimeout(()=>prefetch(link.href),100);}catch{} });
        document.addEventListener('mouseout',()=>clearTimeout(timer));
        document.addEventListener('touchstart',(e)=>{ const link=e.target.closest('a[href]'); if(!link)return; try{const u=new URL(link.href);if(u.hostname===domain&&!u.hash&&!link.href.includes('wp-admin'))prefetch(link.href);}catch{} },{passive:true});
    };

    // ================================================================
    // #7 — SERIES: Animar barra de progreso al entrar en viewport
    // ================================================================
    const initSeriesProgress = () => {
        const block = document.querySelector('.series-nav-block');
        if (!block) return;

        const fill = block.querySelector('.series-progress-fill');
        if (!fill) return;

        // Guardar el ancho target y resetearlo para animar
        const targetWidth = fill.style.width;
        fill.style.width  = '0%';

        const obs = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    setTimeout(() => { fill.style.width = targetWidth; }, 150);
                    obs.unobserve(block);
                }
            });
        }, { threshold: 0.3 });
        obs.observe(block);
    };

    // ================================================================
    // #9 — SKELETON LOADING
    // ================================================================
    const initSkeletonLoading = () => {
        const skGrid   = document.getElementById('skeleton-grid');
        const realGrid = document.getElementById('posts-grid');

        if (!skGrid || !realGrid) return;

        // Si los posts reales ya están en el DOM (PHP los renderizó),
        // ocultamos los skeletons con una pequeña transición.
        const hideSkeletons = () => {
            skGrid.style.transition = 'opacity 0.4s ease';
            skGrid.style.opacity    = '0';
            setTimeout(() => { skGrid.style.display = 'none'; }, 420);
            realGrid.style.opacity    = '0';
            realGrid.style.transition = 'opacity 0.4s ease 0.1s';
            setTimeout(() => { realGrid.style.opacity = '1'; }, 50);
        };

        // Si el grid de posts tiene data-loaded, los posts ya están renderizados
        if (realGrid.dataset.loaded) {
            // Pequeño timeout para que el skeleton sea visible un instante (UX)
            setTimeout(hideSkeletons, 300);
            return;
        }

        // Si por algún motivo el grid no tiene posts, ocultar skeletons igualmente
        if (realGrid.children.length > 0) {
            setTimeout(hideSkeletons, 300);
        }
    };

    // ================================================================
    // Init
    // ================================================================
    const init = () => {
        initThemeToggle();
        initMobileMenu();
        initSmoothScroll();
        initStickyHeader();
        initScrollAnimations();
        initExternalLinks();
        initReadingProgress();
        initCodeCopy();

    // =====================================================================
    // HEADER DINÁMICO v3.0
    // =====================================================================
    const initDynamicHeader = () => {
        const header   = document.getElementById('masthead');
        const bar      = document.getElementById('reading-progress-bar');
        const navPill  = document.getElementById('nav-pill');
        const navLinks = document.querySelectorAll('.main-navigation a');

        if (!header) return;

        // ── Scroll: compactar header + progress bar ─────────────────────
        let lastScroll = 0;
        const onScroll = () => {
            const scrollY = window.scrollY;

            // Estado scrolled
            header.dataset.scrolled = scrollY > 60 ? 'true' : 'false';

            // Barra de progreso de lectura
            if (bar) {
                const docH   = document.documentElement.scrollHeight - window.innerHeight;
                const pct    = docH > 0 ? (scrollY / docH) * 100 : 0;
                bar.style.width = Math.min(pct, 100) + '%';
                bar.style.display = pct > 1 ? 'block' : 'none';
            }

            lastScroll = scrollY;
        };

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll(); // ejecutar al inicio

        // ── Nav pill deslizante ─────────────────────────────────────────
        const movePill = (el) => {
            if (!navPill || !el) return;
            const nav  = document.querySelector('.main-navigation ul');
            const navR = nav ? nav.getBoundingClientRect() : null;
            const r    = el.getBoundingClientRect();
            if (!navR) return;
            navPill.style.opacity = '1';
            navPill.style.left    = (r.left - navR.left) + 'px';
            navPill.style.width   = r.width + 'px';
        };

        const hidePill = () => {
            if (!navPill) return;
            // Si hay un elemento activo, la pill se queda en él
            const active = document.querySelector('.main-navigation .current-menu-item > a, .main-navigation .current_page_item > a');
            if (active) { movePill(active); return; }
            navPill.style.opacity = '0';
        };

        navLinks.forEach(link => {
            link.addEventListener('mouseenter', () => movePill(link));
            link.addEventListener('focus',      () => movePill(link));
        });
        document.querySelector('.main-navigation')?.addEventListener('mouseleave', hidePill);

        // Posición inicial en ítem activo
        const activeLink = document.querySelector('.main-navigation .current-menu-item > a, .main-navigation .current_page_item > a');
        if (activeLink) {
            // Pequeño delay para que el layout esté listo
            setTimeout(() => movePill(activeLink), 100);
        }

        // ── Efecto typing inicial en el logo ────────────────────────────
        const brandEl = document.getElementById('brand-text');
        if (brandEl && !sessionStorage.getItem('anhqv-typed')) {
            const text = brandEl.dataset.text || '>_ANHQV';
            brandEl.textContent = '';
            let i = 0;
            const type = () => {
                if (i < text.length) {
                    brandEl.textContent += text[i++];
                    setTimeout(type, 80 + Math.random() * 40);
                } else {
                    sessionStorage.setItem('anhqv-typed', '1');
                }
            };
            setTimeout(type, 400);
        }
    };

        initBackToTop();
        initImageLightbox();
        initTableOfContents();
        initAjaxSearch();
        initStarRating();
        initHeroParticles();
        initViewToggle();
        initFavorites();
        initNewsletter();
        initPrefetch();
        // v2.5.0
        initSeriesProgress();
        initSkeletonLoading();
        initDynamicHeader();
        console.log('ANHQV Tech v2.5.0 initialized ✓');
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
