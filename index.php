<?php get_header(); ?>

<main id="primary" class="site-main">

    <?php if (is_home() && !is_paged()): ?>
    <!-- ============================================================
         HERO SECTION — Post más reciente a pantalla completa
         ============================================================ -->
    <?php
    $hero_query = new WP_Query(array(
        'posts_per_page'      => 10,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => 1,
        'no_found_rows'       => true,
    ));
    if ($hero_query->have_posts()):
        // Pick a random post from the 10 retrieved
        $random_index = rand(0, $hero_query->post_count - 1);
        $hero_query->current_post = $random_index - 1; 
        $hero_query->the_post();
        $hero_thumb = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'full') : '';
        $hero_cats  = get_the_category();
    ?>
    <section class="hero-section"
             <?php if ($hero_thumb): ?>style="--hero-bg: url('<?php echo esc_url($hero_thumb); ?>')"<?php endif; ?>
             aria-label="Artículo destacado">
        <div class="hero-overlay"></div>
        <div class="hero-particles" aria-hidden="true">
            <?php for ($i = 0; $i < 20; $i++): ?>
                <span class="hero-particle"></span>
            <?php endfor; ?>
        </div>
        <div class="hero-content">
            <?php if (!empty($hero_cats)): ?>
                <a href="<?php echo esc_url(get_category_link($hero_cats[0]->term_id)); ?>"
                   class="hero-category">
                    <?php echo esc_html($hero_cats[0]->name); ?>
                </a>
            <?php endif; ?>

            <h1 class="hero-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h1>

            <p class="hero-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 22); ?></p>

            <div class="hero-meta">
                <span><?php echo get_the_date(); ?></span>
                <span class="hero-sep">·</span>
                <span><?php echo anhqv_reading_time(); ?></span>
                <span class="hero-sep">·</span>
                <span>👁 <?php echo number_format(anhqv_get_post_views(get_the_ID())); ?></span>
            </div>

            <div class="hero-actions">
                <a href="<?php the_permalink(); ?>" class="hero-btn-primary">
                    Leer artículo
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                         viewBox="0 0 24 24" aria-hidden="true">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </a>
                <button class="hero-btn-save save-article-btn"
                        data-id="<?php the_ID(); ?>"
                        data-title="<?php echo esc_attr(get_the_title()); ?>"
                        aria-label="Guardar artículo">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/>
                    </svg>
                    <span>Guardar</span>
                </button>
            </div>
        </div>

        <!-- Scroll indicator -->
        <div class="hero-scroll-hint" aria-hidden="true">
            <span>Scroll</span>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
    </section>
    <?php
    wp_reset_postdata();
    endif;
    ?>
    <?php endif; ?>

    <!-- ============================================================
         CONTENIDO PRINCIPAL
         ============================================================ -->
    <div class="main-container">
        <div class="layout-grid<?php echo !is_active_sidebar('sidebar-1') ? ' no-sidebar' : ''; ?>">

            <div class="content-area">

                <?php if (have_posts()): ?>

                <?php
                $sticky_ids = get_option('sticky_posts');
                $has_sticky = !empty($sticky_ids) && is_home() && !is_paged();
                ?>

                <?php if ($has_sticky): ?>
                <div class="sticky-posts-section">
                    <h2 class="section-label"><span>📌 Artículos destacados</span></h2>
                    <div class="sticky-posts-grid">
                        <?php
                        $sq = new WP_Query(array(
                            'post__in'            => $sticky_ids,
                            'post_status'         => 'publish',
                            'posts_per_page'      => count($sticky_ids),
                            'ignore_sticky_posts' => 1,
                        ));
                        while ($sq->have_posts()): $sq->the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('post-card post-card--sticky'); ?>>
                            <span class="sticky-badge">📌 Destacado</span>
                            <?php if (has_post_thumbnail()): ?>
                                <div class="post-thumbnail">
                                    <a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                                        <?php the_post_thumbnail('anhqv-featured', array('loading' => 'lazy')); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="post-content">
                                <div class="post-category">
                                    <?php $cats = get_the_category();
                                    if (!empty($cats)) echo '<a href="' . esc_url(get_category_link($cats[0]->term_id)) . '">' . esc_html($cats[0]->name) . '</a>'; ?>
                                </div>
                                <h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                <p class="post-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                                <div class="post-meta">
                                    <span><?php echo get_the_date(); ?></span> ·
                                    <span><?php echo anhqv_reading_time(); ?></span> ·
                                    <span>👁 <?php echo number_format(anhqv_get_post_views(get_the_ID())); ?></span>
                                </div>
                            </div>
                        </article>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </div>
                <?php endif; ?>


                <!-- #8 SKELETON LOADING — se ocultan cuando los posts reales cargan -->
                <div class="posts-grid skeleton-grid" id="skeleton-grid" aria-hidden="true">
                    <?php for ($sk = 0; $sk < 6; $sk++): ?>
                    <div class="skeleton-card">
                        <div class="skeleton-thumb"></div>
                        <div class="skeleton-body">
                            <div class="skeleton-line sk-cat"></div>
                            <div class="skeleton-line sk-t1"></div>
                            <div class="skeleton-line sk-t2"></div>
                            <div class="skeleton-line sk-ex1"></div>
                            <div class="skeleton-line sk-ex2"></div>
                            <div class="skeleton-line sk-meta"></div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>

                <!-- Toolbar: título sección + toggle vista -->
                <div class="posts-toolbar">
                    <?php if ($has_sticky): ?>
                        <h2 class="section-label"><span>🗞️ Últimos artículos</span></h2>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>

                    <!-- #2 TOGGLE REVISTA / LISTA -->
                    <div class="view-toggle" role="group" aria-label="Cambiar vista">
                        <button class="view-toggle-btn is-active" data-view="grid"
                                aria-pressed="true" title="Vista en rejilla">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                                <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                            </svg>
                            Revista
                        </button>
                        <button class="view-toggle-btn" data-view="list"
                                aria-pressed="false" title="Vista en lista">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24" aria-hidden="true">
                                <line x1="8" y1="6"  x2="21" y2="6"/>
                                <line x1="8" y1="12" x2="21" y2="12"/>
                                <line x1="8" y1="18" x2="21" y2="18"/>
                                <line x1="3" y1="6"  x2="3.01" y2="6"/>
                                <line x1="3" y1="12" x2="3.01" y2="12"/>
                                <line x1="3" y1="18" x2="3.01" y2="18"/>
                            </svg>
                            Lista
                        </button>
                    </div>
                </div>

                <div class="posts-grid" id="posts-grid" data-loaded="true">
                    <?php while (have_posts()): the_post();
                        if (is_sticky() && is_home() && !is_paged()) continue; ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?>>
                        <?php if (has_post_thumbnail()): ?>
                            <div class="post-thumbnail">
                                <a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                                    <?php the_post_thumbnail('anhqv-featured', array('loading' => 'lazy')); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="post-content">
                            <div class="post-category">
                                <?php $cats = get_the_category();
                                if (!empty($cats)) echo '<a href="' . esc_url(get_category_link($cats[0]->term_id)) . '">' . esc_html($cats[0]->name) . '</a>'; ?>
                            </div>
                            <h2 class="post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <p class="post-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                            <?php $tags = get_the_tags();
                            if ($tags): ?>
                                <div class="card-tags">
                                    <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                                        <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>">#<?php echo esc_html($tag->name); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="post-meta-row">
                                <div class="post-meta">
                                    <span><?php echo get_the_date(); ?></span> ·
                                    <span><?php echo anhqv_reading_time(); ?></span> ·
                                    <span>👁 <?php echo number_format(anhqv_get_post_views(get_the_ID())); ?></span>
                                </div>
                                <!-- #3 BOTÓN GUARDAR -->
                                <button class="save-article-btn card-save-btn"
                                        data-id="<?php the_ID(); ?>"
                                        data-title="<?php echo esc_attr(get_the_title()); ?>"
                                        aria-label="Guardar artículo">
                                    <svg width="14" height="14" fill="none" stroke="currentColor"
                                         stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </article>
                    <?php endwhile; ?>
                </div>

                <?php the_posts_pagination(array('prev_text' => '← Anterior', 'next_text' => 'Siguiente →')); ?>

                <?php else: ?>
                    <p class="no-posts">No se encontraron artículos.</p>
                <?php endif; ?>

            </div><!-- .content-area -->
            <?php get_sidebar(); ?>
        </div>
    </div>

</main>
<?php get_footer(); ?>
