<?php
/**
 * ANHQV Tech functions and definitions
 * Version: 2.1.0 - Performance & SEO Update
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
    load_theme_textdomain('anhqv-tech', get_template_directory() . '/languages');
    add_theme_support('automatic-feed-links');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    add_image_size('anhqv-featured', 1200, 675, true); // 16:9
    add_image_size('anhqv-thumb', 600, 400, true);

    add_theme_support('custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_editor_style('style.css');

    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'anhqv-tech'),
        'footer'  => esc_html__('Footer Menu', 'anhqv-tech'),
    ));
}
add_action('after_setup_theme', 'anhqv_setup');


// =============================================================================
// FIX #1 — GOOGLE FONTS ASÍNCRONO (mejora LCP / Core Web Vitals)
// =============================================================================

/**
 * Elimina el enqueue estándar de Google Fonts para cargarlo de forma asíncrona
 * y no bloqueante directamente en wp_head con la técnica loadCSS.
 */
function anhqv_tech_scripts()
{
    // ── PRECONNECT a recursos externos (se añaden en anhqv_preconnect) ──────

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

    wp_localize_script('anhqv-main', 'anhqvData', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('anhqv-nonce'),
    ));

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'anhqv_tech_scripts');

/**
 * Preconnect + carga asíncrona de Google Fonts (no bloqueante).
 * Se inserta con prioridad 1 para que aparezca lo antes posible en <head>.
 */
function anhqv_preconnect_and_async_fonts()
{
    $fonts_url = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;800&display=swap';
    ?>
    <!-- ANHQV: Preconnect + Async Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style"
          href="<?php echo esc_url($fonts_url); ?>"
          onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="<?php echo esc_url($fonts_url); ?>">
    </noscript>
    <?php
}
add_action('wp_head', 'anhqv_preconnect_and_async_fonts', 1);


// =============================================================================
// FIX #2 — CANONICAL URL (evita contenido duplicado)
// =============================================================================

function anhqv_canonical_tag()
{
    if (is_singular()) {
        echo '<link rel="canonical" href="' . esc_url(get_permalink()) . '" />' . "\n";
    } elseif (is_home() || is_front_page()) {
        echo '<link rel="canonical" href="' . esc_url(home_url('/')) . '" />' . "\n";
    }
}
add_action('wp_head', 'anhqv_canonical_tag', 2);


// =============================================================================
// FIX #3 — META DESCRIPTION + OPEN GRAPH (bug del loop corregido)
// =============================================================================

function anhqv_add_og_tags()
{
    if (!is_singular()) {
        return;
    }

    global $post;

    // setup_postdata para que get_the_excerpt() funcione fuera del loop
    $original_post = $post;
    setup_postdata($post);

    $og_title       = get_the_title();
    $og_url         = get_permalink();
    $og_site_name   = get_bloginfo('name');
    $og_locale      = get_locale();

    // Descripción: excerpt manual → excerpt guardado → primeras palabras del contenido
    if ($post->post_excerpt) {
        $og_description = wp_strip_all_tags($post->post_excerpt);
    } else {
        $og_description = wp_trim_words(
            wp_strip_all_tags(strip_shortcodes($post->post_content)),
            30,
            '...'
        );
    }

    // Imagen OG
    $og_image = '';
    if (has_post_thumbnail()) {
        $og_image = get_the_post_thumbnail_url($post->ID, 'anhqv-featured');
    }

    // Tipo OG
    $og_type = is_page() ? 'website' : 'article';

    // ── Meta Description estándar (usada por Google en snippets) ────────────
    echo '<meta name="description" content="' . esc_attr($og_description) . '" />' . "\n";

    // ── Open Graph ───────────────────────────────────────────────────────────
    echo '<meta property="og:title"       content="' . esc_attr($og_title) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($og_description) . '" />' . "\n";
    echo '<meta property="og:url"         content="' . esc_url($og_url) . '" />' . "\n";
    echo '<meta property="og:type"        content="' . esc_attr($og_type) . '" />' . "\n";
    echo '<meta property="og:site_name"   content="' . esc_attr($og_site_name) . '" />' . "\n";
    echo '<meta property="og:locale"      content="' . esc_attr($og_locale) . '" />' . "\n";

    if ($og_image) {
        echo '<meta property="og:image"       content="' . esc_url($og_image) . '" />' . "\n";
        echo '<meta property="og:image:width" content="1200" />' . "\n";
        echo '<meta property="og:image:height" content="675" />' . "\n";
    }

    // ── Twitter / X Card ─────────────────────────────────────────────────────
    echo '<meta name="twitter:card"        content="summary_large_image" />' . "\n";
    echo '<meta name="twitter:title"       content="' . esc_attr($og_title) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($og_description) . '" />' . "\n";
    if ($og_image) {
        echo '<meta name="twitter:image"   content="' . esc_url($og_image) . '" />' . "\n";
    }

    wp_reset_postdata();
    $post = $original_post;
}
add_action('wp_head', 'anhqv_add_og_tags', 5);


