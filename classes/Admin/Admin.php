<?php
/**
 * Admin class for Easy Sitemap
 *
 * @package EasySitemap
 */

namespace EasySitemap\Admin;

/**
 * Admin class
 */
class Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( EASY_SITEMAP_PLUGIN_FILE ), array( $this, 'add_plugin_action_links' ) );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'Easy Sitemap Settings', 'easy-sitemap' ),
			__( 'Easy Sitemap', 'easy-sitemap' ),
			'manage_options',
			'easy-sitemap-settings',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		// All settings in one group.
		register_setting(
			'easy_sitemap_settings',
			'easy_sitemap_custom_css',
			array( 'sanitize_callback' => 'wp_strip_all_tags' )
		);
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'settings_page_easy-sitemap-settings' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'easy-sitemap-admin',
			plugins_url( 'assets/css/admin.css', EASY_SITEMAP_PLUGIN_FILE ),
			array(),
			'2.0.0'
		);

		wp_enqueue_script(
			'easy-sitemap-admin',
			plugins_url( 'assets/js/admin.js', EASY_SITEMAP_PLUGIN_FILE ),
			array( 'jquery' ),
			'2.0.0',
			true
		);

		wp_localize_script(
			'easy-sitemap-admin',
			'easySitemapAdmin',
			array(
				'copy'                => __( 'Copy Shortcode', 'easy-sitemap' ),
				'copied'              => __( 'Copied!', 'easy-sitemap' ),
				'postTypes'           => $this->get_public_post_types(),
			)
		);
	}

	/**
	 * Add plugin action links
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public function add_plugin_action_links( $links ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=easy-sitemap-settings' ) . '">' . __( 'Settings', 'easy-sitemap' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Settings page
	 */
	public function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'easy-sitemap' ) );
		}
		?>
		<div class="wrap">
			<div class="easy-sitemap-header">
				<h1><?php esc_html_e( 'Easy Sitemap', 'easy-sitemap' ); ?></h1>
				<div class="easy-sitemap-links">
					<a href="https://wordpress.org/plugins/easy-sitemap/" target="_blank" rel="noopener noreferrer" class="button button-secondary">
						<?php esc_html_e( 'WordPress.org Plugin Page', 'easy-sitemap' ); ?>
					</a>
					<a href="https://github.com/snowbedding/wordpress-easy-sitemap" target="_blank" rel="noopener noreferrer" class="button button-secondary">
						<?php esc_html_e( 'GitHub Repository', 'easy-sitemap' ); ?>
					</a>
				</div>
			</div>

			<?php $this->shortcode_generator_section(); ?>

			<form method="post" action="options.php" class="easy-sitemap-settings-form">
				<?php
				settings_fields( 'easy_sitemap_settings' );
				$this->all_settings_section();
				submit_button();
				?>
			</form>
		</div>
		<?php
	}





	/**
	 * Shortcodes help section
	 */
	private function shortcodes_help_section() {
		?>
		<div class="easy-sitemap-shortcodes-help">
			<h3><?php esc_html_e( 'Available Shortcodes', 'easy-sitemap' ); ?></h3>

			<h4><?php esc_html_e( 'Main Sitemap Shortcode', 'easy-sitemap' ); ?></h4>
			<code>[easy_sitemap]</code>
			<p><?php esc_html_e( 'Displays a complete sitemap with posts, pages, and custom post types.', 'easy-sitemap' ); ?></p>

            <h4><?php esc_html_e( 'Filter by Post Type', 'easy-sitemap' ); ?></h4>
            <p><code>[easy_sitemap post_type="page"]</code> &nbsp; <code>[easy_sitemap post_type="post"]</code> &nbsp; <code>[easy_sitemap post_type="product"]</code></p>

			<h4><?php esc_html_e( 'Available Attributes', 'easy-sitemap' ); ?></h4>
			<table class="widefat">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Attribute', 'easy-sitemap' ); ?></th>
						<th><?php esc_html_e( 'Description', 'easy-sitemap' ); ?></th>
						<th><?php esc_html_e( 'Default', 'easy-sitemap' ); ?></th>
						<th><?php esc_html_e( 'Example', 'easy-sitemap' ); ?></th>
					</tr>
				</thead>
				<tbody>
                    <tr>
                        <td>post_type</td>
                        <td><?php esc_html_e( 'Post type to display (empty = all)', 'easy-sitemap' ); ?></td>
                        <td>all</td>
                        <td>post_type="product"</td>
                    </tr>
                    <tr>
                        <td>limit</td>
                        <td><?php esc_html_e( 'Number of items to display', 'easy-sitemap' ); ?></td>
                        <td>100</td>
                        <td>limit="50"</td>
                    </tr>
					<tr>
						<td>orderby</td>
						<td><?php esc_html_e( 'Sort items by', 'easy-sitemap' ); ?></td>
						<td>date</td>
						<td>orderby="title"</td>
					</tr>
					<tr>
						<td>order</td>
						<td><?php esc_html_e( 'Sort order (ASC/DESC)', 'easy-sitemap' ); ?></td>
						<td>DESC</td>
						<td>order="ASC"</td>
					</tr>
					<tr>
						<td>include</td>
						<td><?php esc_html_e( 'Include specific post IDs', 'easy-sitemap' ); ?></td>
						<td>-</td>
						<td>include="1,2,3"</td>
					</tr>
					<tr>
						<td>exclude</td>
						<td><?php esc_html_e( 'Exclude specific post IDs', 'easy-sitemap' ); ?></td>
						<td>-</td>
						<td>exclude="4,5,6"</td>
					</tr>
					<tr>
						<td>category</td>
						<td><?php esc_html_e( 'Filter by category slugs', 'easy-sitemap' ); ?></td>
						<td>-</td>
						<td>category="slug1,slug2"</td>
					</tr>
					<tr>
						<td>tag</td>
						<td><?php esc_html_e( 'Filter by tag slugs', 'easy-sitemap' ); ?></td>
						<td>-</td>
						<td>tag="slug1,slug2"</td>
					</tr>
					<tr>
						<td>author</td>
						<td><?php esc_html_e( 'Filter by author ID', 'easy-sitemap' ); ?></td>
						<td>-</td>
						<td>author="1"</td>
					</tr>
					<tr>
						<td>date_from</td>
						<td><?php esc_html_e( 'Show posts from date (YYYY-MM-DD)', 'easy-sitemap' ); ?></td>
						<td>-</td>
						<td>date_from="2023-01-01"</td>
					</tr>
					<tr>
						<td>date_to</td>
						<td><?php esc_html_e( 'Show posts until date (YYYY-MM-DD)', 'easy-sitemap' ); ?></td>
						<td>-</td>
						<td>date_to="2023-12-31"</td>
					</tr>
					<tr>
						<td>hierarchical</td>
						<td><?php esc_html_e( 'Display hierarchical structure', 'easy-sitemap' ); ?></td>
						<td>0</td>
						<td>hierarchical="1"</td>
					</tr>
					<tr>
						<td>depth</td>
						<td><?php esc_html_e( 'Hierarchy depth (0 = unlimited)', 'easy-sitemap' ); ?></td>
						<td>0</td>
						<td>depth="2"</td>
					</tr>
					<tr>
						<td>show_dates</td>
						<td><?php esc_html_e( 'Show publication dates', 'easy-sitemap' ); ?></td>
						<td>1</td>
						<td>show_dates="0"</td>
					</tr>
					<tr>
						<td>show_excerpts</td>
						<td><?php esc_html_e( 'Show post excerpts', 'easy-sitemap' ); ?></td>
						<td>0</td>
						<td>show_excerpts="1"</td>
					</tr>
					<tr>
						<td>show_images</td>
						<td><?php esc_html_e( 'Show featured images', 'easy-sitemap' ); ?></td>
						<td>0</td>
						<td>show_images="1"</td>
					</tr>
					<tr>
						<td>cache</td>
						<td><?php esc_html_e( 'Enable caching for this shortcode', 'easy-sitemap' ); ?></td>
						<td>1</td>
						<td>cache="0"</td>
					</tr>
				</tbody>
			</table>

            <h4><?php esc_html_e( 'Example Usage', 'easy-sitemap' ); ?></h4>
			<div class="easy-sitemap-examples">
                <p><strong><?php esc_html_e( 'Posts from specific category:', 'easy-sitemap' ); ?></strong></p>
                <code>[easy_sitemap post_type="post" category="news" limit="20" show_dates="1"]</code>

				<p><strong><?php esc_html_e( 'Pages in hierarchical order:', 'easy-sitemap' ); ?></strong></p>
                <code>[easy_sitemap post_type="page" hierarchical="1" orderby="menu_order"]</code>

				<p><strong><?php esc_html_e( 'Recent products with images:', 'easy-sitemap' ); ?></strong></p>
                <code>[easy_sitemap post_type="product" limit="12" show_images="1" orderby="date"]</code>

				<p><strong><?php esc_html_e( 'Custom styled sitemap:', 'easy-sitemap' ); ?></strong></p>
                <code>[easy_sitemap class="my-custom-sitemap" show_excerpts="1" show_images="1"]</code>
			</div>
		</div>
		<?php
	}

	/**
	 * All settings section (combined)
	 */
	private function all_settings_section() {
		?>

		<!-- Removed: Display Settings (dates/excerpts/images) — use shortcode attributes instead. -->

		<h2><?php esc_html_e( 'Custom Styling', 'easy-sitemap' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Custom CSS', 'easy-sitemap' ); ?></th>
				<td>
					<textarea name="easy_sitemap_custom_css" rows="10" cols="50" class="large-text code"><?php echo esc_textarea( get_option( 'easy_sitemap_custom_css', '' ) ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Add custom CSS to style your sitemaps', 'easy-sitemap' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Shortcode Generator Section
	 */
	private function shortcode_generator_section() {
		$post_types = $this->get_public_post_types();
		?>
		<div class="easy-sitemap-settings-section">
			<h3><?php esc_html_e( 'Build your shortcode', 'easy-sitemap' ); ?></h3>
			<div class="easy-sitemap-generator">
				<div class="easy-sitemap-generator__controls">

					<!-- Basic Settings -->
					<h4><?php esc_html_e( 'Basic Settings', 'easy-sitemap' ); ?></h4>
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Post type', 'easy-sitemap' ); ?></th>
							<td>
								<select id="esg_post_type">
									<option value=""><?php esc_html_e( 'All', 'easy-sitemap' ); ?></option>
									<?php foreach ( $post_types as $slug => $label ) : ?>
										<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Limit', 'easy-sitemap' ); ?></th>
							<td><input type="number" id="esg_limit" min="1" max="1000" value="1000" /></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Order by', 'easy-sitemap' ); ?></th>
							<td>
								<select id="esg_orderby">
									<option value="date"><?php esc_html_e( 'date', 'easy-sitemap' ); ?></option>
									<option value="title"><?php esc_html_e( 'title', 'easy-sitemap' ); ?></option>
									<option value="modified"><?php esc_html_e( 'modified', 'easy-sitemap' ); ?></option>
									<option value="menu_order"><?php esc_html_e( 'menu_order', 'easy-sitemap' ); ?></option>
									<option value="rand"><?php esc_html_e( 'rand', 'easy-sitemap' ); ?></option>
									<option value="ID"><?php esc_html_e( 'ID', 'easy-sitemap' ); ?></option>
									<option value="author"><?php esc_html_e( 'author', 'easy-sitemap' ); ?></option>
									<option value="name"><?php esc_html_e( 'name', 'easy-sitemap' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Order', 'easy-sitemap' ); ?></th>
							<td>
								<label><input type="radio" name="esg_order" value="DESC" checked /> DESC</label>
								&nbsp;&nbsp;
								<label><input type="radio" name="esg_order" value="ASC" /> ASC</label>
							</td>
						</tr>
					</table>

					<!-- Content Filtering -->
					<h4><?php esc_html_e( 'Content Filtering', 'easy-sitemap' ); ?></h4>
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Include IDs', 'easy-sitemap' ); ?></th>
							<td>
								<input type="text" id="esg_include" placeholder="1,2,3" class="regular-text" />
								<p class="description"><?php esc_html_e( 'Include only specific post IDs (comma-separated).', 'easy-sitemap' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Exclude IDs', 'easy-sitemap' ); ?></th>
							<td>
								<input type="text" id="esg_exclude" placeholder="4,5,6" class="regular-text" />
								<p class="description"><?php esc_html_e( 'Exclude specific post IDs (comma-separated).', 'easy-sitemap' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Category slugs', 'easy-sitemap' ); ?></th>
							<td>
								<input type="text" id="esg_category" placeholder="news,blog" class="regular-text" />
								<p class="description"><?php esc_html_e( 'Filter by category slugs (comma-separated).', 'easy-sitemap' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Tag slugs', 'easy-sitemap' ); ?></th>
							<td>
								<input type="text" id="esg_tag" placeholder="featured,popular" class="regular-text" />
								<p class="description"><?php esc_html_e( 'Filter by tag slugs (comma-separated).', 'easy-sitemap' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Custom taxonomy', 'easy-sitemap' ); ?></th>
							<td>
								<div class="inline-inputs">
									<input type="text" id="esg_taxonomy" placeholder="product_cat" class="regular-text" />
									<input type="text" id="esg_term" placeholder="electronics" class="regular-text" />
								</div>
								<p class="description"><?php esc_html_e( 'Taxonomy name and term slugs (comma-separated).', 'easy-sitemap' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Author ID', 'easy-sitemap' ); ?></th>
							<td>
								<input type="number" id="esg_author" min="1" placeholder="1" />
								<p class="description"><?php esc_html_e( 'Filter by author user ID.', 'easy-sitemap' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Date range', 'easy-sitemap' ); ?></th>
							<td>
								<div class="inline-inputs">
									<input type="date" id="esg_date_from" />
									<input type="date" id="esg_date_to" />
								</div>
								<p class="description"><?php esc_html_e( 'Filter posts from/to specific dates (YYYY-MM-DD).', 'easy-sitemap' ); ?></p>
							</td>
						</tr>
					</table>

					<!-- Display Options -->
					<h4><?php esc_html_e( 'Display Options', 'easy-sitemap' ); ?></h4>
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Layout', 'easy-sitemap' ); ?></th>
							<td>
								<label><input type="checkbox" id="esg_hierarchical" /> <?php esc_html_e( 'Hierarchical display', 'easy-sitemap' ); ?></label>
								&nbsp;&nbsp;
								<label><?php esc_html_e( 'Depth:', 'easy-sitemap' ); ?> <input type="number" id="esg_depth" min="0" max="10" value="0" style="width: 60px;" /></label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Show content', 'easy-sitemap' ); ?></th>
							<td>
								<label><input type="checkbox" id="esg_show_dates" /> <?php esc_html_e( 'Publication dates', 'easy-sitemap' ); ?></label>
								&nbsp;&nbsp;
								<label><input type="checkbox" id="esg_show_excerpts" /> <?php esc_html_e( 'Post excerpts', 'easy-sitemap' ); ?></label>
								&nbsp;&nbsp;
								<label><input type="checkbox" id="esg_show_images" /> <?php esc_html_e( 'Featured images', 'easy-sitemap' ); ?></label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Columns', 'easy-sitemap' ); ?></th>
							<td>
								<select id="esg_columns">
									<option value="1"><?php esc_html_e( '1 column', 'easy-sitemap' ); ?></option>
									<option value="2"><?php esc_html_e( '2 columns', 'easy-sitemap' ); ?></option>
									<option value="3"><?php esc_html_e( '3 columns', 'easy-sitemap' ); ?></option>
									<option value="4"><?php esc_html_e( '4 columns', 'easy-sitemap' ); ?></option>
									<option value="5"><?php esc_html_e( '5 columns', 'easy-sitemap' ); ?></option>
									<option value="6"><?php esc_html_e( '6 columns', 'easy-sitemap' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Number of columns for responsive grid layout.', 'easy-sitemap' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Custom class', 'easy-sitemap' ); ?></th>
							<td>
								<input type="text" id="esg_class" placeholder="my-sitemap" class="regular-text" />
								<p class="description"><?php esc_html_e( 'Add custom CSS class for styling.', 'easy-sitemap' ); ?></p>
							</td>
						</tr>
					</table>


				</div>
				<div class="easy-sitemap-generator__output">
					<h4><?php esc_html_e( 'Generated Shortcode', 'easy-sitemap' ); ?></h4>
					<textarea readonly class="large-text code" rows="3" id="esg_output">[easy_sitemap]</textarea>
					<p class="description"><?php esc_html_e( 'Select and copy the shortcode above to use in your posts/pages.', 'easy-sitemap' ); ?></p>
					<p><button type="button" class="button" id="esg_copy_button"><?php esc_html_e( 'Copy Shortcode', 'easy-sitemap' ); ?></button></p>
				</div>
			</div>

			<div class="easy-sitemap-attributes-table">
				<h4><?php esc_html_e( 'All Supported Attributes', 'easy-sitemap' ); ?></h4>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Attribute', 'easy-sitemap' ); ?></th>
							<th><?php esc_html_e( 'Supported Values', 'easy-sitemap' ); ?></th>
							<th><?php esc_html_e( 'Default', 'easy-sitemap' ); ?></th>
							<th><?php esc_html_e( 'Description', 'easy-sitemap' ); ?></th>
							<th><?php esc_html_e( 'Example', 'easy-sitemap' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><code>post_type</code></td>
							<td><code>post</code>, <code>page</code>, <code>product</code>, <code>portfolio</code>, etc.<br><small>(any public post type slug)</small></td>
							<td><em><?php esc_html_e( 'empty', 'easy-sitemap' ); ?></em></td>
							<td><?php esc_html_e( 'Post type to display', 'easy-sitemap' ); ?></td>
							<td><code>post_type="product"</code></td>
						</tr>
						<tr>
							<td><code>limit</code></td>
							<td><?php esc_html_e( 'Positive integer', 'easy-sitemap' ); ?><br><small>(1-1000)</small></td>
							<td><code>1000</code></td>
							<td><?php esc_html_e( 'Number of items to display', 'easy-sitemap' ); ?></td>
							<td><code>limit="50"</code></td>
						</tr>
						<tr>
							<td><code>orderby</code></td>
							<td><code>date</code>, <code>title</code>, <code>modified</code>, <code>menu_order</code>, <code>rand</code>, <code>ID</code>, <code>author</code>, <code>name</code></td>
							<td><code>date</code></td>
							<td><?php esc_html_e( 'Sort items by', 'easy-sitemap' ); ?></td>
							<td><code>orderby="title"</code></td>
						</tr>
						<tr>
							<td><code>order</code></td>
							<td><code>DESC</code>, <code>ASC</code></td>
							<td><code>DESC</code></td>
							<td><?php esc_html_e( 'Sort direction', 'easy-sitemap' ); ?></td>
							<td><code>order="ASC"</code></td>
						</tr>
						<tr>
							<td><code>include</code></td>
							<td><?php esc_html_e( 'Comma-separated post IDs', 'easy-sitemap' ); ?><br><small>(e.g., "1,2,3")</small></td>
							<td><em><?php esc_html_e( 'empty', 'easy-sitemap' ); ?></em></td>
							<td><?php esc_html_e( 'Include only specific post IDs', 'easy-sitemap' ); ?></td>
							<td><code>include="1,2,3"</code></td>
						</tr>
						<tr>
							<td><code>exclude</code></td>
							<td><?php esc_html_e( 'Comma-separated post IDs', 'easy-sitemap' ); ?><br><small>(e.g., "4,5,6")</small></td>
							<td><em><?php esc_html_e( 'empty', 'easy-sitemap' ); ?></em></td>
							<td><?php esc_html_e( 'Exclude specific post IDs', 'easy-sitemap' ); ?></td>
							<td><code>exclude="4,5,6"</code></td>
						</tr>
						<tr>
							<td><code>category</code></td>
							<td><?php esc_html_e( 'Comma-separated category slugs', 'easy-sitemap' ); ?><br><small>(e.g., "news,blog")</small></td>
							<td><em><?php esc_html_e( 'empty', 'easy-sitemap' ); ?></em></td>
							<td><?php esc_html_e( 'Filter by category slugs', 'easy-sitemap' ); ?></td>
							<td><code>category="news,blog"</code></td>
						</tr>
						<tr>
							<td><code>tag</code></td>
							<td><?php esc_html_e( 'Comma-separated tag slugs', 'easy-sitemap' ); ?><br><small>(e.g., "featured,popular")</small></td>
							<td><em><?php esc_html_e( 'empty', 'easy-sitemap' ); ?></em></td>
							<td><?php esc_html_e( 'Filter by tag slugs', 'easy-sitemap' ); ?></td>
							<td><code>tag="featured,popular"</code></td>
						</tr>
						<tr>
							<td><code>taxonomy</code></td>
							<td><?php esc_html_e( 'Any taxonomy slug', 'easy-sitemap' ); ?><br><small>(e.g., "product_cat", "genre")</small></td>
							<td><em><?php esc_html_e( 'empty', 'easy-sitemap' ); ?></em></td>
							<td><?php esc_html_e( 'Custom taxonomy name to filter by', 'easy-sitemap' ); ?></td>
							<td><code>taxonomy="product_cat"</code></td>
						</tr>
						<tr>
							<td><code>term</code></td>
							<td><?php esc_html_e( 'Comma-separated term slugs', 'easy-sitemap' ); ?><br><small>(use with taxonomy attribute)</small></td>
							<td><em><?php esc_html_e( 'empty', 'easy-sitemap' ); ?></em></td>
							<td><?php esc_html_e( 'Custom taxonomy term slugs', 'easy-sitemap' ); ?></td>
							<td><code>term="electronics,books"</code></td>
						</tr>
						<tr>
							<td><code>author</code></td>
							<td><?php esc_html_e( 'Author user ID', 'easy-sitemap' ); ?><br><small>(positive integer)</small></td>
							<td><em><?php esc_html_e( 'empty', 'easy-sitemap' ); ?></em></td>
							<td><?php esc_html_e( 'Filter by author ID', 'easy-sitemap' ); ?></td>
							<td><code>author="1"</code></td>
						</tr>
						<tr>
							<td><code>date_from</code></td>
							<td><?php esc_html_e( 'Date in YYYY-MM-DD format', 'easy-sitemap' ); ?><br><small>(e.g., "2023-01-01")</small></td>
							<td><em><?php esc_html_e( 'empty', 'easy-sitemap' ); ?></em></td>
							<td><?php esc_html_e( 'Show posts from this date', 'easy-sitemap' ); ?></td>
							<td><code>date_from="2023-01-01"</code></td>
						</tr>
						<tr>
							<td><code>date_to</code></td>
							<td><?php esc_html_e( 'Date in YYYY-MM-DD format', 'easy-sitemap' ); ?><br><small>(e.g., "2023-12-31")</small></td>
							<td><em><?php esc_html_e( 'empty', 'easy-sitemap' ); ?></em></td>
							<td><?php esc_html_e( 'Show posts until this date', 'easy-sitemap' ); ?></td>
							<td><code>date_to="2023-12-31"</code></td>
						</tr>
						<tr>
							<td><code>hierarchical</code></td>
							<td><code>0</code>, <code>1</code></td>
							<td><code>0</code></td>
							<td><?php esc_html_e( 'Display hierarchical structure', 'easy-sitemap' ); ?></td>
							<td><code>hierarchical="1"</code></td>
						</tr>
						<tr>
							<td><code>depth</code></td>
							<td><?php esc_html_e( 'Non-negative integer', 'easy-sitemap' ); ?><br><small>(0 = unlimited)</small></td>
							<td><code>0</code></td>
							<td><?php esc_html_e( 'Hierarchy depth', 'easy-sitemap' ); ?></td>
							<td><code>depth="2"</code></td>
						</tr>
						<tr>
							<td><code>show_dates</code></td>
							<td><code>0</code>, <code>1</code></td>
							<td><code>0</code></td>
							<td><?php esc_html_e( 'Show publication dates', 'easy-sitemap' ); ?></td>
							<td><code>show_dates="1"</code></td>
						</tr>
						<tr>
							<td><code>show_excerpts</code></td>
							<td><code>0</code>, <code>1</code></td>
							<td><code>0</code></td>
							<td><?php esc_html_e( 'Show post excerpts', 'easy-sitemap' ); ?></td>
							<td><code>show_excerpts="1"</code></td>
						</tr>
						<tr>
							<td><code>show_images</code></td>
							<td><code>0</code>, <code>1</code></td>
							<td><code>0</code></td>
							<td><?php esc_html_e( 'Show featured images', 'easy-sitemap' ); ?></td>
							<td><code>show_images="1"</code></td>
						</tr>
						<tr>
							<td><code>columns</code></td>
							<td><?php esc_html_e( 'Positive integer', 'easy-sitemap' ); ?><br><small>(1-6)</small></td>
							<td><code>1</code></td>
							<td><?php esc_html_e( 'Number of columns', 'easy-sitemap' ); ?></td>
							<td><code>columns="2"</code></td>
						</tr>
						<tr>
							<td><code>class</code></td>
							<td><?php esc_html_e( 'Valid CSS class name', 'easy-sitemap' ); ?><br><small>(letters, numbers, hyphens, underscores)</small></td>
							<td><code>easy-sitemap</code></td>
							<td><?php esc_html_e( 'Custom CSS class for styling', 'easy-sitemap' ); ?></td>
							<td><code>class="my-sitemap"</code></td>
						</tr>
					</tbody>
				</table>
				<p class="description">
					<?php esc_html_e( 'All attributes can be used together. Example: [easy_sitemap post_type="post" category="news" limit="20" show_dates="1" show_images="1"]', 'easy-sitemap' ); ?>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Get public post types list for select controls
	 *
	 * @return array
	 */
	private function get_public_post_types() {
		$objects   = get_post_types( array( 'public' => true ), 'objects' );
		$posttypes = array();
		foreach ( $objects as $slug => $obj ) {
			$posttypes[ $slug ] = $obj->labels->singular_name ? $obj->labels->singular_name : $obj->label;
		}
		return $posttypes;
	}
}
