<?php
/**
 * ANHQV Tech - Custom Widgets
 * Widgets personalizados para el tema
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ============================================
 * 1. WIDGET: Posts Populares
 * Muestra los posts más vistos en el último año
 * ============================================
 */
class ANHQV_Popular_Posts_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'anhqv_popular_posts',
            '📊 ANHQV - Posts Populares',
            array('description' => 'Muestra los posts más vistos en el último año')
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        $title = !empty($instance['title']) ? $instance['title'] : 'Posts Populares';
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        $period = !empty($instance['period']) ? $instance['period'] : 'year';

        echo $args['before_title'] . esc_html($title) . $args['after_title'];

        // Calcular fecha límite según el período
        $date_query = array();
        switch ($period) {
            case 'week':
                $date_query = array(
                    array(
                        'after' => '1 week ago',
                    ),
                );
                break;
            case 'month':
                $date_query = array(
                    array(
                        'after' => '1 month ago',
                    ),
                );
                break;
            case 'year':
            default:
                $date_query = array(
                    array(
                        'after' => '1 year ago',
                    ),
                );
                break;
        }

        $popular_args = array(
            'posts_per_page' => $number,
            'post_status' => 'publish',
            'ignore_sticky_posts' => 1,
            'meta_key' => 'anhqv_post_views',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
            'date_query' => $date_query,
        );

        $popular_posts = new WP_Query($popular_args);

        if ($popular_posts->have_posts()):
            ?>
            <ul class="popular-posts-widget">
                <?php while ($popular_posts->have_posts()):
                    $popular_posts->the_post();
                    $views = get_post_meta(get_the_ID(), 'anhqv_post_views', true);
                    $views = $views ? $views : 0;
                    ?>
                    <li class="popular-post-item">
                        <?php if (has_post_thumbnail()): ?>
                            <div class="popular-post-thumb">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('thumbnail'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <div class="popular-post-content">
                            <h4 class="popular-post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h4>
                            <div class="popular-post-meta">
                                <span class="post-date"><?php echo get_the_date(); ?></span>
                                <span class="post-views">👁️ <?php echo number_format($views); ?> vistas</span>
                            </div>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
            <?php
            wp_reset_postdata();
        else:
            echo '<p>No hay posts populares aún.</p>';
        endif;

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : 'Posts Populares';
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        $period = !empty($instance['period']) ? $instance['period'] : 'year';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Título:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>">Número de posts:</label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('number')); ?>"
                name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="number" step="1" min="1"
                value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('period')); ?>">Período de tiempo:</label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('period')); ?>"
                name="<?php echo esc_attr($this->get_field_name('period')); ?>">
                <option value="week" <?php selected($period, 'week'); ?>>Última semana</option>
                <option value="month" <?php selected($period, 'month'); ?>>Último mes</option>
                <option value="year" <?php selected($period, 'year'); ?>>Último año</option>
            </select>
        </p>
        <p style="background: #f0f0f1; padding: 8px; border-left: 3px solid #2271b1;">
            <small><strong>Nota:</strong> Los posts se ordenan por número de vistas. El contador se activa automáticamente.</small>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 5;
        $instance['period'] = (!empty($new_instance['period'])) ? sanitize_text_field($new_instance['period']) : 'year';
        return $instance;
    }
}

/**
 * ============================================
 * 2. WIDGET: Categorías con Iconos
 * Categorías con iconos y contador de posts
 * ============================================
 */
