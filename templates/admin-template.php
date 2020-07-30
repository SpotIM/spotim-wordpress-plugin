<div class="wrap ow-page-wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <nav class="nav-tab-wrapper">
        <a href="<?php echo esc_url( '?page=' . $this->slug . '&tab=general' ); ?>" class="nav-tab <?php echo ( 'general' === $this->active_tab ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'General', 'ow' ); ?></a>
        <a href="<?php echo esc_url( '?page=' . $this->slug . '&tab=display' ); ?>" class="nav-tab <?php echo ( 'display' === $this->active_tab ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Display', 'ow' ); ?></a>
        <a href="<?php echo esc_url( '?page=' . $this->slug . '&tab=import' ); ?>" class="nav-tab <?php echo ( 'import' === $this->active_tab ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Comments Sync', 'ow' ); ?></a>
        <a href="<?php echo esc_url( '?page=' . $this->slug . '&tab=advanced' ); ?>" class="nav-tab <?php echo ( 'advanced' === $this->active_tab ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Advanced', 'ow' ); ?></a>
        <a href="https://admin.spot.im/" class="nav-tab pull-to-end ow-dashboard-button" target="_blank"><?php esc_html_e( 'OpenWeb.Com Dashboard', 'ow' ); ?></a>
    </nav>

    <form method="post" action="options.php">
        <?php
        settings_fields( $this->option_group );
        do_settings_sections( $this->slug );
        submit_button();
        ?>
    </form>
</div>
