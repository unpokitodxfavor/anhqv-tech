<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a0f1e" id="theme-color-meta">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary">Saltar al contenido</a>

<!-- Barra de progreso de lectura -->
<div id="reading-progress" class="reading-progress" aria-hidden="true">
    <div id="reading-progress-bar" class="reading-progress-bar"></div>
</div>

<header id="masthead" class="site-header" data-scrolled="false">

    <!-- Línea de acento animada superior -->
    <div class="header-accent-line" aria-hidden="true"></div>

    <div class="header-container">

        <!-- BRANDING -->
        <div class="site-branding">
            <?php if (has_custom_logo()): the_custom_logo();
            else: ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" class="logo-link" aria-label="<?php bloginfo('name'); ?>">
                    <span class="logo-bracket" aria-hidden="true">[</span>
                    <span class="brand-text" id="brand-text" data-text=">_ANHQV">>_ANHQV</span>
                    <span class="logo-bracket" aria-hidden="true">]</span>
                    <span class="logo-cursor" aria-hidden="true">█</span>
                </a>
            <?php endif; ?>

            <span class="site-tagline" aria-label="<?php bloginfo('description'); ?>">
                <?php bloginfo('description'); ?>
            </span>
        </div>

        <!-- NAVEGACIÓN -->
        <nav id="site-navigation" class="main-navigation" aria-label="Navegación principal">
            <!-- Pill deslizante -->
            <span class="nav-pill" id="nav-pill" aria-hidden="true"></span>
            <?php wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_id'        => 'primary-menu',
                'container'      => false,
                'fallback_cb'    => false,
            )); ?>
        </nav>

        <!-- ACCIONES -->
        <div class="header-actions">

            <!-- Búsqueda -->
            <button id="search-toggle" class="header-action-btn search-toggle-btn" type="button"
                    aria-label="Abrir búsqueda" aria-expanded="false" aria-controls="ajax-search-panel">
                <svg class="icon-search-open" width="18" height="18" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <svg class="icon-search-close" width="18" height="18" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>

            <!-- Favoritos -->
            <a href="<?php echo esc_url(home_url('/favoritos/')); ?>"
               class="header-action-btn favorites-header-btn"
               aria-label="Artículos guardados" title="Mis favoritos">
                <svg width="18" height="18" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/>
                </svg>
                <span class="favorites-count" id="favorites-count" style="display:none">0</span>
            </a>

            <!-- Dark / Light -->
            <button id="theme-toggle" class="header-action-btn theme-toggle" type="button"
                    aria-label="Cambiar modo claro/oscuro">
                <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
                <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1"  x2="12" y2="3"/>
                    <line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22"  y1="4.22"  x2="5.64"  y2="5.64"/>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1"  y1="12" x2="3"  y2="12"/>
                    <line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22"  y1="19.78" x2="5.64"  y2="18.36"/>
                    <line x1="18.36" y1="5.64"  x2="19.78" y2="4.22"/>
                </svg>
            </button>

            <!-- Menú móvil -->
            <button class="mobile-menu-toggle" aria-label="Abrir menú" aria-expanded="false">
                <span class="hamburger">
                    <span class="line"></span>
                    <span class="line"></span>
                    <span class="line"></span>
                </span>
            </button>

        </div>
    </div>
</header>

<!-- Panel búsqueda AJAX -->
<div id="ajax-search-panel" class="ajax-search-panel" aria-hidden="true" role="search">
    <div class="ajax-search-inner">
        <div class="ajax-search-field-wrap">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" class="ajax-search-icon" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="search" id="ajax-search-input" class="ajax-search-input"
                   placeholder="Buscar artículos, temas, categorías..."
                   autocomplete="off" aria-label="Buscar en el sitio" spellcheck="false">
            <span class="ajax-search-shortcut" aria-hidden="true">ESC para cerrar</span>
        </div>
        <div id="ajax-search-results" class="ajax-search-results" aria-live="polite"></div>
    </div>
</div>
<div class="ajax-search-overlay" aria-hidden="true"></div>

<?php if (!is_front_page() && function_exists('anhqv_breadcrumbs')): ?>
    <div class="breadcrumb-wrapper"><?php anhqv_breadcrumbs(); ?></div>
<?php endif; ?>