// =============================================================================
// FIX #4 — SCHEMA.ORG COMPLETO (para Rich Results en Google)
// =============================================================================

function anhqv_add_schema_markup()
{
    if (!is_single()) {
        return;
    }

    global $post;
    setup_postdata($post);

    $word_count   = str_word_count(strip_tags($post->post_content));
    $image_count  = substr_count($post->post_content, '<img');
    $reading_mins = max(1, ceil($word_count / 238) + ceil($image_count * 0.2));

    $description = $post->post_excerpt
        ? wp_strip_all_tags($post->post_excerpt)
        : wp_trim_words(wp_strip_all_tags(strip_shortcodes($post->post_content)), 30, '...');

    $schema = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'Article',
        'mainEntityOfPage' => array(
            '@type' => 'WebPage',
            '@id'   => get_permalink(),
        ),
        'headline'        => get_the_title(),
        'description'     => $description,
        'wordCount'       => $word_count,
        'timeRequired'    => 'PT' . $reading_mins . 'M',
        'datePublished'   => get_the_date('c'),
        'dateModified'    => get_the_modified_date('c'),
        'inLanguage'      => get_locale(),
        'author'          => array(
            '@type' => 'Person',
            'name'  => get_the_author(),
            'url'   => get_author_posts_url(get_the_author_meta('ID')),
        ),
        'publisher' => array(
            '@type' => 'Organization',
            'name'  => get_bloginfo('name'),
            'url'   => home_url('/'),
            'logo'  => array(
                '@type' => 'ImageObject',
                'url'   => get_site_icon_url(112),
            ),
        ),
    );

    // Categorías como keywords
    $categories = get_the_category();
    if (!empty($categories)) {
        $keywords = array_map(function ($cat) { return $cat->name; }, $categories);
        $tags     = get_the_tags();
        if ($tags) {
            $keywords = array_merge($keywords, array_map(function ($tag) { return $tag->name; }, $tags));
        }
        $schema['keywords'] = implode(', ', $keywords);
    }

    // Imagen destacada
    if (has_post_thumbnail()) {
        $img_url  = get_the_post_thumbnail_url($post->ID, 'anhqv-featured');
        $img_meta = wp_get_attachment_metadata(get_post_thumbnail_id($post->ID));
        $schema['image'] = array(
            '@type'  => 'ImageObject',
            'url'    => $img_url,
            'width'  => $img_meta['width']  ?? 1200,
            'height' => $img_meta['height'] ?? 675,
        );
    }

    // BreadcrumbList
    $cat = get_the_category();
    if (!empty($cat)) {
        $schema['breadcrumb'] = array(
            '@type'           => 'BreadcrumbList',
            'itemListElement' => array(
                array('@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url('/')),
                array('@type' => 'ListItem', 'position' => 2, 'name' => $cat[0]->name, 'item' => get_category_link($cat[0]->term_id)),
                array('@type' => 'ListItem', 'position' => 3, 'name' => get_the_title()),
            ),
        );
    }

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";

    wp_reset_postdata();
}
add_action('wp_head', 'anhqv_add_schema_markup', 6);


