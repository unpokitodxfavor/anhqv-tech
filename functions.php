<?php
/**
 * ANHQV Tech - functions.php
 * Version: 2.5.0
 *
 * Nuevas funcionalidades prioridad alta:
 *  1. Búsqueda AJAX expandible
 *  2. Widget "Posts más leídos" con contador propio
 *  3. Posts sticky con diseño especial
 *  4. Sistema de valoración con estrellas (sin plugin)
 */

if (!defined('ABSPATH')) exit;

// =============================================================================
// THEME SETUP
// =============================================================================
function anhqv_setup()
{
    load_theme_textdomain('anhqv-tech', get_template_directory() . '/languages');
    add_theme_support('automatic-feed-links');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_image_size('anhqv-featured', 1200, 675, true);
    add_image_size('anhqv-thumb', 600, 400, true);
    add_theme_support('custom-logo', array('height' => 60, 'width' => 200, 'flex-height' => true, 'flex-width' => true));
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    add_theme_support('responsive-embeds');
    register_nav_menus(array(
        'primary' => esc_html__('Primary Menu', 'anhqv-tech'),
        'footer'  => esc_html__('Footer Menu', 'anhqv-tech'),
    ));
}
add_action('after_setup_theme', 'anhqv_setup');

// =============================================================================
// SCRIPTS Y ESTILOS
// =============================================================================
function anhqv_tech_scripts()
{
    wp_enqueue_style('anhqv-style', get_stylesheet_uri(), array(), '2.5.0');

    wp_enqueue_script(
        'anhqv-main',
        get_template_directory_uri() . '/js/main.js',
        array(),
        '2.5.0',
        true
    );

    wp_localize_script('anhqv-main', 'anhqvData', array(
        'ajaxurl'      => admin_url('admin-ajax.php'),
        'searchNonce'  => wp_create_nonce('anhqv-search'),
        'ratingNonce'  => wp_create_nonce('anhqv-rating'),
        'noResults'    => esc_html__('No se encontraron resultados.', 'anhqv-tech'),
        'alreadyRated' => esc_html__('Ya has valorado este artículo. ¡Gracias!', 'anhqv-tech'),
    ));

    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'anhqv_tech_scripts');

// =============================================================================
// GOOGLE FONTS ASÍNCRONO (no bloqueante)
// =============================================================================
function anhqv_preconnect_and_async_fonts()
{
    $fonts_url = 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700;800&display=swap';
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="<?php echo esc_url($fonts_url); ?>"
          onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="<?php echo esc_url($fonts_url); ?>"></noscript>
    <?php
}
add_action('wp_head', 'anhqv_preconnect_and_async_fonts', 1);

// =============================================================================
// CANONICAL
// =============================================================================
function anhqv_canonical_tag()
{
    if (is_singular())              echo '<link rel="canonical" href="' . esc_url(get_permalink()) . '" />' . "\n";
    elseif (is_home() || is_front_page()) echo '<link rel="canonical" href="' . esc_url(home_url('/')) . '" />' . "\n";
}
add_action('wp_head', 'anhqv_canonical_tag', 2);

// =============================================================================
// META DESCRIPTION + OPEN GRAPH
// =============================================================================
function anhqv_add_og_tags()
{
    if (!is_singular()) return;
    global $post;
    setup_postdata($post);

    $og_title       = get_the_title();
    $og_url         = get_permalink();
    $og_description = $post->post_excerpt
        ? wp_strip_all_tags($post->post_excerpt)
        : wp_trim_words(wp_strip_all_tags(strip_shortcodes($post->post_content)), 30, '...');
    $og_image = has_post_thumbnail() ? get_the_post_thumbnail_url($post->ID, 'anhqv-featured') : '';

    echo '<meta name="description"       content="' . esc_attr($og_description) . '" />' . "\n";
    echo '<meta property="og:title"      content="' . esc_attr($og_title) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($og_description) . '" />' . "\n";
    echo '<meta property="og:url"        content="' . esc_url($og_url) . '" />' . "\n";
    echo '<meta property="og:type"       content="' . (is_page() ? 'website' : 'article') . '" />' . "\n";
    echo '<meta property="og:site_name"  content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
    if ($og_image) {
        echo '<meta property="og:image"  content="' . esc_url($og_image) . '" />' . "\n";
        echo '<meta name="twitter:card"  content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:image" content="' . esc_url($og_image) . '" />' . "\n";
    }
    wp_reset_postdata();
}
add_action('wp_head', 'anhqv_add_og_tags', 5);

// =============================================================================
// SCHEMA.ORG
// =============================================================================
function anhqv_add_schema_markup()
{
    if (!is_single()) return;
    global $post;
    setup_postdata($post);

    $word_count  = str_word_count(strip_tags($post->post_content));
    $image_count = substr_count($post->post_content, '<img');
    $reading     = max(1, ceil($word_count / 238) + ceil($image_count * 0.2));
    $description = $post->post_excerpt
        ? wp_strip_all_tags($post->post_excerpt)
        : wp_trim_words(wp_strip_all_tags(strip_shortcodes($post->post_content)), 30, '...');

    $schema = array(
        '@context'         => 'https://schema.org',
        '@type'            => 'Article',
        'mainEntityOfPage' => array('@type' => 'WebPage', '@id' => get_permalink()),
        'headline'         => get_the_title(),
        'description'      => $description,
        'wordCount'        => $word_count,
        'timeRequired'     => 'PT' . $reading . 'M',
        'datePublished'    => get_the_date('c'),
        'dateModified'     => get_the_modified_date('c'),
        'author'           => array('@type' => 'Person', 'name' => get_the_author()),
        'publisher'        => array(
            '@type' => 'Organization',
            'name'  => get_bloginfo('name'),
            'url'   => home_url('/'),
        ),
    );

    // Incluir rating en schema si hay valoraciones
    $rating = anhqv_get_rating_data(get_the_ID());
    if ($rating['count'] > 0) {
        $schema['aggregateRating'] = array(
            '@type'       => 'AggregateRating',
            'ratingValue' => round($rating['average'], 1),
            'reviewCount' => $rating['count'],
            'bestRating'  => 5,
            'worstRating' => 1,
        );
    }

    echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    wp_reset_postdata();
}
add_action('wp_head', 'anhqv_add_schema_markup', 6);

// =============================================================================
// WIDGETS AREAS
// =============================================================================
function anhqv_widgets_init()
{
    register_sidebar(array('name' => 'Ad: Before Content', 'id' => 'ad-before-content', 'before_widget' => '<div id="%1$s" class="widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h3 class="widget-title" style="display:none;">', 'after_title' => '</h3>'));
    register_sidebar(array('name' => 'Ad: After Content',  'id' => 'ad-after-content',  'before_widget' => '<div id="%1$s" class="widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h3 class="widget-title" style="display:none;">', 'after_title' => '</h3>'));
    register_sidebar(array('name' => 'Sidebar Home', 'id' => 'sidebar-1', 'before_widget' => '<section id="%1$s" class="widget %2$s">', 'after_widget' => '</section>', 'before_title' => '<h2 class="widget-title">', 'after_title' => '</h2>'));
}
add_action('widgets_init', 'anhqv_widgets_init');

