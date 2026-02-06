<?php 

require WP_TRAVEL_ABSPATH . '/inc/admin/importer/importer.php';

class WP_Travel_Demos_Lists {

	public function __construct() {

		// Hook into the submenu filter
		add_filter( 'wp_travel_submenus', [ $this, 'add_enquiry_settings_submenu' ] );
		
        add_action( 'wp_ajax_wptravel_install_theme', [ $this, 'install_theme' ] );
        add_action( 'wp_ajax_wptravel_activate_theme', [ $this, 'activate_theme' ] );
        add_action( 'wp_ajax_wptravel_import_demo', [ $this, 'import_demo' ] );
	}


	/**
	 * Adds a custom submenu item to WP Travel admin.
	 */
	public function add_enquiry_settings_submenu( $submenus ) {

		$submenus['bookings']['demo_lists'] = array(
			'priority'   => 145,
			'page_title' => 'Demos Lists',
			'menu_title' => 'Demo Sites',
			'menu_slug'  => 'wp-travel-demos-lists',
			'callback'   => [ __CLASS__, 'render_demo_lists_page' ],
		);

		return $submenus;
	}

    /* ---------------- AJAX: INSTALL THEME ---------------- */
    public function install_theme() {

        if ( ! check_ajax_referer( 'wptravel_demo_nonce', false, false ) ) {
            wp_send_json_error( 'Invalid nonce.', 403 );
            exit;
        }

        if ( ! current_user_can( 'install_themes' ) ) {
            wp_send_json_error( 'Permission denied' );
            exit;
        }

        $slug = sanitize_text_field( $_POST['slug'] );

        if ( wp_get_theme( $slug )->exists() ) {
            wp_send_json_success( 'Theme already installed' );
        }

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        // 🔕 Silence output
        $skin     = new Automatic_Upgrader_Skin();
        $upgrader = new Theme_Upgrader( $skin );

        $result = $upgrader->install(
            "https://downloads.wordpress.org/theme/{$slug}.zip"
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
            exit;
        }

        wp_send_json_success();
    }


    /* ---------------- AJAX: ACTIVATE THEME ---------------- */
    public function activate_theme() {

        if ( ! check_ajax_referer( 'wptravel_demo_nonce', false, false ) ) {
            wp_send_json_error( 'Invalid nonce.', 403 );
            exit;
        }

        if ( ! current_user_can( 'switch_themes' ) ) {
            wp_send_json_error( 'Permission denied' );
            exit;
        }

        $slug = sanitize_text_field( $_POST['slug'] );
        $theme = wp_get_theme( $slug );

        if ( ! $theme->exists() ) {
            wp_send_json_error( 'Theme not installed' );
            exit;
        }

        switch_theme( $theme->get_stylesheet() );

        wp_send_json_success();
    }

