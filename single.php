<?php
get_header();
?>

<main id="primary" class="site-main">
    <div class="single-post-container">

        <?php
        while (have_posts()):
            the_post();
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <!-- Hero Header -->
                <header class="entry-header">
                    <div class="header-meta">
                        <span class="cat-links"><?php the_category(' '); ?></span>
                        <span class="posted-on"> / <?php echo esc_html(get_the_date()); ?></span>
                        <span class="reading-time"> / <?php echo anhqv_reading_time(); ?></span>
                    </div>
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                    
                    <?php if (has_excerpt()): ?>
                        <div class="entry-subtitle">
                            <?php the_excerpt(); ?>
                        </div>
                    <?php endif; ?>
                </header>

                <!-- Featured Image -->
                <?php if (has_post_thumbnail()): ?>
                    <figure class="entry-thumbnail-full">
                        <?php 
                        the_post_thumbnail('anhqv-featured', array(
                            'alt' => get_the_title(),
                            'loading' => 'eager' // Primera imagen no lazy
                        )); 
                        ?>
                        <?php if (get_post(get_post_thumbnail_id())->post_excerpt): ?>
                            <figcaption class="image-caption">
                                <?php echo esc_html(get_post(get_post_thumbnail_id())->post_excerpt); ?>
                            </figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endif; ?>

                <!-- Social Share (Top) -->
                <div class="share-top">
                    <?php anhqv_social_share_buttons(); ?>
                </div>

                <!-- Ad: Before Content -->
                <?php if (is_active_sidebar('ad-before-content')): ?>
                    <div class="ad-zone ad-before">
                        <?php dynamic_sidebar('ad-before-content'); ?>
                    </div>
                <?php endif; ?>

                <!-- Content -->
                <div class="entry-content">
                    <?php
                    the_content();

                    wp_link_pages(
                        array(
                            'before' => '<div class="page-links">' . esc_html__('Pages:', 'anhqv-tech'),
                            'after' => '</div>',
                        )
                    );
                    ?>
                </div>

                <!-- Tags -->
                <?php if (has_tag()): ?>
                    <div class="entry-footer-tags">
                        <?php the_tags('<span class="tags-label">' . esc_html__('Tags:', 'anhqv-tech') . '</span> ', ' '); ?>
                    </div>
                <?php endif; ?>

                <!-- Social Share (Bottom) -->
                <div class="share-bottom">
                    <?php anhqv_social_share_buttons(); ?>
                </div>

                <!-- Ad: After Content -->
                <?php if (is_active_sidebar('ad-after-content')): ?>
                    <div class="ad-zone ad-after">
                        <?php dynamic_sidebar('ad-after-content'); ?>
                    </div>
                <?php endif; ?>

                <!-- Author Bio -->
                <div class="author-bio">
                    <div class="author-avatar">
                        <?php echo get_avatar(get_the_author_meta('ID'), 80); ?>
                    </div>
                    <div class="author-info">
                        <h3 class="author-name">
                            <?php 
                            printf(
                                esc_html__('Written by %s', 'anhqv-tech'),
                                '<a href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a>'
                            ); 
                            ?>
                        </h3>
                        <?php if (get_the_author_meta('description')): ?>
                            <p class="author-description"><?php echo esc_html(get_the_author_meta('description')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

            </article>

            <!-- Related Posts -->
            <?php
            $related_posts = anhqv_get_related_posts(get_the_ID(), 3);
            if (!empty($related_posts)):
                ?>
                <section class="related-posts">
                    <h2 class="related-title"><?php esc_html_e('Related Articles', 'anhqv-tech'); ?></h2>
                    <div class="related-grid">
                        <?php foreach ($related_posts as $related_post):
                            setup_postdata($related_post);
                            ?>
                            <article class="related-post-card">
                                <?php if (has_post_thumbnail($related_post->ID)): ?>
                                    <a href="<?php echo esc_url(get_permalink($related_post->ID)); ?>" class="related-thumbnail">
                                        <?php echo get_the_post_thumbnail($related_post->ID, 'anhqv-thumb', array('loading' => 'lazy')); ?>
                                    </a>
                                <?php endif; ?>
                                <div class="related-content">
                                    <div class="related-meta">
                                        <?php
                                        $categories = get_the_category($related_post->ID);
                                        if (!empty($categories)) {
                                            echo '<span class="related-cat">' . esc_html($categories[0]->name) . '</span>';
                                        }
                                        ?>
                                    </div>
                                    <h3 class="related-post-title">
                                        <a href="<?php echo esc_url(get_permalink($related_post->ID)); ?>">
                                            <?php echo esc_html(get_the_title($related_post->ID)); ?>
                                        </a>
                                    </h3>
                                    <time class="related-date" datetime="<?php echo esc_attr(get_the_date('c', $related_post->ID)); ?>">
                                        <?php echo esc_html(get_the_date('', $related_post->ID)); ?>
                                    </time>
                                </div>
                            </article>
                        <?php endforeach;
                        wp_reset_postdata();
                        ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Navigation between posts -->
            <nav class="post-navigation" aria-label="<?php esc_attr_e('Post navigation', 'anhqv-tech'); ?>">
                <div class="nav-links">
                    <?php
                    $prev_post = get_previous_post();
                    $next_post = get_next_post();

                    if ($prev_post):
                        ?>
                        <div class="nav-previous">
                            <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>" rel="prev">
                                <span class="nav-subtitle"><?php esc_html_e('Previous', 'anhqv-tech'); ?></span>
                                <span class="nav-title"><?php echo esc_html(get_the_title($prev_post->ID)); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($next_post): ?>
                        <div class="nav-next">
                            <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>" rel="next">
                                <span class="nav-subtitle"><?php esc_html_e('Next', 'anhqv-tech'); ?></span>
                                <span class="nav-title"><?php echo esc_html(get_the_title($next_post->ID)); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </nav>

            <!-- Comments -->
            <?php
            if (comments_open() || get_comments_number()):
                comments_template();
            endif;
            ?>

        <?php endwhile; ?>

    </div>
</main>

<?php
get_footer();
