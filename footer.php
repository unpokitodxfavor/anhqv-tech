    <footer id="colophon" class="site-footer">
        <div class="site-info">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>.
            </a>
            <span class="sep"> | </span>
            <span><?php bloginfo('description'); ?></span>
            
            <?php if (has_nav_menu('footer')): ?>
                <nav class="footer-navigation" aria-label="<?php esc_attr_e('Footer Navigation', 'anhqv-tech'); ?>">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'menu_id' => 'footer-menu',
                        'container' => false,
                        'depth' => 1,
                    ));
                    ?>
                </nav>
            <?php endif; ?>
        </div><!-- .site-info -->
    </footer>

    <?php wp_footer(); ?>

</body>

</html>