// =============================================================================
// FUNCIONES GENERALES
// =============================================================================
function anhqv_modify_query($query)
{
    if ($query->is_home() && $query->is_main_query() && !is_admin()) {
        $query->set('posts_per_page', 12);
    }
}
add_action('pre_get_posts', 'anhqv_modify_query');

function anhqv_excerpt_length() { return 25; }
add_filter('excerpt_length', 'anhqv_excerpt_length');

function anhqv_excerpt_more() { return '...'; }
add_filter('excerpt_more', 'anhqv_excerpt_more');

function anhqv_reading_time($post_id = null)
{
    if (!$post_id) $post_id = get_the_ID();
    $content    = get_post_field('post_content', $post_id);
    $words      = str_word_count(strip_tags($content));
    $images     = substr_count($content, '<img');
    $time       = max(1, ceil($words / 238) + ceil($images * 0.2));
    return $time . ' min de lectura';
}

function anhqv_get_related_posts($post_id, $number = 3)
{
    $cache_key = 'anhqv_related_' . $post_id . '_' . $number;
    $related   = get_transient($cache_key);
    if (false !== $related) return $related;

    $cats    = get_the_category($post_id);
    if (empty($cats)) return array();
    $cat_ids = wp_list_pluck($cats, 'term_id');

    $related = get_posts(array(
        'category__in'        => $cat_ids,
        'post__not_in'        => array($post_id),
        'posts_per_page'      => $number,
        'orderby'             => 'rand',
        'ignore_sticky_posts' => 1,
        'no_found_rows'       => true,
    ));

    set_transient($cache_key, $related, 12 * HOUR_IN_SECONDS);
    return $related;
}

function anhqv_clear_related_cache($post_id)
{
    if (!wp_is_post_revision($post_id)) delete_transient('anhqv_related_' . $post_id . '_3');
}
add_action('save_post', 'anhqv_clear_related_cache');

function anhqv_breadcrumbs()
{
    if (is_front_page()) return;
    $sep = '<span class="breadcrumb-separator" aria-hidden="true">/</span>';
    echo '<nav class="breadcrumbs" aria-label="Breadcrumb"><ol class="breadcrumb-list">';
    echo '<li class="breadcrumb-item"><a href="' . esc_url(home_url('/')) . '">Home</a></li>' . $sep;
    if (is_category() || is_single()) {
        $cat = get_the_category();
        if (!empty($cat)) {
            echo '<li class="breadcrumb-item"><a href="' . esc_url(get_category_link($cat[0]->term_id)) . '">' . esc_html($cat[0]->name) . '</a></li>';
            if (is_single()) echo $sep;
        }
    }
    if (is_single())     echo '<li class="breadcrumb-item active" aria-current="page">' . get_the_title() . '</li>';
    elseif (is_page())   echo '<li class="breadcrumb-item active" aria-current="page">' . get_the_title() . '</li>';
    elseif (is_search()) echo '<li class="breadcrumb-item active">Resultados de búsqueda</li>';
    elseif (is_404())    echo '<li class="breadcrumb-item active">404</li>';
    echo '</ol></nav>';
}


// =============================================================================
// #1 — BÚSQUEDA AJAX
// =============================================================================

function anhqv_ajax_search()
{
    check_ajax_referer('anhqv-search', 'nonce');

    $query = sanitize_text_field($_GET['query'] ?? '');
    if (strlen($query) < 2) {
        wp_send_json_success(array('results' => array(), 'total' => 0));
    }

    $search = new WP_Query(array(
        's'              => $query,
        'post_status'    => 'publish',
        'posts_per_page' => 6,
        'no_found_rows'  => false,
    ));

    $results = array();
    if ($search->have_posts()) {
        while ($search->have_posts()) {
            $search->the_post();
            $cats = get_the_category();
            $results[] = array(
                'id'       => get_the_ID(),
                'title'    => get_the_title(),
                'url'      => get_permalink(),
                'excerpt'  => wp_trim_words(get_the_excerpt(), 12),
                'thumb'    => has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'thumbnail') : '',
                'date'     => get_the_date(),
                'category' => !empty($cats) ? $cats[0]->name : '',
                'reading'  => anhqv_reading_time(get_the_ID()),
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success(array(
        'results' => $results,
        'total'   => $search->found_posts,
        'query'   => $query,
    ));
}
add_action('wp_ajax_anhqv_search',        'anhqv_ajax_search');
add_action('wp_ajax_nopriv_anhqv_search', 'anhqv_ajax_search');

// =============================================================================
// #2 — CONTADOR DE VISITAS + WIDGET POSTS MÁS LEÍDOS
// =============================================================================

function anhqv_track_post_views($post_id)
{
    if (is_admin()) return;
    $count = (int) get_post_meta($post_id, 'anhqv_view_count', true);
    update_post_meta($post_id, 'anhqv_view_count', $count + 1);
}

function anhqv_get_post_views($post_id)
{
    return (int) get_post_meta($post_id, 'anhqv_view_count', true);
}

class ANHQV_Popular_Posts_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct('anhqv_popular_posts', 'ANHQV: Posts Más Leídos', array(
            'description' => 'Muestra los artículos más visitados con contador propio.',
        ));
    }

    public function widget($args, $instance)
    {
        $title  = apply_filters('widget_title', $instance['title'] ?? 'Lo más leído');
        $number = (int) ($instance['number'] ?? 5);

        $popular = new WP_Query(array(
            'post_status'    => 'publish',
            'posts_per_page' => $number,
            'meta_key'       => 'anhqv_view_count',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        ));

        if (!$popular->have_posts()) return;

        echo $args['before_widget'];
        echo $args['before_title'] . esc_html($title) . $args['after_title'];
        echo '<ol class="popular-posts-list">';

        $rank = 1;
        while ($popular->have_posts()) {
            $popular->the_post();
            $views = anhqv_get_post_views(get_the_ID());
            ?>
            <li class="popular-post-item">
                <span class="popular-rank"><?php echo $rank; ?></span>
                <?php if (has_post_thumbnail()): ?>
                    <a href="<?php the_permalink(); ?>" class="popular-thumb" tabindex="-1" aria-hidden="true">
                        <?php the_post_thumbnail('thumbnail', array('loading' => 'lazy')); ?>
                    </a>
                <?php endif; ?>
                <div class="popular-info">
                    <a href="<?php the_permalink(); ?>" class="popular-title"><?php the_title(); ?></a>
                    <span class="popular-meta">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <?php echo number_format($views); ?> visitas
                        &nbsp;·&nbsp;
                        <?php echo anhqv_reading_time(get_the_ID()); ?>
                    </span>
                </div>
            </li>
            <?php
            $rank++;
        }

        echo '</ol>';
        wp_reset_postdata();
        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title  = $instance['title']  ?? 'Lo más leído';
        $number = $instance['number'] ?? 5;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Título:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>">Número de posts (1-10):</label>
            <input class="tiny-text" id="<?php echo $this->get_field_id('number'); ?>"
                   name="<?php echo $this->get_field_name('number'); ?>"
                   type="number" min="1" max="10" value="<?php echo (int) $number; ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        return array(
            'title'  => sanitize_text_field($new_instance['title']),
            'number' => (int) $new_instance['number'],
        );
    }
}