class ANHQV_Icon_Categories_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'anhqv_icon_categories',
            '📁 ANHQV - Categorías con Iconos',
            array('description' => 'Muestra categorías con iconos personalizables')
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        $title = !empty($instance['title']) ? $instance['title'] : 'Categorías';
        $show_count = isset($instance['show_count']) ? (bool) $instance['show_count'] : true;

        echo $args['before_title'] . esc_html($title) . $args['after_title'];

        // Iconos por categoría (puedes personalizarlos)
        $category_icons = array(
            'tecnologia' => '💻',
            'ciencia' => '🔬',
            'ia' => '🤖',
            'hardware' => '🖥️',
            'software' => '📱',
            'astronomia' => '🌌',
            'espacio' => '🚀',
            'cartagena' => '🏛️',
            'historia' => '📜',
            'salud' => '⚕️',
            'juegos' => '🎮',
            'internet' => '🌐',
            'seguridad' => '🔒',
        );

        $categories = get_categories(array(
            'orderby' => 'count',
            'order' => 'DESC',
            'number' => 10,
            'hide_empty' => true,
        ));

        if (!empty($categories)):
            ?>
            <ul class="icon-categories-widget">
                <?php foreach ($categories as $category):
                    $icon = isset($category_icons[$category->slug]) ? $category_icons[$category->slug] : '📌';
                    ?>
                    <li class="icon-category-item">
                        <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="category-link">
                            <span class="category-icon"><?php echo $icon; ?></span>
                            <span class="category-name"><?php echo esc_html($category->name); ?></span>
                            <?php if ($show_count): ?>
                                <span class="category-count">(<?php echo $category->count; ?>)</span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php
        endif;

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : 'Categorías';
        $show_count = isset($instance['show_count']) ? (bool) $instance['show_count'] : true;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Título:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_count); ?>
                id="<?php echo esc_attr($this->get_field_id('show_count')); ?>"
                name="<?php echo esc_attr($this->get_field_name('show_count')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_count')); ?>">Mostrar contador de posts</label>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['show_count'] = (!empty($new_instance['show_count'])) ? 1 : 0;
        return $instance;
    }
}

/**
 * ============================================
 * 3. WIDGET: Redes Sociales
 * Links a redes sociales con iconos
 * ============================================
 */