// =============================================================================
// FIX #5 — POSTS RELACIONADOS CON CACHÉ (transients, evita query rand en cada carga)
// =============================================================================

function anhqv_get_related_posts($post_id, $number_posts = 3)
{
    $cache_key = 'anhqv_related_' . $post_id . '_' . $number_posts;
    $related   = get_transient($cache_key);

    if (false !== $related) {
        return $related;
    }

    $categories = get_the_category($post_id);

    if (empty($categories)) {
        return array();
    }

    $category_ids = wp_list_pluck($categories, 'term_id');

    $args = array(
        'category__in'        => $category_ids,
        'post__not_in'        => array($post_id),
        'posts_per_page'      => $number_posts,
        'orderby'             => 'rand',
        'ignore_sticky_posts' => 1,
        'no_found_rows'       => true,   // optimización: no contar total de posts
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    );

    $related = get_posts($args);

    // Caché durante 12 horas; se invalida automáticamente al actualizar el post
    set_transient($cache_key, $related, 12 * HOUR_IN_SECONDS);

    return $related;
}

/**
 * Limpia la caché de posts relacionados cuando se guarda/actualiza un post.
 */
function anhqv_clear_related_cache($post_id)
{
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
        return;
    }
    delete_transient('anhqv_related_' . $post_id . '_3');
    delete_transient('anhqv_related_' . $post_id . '_4');
    delete_transient('anhqv_related_' . $post_id . '_6');
}
add_action('save_post', 'anhqv_clear_related_cache');


// =============================================================================
// FIX #6 — TIEMPO DE LECTURA ACTUALIZADO (238 ppm + tiempo por imágenes)
// =============================================================================

function anhqv_reading_time($post_id = null)
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $content      = get_post_field('post_content', $post_id);
    $word_count   = str_word_count(strip_tags($content));
    $image_count  = substr_count($content, '<img');

    // 238 palabras/minuto (estándar Nielsen Norman Group)
    // + 0.2 min por imagen (12 segundos aprox)
    $reading_time = ceil($word_count / 238) + ceil($image_count * 0.2);

    if ($reading_time < 1) {
        $reading_time = 1;
    }

    return sprintf(
        esc_html(_n('%s min de lectura', '%s min de lectura', $reading_time, 'anhqv-tech')),
        $reading_time
    );
}


// =============================================================================
// FIX #7 — SOCIAL SHARE: Twitter → X (URL correcta desde 2023)
// =============================================================================

function anhqv_social_share_buttons()
{
    $url   = urlencode(get_permalink());
    $title = urlencode(get_the_title());
    ?>
    <div class="social-share">
        <span class="share-label"><?php esc_html_e('Compartir:', 'anhqv-tech'); ?></span>

        <!-- X (antes Twitter) -->
        <a href="https://x.com/intent/tweet?url=<?php echo $url; ?>&text=<?php echo $title; ?>"
           target="_blank" rel="noopener noreferrer"
           class="share-btn x-twitter"
           aria-label="<?php esc_attr_e('Compartir en X (Twitter)', 'anhqv-tech'); ?>">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L2.25 2.25h6.985l4.26 5.634L18.244 2.25Zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77Z"/>
            </svg>
        </a>

        <!-- Facebook -->
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $url; ?>"
           target="_blank" rel="noopener noreferrer"
           class="share-btn facebook"
           aria-label="<?php esc_attr_e('Compartir en Facebook', 'anhqv-tech'); ?>">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
            </svg>
        </a>

        <!-- LinkedIn -->
        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url; ?>&title=<?php echo $title; ?>"
           target="_blank" rel="noopener noreferrer"
           class="share-btn linkedin"
           aria-label="<?php esc_attr_e('Compartir en LinkedIn', 'anhqv-tech'); ?>">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/>
                <circle cx="4" cy="4" r="2"/>
            </svg>
        </a>

        <!-- WhatsApp -->
        <a href="https://wa.me/?text=<?php echo $title . '%20' . $url; ?>"
           target="_blank" rel="noopener noreferrer"
           class="share-btn whatsapp"
           aria-label="<?php esc_attr_e('Compartir en WhatsApp', 'anhqv-tech'); ?>">
            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
        </a>
    </div>
    <?php
}


