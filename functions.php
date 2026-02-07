<?php
/**
 * ANHQV Tech functions and definitions
 * Version: 2.0.0 - Modernized
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Setup
 */
function anhqv_setup()
{
    // Text domain para traducciones
    load_theme_textdomain('anhqv-tech', get_template_directory() . '/languages');

    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails
    add_theme_support('post-thumbnails');
    
    // Tamaños de imagen personalizados
    add_image_size('anhqv-featured', 1200, 675, true); // 16:9
    add_image_size('anhqv-thumb', 600, 400, true);

    // Logo support
    add_theme_support('custom-logo', array(
        'height' => 60,
        'width' => 200,
        'flex-height' => true,
        'flex-width' => true,
    ));

    // HTML5 support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    // Responsive embeds
    add_theme_support('responsive-embeds');

    // Editor styles
    add_theme_support('editor-styles');
    add_editor_style('style.css');

    // Register navigation menus
    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'anhqv-tech'),
        'footer' => esc_html__('Footer Menu', 'anhqv-tech'),
    ));
}
add_action('after_setup_theme', 'anhqv_setup');

/**
 * Enqueue scripts and styles
 */
function anhqv_tech_scripts()
{
    // Preconnect to external resources
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';

    // Google Fonts (Outfit + Inter)
    wp_enqueue_style(
        'anhqv-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;800&display=swap',
        array(),
        null
    );

    // Main Stylesheet
    wp_enqueue_style(
        'anhqv-style',
        get_stylesheet_uri(),
        array(),
        filemtime(get_template_directory() . '/style.css')
    );

    // Main JavaScript
    wp_enqueue_script(
        'anhqv-main',
        get_template_directory_uri() . '/js/main.js',
        array(),
        filemtime(get_template_directory() . '/js/main.js'),
        true
    );

    // Localize script for AJAX
    wp_localize_script('anhqv-main', 'anhqvData', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('anhqv-nonce'),
    ));

    // Comments script
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'anhqv_tech_scripts');

/**
 * Register Widget Areas
 */
