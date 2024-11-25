<?php
/**
 * WordPress Admin UI
 *
 * @package typesense
 */

namespace Typesense;

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( 'options-reading.php' === $hook ) {
			wp_register_style( 'admin-typesense', plugin_uri( '/css/admin-typesense.css' ), false, plugin_version() );
			wp_enqueue_script( 'admin-typesense', plugin_uri( '/js/admin-typesense.js' ), array( 'jquery' ), plugin_version(), true );
			wp_enqueue_style( 'admin-typesense' );
		}
	}
);

add_action(
	'admin_init',
	function () {
		add_settings_section(
			'typesense_settings_section',
			__( 'Search settings', 'typesense' ),
			function () {
				echo '<p>' . esc_html( __( 'By default, WordPress uses the internal MySQL database for searches. If you have a more suitable datbase engine available, you can use it to run a faster and more accurate search engine.', 'typesense' ) ) . '</p>';
			},
			'reading',
			array(
				'section_class'  => 'typesense_settings',
				'before_section' => '<div style="margin-bottom: 40px;"></div>',
				'after_section'  => '<div style="margin-bottom: 40px;"></div>',
			),
		);

		$settings = array(
			'typesense_enabled'    => array(
				'type'          => 'string',
				'label'         => __( 'Database for searches', 'typesense' ),
				'description'   => __( 'The type of database used for searches.', 'typesense' ),
				'show_in_rest'  => true,
				'html_callback' => function ( $args ) {
					?>
	<fieldset>
		<legend class="screen-reader-text">
		<span><?php echo esc_html( $args['label'] ); ?></span></legend>
					<?php
					$options = array(
						'default'   => __( 'Default WordPress MySQL', 'typesense' ),
						'typesense' => __( 'Typesense external database', 'typesense' ),
					);
					$options = apply_filters( 'search_engine_options', $options );

					foreach ( $options as $option => $label ) :
						?>
		<p>
			<label>
				<input name="typesense_enabled" type="radio" value="<?php echo esc_attr( $option ); ?>" class="tog" <?php echo esc_attr( checked( $option, get_option( 'typesense_enabled', 'default' ), false ) ); ?> />
						<?php echo esc_html( $label ); ?>
			</label>
		</p>
					<?php endforeach; ?>
	</fieldset>
					<?php
				},
			),

			'typesense_url'        => array(
				'type'              => 'string',
				'label'             => __( 'Server address', 'typesense' ),
				'label_for'         => 'typesense-server',
				'html_callback'     => function ( $args ) {
					?>
	<input name="typesense_url" id="<?php echo esc_attr( $args['label_for'] ); ?>" type="text" placeholder="https://typesense.example.com" class="regular-text code typesense-input" value="<?php echo esc_attr( get_option( 'typesense_url' ) ); ?>" />
					<?php
				},
				'sanitize_callback' => 'sanitize_text_field',
			),

			'typesense_port'       => array(
				'type'              => 'integer',
				'sanitize_callback' => 'intval',
			),

			'typesense_api_key'    => array(
				'type'              => 'string',
				'label'             => __( 'Token', 'typesense' ),
				'sanitize_callback' => 'sanitize_text_field',
				'label_for'         => 'typesense-token',
				'class'             => 'typesenseserver-pass-wrap',
				'html_callback'     => function ( $args ) {
					?>
	<input type="hidden" value=" "><!-- #24364 workaround -->
	<span class="wp-pwd">
		<input type="password" name="typesense_api_key" id="<?php echo esc_attr( $args['label_for'] ); ?>" class="regular-text <?php echo is_rtl() ? 'rtl' : 'ltr'; ?> typesense-input" autocomplete="off" value="<?php echo esc_attr( get_option( 'typesense_api_key' ) ); ?>">
		<button type="button" class="button wp-hide-pw hide-if-no-js typesense-input" data-toggle="0" data-start-masked="1" aria-label="<?php esc_attr_e( 'Show token' ); ?>">
			<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
		</button>
	</span>
					<?php
				},
			),

			'typesense_collection' => array(
				'type'              => 'string',
				'label'             => __( 'Collection', 'typesense' ),
				'sanitize_callback' => 'sanitize_text_field',
				'label_for'         => 'typesense-collection',
				'html_callback'     => function ( $args ) {
					?>
	<input name="typesense_collection" type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" placeholder="wp_posts" class="regular-text <?php echo is_rtl() ? 'rtl' : 'ltr'; ?> typesense-input" value="<?php echo esc_attr( get_option( 'typesense_collection' ) ); ?>" />
					<?php
				},
			),
		);

		foreach ( $settings as $field => $args ) {
			register_setting(
				'reading',
				$field,
				array_intersect_key(
					$args,
					array_flip(
						array(
							'type',
							'description',
							'sanitize_callback',
							'show_in_rest',
						)
					)
				)
			);

			if ( isset( $args['html_callback'] ) ) {
				add_settings_field(
					$field,
					$args['label'],
					$args['html_callback'],
					'reading',
					'typesense_settings_section',
					array_diff_key( $args, array_flip( array( 'html_callback' ) ) ),
				);
			}
		}
	}
);
