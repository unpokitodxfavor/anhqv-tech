<?php
if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>


<aside id="secondary" class="widget-area">
    <div class="sidebar-search">
        <?php get_search_form(); ?>
    </div>
    <?php dynamic_sidebar('sidebar-1'); ?>
</aside><!-- #secondary -->
