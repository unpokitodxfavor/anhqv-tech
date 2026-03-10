<?php
get_header();

// Contar visita al cargar el post
if (is_singular('post')) {
    anhqv_track_post_views(get_the_ID());
}
?>

<main id="primary" class="site-main main-container">
    <div class="layout-grid">

        <div class="content-area">
            <div class="single-post-container">

                <?php while (have_posts()): the_post(); ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                    <header class="entry-header">
                        <div class="header-meta">
                            <span class="cat-links"><?php the_category(' '); ?></span>
                            <span class="posted-on"> / <?php echo esc_html(get_the_date()); ?></span>
                            <span class="reading-time"> / <?php echo anhqv_reading_time(); ?></span>
                            <span class="view-count"> / 👁 <?php echo number_format(anhqv_get_post_views(get_the_ID())); ?> visitas</span>
                        </div>
                        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                        <?php if (has_excerpt()): ?>
                            <div class="entry-subtitle"><?php the_excerpt(); ?></div>
                        <?php endif; ?>
                    </header>

                    <?php if (has_post_thumbnail()): ?>
                        <figure class="entry-thumbnail-full">
                            <?php the_post_thumbnail('anhqv-featured', array(
                                'alt'     => get_the_title(),
                                'loading' => 'eager',
                            )); ?>
                            <?php
                            $caption = get_post(get_post_thumbnail_id())->post_excerpt;
                            if ($caption): ?>
                                <figcaption class="image-caption"><?php echo esc_html($caption); ?></figcaption>
                            <?php endif; ?>
                        </figure>
                    <?php endif; ?>

                    <?php anhqv_social_share_buttons(); ?>

                    <?php if (is_active_sidebar('ad-before-content')): ?>
                        <div class="ad-zone ad-before">
                            <?php dynamic_sidebar('ad-before-content'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="entry-content">
                        <?php the_content(); ?>
                        <?php wp_link_pages(array('before' => '<div class="page-links">Páginas:', 'after' => '</div>')); ?>
                    </div>

                    <?php if (has_tag()): ?>
                        <div class="entry-footer-tags">
                            <?php the_tags('<span class="tags-label">Tags:</span> ', ' '); ?>
                        </div>
                    <?php endif; ?>

                    <!-- #4 VALORACIÓN CON ESTRELLAS -->
                    <!-- Botón guardar -->
                    <div class="single-save-row">
                        <button class="save-article-btn save-article-btn--large"
                                data-id="<?php the_ID(); ?>"
                                data-title="<?php echo esc_attr(get_the_title()); ?>"
                                aria-label="Guardar artículo">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                            <span class="save-btn-text">Guardar artículo</span>
                        </button>
                    </div>

                    <?php anhqv_rating_widget(); ?>

                    <?php anhqv_social_share_buttons(); ?>

                    <?php if (is_active_sidebar('ad-after-content')): ?>
                        <div class="ad-zone ad-after">
                            <?php dynamic_sidebar('ad-after-content'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="author-bio">
                        <div class="author-avatar">
                            <?php echo get_avatar(get_the_author_meta('ID'), 80); ?>
                        </div>
                        <div class="author-info">
                            <h3 class="author-name">
                                <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
                                    <?php the_author(); ?>
                                </a>
                            </h3>
                            <?php if (get_the_author_meta('description')): ?>
                                <p class="author-description"><?php echo esc_html(get_the_author_meta('description')); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                </article>

                <!-- #7 SERIE DE ARTÍCULOS -->
                <?php anhqv_series_navigation(); ?>

                <!-- Posts relacionados -->
                <?php
                $related = anhqv_get_related_posts(get_the_ID(), 3);
                if (!empty($related)): ?>
                    <section class="related-posts">
                        <h2 class="related-title">Artículos relacionados</h2>
                        <div class="related-grid">
                            <?php foreach ($related as $post):
                                setup_postdata($post); ?>
                                <article class="related-post-card">
                                    <?php if (has_post_thumbnail($post->ID)): ?>
                                        <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" class="related-thumbnail">
                                            <?php echo get_the_post_thumbnail($post->ID, 'anhqv-thumb', array('loading' => 'lazy')); ?>
                                        </a>
                                    <?php endif; ?>
                                    <div class="related-content">
                                        <div class="related-meta">
                                            <?php $cats = get_the_category($post->ID);
                                            if (!empty($cats)): ?>
                                                <span class="related-cat"><?php echo esc_html($cats[0]->name); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <h3 class="related-post-title">
                                            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                                                <?php echo esc_html(get_the_title($post->ID)); ?>
                                            </a>
                                        </h3>
                                        <span class="related-date"><?php echo esc_html(get_the_date('', $post->ID)); ?></span>
                                    </div>
                                </article>
                            <?php endforeach;
                            wp_reset_postdata(); ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Navegación entre posts -->
                <nav class="post-navigation" aria-label="Navegación de posts">
                    <div class="nav-links">
                        <?php
                        $prev = get_previous_post();
                        $next = get_next_post();
                        if ($prev): ?>
                            <div class="nav-previous">
                                <a href="<?php echo esc_url(get_permalink($prev->ID)); ?>" rel="prev">
                                    <span class="nav-subtitle">← Anterior</span>
                                    <span class="nav-title"><?php echo esc_html(get_the_title($prev->ID)); ?></span>
                                </a>
                            </div>
                        <?php endif;
                        if ($next): ?>
                            <div class="nav-next">
                                <a href="<?php echo esc_url(get_permalink($next->ID)); ?>" rel="next">
                                    <span class="nav-subtitle">Siguiente →</span>
                                    <span class="nav-title"><?php echo esc_html(get_the_title($next->ID)); ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </nav>

                <?php if (comments_open() || get_comments_number()): ?>
                    <?php comments_template(); ?>
                <?php endif; ?>

                <?php endwhile; ?>

            </div>
        </div>

        <?php get_sidebar(); ?>

    </div>
</main>

<?php get_footer(); ?>