class ANHQV_Social_Links_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'anhqv_social_links',
            '📱 ANHQV - Redes Sociales',
            array('description' => 'Muestra enlaces a redes sociales')
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        $title = !empty($instance['title']) ? $instance['title'] : 'Síguenos';

        echo $args['before_title'] . esc_html($title) . $args['after_title'];

        ?>
        <div class="social-links-widget">
            <?php if (!empty($instance['twitter'])): ?>
                <a href="<?php echo esc_url($instance['twitter']); ?>" target="_blank" rel="noopener noreferrer"
                    class="social-link twitter" aria-label="Twitter">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z">
                        </path>
                    </svg>
                </a>
            <?php endif; ?>

            <?php if (!empty($instance['facebook'])): ?>
                <a href="<?php echo esc_url($instance['facebook']); ?>" target="_blank" rel="noopener noreferrer"
                    class="social-link facebook" aria-label="Facebook">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"></path>
                    </svg>
                </a>
            <?php endif; ?>

            <?php if (!empty($instance['instagram'])): ?>
                <a href="<?php echo esc_url($instance['instagram']); ?>" target="_blank" rel="noopener noreferrer"
                    class="social-link instagram" aria-label="Instagram">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                        <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"></path>
                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                    </svg>
                </a>
            <?php endif; ?>

            <?php if (!empty($instance['youtube'])): ?>
                <a href="<?php echo esc_url($instance['youtube']); ?>" target="_blank" rel="noopener noreferrer"
                    class="social-link youtube" aria-label="YouTube">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M22.54 6.42a2.78 2.78 0 00-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 2A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 001.94-2 29 29 0 00.46-5.25 29 29 0 00-.46-5.33z">
                        </path>
                        <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                    </svg>
                </a>
            <?php endif; ?>

            <?php if (!empty($instance['github'])): ?>
                <a href="<?php echo esc_url($instance['github']); ?>" target="_blank" rel="noopener noreferrer"
                    class="social-link github" aria-label="GitHub">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 00-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0020 4.77 5.07 5.07 0 0019.91 1S18.73.65 16 2.48a13.38 13.38 0 00-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 005 4.77a5.44 5.44 0 00-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 009 18.13V22">
                        </path>
                    </svg>
                </a>
            <?php endif; ?>

            <?php if (!empty($instance['linkedin'])): ?>
                <a href="<?php echo esc_url($instance['linkedin']); ?>" target="_blank" rel="noopener noreferrer"
                    class="social-link linkedin" aria-label="LinkedIn">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"></path>
                        <circle cx="4" cy="4" r="2"></circle>
                    </svg>
                </a>
            <?php endif; ?>

            <?php if (!empty($instance['rss'])): ?>
                <a href="<?php echo esc_url($instance['rss']); ?>" target="_blank" rel="noopener noreferrer"
                    class="social-link rss" aria-label="RSS">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4 11a9 9 0 019 9"></path>
                        <path d="M4 4a16 16 0 0116 16"></path>
                        <circle cx="5" cy="19" r="1"></circle>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
        <?php

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : 'Síguenos';
        $twitter = !empty($instance['twitter']) ? $instance['twitter'] : '';
        $facebook = !empty($instance['facebook']) ? $instance['facebook'] : '';
        $instagram = !empty($instance['instagram']) ? $instance['instagram'] : '';
        $youtube = !empty($instance['youtube']) ? $instance['youtube'] : '';
        $github = !empty($instance['github']) ? $instance['github'] : '';
        $linkedin = !empty($instance['linkedin']) ? $instance['linkedin'] : '';
        $rss = !empty($instance['rss']) ? $instance['rss'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Título:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('twitter')); ?>">Twitter URL:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('twitter')); ?>"
                name="<?php echo esc_attr($this->get_field_name('twitter')); ?>" type="text"
                value="<?php echo esc_url($twitter); ?>" placeholder="https://twitter.com/tu_usuario">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('facebook')); ?>">Facebook URL:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('facebook')); ?>"
                name="<?php echo esc_attr($this->get_field_name('facebook')); ?>" type="text"
                value="<?php echo esc_url($facebook); ?>" placeholder="https://facebook.com/tu_pagina">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('instagram')); ?>">Instagram URL:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('instagram')); ?>"
                name="<?php echo esc_attr($this->get_field_name('instagram')); ?>" type="text"
                value="<?php echo esc_url($instagram); ?>" placeholder="https://instagram.com/tu_usuario">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('youtube')); ?>">YouTube URL:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('youtube')); ?>"
                name="<?php echo esc_attr($this->get_field_name('youtube')); ?>" type="text"
                value="<?php echo esc_url($youtube); ?>" placeholder="https://youtube.com/c/tu_canal">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('github')); ?>">GitHub URL:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('github')); ?>"
                name="<?php echo esc_attr($this->get_field_name('github')); ?>" type="text"
                value="<?php echo esc_url($github); ?>" placeholder="https://github.com/tu_usuario">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('linkedin')); ?>">LinkedIn URL:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('linkedin')); ?>"
                name="<?php echo esc_attr($this->get_field_name('linkedin')); ?>" type="text"
                value="<?php echo esc_url($linkedin); ?>" placeholder="https://linkedin.com/in/tu_perfil">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('rss')); ?>">RSS URL:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('rss')); ?>"
                name="<?php echo esc_attr($this->get_field_name('rss')); ?>" type="text"
                value="<?php echo esc_url($rss); ?>"
                placeholder="<?php echo esc_url(get_bloginfo('rss2_url')); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['twitter'] = (!empty($new_instance['twitter'])) ? esc_url_raw($new_instance['twitter']) : '';
        $instance['facebook'] = (!empty($new_instance['facebook'])) ? esc_url_raw($new_instance['facebook']) : '';
        $instance['instagram'] = (!empty($new_instance['instagram'])) ? esc_url_raw($new_instance['instagram']) : '';
        $instance['youtube'] = (!empty($new_instance['youtube'])) ? esc_url_raw($new_instance['youtube']) : '';
        $instance['github'] = (!empty($new_instance['github'])) ? esc_url_raw($new_instance['github']) : '';
        $instance['linkedin'] = (!empty($new_instance['linkedin'])) ? esc_url_raw($new_instance['linkedin']) : '';
        $instance['rss'] = (!empty($new_instance['rss'])) ? esc_url_raw($new_instance['rss']) : '';
        return $instance;
    }
}

/**
 * ============================================
 * 4. WIDGET: About / Perfil del Autor
 * Información sobre el autor del blog
 * ============================================
 */
