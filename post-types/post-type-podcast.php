<?php

if( !class_exists( 'PostTypePodcast' ) ) {
	/**
	 * The Podcast Post Type
	 */
	class PostTypePodcast {
		const POST_TYPE = 'podcast';
		private $_meta  = array(
			'_meta_a',
			'_meta_b',
			'_meta_c',
			'_dicentis_podcast_medialink'
		);
		/* push each taxonomy name, which is used in this plugin
		 * into this->_tax array. filter_posts() uses this array
		 * to know which taxonomy is used and display filter options
		 * for that
		 */
		private $_tax = array();

		public function __construct() {
			// register actions
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		} // END public function __construct()

		/**
		 * hook into WP's init action hook
		 */
		public function init() {
			// Initialize Post Type
			$this->register_podcast_post_type();
			$this->register_podcast_taxonomy();
			add_action( 'save_post', array( $this,'save_post' ) );

			// add additional filter options to podcast site
			add_action( 'restrict_manage_posts', array( $this, 'filter_posts' ) );
			// script & style action with page detection
			add_action( 'admin_print_scripts-post.php', array( $this, 'media_admin_script' ) );
			add_action( 'admin_print_scripts-post-new.php', array( $this, 'media_admin_script' ) );
			add_action( 'admin_print_style-post.php', array( $this, 'media_admin_style' ) );
			add_action( 'admin_print_style-post-new.php', array( $this, 'media_admin_style' ) );
		} // END public function init()

		/**
		 * Create the post type
		 */
		public function register_podcast_post_type() {
			// set up arguments for podcast post type
			$podcast_args = array(
				'labels' => array(
					'name' => __( "Podcasts" ),
					'singular_name' => __( "Podcast" ),
					'add_new' => __( "Add New Podcast" ),
					'add_new_item' => __( "Add New Podcast" ),
					'edit_item' => __( "Edit Podcast" ),
					'new_item' => __( "New Podcast" ),
					'view_item' => __( "View Podcast" ),
					'search_items' => __( "Search Podcast" ),
					'not_found' => __( "No Podcasts Found" ),
					'not_found_in_trash' => __( "No Podcast Found In Trash" )
				),
				'public' => true,
				'has_archive' => true,
				'description' => __( "A podcast plugin which allows to define multipel podcasts with individual feeds" ),
				'supports' => array(
					'editor',
					'thumbnail',
					'title',
				),
				'menu_icon' => plugins_url( 'dicentis/assets/img/podcast-icon.png' ),
				'can_export' => 'true',
				// 'capabilities' => array(
				// 	'edit_post' => 'edit_podcast',
				// 	'edit_posts' => 'edit_podcasts',
				// 	'edit_other_posts' => 'edit_other_podcasts',
				// 	'publish_post' => 'publish_podcast',
				// 	'read_post' => 'read_podcast',
				// 	'read_private_posts' => 'read_private_podcast',
				// 	'delete_post' => 'delete_podcast',
				// ),
			);

			// Register the dicentis podcast post type
			if ( post_type_exists( self::POST_TYPE ) ) {
				/* @TODO: show admin notice */
				// don't register post type b/c there already exists one
				// with the same name.
			} else {
				register_post_type( self::POST_TYPE, $podcast_args );
			}
		} // END public function register_podcast_post_type()

		/**
		 * creates custom taxonomies for categorizing podcasts
		 * in series
		 */
		public function register_podcast_taxonomy() {
			// Set up the series taxonomy
			$series_args = array(
				'hierarchical' => true,
				'query_var' => 'podcast_series',
				'rewrite' => array(
					'slug' => self::POST_TYPE . '/series',
				),
				'labels' => array(
					'name' => 'Series',
					'singular_name' => 'Series',
					'edit_item' => 'Edit Series',
					'update_item' => 'Update Series',
					'add_new_item' => 'Add New Series',
					'new_item_name' => 'New Series Name',
					'all_items' => 'All Series',
					'search_items' => 'Search Series',
					'parent_item' => 'Parent Series',
					'parent_item_colon' => 'Parent Series:',
				),
			);

			// Set up the speaker taxonomy
			$speaker_args = array(
				'hierarchical' => true,
				'query_var' => 'podcast_speaker',
				'rewrite' => array(
					'slug' => self::POST_TYPE . '/speaker',
				),
				'labels' => array(
					'name' => 'Speaker',
					'singular_name' => 'Speaker',
					'edit_item' => 'Edit Speaker',
					'update_item' => 'Update Speaker',
					'add_new_item' => 'Add New Speaker',
					'new_item_name' => 'New Speaker Name',
					'all_items' => 'All Speaker',
					'search_items' => 'Search Speaker',
					'parent_item' => 'Parent Speaker',
					'parent_item_colon' => 'Parent Speaker:',
				),
			);

			/* push each taxonomy name, which is used in this plugin
			 * into this->_tax array. filter_posts() uses this array
			 * to know which taxonomy is used and display filter options
			 * for that
			 */
			// register series taxonomy
			if ( taxonomy_exists( 'celebration_series' ) ) {
				// avantgarde-celebration plugin is installed and active
				register_taxonomy_for_object_type( 'celebration_series', self::POST_TYPE );
				array_push( $this->_tax, 'celebration_series' );
			} else if ( taxonomy_exists( 'podcast_series' ) ) {
				/* @TODO: show admin notice */
			} else {
				register_taxonomy( 'podcast_series', array( self::POST_TYPE ), $series_args );
				array_push( $this->_tax, 'podcast_series' );
			}

			// register speaker taxonomy
			if ( taxonomy_exists( 'celebration_preachers' ) ) {
				// avantgarde-celebration plugin is installed and active
				register_taxonomy_for_object_type( 'celebration_preachers', self::POST_TYPE );
				array_push( $this->_tax, 'celebration_preachers' );
			} else if ( taxonomy_exists( 'podcast_speaker' ) ) {
				/* @TODO: show admin notice */
			} else {
				register_taxonomy( 'podcast_speaker', array( self::POST_TYPE ), $speaker_args );
				array_push( $this->_tax, 'podcast_speaker' );
			}
		} // END public function register_podcast_taxonomy()

		/**
		 * add additional filter options to post type site for each
		 * taxonomy which is used for this plugin
		 */
		public function filter_posts() {
			global $typenow;

			if( 'podcast' == $typenow ){

				/* push each taxonomy name, which is used in this plugin
				 * into this->_tax array. filter_posts() uses this array
				 * to know which taxonomy is used and display filter options
				 * for that
				 */
				foreach ( $this->_tax as $tax_slug ) {
					$tax_obj = get_taxonomy( $tax_slug );
					$tax_name = $tax_obj->labels->name;
					$terms = get_terms($tax_slug);

					echo "<select name='$tax_slug' id='$tax_slug' class='postform'>";
					echo "<option value=''>Show All $tax_name</option>";

					foreach ( $terms as $term ) {
						$selected = '';
						if ( isset( $_GET[$tax_slug] ) ) {
							if ( $_GET[$tax_slug] == $term->slug )
								$selected = ' selected="selected"';
						}

						echo '<option value='. $term->slug, $selected,'>' . $term->name .' (' . $term->count .')</option>';
					}

					echo "</select>";
				}
			}
		}

		/**
		 * Save the metaboxes for this custom post type
		 * @param  [type] $post_id [description]
		 * @return [type]          [description]
		 */
		public function save_post( $post_id ) {
			// verify if this is an auto save routine
			// If it is our form has not been submitted, so we don't want to do anything
			if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			{
				return;
			}

			if( $_POST['post_type'] == self::POST_TYPE && current_user_can( 'edit_post', $post_id ) )
			{
				foreach ($this->_meta as $field_name)
				{
					// update the post's meta field
					if ( strcmp( $field_name, '_dicentis_podcast_medialink' ) == 0 ) {
						update_post_meta( $post_id, $field_name, esc_url_raw( $_POST[ 'dicentis-podcast-medialink' ] ) );
						
					} else {
						update_post_meta( $post_id, $field_name, $_POST[substr($field_name, 1)] );
					}
				}
			}
			else
			{
				return;
			} // if( $_POST['post_type'] == self::POST_TYPE && current_user_can( 'edit_post', $post_id ) )
		} // END public function save_post( $post_id )

		/**
		 * hook into WP's admin_init action hook
		 * @return [type] [description]
		 */
		public function admin_init() {
			// Add metaboxes
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		} // END public function admin_init()

		/**
		 * hook into WP's add_meta_boxes action hook
		 * @param string $value [description]
		 */
		public function add_meta_boxes() {
			// Add this metabox to every selected post
			add_meta_box(
				sprintf( 'dicentis_%s_selection', self::POST_TYPE ),
				sprintf( '%s Information', ucwords(str_replace("_", " ", self::POST_TYPE)) ),
				array( $this, 'add_inner_meta_boxes' ),
				self::POST_TYPE
			);
		} // END public function add_meta_boxes()

		/**
		 * called off of the add meta box
		 * @param [type] $post [description]
		 */
		public function add_inner_meta_boxes( $post ) {
			// Render the job order metabox
			include( sprintf('%s/../templates/%s_metabox.php', dirname(__FILE__), self::POST_TYPE) );
		} // END public function add_inner_meta_boxes( $post )

		public function media_admin_script() {
			wp_enqueue_script( 'dicentis-media-upload',
				plugin_dir_path( __FILE__ ) . '../assets/js/dicentis-medialink.js',
				// plugins_url( 'dicentis/assets/js/dicentis-medialink.js' ),
				array( 'jquery', 'media-upload', 'thickbox' )
			);
		}

		public function media_admin_style() {
			wp_enqueue_style( 'thickbox' );
		}

		// public function updated_messages( $messages ) {
		// 	global $post, $post_ID;
		// 	$messages['podcast'] = array(
		// 		0 => '',
		// 		1 => sprintf( __('Podcast updated. <a href="%s">View podcast</a>'), esc_url( get_permalink($post_ID) ) ),
		// 		2 => __('Custom field updated.'),
		// 		3 => __('Custom field deleted.'),
		// 		4 => __('Podcast updated.'),
		// 		5 => isset($_GET['revision']) ? sprintf( __('Podcast restored from revision from %s'), wp_post_revision_title_( (int) $_GET['revision'], false ) ) : false,
		// 		6 => sprintf( __('Podcast published. <a href="%s">View podcast</a>'), esc_url( get_permalink($post_ID) ) ),
		// 		7 => __('Podcast saved.'),
		// 		8 => sprintf( __('Podcast submitted. <a target="_blank" href="%s">Preview podcast</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		// 		9 => sprintf( __('Podcast scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview podcast</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		// 		10 => sprintf( __('Podcast draft updated. <a target="_blank" href="%s">Preview podcast</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		// 	);
		// 	return $messages;
		// }
	} // END class PostTypePodcast
} // END if( !class_exists( 'PostTypePodcast' ) )
