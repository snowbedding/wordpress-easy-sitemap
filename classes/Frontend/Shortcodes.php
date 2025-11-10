<?php
/**
 * Shortcodes class for Easy Sitemap
 *
 * @package EasySitemap
 */

namespace EasySitemap\Frontend;


/**
 * Shortcodes class
 */
class Shortcodes {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Get posts based on attributes
	 *
	 * @param array $atts Query attributes.
	 * @return array
	 */
	private function get_posts( $atts ) {
		$query_args = $this->build_query_args( $atts );
		$query = new \WP_Query( $query_args );
		return $query->posts;
	}

	/**
	 * Build WP_Query arguments
	 *
	 * @param array $atts Query attributes.
	 * @return array
	 */
	private function build_query_args( $atts ) {
		$query_args = array(
			'post_status'    => 'publish',
			'posts_per_page' => $atts['limit'],
			'orderby'        => $atts['orderby'],
			'order'          => $atts['order'],
			'no_found_rows'  => true, // Performance optimization.
		);

		// Set post type based on type.
		switch ( $atts['type'] ) {
			case 'posts':
				$query_args['post_type'] = 'post';
				break;
			case 'pages':
				$query_args['post_type'] = 'page';
				break;
			case 'cpt':
				$query_args['post_type'] = $atts['post_type'];
				break;
			case 'all':
				$query_args['post_type'] = 'any';
				break;
			default:
				$query_args['post_type'] = 'post';
		}

		// Handle inclusions and exclusions.
		$this->handle_inclusions_exclusions( $query_args, $atts );

		// Handle taxonomy filters.
		$this->handle_taxonomy_filters( $query_args, $atts );

		// Handle author filter.
		if ( ! empty( $atts['author'] ) ) {
			$query_args['author'] = $atts['author'];
		}

		// Handle date filters.
		$this->handle_date_filters( $query_args, $atts );

		// Handle hierarchical display for pages.
		if ( 'pages' === $atts['type'] && $atts['hierarchical'] ) {
			$query_args['post_parent'] = 0; // Only get top-level pages initially.
			$query_args['orderby']     = 'menu_order title';
			$query_args['sort_column'] = 'menu_order, post_title';
		}

		return $query_args;
	}

	/**
	 * Handle post inclusions and exclusions
	 *
	 * @param array $query_args Query arguments.
	 * @param array $atts       Attributes.
	 */
	private function handle_inclusions_exclusions( &$query_args, $atts ) {
		// Include specific posts.
		if ( ! empty( $atts['include'] ) ) {
			$include_ids = $this->parse_id_list( $atts['include'] );
			if ( ! empty( $include_ids ) ) {
				$query_args['post__in'] = $include_ids;
			}
		}

		// Exclude specific posts.
		if ( ! empty( $atts['exclude'] ) ) {
			$exclude_ids = $this->parse_id_list( $atts['exclude'] );
			if ( ! empty( $exclude_ids ) ) {
				$query_args['post__not_in'] = $exclude_ids;
			}
		}
	}