class ANHQV_About_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'anhqv_about',
            '👤 ANHQV - Sobre el Autor',
            array('description' => 'Información sobre el autor del blog')
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        $title = !empty($instance['title']) ? $instance['title'] : 'Sobre el Autor';
        $name = !empty($instance['name']) ? $instance['name'] : get_bloginfo('name');
        $description = !empty($instance['description']) ? $instance['description'] : '';
        $avatar = !empty($instance['avatar']) ? $instance['avatar'] : '';

        echo $args['before_title'] . esc_html($title) . $args['after_title'];

        ?>
        <div class="about-widget">
            <?php if ($avatar): ?>
                <div class="about-avatar">
                    <img src="<?php echo esc_url($avatar); ?>" alt="<?php echo esc_attr($name); ?>">
                </div>
            <?php endif; ?>
            <div class="about-content">
                <h3 class="about-name"><?php echo esc_html($name); ?></h3>
                <?php if ($description): ?>
                    <p class="about-description"><?php echo wp_kses_post($description); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : 'Sobre el Autor';
        $name = !empty($instance['name']) ? $instance['name'] : '';
        $description = !empty($instance['description']) ? $instance['description'] : '';
        $avatar = !empty($instance['avatar']) ? $instance['avatar'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Título:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('name')); ?>">Nombre:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('name')); ?>"
                name="<?php echo esc_attr($this->get_field_name('name')); ?>" type="text"
                value="<?php echo esc_attr($name); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('description')); ?>">Descripción:</label>
            <textarea class="widefat" rows="5" id="<?php echo esc_attr($this->get_field_id('description')); ?>"
                name="<?php echo esc_attr($this->get_field_name('description')); ?>"><?php echo esc_textarea($description); ?></textarea>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('avatar')); ?>">URL de Avatar:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('avatar')); ?>"
                name="<?php echo esc_attr($this->get_field_name('avatar')); ?>" type="text"
                value="<?php echo esc_url($avatar); ?>" placeholder="https://ejemplo.com/avatar.jpg">
            <small>Sube una imagen en Medios y pega la URL aquí</small>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['name'] = (!empty($new_instance['name'])) ? sanitize_text_field($new_instance['name']) : '';
        $instance['description'] = (!empty($new_instance['description'])) ? wp_kses_post($new_instance['description']) : '';
        $instance['avatar'] = (!empty($new_instance['avatar'])) ? esc_url_raw($new_instance['avatar']) : '';
        return $instance;
    }
}

/**
 * ============================================
 * 5. WIDGET: Últimas Noticias por Categoría
 * Muestra posts recientes de una categoría
 * ============================================
 */
class ANHQV_Category_Posts_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'anhqv_category_posts',
            '📰 ANHQV - Posts por Categoría',
            array('description' => 'Muestra posts de una categoría específica')
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        $title = !empty($instance['title']) ? $instance['title'] : 'Últimas Noticias';
        $category = !empty($instance['category']) ? $instance['category'] : '';
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;

        echo $args['before_title'] . esc_html($title) . $args['after_title'];

        if ($category):
            $cat_posts = new WP_Query(array(
                'cat' => $category,
                'posts_per_page' => $number,
                'post_status' => 'publish',
            ));

            if ($cat_posts->have_posts()):
                ?>
                <ul class="category-posts-widget">
                    <?php while ($cat_posts->have_posts()):
                        $cat_posts->the_post(); ?>
                        <li class="category-post-item">
                            <a href="<?php the_permalink(); ?>" class="category-post-link">
                                <span class="category-post-title"><?php the_title(); ?></span>
                                <span class="category-post-date"><?php echo get_the_date(); ?></span>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
                <?php
                wp_reset_postdata();
            endif;
        else:
            echo '<p>Por favor, selecciona una categoría en la configuración del widget.</p>';
        endif;

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : 'Últimas Noticias';
        $category = !empty($instance['category']) ? $instance['category'] : '';
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;

        $categories = get_categories(array('hide_empty' => true));
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Título:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('category')); ?>">Categoría:</label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('category')); ?>"
                name="<?php echo esc_attr($this->get_field_name('category')); ?>">
                <option value="">-- Seleccionar --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat->term_id; ?>" <?php selected($category, $cat->term_id); ?>>
                        <?php echo esc_html($cat->name); ?> (<?php echo $cat->count; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>">Número de posts:</label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('number')); ?>"
                name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="number" step="1" min="1"
                value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['category'] = (!empty($new_instance['category'])) ? absint($new_instance['category']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 5;
        return $instance;
    }
}

/**
 * ============================================
 * 6. WIDGET: Tags Cloud Mejorado
 * Nube de tags con estilo moderno
 * ============================================
 */
class ANHQV_Tags_Cloud_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'anhqv_tags_cloud',
            '🏷️ ANHQV - Nube de Tags',
            array('description' => 'Muestra tags con diseño moderno')
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];

        $title = !empty($instance['title']) ? $instance['title'] : 'Tags Populares';
        $number = !empty($instance['number']) ? absint($instance['number']) : 20;

        echo $args['before_title'] . esc_html($title) . $args['after_title'];

        $tags = get_tags(array(
            'orderby' => 'count',
            'order' => 'DESC',
            'number' => $number,
        ));

        if (!empty($tags)):
            ?>
            <div class="tags-cloud-widget">
                <?php foreach ($tags as $tag): ?>
                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="tag-item"
                        title="<?php echo esc_attr($tag->count . ' posts'); ?>">
                        <?php echo esc_html($tag->name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php
        endif;

        echo $args['after_widget'];
    }

    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : 'Tags Populares';
        $number = !empty($instance['number']) ? absint($instance['number']) : 20;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Título:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>">Número de tags:</label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('number')); ?>"
                name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="number" step="1" min="1"
                value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 20;
        return $instance;
    }
}

