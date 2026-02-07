<?php
get_header();
?>

<main id="primary" class="site-main main-container">

    <div class="layout-grid">

        <div class="content-area">
            <?php if (have_posts()): ?>

                <div class="posts-grid">
                    <?php
                    while (have_posts()):
                        the_post();
                        ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?>>
                            <?php if (has_post_thumbnail()): ?>
                                <div class="post-thumbnail">
                                    <a href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
                                        <?php the_post_thumbnail('anhqv-thumb', array('loading' => 'lazy')); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="post-content">
                                <div class="post-category">
                                    <?php
                                    $categories = get_the_category();
                                    if (!empty($categories)) {
                                        echo '<a href="' . esc_url(get_category_link($categories[0]->term_id)) . '">' . esc_html($categories[0]->name) . '</a>';
                                    }
                                    ?>
                                </div>

                                <h2 class="post-title">
                                    <a href="<?php the_permalink(); ?>" rel="bookmark">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>

                                <div class="post-excerpt">
                                    <?php the_excerpt(); ?>
                                </div>

                                <?php if (has_tag()): ?>
                                    <div class="card-tags">
                                        <?php
                                        $tags = get_the_tags();
                                        if ($tags) {
                                            $count = 0;
                                            foreach ($tags as $tag) {
                                                if ($count >= 3) break; // Limitar a 3 tags
                                                echo '<a href="' . esc_url(get_tag_link($tag->term_id)) . '">' . esc_html($tag->name) . '</a>';
                                                $count++;
                                            }
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>

                                <div class="post-meta">
                                    <time class="posted-on" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                        <?php echo esc_html(get_the_date()); ?>
                                    </time>
                                    <?php if (function_exists('anhqv_reading_time')): ?>
                                        <span class="sep"> • </span>
                                        <span class="reading-time"><?php echo anhqv_reading_time(); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article><!-- #post-<?php the_ID(); ?> -->

                    <?php endwhile; ?>
                </div><!-- .posts-grid -->

                <?php
                // Paginación moderna
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => __('← Previous', 'anhqv-tech'),
                    'next_text' => __('Next →', 'anhqv-tech'),
                ));
                ?>

            <?php else: ?>

                <section class="no-results not-found">
                    <header class="page-header">
                        <h1 class="page-title"><?php esc_html_e('Nothing Found', 'anhqv-tech'); ?></h1>
                    </header>
                    <div class="page-content">
                        <p><?php esc_html_e('It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'anhqv-tech'); ?></p>
                        <?php get_search_form(); ?>
                    </div>
                </section>

            <?php endif; ?>
        </div> <!-- .content-area -->

        <?php get_sidebar(); ?>

    </div> <!-- .layout-grid -->

</main><!-- #main -->

<?php
get_footer();
