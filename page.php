<?php
get_header();
?>

<main id="primary" class="site-main main-container">
    <div class="layout-grid">
        <div class="content-area">
            <div class="single-post-container">

                <?php
                while (have_posts()):
                    the_post();
                    ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                        <!-- Page Header -->
                        <header class="entry-header">
                            <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                        </header>

                        <!-- Featured Image (Optional for pages) -->
                        <?php if (has_post_thumbnail()): ?>
                            <div class="entry-thumbnail-full">
                                <?php the_post_thumbnail('full'); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Content -->
                        <div class="entry-content">
                            <?php
                            // Display full content
                            the_content();

                            wp_link_pages(
                                array(
                                    'before' => '<div class="page-links">' . esc_html__('Pages:', 'anhqv-tech'),
                                    'after' => '</div>',
                                )
                            );
                            ?>
                        </div>

                    </article>

                <?php endwhile; // End of the loop. ?>

            </div> <!-- .single-post-container -->
        </div> <!-- .content-area -->

        <?php get_sidebar(); ?>

    </div> <!-- .layout-grid -->
</main>


<?php
get_footer();