	/**
	 * Handle taxonomy filters
	 *
	 * @param array $query_args Query arguments.
	 * @param array $atts       Attributes.
	 */
    private function handle_taxonomy_filters( &$query_args, $atts ) {
        $tax_query = array();

        $post_type = isset( $query_args['post_type'] ) ? $query_args['post_type'] : ( isset( $atts['post_type'] ) ? $atts['post_type'] : '' );

        // Resolve taxonomy names for category-like and tag-like filters depending on post type.
        $resolved_category_tax = 'category';
        $resolved_tag_tax      = 'post_tag';

        if ( ! empty( $post_type ) && 'any' !== $post_type ) {
            // WooCommerce products special-case.
            if ( 'product' === $post_type ) {
                $resolved_category_tax = 'product_cat';
                $resolved_tag_tax      = 'product_tag';
            } else {
                // If the default category taxonomy isn't attached, try to find a suitable hierarchical taxonomy.
                if ( ! is_object_in_taxonomy( $post_type, 'category' ) ) {
                    // Try <post_type>_category if it exists.
                    $candidate = $post_type . '_category';
                    if ( taxonomy_exists( $candidate ) && is_object_in_taxonomy( $post_type, $candidate ) ) {
                        $resolved_category_tax = $candidate;
                    } else {
                        // Fallback: pick the first public hierarchical taxonomy attached to this post type.
                        $tax_objects = get_object_taxonomies( $post_type, 'objects' );
                        foreach ( $tax_objects as $tax_obj ) {
                            if ( ! empty( $tax_obj->public ) && ! empty( $tax_obj->hierarchical ) ) {
                                $resolved_category_tax = $tax_obj->name;
                                break;
                            }
                        }
                    }
                }

                // Resolve tag-like taxonomy if post_tag isn't attached.
                if ( ! is_object_in_taxonomy( $post_type, 'post_tag' ) ) {
                    // Try <post_type>_tag if it exists.
                    $candidate_tag = $post_type . '_tag';
                    if ( taxonomy_exists( $candidate_tag ) && is_object_in_taxonomy( $post_type, $candidate_tag ) ) {
                        $resolved_tag_tax = $candidate_tag;
                    } else {
                        // Fallback: pick the first public non-hierarchical taxonomy attached.
                        $tax_objects = get_object_taxonomies( $post_type, 'objects' );
                        foreach ( $tax_objects as $tax_obj ) {
                            if ( ! empty( $tax_obj->public ) && empty( $tax_obj->hierarchical ) ) {
                                $resolved_tag_tax = $tax_obj->name;
                                break;
                            }
                        }
                    }
                }
            }
        }

        // Category filter.
        if ( ! empty( $atts['category'] ) ) {
            $tax_query[] = array(
                'taxonomy' => $resolved_category_tax,
                'field'    => 'slug',
                'terms'    => array_map( 'sanitize_title', array_map( 'trim', explode( ',', $atts['category'] ) ) ),
            );
        }

        // Tag filter.
        if ( ! empty( $atts['tag'] ) ) {
            $tax_query[] = array(
                'taxonomy' => $resolved_tag_tax,
                'field'    => 'slug',
                'terms'    => array_map( 'sanitize_title', array_map( 'trim', explode( ',', $atts['tag'] ) ) ),
            );
        }

        // Custom taxonomy filter.
        if ( ! empty( $atts['taxonomy'] ) && ! empty( $atts['term'] ) ) {
            $tax_query[] = array(
                'taxonomy' => sanitize_key( $atts['taxonomy'] ),
                'field'    => 'slug',
                'terms'    => array_map( 'sanitize_title', array_map( 'trim', explode( ',', $atts['term'] ) ) ),
            );
        }

        if ( ! empty( $tax_query ) ) {
            $query_args['tax_query'] = $tax_query;
        }
    }

	/**
	 * Handle date filters
	 *
	 * @param array $query_args Query arguments.
	 * @param array $atts       Attributes.
	 */
	private function handle_date_filters( &$query_args, $atts ) {
		$date_query = array();

		// Date from.
		if ( ! empty( $atts['date_from'] ) ) {
			$date_query['after'] = $atts['date_from'];
		}

		// Date to.
		if ( ! empty( $atts['date_to'] ) ) {
			$date_query['before'] = $atts['date_to'];
		}

		if ( ! empty( $date_query ) ) {
			$query_args['date_query'] = array( $date_query );
		}
	}

	/**
	 * Parse comma-separated ID list
	 *
	 * @param string $id_list Comma-separated IDs.
	 * @return array
	 */
	private function parse_id_list( $id_list ) {
		$ids = explode( ',', $id_list );
		$ids = array_map( 'trim', $ids );
		$ids = array_map( 'absint', $ids );
		$ids = array_filter( $ids );

		return $ids;
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
        add_shortcode( 'easy_sitemap', array( $this, 'sitemap_shortcode' ) );

		// Add quicktags.
		add_action( 'admin_print_footer_scripts', array( $this, 'add_quicktags' ) );
	}

