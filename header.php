<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <!-- Skip to content link para accesibilidad -->
    <a class="skip-link screen-reader-text" href="#primary">
        <?php esc_html_e('Skip to content', 'anhqv-tech'); ?>
    </a>

    <header id="masthead" class="site-header">
        <div class="header-container">
            <div class="site-branding">
                <?php
                if (has_custom_logo()) {
                    the_custom_logo();
                } else {
                    ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" class="logo-link">
                        <?php if (file_exists(get_template_directory() . '/images/logo.png')): ?>
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/images/logo.png'); ?>"
                                alt="<?php bloginfo('name'); ?>" class="site-logo">
                        <?php endif; ?>
                        <span class="brand-text">>_ANHQV</span>
                        <span class="site-tagline"><?php bloginfo('description'); ?></span>
                    </a>
                    <?php
                }
                ?>
            </div><!-- .site-branding -->

            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" aria-label="<?php esc_attr_e('Toggle menu', 'anhqv-tech'); ?>" aria-expanded="false">
                <span class="hamburger">
                    <span class="line"></span>
                    <span class="line"></span>
                    <span class="line"></span>
                </span>
            </button>

            <nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e('Primary Navigation', 'anhqv-tech'); ?>">
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'primary',
                        'menu_id' => 'primary-menu',
                        'container' => false,
                        'fallback_cb' => 'anhqv_default_menu',
                    )
                );
                ?>
            </nav><!-- #site-navigation -->
        </div>
    </header><!-- #masthead -->

    <?php
    // Mostrar breadcrumbs en páginas internas
    if (!is_front_page() && function_exists('anhqv_breadcrumbs')) {
        echo '<div class="breadcrumb-wrapper">';
        anhqv_breadcrumbs();
        echo '</div>';
    }
    ?>
