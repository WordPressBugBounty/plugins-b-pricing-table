<?php

if ( !defined( 'ABSPATH' ) ) { exit; }

class BPTB_Shortcode {
    private $post_type = 'bptb';
    public function __construct() {
			add_action( 'init', [$this, 'onInit'] );
			add_shortcode( 'bptb', [$this, 'onAddShortcode'] );
			add_filter( 'manage_bptb_posts_columns', [$this, 'manageBPTBPostsColumns'], 10 );
			add_action( 'manage_bptb_posts_custom_column', [$this, 'manageBPTBPostsCustomColumns'], 10, 2 );
			add_action( 'use_block_editor_for_post', [$this, 'useBlockEditorForPost'], 999, 2 );
		  add_action( 'admin_enqueue_scripts', [$this, 'adminEnqueueScripts'] );
	}

	function onInit(){

		register_post_type( 'bptb', [
			'labels'				=> [
				'name'			=> __( 'ShortCodes', 'b-pricing-table' ),
				'singular_name'	=> __( 'ShortCode', 'b-pricing-table' ),
				'add_new'		=> __( 'Add New ', 'b-pricing-table' ),
				'add_new_item'	=> __( 'Add New ShortCode', 'b-pricing-table' ),
				'edit_item'		=> __( 'Edit ShortCode', 'b-pricing-table' ),
				'new_item'		=> __( 'New ShortCode', 'b-pricing-table' ),
				'view_item'		=> __( 'View ShortCode', 'b-pricing-table' ),
				'search_items'	=> __( 'Search ShortCodes', 'b-pricing-table' ),
				'not_found'		=> __( 'Sorry, we couldn\'t find the ShortCode you are looking for.', 'b-pricing-table' )
			],
			'public'				=> false,
			'show_ui'				=> true,
			'show_in_rest'			=> true,
			'publicly_queryable'	=> false,
			'show_in_menu'			=> 'b-pricing-table',
			'exclude_from_search'	=> true,
			'menu_position'			=> 14,
			'has_archive'			=> false,
			'hierarchical'			=> false,
			'capability_type'		=> 'page',
			'rewrite'				=> [ 'slug' => 'bptb' ],
			'supports'				=> [ 'title', 'editor' ],
			'template' => [ [ 'bptb/pricing-table' ] ],
			'template_lock'			=> 'all',
		] );
	}

	function onAddShortcode( $atts ) {
		$post_id = $atts['id'];
		$post = get_post( $post_id );

		if ( !$post ) {
			return '';
		}

		if ( post_password_required( $post ) ) {
			return get_the_password_form( $post );
		}

		switch ( $post->post_status ) {
			case 'publish':
				return $this->displayContent( $post );
			case 'private':
				if ( current_user_can( 'read_private_posts' ) ) {
					return $this->displayContent( $post );
				}
				return '';

			case 'draft':
			case 'pending':
			case 'future':
				if ( current_user_can( 'edit_post', $post_id ) ) {
					return $this->displayContent( $post );
				}
				return '';

			default:
				return '';
		}
	}

	function displayContent( $post ){
		$blocks = parse_blocks( $post->post_content );
		return render_block( $blocks[0] );
	}

	//*shortcode list create this function
	function manageBPTBPostsColumns( $defaults ) {
		unset( $defaults['date'] );
		$defaults['shortcode'] = 'ShortCode';
		$defaults['date'] = 'Date';
		return $defaults;
	}

	// *Adjusted with post.js  //And the short code copies it with this function.
	function manageBPTBPostsCustomColumns( $column_name, $post_ID ) {
		if ( $column_name == 'shortcode' ) {
			echo '<div class="bPlAdminShortcode" id="bPlAdminShortcode-' . esc_attr( $post_ID ) . '">
				<input value="[bptb id=' . esc_attr( $post_ID ) . ']" onclick="copyBPlAdminShortcode(\'' . esc_attr( $post_ID ) . '\')">
				<span class="tooltip">' . esc_html__( 'Copy To Clipboard', 'b-pricing-table' ) . '</span>
			</div>';
		}
	}

	function useBlockEditorForPost( $use, $post ){
		if ( is_object( $post ) && isset( $post->post_type ) && $this->post_type === $post->post_type ) {
			return true;
		}
		return $use;
	}

  function adminEnqueueScripts( $hook ){
		if( 'edit.php' === $hook || 'post.php' === $hook ){
			wp_enqueue_style( 'bptb-admin-post', BPTB_DIR_URL . 'build/admin-post.css', [], BPTB_VERSION );
			wp_enqueue_script( 'bptb-admin-post', BPTB_DIR_URL . 'build/admin-post.js', [], BPTB_VERSION, true );
			wp_set_script_translations( 'bptb-admin-post', 'b-pricing-table', BPTB_DIR_PATH . 'languages' );
	  }
	}

}
new BPTB_Shortcode();