	/**
	 * Main sitemap shortcode
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
    public function sitemap_shortcode( $atts ) {
        $atts = shortcode_atts(
			array(
                'post_type'      => '', // when empty -> combined output
                'limit'          => 1000,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'include'        => '',
				'exclude'        => '',
				'category'       => '',
				'tag'            => '',
				'taxonomy'       => '',
				'term'           => '',
				'author'         => '',
				'date_from'      => '',
				'date_to'        => '',
                'show_dates'     => '0',
                'show_excerpts'  => '0',
                'show_images'    => '0',
				'hierarchical'   => '0',
				'depth'          => '0',
				'columns'        => '1',
				'class'          => 'easy-sitemap',
				'cache'          => get_option( 'easy_sitemap_cache_enabled', '1' ),
				'cache_expiry'   => get_option( 'easy_sitemap_cache_expiry', 3600 ),
            ),
			$atts,
			'easy_sitemap'
		);

		// Sanitize attributes.
		$atts = $this->sanitize_attributes( $atts );

        // Derive type from post_type for internal rendering.
        if ( ! empty( $atts['post_type'] ) ) {
            if ( 'post' === $atts['post_type'] ) {
                $atts['type'] = 'posts';
            } elseif ( 'page' === $atts['post_type'] ) {
                $atts['type'] = 'pages';
            } else {
                $atts['type'] = 'cpt';
            }
        } else {
            $atts['type'] = 'all';
        }

		// Generate cache key.
		$cache_key = 'easy_sitemap_' . md5( serialize( $atts ) );

		// Check cache if enabled.
		if ( $atts['cache'] && ! is_user_logged_in() ) {
			$cached_output = get_transient( $cache_key );
			if ( false !== $cached_output ) {
				return $cached_output;
			}
		}

		// Generate sitemap.
		$output = $this->generate_sitemap( $atts );

		// Cache output if enabled.
		if ( $atts['cache'] && ! is_user_logged_in() ) {
			set_transient( $cache_key, $output, $atts['cache_expiry'] );
		}

		return $output;
	}

	/**
	 * Add quicktags for backward compatibility
	 */
	public function add_quicktags() {
		if ( wp_script_is( 'quicktags' ) ) {
			?>
			<script type="text/javascript">
			QTags.addButton( 'easy_sitemap', 'Easy Sitemap', '[easy_sitemap]', '', '', '',  );
			</script>
			<?php
		}
	}

	/**
	 * Sanitize shortcode attributes
	 *
	 * @param array $atts Raw attributes.
	 * @return array Sanitized attributes.
	 */
	private function sanitize_attributes( $atts ) {
		// Sanitize integers.
		$int_fields = array( 'limit', 'depth', 'columns', 'cache_expiry' );
		foreach ( $int_fields as $field ) {
			if ( isset( $atts[ $field ] ) ) {
				$atts[ $field ] = absint( $atts[ $field ] );
			}
		}

		// Sanitize booleans.
		$bool_fields = array( 'show_dates', 'show_excerpts', 'show_images', 'hierarchical', 'cache' );
		foreach ( $bool_fields as $field ) {
			if ( isset( $atts[ $field ] ) ) {
				$atts[ $field ] = $this->string_to_bool( $atts[ $field ] );
			}
		}

		// Sanitize orderby.
		$valid_orderby = array( 'date', 'title', 'modified', 'menu_order', 'rand', 'ID', 'author', 'name' );
		if ( ! in_array( $atts['orderby'], $valid_orderby, true ) ) {
			$atts['orderby'] = 'date';
		}

		// Sanitize order.
		if ( ! in_array( strtoupper( $atts['order'] ), array( 'ASC', 'DESC' ), true ) ) {
			$atts['order'] = 'DESC';
		}

		// Sanitize type.
            // post_type is primary; type derived later

		// Sanitize CSS class.
		$atts['class'] = sanitize_html_class( $atts['class'] );

		return $atts;
	}

