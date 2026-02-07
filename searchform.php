<?php
/**
 * The template for displaying search forms in ANHQV Tech
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <label>
        <span class="screen-reader-text">
            <?php echo _x('Search for:', 'label', 'anhqv-tech'); ?>
        </span>
        <input type="search" class="search-field"
            placeholder="<?php echo esc_attr_x('Buscar noticias...', 'placeholder', 'anhqv-tech'); ?>"
            value="<?php echo get_search_query(); ?>" name="s" />
    </label>
    <button type="submit" class="search-submit">
        <span class="icon-search">Go</span>
    </button>
</form>