function anhqv_register_widgets()
{
    register_widget('ANHQV_Popular_Posts_Widget');
}
add_action('widgets_init', 'anhqv_register_widgets');

// =============================================================================
// #3 — POSTS STICKY — función helper para la badge en index.php
// =============================================================================

function anhqv_sticky_badge()
{
    if (is_sticky() && !is_paged()) {
        echo '<span class="sticky-badge" aria-label="Post destacado">📌 Destacado</span>';
    }
}

// =============================================================================
// #4 — SISTEMA DE VALORACIÓN CON ESTRELLAS
// =============================================================================

function anhqv_get_rating_data($post_id)
{
    $ratings = get_post_meta($post_id, 'anhqv_ratings', true);
    if (empty($ratings) || !is_array($ratings)) {
        return array('average' => 0, 'count' => 0);
    }
    return array(
        'average' => array_sum($ratings) / count($ratings),
        'count'   => count($ratings),
    );
}

function anhqv_rating_widget($post_id = null)
{
    if (!$post_id) $post_id = get_the_ID();
    $data    = anhqv_get_rating_data($post_id);
    $average = round($data['average'], 1);
    $count   = $data['count'];
    $percent = $count > 0 ? ($average / 5) * 100 : 0;
    ?>
    <div class="rating-widget" data-post-id="<?php echo (int) $post_id; ?>">
        <p class="rating-label">¿Te ha gustado este artículo?</p>

        <div class="star-rating-input" role="group" aria-label="Valorar del 1 al 5">
            <?php for ($i = 5; $i >= 1; $i--): ?>
                <button type="button" class="star-btn" data-value="<?php echo $i; ?>"
                        aria-label="<?php echo $i; ?> estrella<?php echo $i > 1 ? 's' : ''; ?>">★</button>
            <?php endfor; ?>
        </div>

        <div class="rating-result" <?php echo $count === 0 ? 'hidden' : ''; ?>>
            <div class="rating-stars-display">
                <div class="stars-bg">★★★★★</div>
                <div class="stars-fill" style="width:<?php echo $percent; ?>%">★★★★★</div>
            </div>
            <span class="rating-summary">
                <strong><?php echo number_format($average, 1); ?></strong>/5
                <span class="rating-count">(<?php echo $count; ?> valoraciones)</span>
            </span>
        </div>

        <p class="rating-feedback" aria-live="polite"></p>
    </div>
    <?php
}

function anhqv_save_rating()
{
    check_ajax_referer('anhqv-rating', 'nonce');

    $post_id = (int) ($_POST['post_id'] ?? 0);
    $rating  = (int) ($_POST['rating']  ?? 0);

    if (!$post_id || $rating < 1 || $rating > 5 || get_post_status($post_id) !== 'publish') {
        wp_send_json_error(array('message' => 'invalid'));
    }

    // Anti-doble-voto por IP hasheada
    $user_ip   = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    $voted_ips = get_post_meta($post_id, 'anhqv_rated_ips', true) ?: array();

    if (in_array($user_ip, $voted_ips, true)) {
        $data = anhqv_get_rating_data($post_id);
        wp_send_json_error(array(
            'message' => 'already_rated',
            'average' => round($data['average'], 1),
            'count'   => $data['count'],
            'percent' => ($data['average'] / 5) * 100,
        ));
    }

    $ratings   = get_post_meta($post_id, 'anhqv_ratings', true) ?: array();
    $ratings[] = $rating;
    update_post_meta($post_id, 'anhqv_ratings', $ratings);

    $voted_ips[] = $user_ip;
    update_post_meta($post_id, 'anhqv_rated_ips', $voted_ips);

    $data = anhqv_get_rating_data($post_id);
    wp_send_json_success(array(
        'message' => 'saved',
        'average' => round($data['average'], 1),
        'count'   => $data['count'],
        'percent' => ($data['average'] / 5) * 100,
    ));
}
add_action('wp_ajax_anhqv_save_rating',        'anhqv_save_rating');
add_action('wp_ajax_nopriv_anhqv_save_rating', 'anhqv_save_rating');

// =============================================================================
// #5 — NEWSLETTER WIDGET
// =============================================================================

/**
 * Handler AJAX para suscripción al newsletter.
 * Guarda el email en una option de WP y envía notificación al admin.
 * Compatible con webhook externo (Mailchimp, etc.) si se configura.
 */
function anhqv_newsletter_subscribe()
{
    check_ajax_referer('anhqv-newsletter', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Email no válido.'));
    }

    // Guardar en lista propia (option de WP)
    $subscribers = get_option('anhqv_newsletter_subscribers', array());
    if (in_array($email, $subscribers, true)) {
        wp_send_json_error(array('message' => '¡Ya estás suscrito!'));
    }

    $subscribers[] = $email;
    update_option('anhqv_newsletter_subscribers', $subscribers);

    // Notificación al admin
    wp_mail(
        get_option('admin_email'),
        'Nueva suscripción al newsletter — ' . get_bloginfo('name'),
        "Nuevo suscriptor: $email\n\nTotal suscriptores: " . count($subscribers),
        array('Content-Type: text/plain; charset=UTF-8')
    );

    // Webhook externo opcional (configurable desde wp-config.php)
    $webhook = defined('ANHQV_NEWSLETTER_WEBHOOK') ? ANHQV_NEWSLETTER_WEBHOOK : '';
    if ($webhook) {
        wp_remote_post($webhook, array(
            'body'    => wp_json_encode(array('email' => $email)),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 5,
        ));
    }

    wp_send_json_success(array('message' => '¡Suscripción confirmada! Bienvenido/a 🎉'));
}
add_action('wp_ajax_anhqv_newsletter',        'anhqv_newsletter_subscribe');
add_action('wp_ajax_nopriv_anhqv_newsletter', 'anhqv_newsletter_subscribe');

/**
 * Widget Newsletter
 */