	/**
	 * Convert string to boolean
	 *
	 * @param mixed $value Value to convert.
	 * @return bool
	 */
	private function string_to_bool( $value ) {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Generate sitemap output
	 *
	 * @param array $atts Sanitized attributes.
	 * @return string
	 */
	private function generate_sitemap( $atts ) {
		$posts = $this->get_posts( $atts );

		if ( empty( $posts ) ) {
			return '<p>' . esc_html__( 'No content found.', 'easy-sitemap' ) . '</p>';
		}

		// Build the sitemap structure based on type.
		switch ( $atts['type'] ) {
			case 'posts':
				return $this->build_posts_sitemap( $posts, $atts );
			case 'pages':
				return $this->build_pages_sitemap( $posts, $atts );
			case 'cpt':
				$include_title = 'product' !== $atts['post_type'];
				return $this->build_cpt_sitemap( $posts, $atts, $include_title );
			default:
				return $this->build_combined_sitemap( $atts );
		}
	}

	/**
	 * Build posts sitemap
	 *
	 * @param array $posts Posts array.
	 * @param array $atts  Attributes.
	 * @return string
	 */
	private function build_posts_sitemap( $posts, $atts ) {
		$classes = array( 'easy-sitemap-posts', $atts['class'] );
		if ( $atts['columns'] > 1 ) {
			$classes[] = 'easy-sitemap-columns-' . $atts['columns'];
		}
		$output = '<div class="' . esc_attr( implode( ' ', array_filter( $classes ) ) ) . '">';

		if ( $atts['hierarchical'] ) {
			$output .= $this->build_hierarchical_posts( $posts, $atts );
		} else {
			$output .= $this->build_flat_posts( $posts, $atts );
		}

		$output .= '</div>';
		return $output;
	}

	/**
	 * Build flat posts list
	 *
	 * @param array $posts Posts array.
	 * @param array $atts  Attributes.
	 * @return string
	 */
	private function build_flat_posts( $posts, $atts ) {
		$output = '<ul class="easy-sitemap-list">';

		foreach ( $posts as $post ) {
			$output .= $this->build_post_item( $post, $atts );
		}

		$output .= '</ul>';
		return $output;
	}

	/**
	 * Build hierarchical posts by category
	 *
	 * @param array $posts Posts array.
	 * @param array $atts  Attributes.
	 * @return string
	 */
	private function build_hierarchical_posts( $posts, $atts ) {
		$posts_by_category = array();

		foreach ( $posts as $post ) {
			$categories = get_the_category( $post->ID );
			if ( empty( $categories ) ) {
				$category_name = __( 'Uncategorized', 'easy-sitemap' );
				$posts_by_category[ $category_name ][] = $post;
			} else {
				foreach ( $categories as $category ) {
					$posts_by_category[ $category->name ][] = $post;
					break; // Only use first category.
				}
			}
		}

		ksort( $posts_by_category );

		$output = '';
		foreach ( $posts_by_category as $category_name => $category_posts ) {
			$output .= '<h3 class="easy-sitemap-category">' . esc_html( $category_name ) . '</h3>';
			$output .= '<ul class="easy-sitemap-list">';

			foreach ( $category_posts as $post ) {
				$output .= $this->build_post_item( $post, $atts );
			}

			$output .= '</ul>';
		}

		return $output;
	}

	/**
	 * Build pages sitemap
	 *
	 * @param array $posts Posts array.
	 * @param array $atts  Attributes.
	 * @return string
	 */
	private function build_pages_sitemap( $posts, $atts ) {
		$classes = array( 'easy-sitemap-pages', $atts['class'] );
		if ( $atts['columns'] > 1 ) {
			$classes[] = 'easy-sitemap-columns-' . $atts['columns'];
		}
		$output = '<div class="' . esc_attr( implode( ' ', array_filter( $classes ) ) ) . '">';

		if ( $atts['hierarchical'] ) {
			$output .= $this->build_hierarchical_pages( $posts, $atts );
		} else {
			$output .= '<ul class="easy-sitemap-list">';
			foreach ( $posts as $post ) {
				$output .= $this->build_post_item( $post, $atts );
			}
			$output .= '</ul>';
		}

		$output .= '</div>';
		return $output;
	}

	/**
	 * Build hierarchical pages
	 *
	 * @param array $posts Posts array.
	 * @param array $atts  Attributes.
	 * @return string
	 */
	private function build_hierarchical_pages( $posts, $atts ) {
		// Build page hierarchy.
		$pages_tree = $this->build_page_tree( $posts );

		return $this->render_page_tree( $pages_tree, $atts );
	}

	/**
	 * Build page tree structure
	 *
	 * @param array $pages Pages array.
	 * @return array
	 */
	private function build_page_tree( $pages ) {
		$tree = array();
		$refs = array();

		foreach ( $pages as $page ) {
			$page->children = array();
			$refs[ $page->ID ] = $page;

			if ( 0 == $page->post_parent ) {
				$tree[ $page->ID ] = $page;
			} else {
				if ( isset( $refs[ $page->post_parent ] ) ) {
					$refs[ $page->post_parent ]->children[ $page->ID ] = $page;
				}
			}
		}

		return $tree;
	}

	/**
	 * Render page tree
	 *
	 * @param array $tree Page tree.
	 * @param array $atts Attributes.
	 * @param int   $depth Current depth.
	 * @return string
	 */
	private function render_page_tree( $tree, $atts, $depth = 0 ) {
		if ( $atts['depth'] > 0 && $depth >= $atts['depth'] ) {
			return '';
		}

		$output = '<ul class="easy-sitemap-list' . ( $depth > 0 ? ' easy-sitemap-children' : '' ) . '">';

		foreach ( $tree as $page ) {
			$output .= $this->build_post_item( $page, $atts );

			if ( ! empty( $page->children ) ) {
				$output .= $this->render_page_tree( $page->children, $atts, $depth + 1 );
			}
		}

		$output .= '</ul>';
		return $output;
	}

	/**
	 * Build CPT sitemap
	 *
	 * @param array $posts Posts array.
	 * @param array $atts  Attributes.
	 * @return string
	 */
	private function build_cpt_sitemap( $posts, $atts, $include_title = true ) {
		$post_type_obj = get_post_type_object( $atts['post_type'] );
		$title = $post_type_obj ? $post_type_obj->labels->name : ucfirst( $atts['post_type'] );

		$classes = array( 'easy-sitemap-cpt', $atts['class'] );
		if ( $atts['columns'] > 1 ) {
			$classes[] = 'easy-sitemap-columns-' . $atts['columns'];
		}
		$output = '<div class="' . esc_attr( implode( ' ', array_filter( $classes ) ) ) . '">';
		if ( $include_title ) {
			$output .= '<h2 class="easy-sitemap-title">' . esc_html( $title ) . '</h2>';
		}
		$output .= '<ul class="easy-sitemap-list">';

		foreach ( $posts as $post ) {
			$output .= $this->build_post_item( $post, $atts );
		}

		$output .= '</ul></div>';
		return $output;
	}

	/**
	 * Build combined sitemap
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	private function build_combined_sitemap( $atts ) {
		$classes = array( 'easy-sitemap-combined', $atts['class'] );
		if ( $atts['columns'] > 1 ) {
			$classes[] = 'easy-sitemap-columns-' . $atts['columns'];
		}
		$output = '<div class="' . esc_attr( implode( ' ', array_filter( $classes ) ) ) . '">';

		// Posts section.
		$atts_posts = array_merge( $atts, array( 'type' => 'posts' ) );
		$posts = $this->get_posts( $atts_posts );
		if ( ! empty( $posts ) ) {
			$output .= '<h2>' . esc_html__( 'Posts', 'easy-sitemap' ) . '</h2>';
			$output .= $this->build_posts_sitemap( $posts, $atts_posts );
		}

		// Pages section.
		$atts_pages = array_merge( $atts, array( 'type' => 'pages' ) );
		$pages = $this->get_posts( $atts_pages );
		if ( ! empty( $pages ) ) {
			$output .= '<h2>' . esc_html__( 'Pages', 'easy-sitemap' ) . '</h2>';
			$output .= $this->build_pages_sitemap( $pages, $atts_pages );
		}

		// Custom post types section.
		$post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );
		foreach ( $post_types as $post_type ) {
			$atts_cpt = array_merge( $atts, array(
				'type'      => 'cpt',
				'post_type' => $post_type->name,
			) );
			$cpt_posts = $this->get_posts( $atts_cpt );
			if ( ! empty( $cpt_posts ) ) {
				$output .= '<h2>' . esc_html( $post_type->labels->name ) . '</h2>';
				$output .= $this->build_cpt_sitemap( $cpt_posts, $atts_cpt, false );
			}
		}

		$output .= '</div>';
		return $output;
	}

	/**
	 * Build individual post item
	 *
	 * @param WP_Post $post Post object.
	 * @param array   $atts Attributes.
	 * @return string
	 */
	private function build_post_item( $post, $atts ) {
		$output = '<li class="easy-sitemap-item">';

		// Date.
		if ( $atts['show_dates'] ) {
			$date_format = get_option( 'date_format' );
			$output .= '<span class="easy-sitemap-date">' . get_the_date( $date_format, $post ) . '</span> ';
		}

		// Link.
		$output .= '<a href="' . esc_url( get_permalink( $post ) ) . '" title="' . esc_attr( get_the_title( $post ) ) . '">';
		$output .= esc_html( get_the_title( $post ) );
		$output .= '</a>';

		// Excerpt.
		if ( $atts['show_excerpts'] && has_excerpt( $post ) ) {
			$output .= '<div class="easy-sitemap-excerpt">' . wp_kses_post( get_the_excerpt( $post ) ) . '</div>';
		}

		// Featured image.
		if ( $atts['show_images'] && has_post_thumbnail( $post ) ) {
			$image_size = apply_filters( 'easy_sitemap_image_size', 'thumbnail' );
			$image = get_the_post_thumbnail( $post, $image_size, array( 'class' => 'easy-sitemap-image' ) );
			$output .= '<div class="easy-sitemap-image-container">' . $image . '</div>';
		}

		$output .= '</li>';
		return $output;
	}
}