/**
 * ============================================
 * Registrar todos los widgets
 * ============================================
 */
function anhqv_register_custom_widgets()
{
    register_widget('ANHQV_Popular_Posts_Widget');
    register_widget('ANHQV_Icon_Categories_Widget');
    register_widget('ANHQV_Social_Links_Widget');
    register_widget('ANHQV_About_Widget');
    register_widget('ANHQV_Category_Posts_Widget');
    register_widget('ANHQV_Tags_Cloud_Widget');
}
add_action('widgets_init', 'anhqv_register_custom_widgets');

/**
 * ============================================
 * Sistema de Contador de Visitas
 * Cuenta las visitas a cada post automáticamente
 * ============================================
 */

/**
 * Incrementar contador de visitas al ver un post
 */
function anhqv_track_post_views($post_id)
{
    // No contar si es admin, bot o preview
    if (is_admin() || wp_is_json_request() || is_preview()) {
        return;
    }

    // No contar múltiples vistas en la misma sesión (opcional)
    $session_key = 'anhqv_viewed_' . $post_id;
    if (isset($_SESSION[$session_key])) {
        return;
    }

    // Obtener contador actual
    $views = get_post_meta($post_id, 'anhqv_post_views', true);
    $views = $views ? intval($views) : 0;

    // Incrementar
    $views++;

    // Guardar
    update_post_meta($post_id, 'anhqv_post_views', $views);

    // Marcar como visto en esta sesión
    if (!session_id()) {
        session_start();
    }
    $_SESSION[$session_key] = true;
}

/**
 * Hook para contar visitas cuando se carga un post
 */
function anhqv_count_post_view()
{
    if (is_single()) {
        global $post;
        if (!empty($post)) {
            anhqv_track_post_views($post->ID);
        }
    }
}
add_action('wp_head', 'anhqv_count_post_view');

/**
 * Añadir columna de vistas en el admin
 */
function anhqv_add_views_column($columns)
{
    $columns['post_views'] = '👁️ Vistas';
    return $columns;
}
add_filter('manage_posts_columns', 'anhqv_add_views_column');

/**
 * Mostrar número de vistas en la columna
 */
function anhqv_display_views_column($column, $post_id)
{
    if ($column === 'post_views') {
        $views = get_post_meta($post_id, 'anhqv_post_views', true);
        echo $views ? number_format($views) : '0';
    }
}
add_action('manage_posts_custom_column', 'anhqv_display_views_column', 10, 2);

/**
 * Hacer columna de vistas ordenable
 */
function anhqv_views_column_sortable($columns)
{
    $columns['post_views'] = 'post_views';
    return $columns;
}
add_filter('manage_edit-post_sortable_columns', 'anhqv_views_column_sortable');

/**
 * Ordenar posts por número de vistas
 */
function anhqv_views_column_orderby($query)
{
    if (!is_admin()) {
        return;
    }

    $orderby = $query->get('orderby');
    if ($orderby === 'post_views') {
        $query->set('meta_key', 'anhqv_post_views');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'anhqv_views_column_orderby');

/**
 * Mostrar contador de vistas en el post (shortcode)
 * Uso: [post_views]
 */
function anhqv_post_views_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'post_id' => get_the_ID(),
        'text' => 'Vistas: ',
    ), $atts);

    $views = get_post_meta($atts['post_id'], 'anhqv_post_views', true);
    $views = $views ? intval($views) : 0;

    return '<span class="post-views-count">' . esc_html($atts['text']) . number_format($views) . '</span>';
}
add_shortcode('post_views', 'anhqv_post_views_shortcode');

/**
 * Función helper para obtener vistas de un post
 * Uso en template: echo anhqv_get_post_views($post_id);
 */
function anhqv_get_post_views($post_id = 0)
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $views = get_post_meta($post_id, 'anhqv_post_views', true);
    return $views ? intval($views) : 0;
}
