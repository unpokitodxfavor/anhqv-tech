<?php
/**
 * Sidebar - ANHQV Tech v3
 * El buscador se eliminó del sidebar porque ahora existe
 * la búsqueda AJAX expandible en el header.
 */

if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside id="secondary" class="widget-area" role="complementary"
       aria-label="<?php esc_attr_e('Sidebar', 'anhqv-tech'); ?>">
    <?php dynamic_sidebar('sidebar-1'); ?>
</aside>