function anhqv_widgets_init()
{
    // Ad: Before Content
    register_sidebar(array(
        'name' => esc_html__('Ad: Before Content', 'anhqv-tech'),
        'id' => 'ad-before-content',
        'description' => esc_html__('Add your Amazon/AdSense code here. Displays before the article text.', 'anhqv-tech'),
        'before_widget' => '<div id="%1$s" class="widget ad-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title" style="display:none;">',
        'after_title' => '</h3>',
    ));

    // Ad: After Content
    register_sidebar(array(
        'name' => esc_html__('Ad: After Content', 'anhqv-tech'),
        'id' => 'ad-after-content',
        'description' => esc_html__('Add your Amazon/AdSense code here. Displays after the article text.', 'anhqv-tech'),
        'before_widget' => '<div id="%1$s" class="widget ad-widget %2$s">',
        'after_widget' => '</div>',
    ));

    // Sidebar Home
    register_sidebar(array(
        'name' => esc_html__('Sidebar Home', 'anhqv-tech'),
        'id' => 'sidebar-1',
        'description' => esc_html__('Add widgets here (e.g. Friends Links), appears on the right.', 'anhqv-tech'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

    // Footer Widgets
    for ($i = 1; $i <= 3; $i++) {
        register_sidebar(array(
            'name' => sprintf(esc_html__('Footer %d', 'anhqv-tech'), $i),
            'id' => 'footer-' . $i,
            'description' => sprintf(esc_html__('Footer widget area %d', 'anhqv-tech'), $i),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widget-title">',
            'after_title' => '</h3>',
        ));
    }
}
add_action('widgets_init', 'anhqv_widgets_init');

/**
 * Modify Main Query
 */
function anhqv_modify_query($query)
{
    if ($query->is_home() && $query->is_main_query() && !is_admin()) {
        $query->set('posts_per_page', 12);
    }
}
add_action('pre_get_posts', 'anhqv_modify_query');

/**
 * Custom excerpt length
 */
function anhqv_excerpt_length($length)
{
    return 25;
}
add_filter('excerpt_length', 'anhqv_excerpt_length');

/**
 * Custom excerpt more
 */
function anhqv_excerpt_more($more)
{
    return '...';
}
add_filter('excerpt_more', 'anhqv_excerpt_more');

/**
 * Add lazy loading to images
 */
function anhqv_add_lazy_loading($attr)
{
    if (!is_admin() && !is_feed()) {
        $attr['loading'] = 'lazy';
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'anhqv_add_lazy_loading');

/**
 * Breadcrumbs function
 */
function anhqv_breadcrumbs()
{
    if (is_front_page()) {
        return;
    }

    $separator = '<span class="breadcrumb-separator">/</span>';
    $home_title = esc_html__('Home', 'anhqv-tech');

    echo '<nav class="breadcrumbs" aria-label="' . esc_attr__('Breadcrumb', 'anhqv-tech') . '">';
    echo '<ol class="breadcrumb-list">';
    echo '<li class="breadcrumb-item"><a href="' . esc_url(home_url('/')) . '">' . $home_title . '</a></li>';
    echo $separator;

    if (is_category() || is_single()) {
        $category = get_the_category();
        if (!empty($category)) {
            $cat = $category[0];
            echo '<li class="breadcrumb-item"><a href="' . esc_url(get_category_link($cat->term_id)) . '">' . esc_html($cat->name) . '</a></li>';
            if (is_single()) {
                echo $separator;
            }
        }
    }

    if (is_single()) {
        echo '<li class="breadcrumb-item active" aria-current="page">' . get_the_title() . '</li>';
    } elseif (is_page()) {
        echo '<li class="breadcrumb-item active" aria-current="page">' . get_the_title() . '</li>';
    } elseif (is_search()) {
        echo '<li class="breadcrumb-item active" aria-current="page">' . esc_html__('Search Results', 'anhqv-tech') . '</li>';
    } elseif (is_404()) {
        echo '<li class="breadcrumb-item active" aria-current="page">' . esc_html__('404 Not Found', 'anhqv-tech') . '</li>';
    }

    echo '</ol>';
    echo '</nav>';
}

/**
 * Get related posts
 */
function anhqv_get_related_posts($post_id, $number_posts = 3)
{
    $categories = get_the_category($post_id);

    if (empty($categories)) {
        return array();
    }

    $category_ids = array();
    foreach ($categories as $category) {
        $category_ids[] = $category->term_id;
    }

    $args = array(
        'category__in' => $category_ids,
        'post__not_in' => array($post_id),
        'posts_per_page' => $number_posts,
        'orderby' => 'rand',
        'ignore_sticky_posts' => 1,
    );

    return get_posts($args);
}

/**
 * Social Share Buttons
 */
function anhqv_social_share_buttons()
{
    $url = urlencode(get_permalink());
    $title = urlencode(get_the_title());

    ?>
    <div class="social-share">
        <span class="share-label"><?php esc_html_e('Share:', 'anhqv-tech'); ?></span>
        <a href="https://twitter.com/intent/tweet?url=<?php echo $url; ?>&text=<?php echo $title; ?>" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="share-btn twitter"
           aria-label="<?php esc_attr_e('Share on Twitter', 'anhqv-tech'); ?>">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"></path></svg>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $url; ?>" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="share-btn facebook"
           aria-label="<?php esc_attr_e('Share on Facebook', 'anhqv-tech'); ?>">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"></path></svg>
        </a>
        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url; ?>&title=<?php echo $title; ?>" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="share-btn linkedin"
           aria-label="<?php esc_attr_e('Share on LinkedIn', 'anhqv-tech'); ?>">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"></path><circle cx="4" cy="4" r="2"></circle></svg>
        </a>
        <a href="https://wa.me/?text=<?php echo $title . ' ' . $url; ?>" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="share-btn whatsapp"
           aria-label="<?php esc_attr_e('Share on WhatsApp', 'anhqv-tech'); ?>">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"></path></svg>
        </a>
    </div>
    <?php
}

/**
 * Reading Time Calculator
 */
function anhqv_reading_time()
{
    $content = get_post_field('post_content', get_the_ID());
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // Asumiendo 200 palabras por minuto

    if ($reading_time < 1) {
        $reading_time = 1;
    }

    return sprintf(
        esc_html(_n('%s min read', '%s min read', $reading_time, 'anhqv-tech')),
        $reading_time
    );
}

/**
 * Add Open Graph Meta Tags
 */
function anhqv_add_og_tags()
{
    if (is_single() || is_page()) {
        global $post;
        $og_title = get_the_title();
        $og_description = get_the_excerpt();
        $og_url = get_permalink();
        $og_image = has_post_thumbnail() ? get_the_post_thumbnail_url($post->ID, 'full') : '';

        echo '<meta property="og:title" content="' . esc_attr($og_title) . '" />';
        echo '<meta property="og:description" content="' . esc_attr($og_description) . '" />';
        echo '<meta property="og:url" content="' . esc_url($og_url) . '" />';
        echo '<meta property="og:type" content="article" />';
        if ($og_image) {
            echo '<meta property="og:image" content="' . esc_url($og_image) . '" />';
        }
        echo '<meta name="twitter:card" content="summary_large_image" />';
    }
}
add_action('wp_head', 'anhqv_add_og_tags');

/**
 * Add Schema.org markup for articles
 */
function anhqv_add_schema_markup()
{
    if (is_single()) {
        global $post;
        $schema = array(
            "@context" => "https://schema.org",
            "@type" => "Article",
            "headline" => get_the_title(),
            "datePublished" => get_the_date('c'),
            "dateModified" => get_the_modified_date('c'),
            "author" => array(
                "@type" => "Person",
                "name" => get_the_author()
            ),
            "publisher" => array(
                "@type" => "Organization",
                "name" => get_bloginfo('name'),
                "logo" => array(
                    "@type" => "ImageObject",
                    "url" => get_site_icon_url()
                )
            )
        );

        if (has_post_thumbnail()) {
            $schema["image"] = get_the_post_thumbnail_url($post->ID, 'full');
        }

        echo '<script type="application/ld+json">' . json_encode($schema) . '</script>';
    }
}
add_action('wp_head', 'anhqv_add_schema_markup');