// =============================================================================
// RESTO DE FUNCIONES (sin cambios, mantenidas)
// =============================================================================

function anhqv_widgets_init()
{
    register_sidebar(array(
        'name'          => esc_html__('Ad: Before Content', 'anhqv-tech'),
        'id'            => 'ad-before-content',
        'description'   => esc_html__('Add your Amazon/AdSense code here. Displays before the article text.', 'anhqv-tech'),
        'before_widget' => '<div id="%1$s" class="widget ad-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title" style="display:none;">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => esc_html__('Ad: After Content', 'anhqv-tech'),
        'id'            => 'ad-after-content',
        'description'   => esc_html__('Add your Amazon/AdSense code here. Displays after the article text.', 'anhqv-tech'),
        'before_widget' => '<div id="%1$s" class="widget ad-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title" style="display:none;">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => esc_html__('Sidebar Home', 'anhqv-tech'),
        'id'            => 'sidebar-1',
        'description'   => esc_html__('Add widgets here, appears on the right.', 'anhqv-tech'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    for ($i = 1; $i <= 3; $i++) {
        register_sidebar(array(
            'name'          => sprintf(esc_html__('Footer %d', 'anhqv-tech'), $i),
            'id'            => 'footer-' . $i,
            'description'   => sprintf(esc_html__('Footer widget area %d', 'anhqv-tech'), $i),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget-title">',
            'after_title'   => '</h3>',
        ));
    }
}
add_action('widgets_init', 'anhqv_widgets_init');

function anhqv_modify_query($query)
{
    if ($query->is_home() && $query->is_main_query() && !is_admin()) {
        $query->set('posts_per_page', 12);
    }
}
add_action('pre_get_posts', 'anhqv_modify_query');

function anhqv_excerpt_length($length)
{
    return 25;
}
add_filter('excerpt_length', 'anhqv_excerpt_length');

function anhqv_excerpt_more($more)
{
    return '...';
}
add_filter('excerpt_more', 'anhqv_excerpt_more');

function anhqv_add_lazy_loading($attr)
{
    if (!is_admin() && !is_feed()) {
        $attr['loading'] = 'lazy';
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'anhqv_add_lazy_loading');

function anhqv_breadcrumbs()
{
    if (is_front_page()) {
        return;
    }

    $separator  = '<span class="breadcrumb-separator" aria-hidden="true">/</span>';
    $home_title = esc_html__('Home', 'anhqv-tech');

    echo '<nav class="breadcrumbs" aria-label="' . esc_attr__('Breadcrumb', 'anhqv-tech') . '">';
    echo '<ol class="breadcrumb-list" itemscope itemtype="https://schema.org/BreadcrumbList">';

    echo '<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
    echo '<a href="' . esc_url(home_url('/')) . '" itemprop="item"><span itemprop="name">' . $home_title . '</span></a>';
    echo '<meta itemprop="position" content="1">';
    echo '</li>';
    echo $separator;

    $position = 2;

    if (is_category() || is_single()) {
        $category = get_the_category();
        if (!empty($category)) {
            $cat = $category[0];
            echo '<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            echo '<a href="' . esc_url(get_category_link($cat->term_id)) . '" itemprop="item"><span itemprop="name">' . esc_html($cat->name) . '</span></a>';
            echo '<meta itemprop="position" content="' . $position . '">';
            echo '</li>';
            $position++;
            if (is_single()) {
                echo $separator;
            }
        }
    }

    if (is_single()) {
        echo '<li class="breadcrumb-item active" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
        echo '<span itemprop="name">' . get_the_title() . '</span>';
        echo '<meta itemprop="position" content="' . $position . '">';
        echo '</li>';
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