class ANHQV_Newsletter_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct('anhqv_newsletter', 'ANHQV: Newsletter', array(
            'description' => 'Formulario de suscripción al newsletter.',
        ));
    }

    public function widget($args, $instance)
    {
        $title    = apply_filters('widget_title', $instance['title']    ?? '📬 Newsletter');
        $subtitle = $instance['subtitle'] ?? 'Recibe los mejores artículos en tu email.';
        $btn_text = $instance['btn_text'] ?? 'Suscribirme';

        echo $args['before_widget'];
        ?>
        <div class="newsletter-widget">
            <?php echo $args['before_title'] . esc_html($title) . $args['after_title']; ?>
            <p class="newsletter-subtitle"><?php echo esc_html($subtitle); ?></p>
            <form class="newsletter-form" data-nonce="<?php echo wp_create_nonce('anhqv-newsletter'); ?>">
                <input type="email"
                       class="newsletter-email"
                       placeholder="tu@email.com"
                       required
                       aria-label="Email">
                <button type="submit" class="newsletter-submit">
                    <?php echo esc_html($btn_text); ?>
                </button>
            </form>
            <p class="newsletter-feedback" aria-live="polite"></p>
            <p class="newsletter-privacy">Sin spam. Cancela cuando quieras.</p>
        </div>
        <?php
        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title    = $instance['title']    ?? '📬 Newsletter';
        $subtitle = $instance['subtitle'] ?? 'Recibe los mejores artículos en tu email.';
        $btn_text = $instance['btn_text'] ?? 'Suscribirme';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Título:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('subtitle'); ?>">Subtítulo:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('subtitle'); ?>"
                   name="<?php echo $this->get_field_name('subtitle'); ?>"
                   type="text" value="<?php echo esc_attr($subtitle); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('btn_text'); ?>">Texto del botón:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('btn_text'); ?>"
                   name="<?php echo $this->get_field_name('btn_text'); ?>"
                   type="text" value="<?php echo esc_attr($btn_text); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        return array(
            'title'    => sanitize_text_field($new_instance['title']),
            'subtitle' => sanitize_text_field($new_instance['subtitle']),
            'btn_text' => sanitize_text_field($new_instance['btn_text']),
        );
    }
}

function anhqv_register_newsletter_widget()
{
    register_widget('ANHQV_Newsletter_Widget');
}
add_action('widgets_init', 'anhqv_register_newsletter_widget');


// =============================================================================
// #6 — PÁGINA DE FAVORITOS (shortcode [anhqv_favorites])
// =============================================================================

/**
 * Shortcode que renderiza el listado de artículos guardados.
 * Los IDs vienen del localStorage del navegador vía JS.
 */
function anhqv_favorites_shortcode()
{
    ob_start();
    ?>
    <div class="favorites-page" id="favorites-container">
        <div class="favorites-empty" id="favorites-empty" style="display:none;">
            <span class="favorites-empty-icon">🔖</span>
            <p>Todavía no has guardado ningún artículo.</p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn-primary">Explorar artículos</a>
        </div>
        <div class="posts-grid" id="favorites-grid"></div>
    </div>
    <script>
    (function(){
        const grid    = document.getElementById('favorites-grid');
        const empty   = document.getElementById('favorites-empty');
        const ids     = JSON.parse(localStorage.getItem('anhqv-favorites') || '[]');
        if (!ids.length) { empty.style.display = 'block'; return; }
        fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=anhqv_get_favorites&ids=' + ids.join(',') + '&nonce=<?php echo wp_create_nonce('anhqv-favorites'); ?>')
            .then(r => r.json())
            .then(json => {
                if (!json.success || !json.data.posts.length) { empty.style.display = 'block'; return; }
                json.data.posts.forEach(p => {
                    const el = document.createElement('article');
                    el.className = 'post-card';
                    el.innerHTML = `
                        ${p.thumb ? `<div class="post-thumbnail"><a href="${p.url}">${p.thumb}</a></div>` : ''}
                        <div class="post-content">
                            <div class="post-category">${p.category ? `<span>${p.category}</span>` : ''}</div>
                            <h2 class="post-title"><a href="${p.url}">${p.title}</a></h2>
                            <p class="post-excerpt">${p.excerpt}</p>
                            <div class="post-meta">${p.date} · ${p.reading}</div>
                        </div>`;
                    grid.appendChild(el);
                });
            });
    })();
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('anhqv_favorites', 'anhqv_favorites_shortcode');

/**
 * Handler AJAX que devuelve los datos de los posts favoritos.
 */
function anhqv_get_favorites()
{
    check_ajax_referer('anhqv-favorites', 'nonce');

    $raw_ids = sanitize_text_field($_GET['ids'] ?? '');
    $ids     = array_filter(array_map('intval', explode(',', $raw_ids)));

    if (empty($ids)) wp_send_json_error();

    $posts = get_posts(array(
        'post__in'    => $ids,
        'post_status' => 'publish',
        'orderby'     => 'post__in',
        'numberposts' => 20,
    ));

    $data = array_map(function ($post) {
        $cats = get_the_category($post->ID);
        return array(
            'id'       => $post->ID,
            'title'    => get_the_title($post->ID),
            'url'      => get_permalink($post->ID),
            'excerpt'  => wp_trim_words(get_the_excerpt($post->ID), 18),
            'thumb'    => has_post_thumbnail($post->ID) ? get_the_post_thumbnail($post->ID, 'anhqv-featured', array('loading' => 'lazy')) : '',
            'date'     => get_the_date('', $post->ID),
            'reading'  => anhqv_reading_time($post->ID),
            'category' => !empty($cats) ? esc_html($cats[0]->name) : '',
        );
    }, $posts);

    wp_send_json_success(array('posts' => $data));
}
add_action('wp_ajax_anhqv_get_favorites',        'anhqv_get_favorites');
add_action('wp_ajax_nopriv_anhqv_get_favorites', 'anhqv_get_favorites');


// =============================================================================
// Pasar datos adicionales al JS (newsletter + favorites nonce)
// =============================================================================
function anhqv_extra_js_data()
{
    ?>
    <script>
    anhqvData = Object.assign(anhqvData || {}, {
        newsletterNonce:  '<?php echo wp_create_nonce('anhqv-newsletter'); ?>',
        favoritesNonce:   '<?php echo wp_create_nonce('anhqv-favorites'); ?>',
        favoritesAjax:    '<?php echo admin_url('admin-ajax.php'); ?>',
        savedText:        '🔖 Guardado',
        saveText:         '🔖 Guardar',
    });
    </script>
    <?php
}
add_action('wp_footer', 'anhqv_extra_js_data', 5);

// =============================================================================
// #7 — SISTEMA DE SERIES DE ARTÍCULOS
// =============================================================================

/**
 * Registrar taxonomía "serie"
 */
function anhqv_register_series_taxonomy()
{
    register_taxonomy('serie', 'post', array(
        'label'             => 'Series',
        'singular_label'    => 'Serie',
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'serie'),
        'labels'            => array(
            'name'              => 'Series',
            'singular_name'     => 'Serie',
            'add_new_item'      => 'Añadir nueva serie',
            'edit_item'         => 'Editar serie',
            'search_items'      => 'Buscar series',
            'not_found'         => 'No se encontraron series',
            'all_items'         => 'Todas las series',
        ),
    ));
}
add_action('init', 'anhqv_register_series_taxonomy');

