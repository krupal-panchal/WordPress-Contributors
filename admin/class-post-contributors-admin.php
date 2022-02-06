<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://krupal-panchal.github.io
 * @since      1.0.0
 *
 * @package    Post_Contributors
 * @subpackage Post_Contributors/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Post_Contributors
 * @subpackage Post_Contributors/admin
 * @author     Krupal Panchal <krupalpanchal1506@gmail.com>
 */
class Post_Contributors_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name       The name of this plugin.
	 * @param    string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Post_Contributors_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Post_Contributors_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/post-contributors-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Post_Contributors_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Post_Contributors_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/post-contributors-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Adds the Contributors meta box to Posts.
	 */
	public function add_meta_box() {
		add_meta_box(
			'contributors',
			__( 'Contributors', 'textdomain' ),
			array( $this, 'render_contributors_meta_value' ),
			'post',
			'advanced',
			'high'
		);
	}

	/**
	 * Save the Contributors meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_post_contributors_data( $post_id ) {

		/**
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['contributor_checkbox_nonce'] ) ) {
			return $post_id;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['contributor_checkbox_nonce'] ) );
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'contributor_checkbox' ) ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Sanitize the Data.
		if ( ! empty( $_POST['author'] ) ) {
			$author_data      = sanitize_text_field( wp_unslash( $_POST['author'] ) );
			$contributor_data = implode( ', ', $author_data );
		}

		// Update the meta field.
		update_post_meta( $post_id, 'post_contributors', $contributor_data );
	}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_contributors_meta_value( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'contributor_checkbox', 'contributor_checkbox_nonce' );

		// Get Contributors meta.
		$contributors_meta = get_post_meta( $post->ID, 'post_contributors', true );

		// Get Authors.
		$get_authors = get_users( array( 'role__in' => array( 'author' ) ) );
		$authors_id  = explode( ', ', $contributors_meta );

		foreach ( $get_authors as $user ) { ?>
			<input type="checkbox" name="author[]" value="<?php echo esc_attr( $user->ID ); ?>" <?php echo ( in_array( $user->ID, $authors_id, true ) ? 'checked' : '' ); ?>> <?php echo esc_html( $user->display_name ); ?>
		<?php }
	}

	/**
	 * Get Contributor data in Post content.
	 *
	 * @param string $content The post Content.
	 */
	public function get_contributors_in_post( $content ) {

		$post_contributors = get_post_meta( get_the_ID(), 'post_contributors', true );

		/* Show Contributors only for Posts and if Contributors selected */
		if ( is_singular( 'post' ) && ( ! empty( $post_contributors ) ) ) :

			$get_author_id = explode( ', ', $post_contributors );

			$content .= '<p><span>Contributors:</span></p>';
			$content .= '<div class="post-contributors">';
			foreach ( $get_author_id as $author_id ) {
				$user_info   = get_userdata( $author_id );
				$get_gravtar = '<img class="gravtar-img" src="' . esc_url( get_avatar_url( $user->ID ) ) . '" />';
				$content    .= '<div class="gravtar-item"><a href="' . esc_url( get_author_posts_url( $author_id ) ) . '"> ' . $get_gravtar . ' ' . $user_info->first_name . ' </a></div>';
			}

			$content .= '</div>';

		else :
			$content;
		endif;

		return $content;
	}

}