     /* ---------------- AJAX: IMPORT DEMO ---------------- */
    public function import_demo() {
        
        if ( ! check_ajax_referer( 'wptravel_demo_nonce', false, false ) ) {
            wp_send_json_error( 'Invalid nonce.', 403 );
            exit;
        }

        if ( ! current_user_can( 'import' ) ) {
            wp_send_json_error( 'Permission denied' );
            exit;
        }

        // Include required plugin functions
        if ( ! function_exists('is_plugin_active') ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        if ( ! function_exists('activate_plugin') ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        if ( ! class_exists('Plugin_Upgrader') ) {
            include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
        }
        if ( ! function_exists('plugins_api') ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
        }

        // Plugin slug/path
        $plugin_file = 'wp-travel-blocks/wp-travel-blocks.php';

        // Check if plugin is installed & active
        if ( ! is_plugin_active($plugin_file) ) {
           
            // Try to install the plugin from WordPress.org
            $api = plugins_api( 'plugin_information', [ 'slug' => 'wp-travel-blocks', 'fields' => ['sections'=>false] ] );
            if ( is_wp_error($api) ) {
                wp_send_json_error( 'Failed to get plugin info: ' . $api->get_error_message() );
                exit;
            }

            $upgrader = new Plugin_Upgrader();
            $installed = $upgrader->install( $api->download_link );

            // Activate the plugin
            $activate = activate_plugin($plugin_file);
            if ( is_wp_error($activate) ) {
                wp_send_json_error( 'Failed to activate wp-travel-blocks plugin: ' . $activate->get_error_message() );
                exit;
            }
        }

        
        // Get the demo slug from AJAX
        $slug = sanitize_text_field( $_POST['slug'] );


         if ( ! class_exists('WP_Importer') ) {
            $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
            if ( file_exists( $class_wp_importer ) ) {
                require $class_wp_importer;
            }            
        }       

        // Download the XML file to a temporary location
        $tmp_file = "https://wpdemo.wensolutions.com/demo-data/fse-demo/{$slug}/content.xml";
   
        // Check for errors while downloading the file
        if ( is_wp_error($tmp_file) ) {
            wp_send_json_error( array( 'message' => 'Error downloading XML file: ' . $tmp_file->get_error_message() ) );
            exit;
        }

        // Initialize the importer object
        $importer = new WP_Demo_Import();

        // Start the import process using the temporary downloaded file
        $importer->import($tmp_file);

        // Optionally, clean up the temporary file
        @unlink($tmp_file);

        wp_send_json_success( );
    }

	/**
	 * Renders the submenu page content.
	 */
    public static function render_demo_lists_page() {

        // 1. Get demo list from transient
        $transient_key = 'wptravel_demo_lists';
        $demo_list     = get_transient( $transient_key );

        if ( false === $demo_list ) {
            $url      = 'https://wpdemo.wensolutions.com/demo-data/fse-demo/demo.json';
            $response = wp_remote_get( $url );

            if ( is_wp_error( $response ) ) {
                echo '<p>Unable to load demos.</p>';
                return;
            }

            $demo_list = json_decode( wp_remote_retrieve_body( $response ), true );

            // Cache for 12 hours
            set_transient( $transient_key, $demo_list, 12 * HOUR_IN_SECONDS );
        }
        wp_nonce_field( 'wptravel_demo_nonce', 'wptravel_demo_nonce_field' );

        ?>

        <script>
            window.wptravelDemo = {
                nonce: document.getElementById('wptravel_demo_nonce_field').value
            };
        </script>

        <div id="wptravel-demo-lists-page">
            <h1 class="wp-heading-inline">Travel Demo Sites</h1>
            <hr class="wp-header-end">

            <div class="demo-grid">
                <?php foreach ( $demo_list as $key => $demo ) : ?>
                    <div class="demo-item">
                        
                        <img
                            src="https://wpdemo.wensolutions.com/demo-data/fse-demo/<?php echo esc_attr( $key ); ?>/screenshot.png"
                            alt="<?php echo esc_attr( $key ); ?>"
                        >

                        <h3 class="demo-title">
                            <?php echo esc_html( $demo['demo_name'] ); ?>
                        </h3>

                        <div class="demo-actions">
                            <button
                                class="button button-secondary wptravel-view-demo"
                                data-demo-url="https://wpdemo.wensolutions.com/<?php echo esc_attr( $key ); ?>"
                                data-demo-name="<?php echo esc_attr( $demo['demo_name'] ); ?>">
                                View Demo
                            </button>

                            <button class="wp-travel-theme-demo-import button button-primary" data-slug="<?php echo esc_attr( $key ); ?>">
                                Import Demo
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ================= Modal ================= -->
        <div id="wptravel-demo-modal">
            <div class="modal-inner">

                <div class="modal-header">
                    <h2 class="modal-title"></h2>
                    <div class="view-buttons">
                        <button class="view-btn" data-width="500">📱 Mobile</button>
                        <button class="view-btn" data-width="768">🖥 Tablet</button>
                        <button class="view-btn" data-width="1200">💻 Desktop</button>
                    </div>
                    <button class="modal-close" aria-label="Close">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="modal-loader">
                        <svg viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" width="100" height="100" style="shape-rendering: auto; display: block; background: transparent;">
                            <g>
                                <path stroke="none" fill="#279b37" d="M10 50A40 40 0 0 0 90 50A40 42 0 0 1 10 50">
                                    <animateTransform values="0 50 51;360 50 51" keyTimes="0;1" repeatCount="indefinite" dur="1s" type="rotate" attributeName="transform"></animateTransform>
                                </path>
                            </g>
                        </svg>
                        <p>Loading...</p>
                    </div>
                    <iframe src="" frameborder="0"></iframe>
                </div>

            </div>
        </div>

        <div id="wptravel-import-modal">
            <div class="import-inner">
                <h2>Importing Demo</h2>

                <ul class="import-steps">
                    <li data-step="theme">
                        Installing Theme <span class="step-loader">⏳</span>
                    </li>
                    <li data-step="activate">Activating Theme <span>⏳</span></li>
                    <li data-step="demo">Importing Demo - it may take 15-20 minutes<span>⏳</span></li>
                </ul>

                <p class="import-done" style="display:none;">✅ Demo Imported Successfully</p>
            </div>
        </div>

        <style>
            #wptravel-import-modal {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.6);
                display: none;
                align-items: center;
                justify-content: center;
                z-index: 999999;
            }

            #wptravel-import-modal.active {
                display: flex;
            }

            .import-inner {
                background: #fff;
                padding: 0 25px 25px 25px;
                width: 500px;
                border-radius: 8px;
            }

            .import-steps li {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
                font-size: 14px;
            }

            .import-steps li.done span {
                color: green;
            }

            .step-loader {
                display: inline-block;
                animation: wptravel-spin 1s linear infinite;
            }

            @keyframes wptravel-spin {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }

            /* Stop spinning when done */
            .import-steps li.done .step-loader {
                animation: none;
            }


        </style>

        <script>
            (function () {

                const importModal = document.getElementById('wptravel-import-modal');

                function openImportModal() {
                    importModal.classList.add('active');
                }

                function markDone(step) {
                    const el = importModal.querySelector(`[data-step="${step}"]`);
                    if (el) {
                        el.classList.add('done');
                        el.querySelector('span').textContent = '✔';
                    }
                }

                document.querySelectorAll('.wp-travel-theme-demo-import').forEach(btn => {
                    btn.addEventListener('click', function () {

                        const slug = this.dataset.slug;
                        openImportModal();

                        // Step 1: Install Theme
                        fetch(ajaxurl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({
                                action: 'wptravel_install_theme',
                                slug: slug,
                                _wpnonce: wptravelDemo.nonce
                            })
                        })
                        .then(res => res.json())
                        .then(() => {
                            markDone('theme');

                            // Step 2: Activate Theme
                            return fetch(ajaxurl, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: new URLSearchParams({
                                    action: 'wptravel_activate_theme',
                                    slug: slug,
                                    _wpnonce: wptravelDemo.nonce
                                })
                            });
                        })
                        .then(res => res.json())
                        .then(() => {
                            markDone('activate');
                            document.querySelector(`[data-step="demo"] span`).classList.add('step-loader');

                            // Step 3: Import Demo
                            return fetch(ajaxurl, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: new URLSearchParams({
                                    action: 'wptravel_import_demo',
                                    slug: slug,
                                    _wpnonce: wptravelDemo.nonce
                                })
                            });
                        })
                        .then(() => {
                            // Mark the demo step as done
                            markDone('demo');

                            // Show the success message
                            const doneMsg = importModal.querySelector('.import-done');
                            doneMsg.style.display = 'block';
                            doneMsg.textContent = '✅ Demo Imported Successfully. Redirecting to homepage…';

                            // Redirect after 2 seconds (2000 ms)
                            setTimeout(() => {
                                window.location.href = '/'; // Replace '/' with your dashboard URL if needed
                            }, 2000);
                        });                    

                    });
                });

            })();
        </script>

        <!-- ================= JS ================= -->
        <script>
        (function () {
            const modal    = document.getElementById('wptravel-demo-modal');
            const iframe   = modal.querySelector('iframe');
            const titleEl  = modal.querySelector('.modal-title');
            const closeBtn = modal.querySelector('.modal-close');
            const loader   = modal.querySelector('.modal-loader');
            const viewBtns = modal.querySelectorAll('.view-btn');

            function setIframeView(width) {
                if (width >= 1200) {
                    iframe.style.width = '100%'; // full modal width
                } else {
                    iframe.style.width = width + 'px'; // fixed width for mobile/tablet
                }
                iframe.style.maxWidth = '100%';
                viewBtns.forEach(b => b.classList.remove('active'));
                const btn = Array.from(viewBtns).find(b => b.dataset.width == width);
                if(btn) btn.classList.add('active');
            }

            document.querySelectorAll('.wptravel-view-demo').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    iframe.style.display = 'none';
                    loader.style.display = 'flex';
                    titleEl.textContent = this.dataset.demoName;
                    modal.classList.add('active');

                    // Default view = Desktop
                    setIframeView(1200);

                    iframe.src = this.dataset.demoUrl;

                    const hideLoader = () => {
                        loader.style.display = 'none';
                        iframe.style.display = 'block';
                    };

                    iframe.onload = hideLoader;
                    setTimeout(hideLoader, 2500);
                });
            });

            viewBtns.forEach(btn => {
                btn.addEventListener('click', function () {
                    setIframeView(this.dataset.width);
                });
            });

            function closeModal() {
                iframe.src = '';
                iframe.style.display = 'none';
                loader.style.display = 'flex';
                modal.classList.remove('active');
            }

            closeBtn.addEventListener('click', closeModal);

            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });

        })();
        </script>

    <?php
    }

}

new WP_Travel_Demos_Lists();