/**
 * Meta box para el orden dentro de la serie
 */
function anhqv_series_order_meta_box()
{
    add_meta_box(
        'anhqv_series_order',
        '📚 Orden en la serie',
        'anhqv_series_order_meta_box_html',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'anhqv_series_order_meta_box');

function anhqv_series_order_meta_box_html($post)
{
    wp_nonce_field('anhqv_series_order_nonce', 'anhqv_series_order_nonce_field');
    $order = get_post_meta($post->ID, 'anhqv_series_order', true) ?: '';
    $desc  = get_post_meta($post->ID, 'anhqv_series_desc', true)  ?: '';
    ?>
    <p style="margin-bottom:8px">
        <label style="font-weight:600;display:block;margin-bottom:4px">Parte número:</label>
        <input type="number" name="anhqv_series_order" value="<?php echo esc_attr($order); ?>"
               min="1" style="width:80px" placeholder="1">
    </p>
    <p>
        <label style="font-weight:600;display:block;margin-bottom:4px">Descripción de la serie <small>(opcional)</small>:</label>
        <textarea name="anhqv_series_desc" rows="3" style="width:100%"
                  placeholder="Breve descripción de la serie..."><?php echo esc_textarea($desc); ?></textarea>
    </p>
    <p style="color:#888;font-size:11px">Asigna una "Serie" en el panel de taxonomías de la derecha.</p>
    <?php
}

function anhqv_save_series_order_meta($post_id)
{
    if (!isset($_POST['anhqv_series_order_nonce_field'])) return;
    if (!wp_verify_nonce($_POST['anhqv_series_order_nonce_field'], 'anhqv_series_order_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['anhqv_series_order'])) {
        update_post_meta($post_id, 'anhqv_series_order', absint($_POST['anhqv_series_order']));
    }
    if (isset($_POST['anhqv_series_desc'])) {
        update_post_meta($post_id, 'anhqv_series_desc', sanitize_textarea_field($_POST['anhqv_series_desc']));
    }
}
add_action('save_post', 'anhqv_save_series_order_meta');

/**
 * Obtiene todos los posts de una serie, ordenados
 */
function anhqv_get_series_posts($serie_slug)
{
    $cache_key = 'anhqv_series_' . md5($serie_slug);
    $posts     = get_transient($cache_key);
    if (false !== $posts) return $posts;

    $posts = get_posts(array(
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'tax_query'      => array(array(
            'taxonomy' => 'serie',
            'field'    => 'slug',
            'terms'    => $serie_slug,
        )),
        'meta_key'       => 'anhqv_series_order',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC',
    ));

    set_transient($cache_key, $posts, 6 * HOUR_IN_SECONDS);
    return $posts;
}

/**
 * Renderiza el bloque de navegación de serie dentro del artículo
 */
function anhqv_series_navigation($post_id = null)
{
    if (!$post_id) $post_id = get_the_ID();

    $series = get_the_terms($post_id, 'serie');
    if (empty($series) || is_wp_error($series)) return;

    $serie        = $series[0];
    $series_posts = anhqv_get_series_posts($serie->slug);
    if (count($series_posts) < 2) return;

    $current_order = (int) get_post_meta($post_id, 'anhqv_series_order', true);
    $total         = count($series_posts);
    $series_desc   = get_post_meta($post_id, 'anhqv_series_desc', true);

    // Calcular porcentaje de progreso
    $percent = $total > 1 ? round(($current_order / $total) * 100) : 0;
    ?>
    <div class="series-nav-block">
        <div class="series-nav-header">
            <div class="series-nav-label">
                <span class="series-icon">📚</span>
                <span>Esta es la parte <strong><?php echo $current_order; ?></strong> de <strong><?php echo $total; ?></strong> de la serie</span>
            </div>
            <a href="<?php echo esc_url(get_term_link($serie, 'serie')); ?>" class="series-nav-title">
                <?php echo esc_html($serie->name); ?>
            </a>
            <?php if ($series_desc): ?>
                <p class="series-nav-desc"><?php echo esc_html($series_desc); ?></p>
            <?php endif; ?>

            <!-- Barra de progreso -->
            <div class="series-progress-bar" role="progressbar"
                 aria-valuenow="<?php echo $current_order; ?>"
                 aria-valuemin="1" aria-valuemax="<?php echo $total; ?>"
                 aria-label="Progreso en la serie">
                <div class="series-progress-fill" style="width:<?php echo $percent; ?>%"></div>
            </div>
            <span class="series-progress-label"><?php echo $percent; ?>% completado</span>
        </div>

        <ol class="series-posts-list">
            <?php foreach ($series_posts as $s_post):
                $s_order   = (int) get_post_meta($s_post->ID, 'anhqv_series_order', true);
                $is_current = $s_post->ID === $post_id;
                $is_future  = $s_order > $current_order;
            ?>
            <li class="series-post-item <?php echo $is_current ? 'is-current' : ($is_future ? 'is-future' : 'is-done'); ?>">
                <span class="series-part-num"><?php echo $s_order; ?></span>
                <?php if ($is_current): ?>
                    <span class="series-post-title is-current">
                        <?php echo esc_html($s_post->post_title); ?>
                        <span class="series-current-badge">Leyendo</span>
                    </span>
                <?php elseif ($is_future): ?>
                    <span class="series-post-title is-future">
                        <?php echo esc_html($s_post->post_title); ?>
                    </span>
                <?php else: ?>
                    <a href="<?php echo esc_url(get_permalink($s_post->ID)); ?>" class="series-post-title">
                        <?php echo esc_html($s_post->post_title); ?>
                        <span class="series-done-icon">✓</span>
                    </a>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ol>

        <!-- Navegación anterior/siguiente dentro de la serie -->
        <?php
        $prev_s = null; $next_s = null;
        foreach ($series_posts as $idx => $s_post) {
            if ($s_post->ID === $post_id) {
                $prev_s = $series_posts[$idx - 1] ?? null;
                $next_s = $series_posts[$idx + 1] ?? null;
                break;
            }
        }
        ?>
        <div class="series-prev-next">
            <?php if ($prev_s): ?>
                <a href="<?php echo esc_url(get_permalink($prev_s->ID)); ?>" class="series-nav-btn series-prev">
                    ← Anterior en la serie
                    <span><?php echo esc_html($prev_s->post_title); ?></span>
                </a>
            <?php else: ?>
                <div></div>
            <?php endif; ?>
            <?php if ($next_s): ?>
                <a href="<?php echo esc_url(get_permalink($next_s->ID)); ?>" class="series-nav-btn series-next">
                    Siguiente en la serie →
                    <span><?php echo esc_html($next_s->post_title); ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}


// =============================================================================
// #8 — DASHBOARD DE ESTADÍSTICAS EN EL ADMIN
// =============================================================================

/**
 * Registrar página del dashboard en el menú admin
 */
function anhqv_register_stats_dashboard()
{
    add_menu_page(
        'Estadísticas ANHQV',
        '📊 Estadísticas',
        'edit_posts',
        'anhqv-stats',
        'anhqv_render_stats_dashboard',
        'dashicons-chart-bar',
        25
    );
}
add_action('admin_menu', 'anhqv_register_stats_dashboard');

/**
 * Registrar también en el hook admin_head para los estilos inline
 */
function anhqv_stats_admin_styles()
{
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'toplevel_page_anhqv-stats') return;
    ?>
    <style>
    .anhqv-stats-wrap { max-width: 1200px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    .anhqv-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin: 20px 0; }
    .anhqv-stat-card  { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
    .anhqv-stat-card .stat-number { font-size: 2.5rem; font-weight: 800; color: #1d4ed8; line-height: 1; }
    .anhqv-stat-card .stat-label  { font-size: 0.85rem; color: #6b7280; margin-top: 6px; font-weight: 500; }
    .anhqv-stat-card .stat-icon   { font-size: 1.5rem; margin-bottom: 8px; }
    .anhqv-section-title { font-size: 1.1rem; font-weight: 700; color: #111827; margin: 28px 0 12px; border-left: 4px solid #1d4ed8; padding-left: 10px; }
    .anhqv-table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
    .anhqv-table th { background: #f9fafb; padding: 12px 16px; text-align: left; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
    .anhqv-table td { padding: 12px 16px; border-bottom: 1px solid #f3f4f6; font-size: 0.9rem; color: #374151; }
    .anhqv-table tr:last-child td { border-bottom: none; }
    .anhqv-table tr:hover td { background: #f9fafb; }
    .anhqv-table .rank { font-weight: 800; color: #1d4ed8; width: 40px; text-align: center; }
    .anhqv-table .views-bar { display: flex; align-items: center; gap: 8px; }
    .anhqv-table .bar { height: 6px; background: #1d4ed8; border-radius: 99px; min-width: 4px; transition: width 0.5s ease; }
    .anhqv-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
    .anhqv-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
    .anhqv-panel h3 { margin: 0 0 14px; font-size: 1rem; color: #111827; }
    .anhqv-export-btn { display: inline-block; background: #1d4ed8; color: #fff; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600; margin-top: 10px; }
    .anhqv-export-btn:hover { background: #1e40af; color: #fff; }
    .anhqv-reset-btn { display: inline-block; background: #ef4444; color: #fff; padding: 6px 12px; border-radius: 8px; text-decoration: none; font-size: 0.8rem; font-weight: 600; margin-left: 10px; cursor: pointer; border: none; }
    .rating-star { color: #f59e0b; }
    @media (max-width: 900px) { .anhqv-cols { grid-template-columns: 1fr; } }
    </style>
    <?php
}
add_action('admin_head', 'anhqv_stats_admin_styles');

/**
 * Renderizar el dashboard de estadísticas
 */
function anhqv_render_stats_dashboard()
{
    // Manejar exportación CSV
    if (isset($_GET['export']) && $_GET['export'] === 'csv' && current_user_can('edit_posts')) {
        anhqv_export_stats_csv();
        return;
    }

    // Manejar reset de stats
    if (isset($_POST['anhqv_reset_stats']) && check_admin_referer('anhqv_reset_stats_nonce')) {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'anhqv_view_count'");
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'anhqv_daily_views'");
        echo '<div class="notice notice-success"><p>✅ Estadísticas reiniciadas correctamente.</p></div>';
    }

    global $wpdb;

    // ── Totales generales ──
    $total_posts    = wp_count_posts()->publish;
    $total_comments = get_comments(array('count' => true, 'status' => 'approve'));
    $total_views    = (int) $wpdb->get_var("SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = 'anhqv_view_count'");
    $total_ratings  = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'anhqv_ratings'");
    $total_subs     = count(get_option('anhqv_newsletter_subscribers', array()));
    $total_favs     = (int) $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = 'anhqv_rated_ips'");

    // ── Top 10 posts más vistos ──
    $top_posts = $wpdb->get_results("
        SELECT p.ID, p.post_title, pm.meta_value AS views
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE pm.meta_key = 'anhqv_view_count'
          AND p.post_status = 'publish'
          AND p.post_type = 'post'
        ORDER BY CAST(pm.meta_value AS UNSIGNED) DESC
        LIMIT 10
    ");
    $max_views = !empty($top_posts) ? (int) $top_posts[0]->views : 1;

    // ── Posts por categoría con vistas ──
    $cats_stats = get_categories(array('hide_empty' => true, 'number' => 8));

    // ── Valoraciones top ──
    $top_rated = $wpdb->get_results("
        SELECT p.ID, p.post_title, pm.meta_value AS ratings_data
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE pm.meta_key = 'anhqv_ratings'
          AND p.post_status = 'publish'
        LIMIT 20
    ");
    // Calcular media
    $rated_list = array();
    foreach ($top_rated as $row) {
        $ratings = maybe_unserialize($row->ratings_data);
        if (!is_array($ratings) || empty($ratings)) continue;
        $rated_list[] = array(
            'id'      => $row->ID,
            'title'   => $row->post_title,
            'average' => round(array_sum($ratings) / count($ratings), 1),
            'count'   => count($ratings),
        );
    }
    usort($rated_list, fn($a,$b) => $b['average'] <=> $a['average']);
    $rated_list = array_slice($rated_list, 0, 5);

    // ── Suscriptores del newsletter ──
    $subscribers = get_option('anhqv_newsletter_subscribers', array());

    ?>
    <div class="wrap anhqv-stats-wrap">
        <h1 style="margin-bottom:4px">📊 Estadísticas de ANHQV</h1>
        <p style="color:#6b7280;margin-top:0">Datos propios, sin Google Analytics. Actualizado en tiempo real.</p>

        <!-- Tarjetas de resumen -->
        <div class="anhqv-stats-grid">
            <div class="anhqv-stat-card"><div class="stat-icon">📝</div><div class="stat-number"><?php echo number_format($total_posts); ?></div><div class="stat-label">Artículos publicados</div></div>
            <div class="anhqv-stat-card"><div class="stat-icon">👁</div><div class="stat-number"><?php echo number_format($total_views); ?></div><div class="stat-label">Visitas totales</div></div>
            <div class="anhqv-stat-card"><div class="stat-icon">💬</div><div class="stat-number"><?php echo number_format($total_comments); ?></div><div class="stat-label">Comentarios</div></div>
            <div class="anhqv-stat-card"><div class="stat-icon">⭐</div><div class="stat-number"><?php echo number_format($total_ratings); ?></div><div class="stat-label">Posts valorados</div></div>
            <div class="anhqv-stat-card"><div class="stat-icon">📬</div><div class="stat-number"><?php echo number_format($total_subs); ?></div><div class="stat-label">Suscriptores newsletter</div></div>
            <div class="anhqv-stat-card"><div class="stat-icon">🔖</div><div class="stat-number"><?php echo number_format($total_favs); ?></div><div class="stat-label">Posts con favoritos</div></div>
        </div>

        <!-- Top 10 posts más vistos -->
        <h2 class="anhqv-section-title">🏆 Top 10 artículos más vistos</h2>
        <table class="anhqv-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Artículo</th>
                    <th>Visitas</th>
                    <th>Barra</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_posts as $i => $post):
                    $bar_w = $max_views > 0 ? round(((int)$post->views / $max_views) * 200) : 0;
                ?>
                <tr>
                    <td class="rank"><?php echo $i + 1; ?></td>
                    <td><a href="<?php echo esc_url(get_permalink($post->ID)); ?>" target="_blank"><?php echo esc_html($post->post_title); ?></a></td>
                    <td><strong><?php echo number_format((int)$post->views); ?></strong></td>
                    <td><div class="views-bar"><div class="bar" style="width:<?php echo $bar_w; ?>px"></div></div></td>
                    <td><a href="<?php echo get_edit_post_link($post->ID); ?>">Editar</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($top_posts)): ?>
                    <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:2rem">Todavía no hay datos de visitas. Las visitas se registran automáticamente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="anhqv-cols">

            <!-- Top valorados -->
            <div class="anhqv-panel">
                <h3>⭐ Posts mejor valorados</h3>
                <?php if (!empty($rated_list)): ?>
                <table class="anhqv-table" style="box-shadow:none;border:none">
                    <thead><tr><th>#</th><th>Artículo</th><th>Media</th><th>Votos</th></tr></thead>
                    <tbody>
                        <?php foreach ($rated_list as $i => $r): ?>
                        <tr>
                            <td class="rank"><?php echo $i+1; ?></td>
                            <td><a href="<?php echo esc_url(get_permalink($r['id'])); ?>" target="_blank"><?php echo esc_html($r['title']); ?></a></td>
                            <td><span class="rating-star">★</span> <strong><?php echo $r['average']; ?></strong>/5</td>
                            <td><?php echo $r['count']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="color:#9ca3af">Todavía no hay valoraciones.</p>
                <?php endif; ?>
            </div>

            <!-- Visitas por categoría -->
            <div class="anhqv-panel">
                <h3>📁 Visitas por categoría</h3>
                <table class="anhqv-table" style="box-shadow:none;border:none">
                    <thead><tr><th>Categoría</th><th>Posts</th><th>Total visitas</th></tr></thead>
                    <tbody>
                        <?php foreach ($cats_stats as $cat):
                            $cat_posts = get_posts(array('category' => $cat->term_id, 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'publish'));
                            $cat_views = 0;
                            foreach ($cat_posts as $pid) {
                                $cat_views += (int) get_post_meta($pid, 'anhqv_view_count', true);
                            }
                        ?>
                        <tr>
                            <td><a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>" target="_blank"><?php echo esc_html($cat->name); ?></a></td>
                            <td><?php echo count($cat_posts); ?></td>
                            <td><strong><?php echo number_format($cat_views); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Suscriptores newsletter -->
        <?php if (!empty($subscribers)): ?>
        <h2 class="anhqv-section-title">📬 Suscriptores del Newsletter (<?php echo count($subscribers); ?>)</h2>
        <div class="anhqv-panel" style="margin-top:0">
            <p style="font-size:0.85rem;color:#6b7280">
                <?php echo implode(' · ', array_map('esc_html', array_slice($subscribers, 0, 20))); ?>
                <?php if (count($subscribers) > 20): ?> <em>... y <?php echo count($subscribers) - 20; ?> más.</em><?php endif; ?>
            </p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=anhqv-stats&export=csv')); ?>" class="anhqv-export-btn">⬇ Exportar lista CSV</a>
        </div>
        <?php endif; ?>

        <!-- Zona peligrosa -->
        <h2 class="anhqv-section-title">⚙️ Herramientas</h2>
        <div class="anhqv-panel">
            <p style="font-size:0.9rem;color:#374151">Reinicia todos los contadores de visitas. <strong>Esta acción no se puede deshacer.</strong></p>
            <form method="post" onsubmit="return confirm('¿Seguro? Se borrarán TODOS los contadores de visitas.')">
                <?php wp_nonce_field('anhqv_reset_stats_nonce'); ?>
                <button type="submit" name="anhqv_reset_stats" value="1" class="anhqv-reset-btn">🗑 Reiniciar contadores</button>
            </form>
        </div>

        <p style="margin-top:2rem;font-size:0.8rem;color:#9ca3af">
            ANHQV Stats Dashboard v2.5.0 · Sin cookies · Sin Google Analytics · 100% propio.
        </p>
    </div>
    <?php
}

/**
 * Exportar suscriptores como CSV
 */
function anhqv_export_stats_csv()
{
    if (!current_user_can('edit_posts')) return;
    $subscribers = get_option('anhqv_newsletter_subscribers', array());
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="suscriptores-anhqv-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, array('Email', 'Fecha_registro'));
    foreach ($subscribers as $email) {
        fputcsv($out, array($email, ''));
    }
    fclose($out);
    exit;
}


// =============================================================================
// Limpiar caché de series al guardar
// =============================================================================
function anhqv_clear_series_cache($post_id)
{
    $series = get_the_terms($post_id, 'serie');
    if (empty($series) || is_wp_error($series)) return;
    foreach ($series as $s) {
        delete_transient('anhqv_series_' . md5($s->slug));
    }
}
add_action('save_post', 'anhqv_clear_series_cache');

// =============================================================================
// CONFIGURACIÓN REDES SOCIALES — WordPress Customizer
// =============================================================================

/**
 * Añadir sección "Redes Sociales" al Customizer de WordPress.
 * Acceso: Apariencia → Personalizar → Redes Sociales
 */
function anhqv_customize_register($wp_customize)
{
    // ── Sección ──────────────────────────────────────────────
    $wp_customize->add_section('anhqv_social_media', array(
        'title'       => '🌐 Redes Sociales',
        'description' => 'Configura tus perfiles de redes sociales. Los que dejes vacíos no aparecerán en los botones de compartir.',
        'priority'    => 50,
    ));

    // ── Campos ───────────────────────────────────────────────
    $social_networks = array(
        'twitter'   => array('label' => '𝕏 / Twitter — usuario (sin @)',   'placeholder' => 'tuusuario'),
        'facebook'  => array('label' => 'Facebook — URL de tu página',      'placeholder' => 'https://facebook.com/tupagina'),
        'linkedin'  => array('label' => 'LinkedIn — URL de tu perfil',      'placeholder' => 'https://linkedin.com/in/tuperfil'),
        'instagram' => array('label' => 'Instagram — usuario (sin @)',      'placeholder' => 'tuusuario'),
        'youtube'   => array('label' => 'YouTube — URL de tu canal',        'placeholder' => 'https://youtube.com/@tucanal'),
        'tiktok'    => array('label' => 'TikTok — usuario (sin @)',         'placeholder' => 'tuusuario'),
        'whatsapp'  => array('label' => 'WhatsApp — activar botón compartir','placeholder' => ''),
        'telegram'  => array('label' => 'Telegram — activar botón compartir','placeholder' => ''),
        'pinterest' => array('label' => 'Pinterest — activar botón compartir','placeholder' => ''),
    );

    foreach ($social_networks as $key => $opts) {
        $setting_id = 'anhqv_social_' . $key;

        $wp_customize->add_setting($setting_id, array(
            'default'           => '',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));

        $wp_customize->add_control($setting_id, array(
            'label'       => $opts['label'],
            'section'     => 'anhqv_social_media',
            'type'        => 'text',
            'input_attrs' => array('placeholder' => $opts['placeholder']),
        ));
    }

    // ── Opción: mostrar botón de email ──
    $wp_customize->add_setting('anhqv_social_show_email', array(
        'default'           => '1',
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('anhqv_social_show_email', array(
        'label'   => 'Mostrar botón "Compartir por email"',
        'section' => 'anhqv_social_media',
        'type'    => 'checkbox',
    ));
}
add_action('customize_register', 'anhqv_customize_register');

/**
 * Helper: obtiene el valor de una red social configurada.
 */
function anhqv_social($key)
{
    return get_theme_mod('anhqv_social_' . $key, '');
}

/**
 * Genera los botones de compartir basándose en la configuración del Customizer.
 * Reemplaza a la función anterior anhqv_social_share_buttons().
 */
function anhqv_social_share_buttons()
{
    $url   = urlencode(get_permalink());
    $title = urlencode(get_the_title());
    $img   = has_post_thumbnail() ? urlencode(get_the_post_thumbnail_url(get_the_ID(), 'large')) : '';

    $twitter   = anhqv_social('twitter');
    $facebook  = anhqv_social('facebook');
    $linkedin  = anhqv_social('linkedin');
    $instagram = anhqv_social('instagram');
    $youtube   = anhqv_social('youtube');
    $tiktok    = anhqv_social('tiktok');
    $whatsapp  = anhqv_social('whatsapp');
    $telegram  = anhqv_social('telegram');
    $pinterest = anhqv_social('pinterest');
    $show_email = get_theme_mod('anhqv_social_show_email', '1');
    ?>
    <div class="social-share">
        <span class="share-label">Compartir:</span>

        <!-- X / Twitter — solo si está configurado -->
        <?php if ($twitter): ?>
        <?php $via = '&via=' . urlencode($twitter); ?>
        <a href="https://x.com/intent/tweet?url=<?php echo $url; ?>&text=<?php echo $title . $via; ?>"
           target="_blank" rel="noopener noreferrer"
           class="share-btn x-twitter" aria-label="Compartir en X / Twitter">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L2.25 2.25h6.985l4.26 5.634L18.244 2.25Zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77Z"/>
            </svg>
        </a>
        <?php endif; ?>

        <!-- Facebook — solo si está configurado -->
        <?php if ($facebook): ?>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $url; ?>"
           target="_blank" rel="noopener noreferrer"
           class="share-btn facebook" aria-label="Compartir en Facebook">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
            </svg>
        </a>
        <?php endif; ?>

        <!-- WhatsApp — solo si está activado -->
        <?php if ($whatsapp): ?>
        <a href="https://wa.me/?text=<?php echo $title . '%20' . $url; ?>"
           target="_blank" rel="noopener noreferrer"
           class="share-btn whatsapp" aria-label="Compartir en WhatsApp">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
        </a>
        <?php endif; ?>

        <!-- Telegram — solo si está activado -->
        <?php if ($telegram): ?>
        <a href="https://t.me/share/url?url=<?php echo $url; ?>&text=<?php echo $title; ?>"
           target="_blank" rel="noopener noreferrer"
           class="share-btn telegram" aria-label="Compartir en Telegram">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12l-6.871 4.326-2.962-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.194 1.006.131.833.941z"/>
            </svg>
        </a>
        <?php endif; ?>

        <!-- LinkedIn — solo si está configurado -->
        <?php if ($linkedin): ?>
        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url; ?>&title=<?php echo $title; ?>"
           target="_blank" rel="noopener noreferrer"
           class="share-btn linkedin" aria-label="Compartir en LinkedIn">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/>
                <circle cx="4" cy="4" r="2"/>
            </svg>
        </a>
        <?php endif; ?>

        <!-- Pinterest — solo si está activado -->
        <?php if ($pinterest): ?>
        <a href="https://pinterest.com/pin/create/button/?url=<?php echo $url; ?>&media=<?php echo $img; ?>&description=<?php echo $title; ?>"
           target="_blank" rel="noopener noreferrer"
           class="share-btn pinterest" aria-label="Compartir en Pinterest">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 01.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/>
            </svg>
        </a>
        <?php endif; ?>

        <!-- Instagram — enlace al perfil si está configurado -->
        <?php if ($instagram): ?>
        <a href="https://instagram.com/<?php echo urlencode($instagram); ?>/"
           target="_blank" rel="noopener noreferrer"
           class="share-btn instagram" aria-label="Seguir en Instagram">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
            </svg>
        </a>
        <?php endif; ?>

        <!-- YouTube — enlace al canal si está configurado -->
        <?php if ($youtube): ?>
        <a href="<?php echo esc_url($youtube); ?>"
           target="_blank" rel="noopener noreferrer"
           class="share-btn youtube" aria-label="Ver en YouTube">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
        </a>
        <?php endif; ?>

        <!-- TikTok — enlace al perfil si está configurado -->
        <?php if ($tiktok): ?>
        <a href="https://tiktok.com/@<?php echo urlencode($tiktok); ?>"
           target="_blank" rel="noopener noreferrer"
           class="share-btn tiktok" aria-label="Seguir en TikTok">
            <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.78 1.52V6.76a4.85 4.85 0 0 1-1.01-.07z"/>
            </svg>
        </a>
        <?php endif; ?>

        <!-- Email — si está activado en el Customizer -->
        <?php if ($show_email): ?>
        <a href="mailto:?subject=<?php echo $title; ?>&body=<?php echo $url; ?>"
           class="share-btn email" aria-label="Compartir por email">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
        </a>
        <?php endif; ?>

    </div>
    <?php
}
