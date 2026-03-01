<?php
/**
 * Sidebar - ANHQV Tech
 * FIX #10: Si no hay widgets activos, no renderizar el sidebar
 * para que el layout no quede roto con una columna vacía.
 */

// Si no hay widgets en el sidebar, salir sin renderizar nada.
// El layout-grid en index.php tendrá la clase 'no-sidebar' y
// usará una sola columna ocupando el 100% del ancho.
if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside id="secondary" class="widget-area" role="complementary" aria-label="<?php esc_attr_e('Sidebar', 'anhqv-tech'); ?>">

    <!-- Buscador -->
    <div class="sidebar-search widget">
        <?php get_search_form(); ?>
    </div>

    <?php dynamic_sidebar('sidebar-1'); ?>

</aside>
