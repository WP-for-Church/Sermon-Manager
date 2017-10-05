<?php
defined( 'ABSPATH' ) or die; // exit if accessed directly

class WPFC_Shortcodes {
	/**
	 * Instance of this class
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Add all shortcodes to WP and assign functions
	 *
	 * @return void
	 */
	public function init() {
		// List all series or speakers in a simple unordered list
		add_shortcode( 'list_sermons', array( self::getInstance(), 'displaySermonsList' ) );
		// Display all series or speakers in a grid of images
		add_shortcode( 'sermon_images', array( self::getInstance(), 'displayImages' ) );
		// Display the latest sermon series image (optional - by service type)
		add_shortcode( 'latest_series', array( self::getInstance(), 'displayLatestSeriesImage' ) );
		// main shortcode
		add_shortcode( 'sermons', array( self::getInstance(), 'displaySermons' ) );
		add_shortcode( 'sermon_sort_fields', array( self::getInstance(), 'displaySermonSorting' ) );

		// deprecated
		$this->legacyShortcodes();
	}

	/**
	 * Get new instance self or current one if exists
	 *
	 * @return WPFC_Shortcodes
	 */
	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * They are here for compatibility purposes
	 */
	public function legacyShortcodes() {
		add_shortcode( 'list-sermons', array( self::getInstance(), 'displaySermonsList' ) );
		add_shortcode( 'sermon-images', array( self::getInstance(), 'displayImages' ) );
	}

	/**
	 * Display an unordered list of series, preachers, topics or books
	 *
	 * @param array[] $atts Shortcode parameters
	 *
	 * @type string   $atts ['display'] The taxonomy, possible options: series, preachers, topics, books
	 * @type string   $atts ['order'] Sorting order, possible options: ASC, DESC
	 * @type string   $atts ['ordrerby'] Possible options: id, count, name, slug, term_group, none
	 *
	 * @return string List or error message.
	 */
	public function displaySermonsList( $atts ) {
		// enqueue scripts and styles
		if ( ! defined( 'SM_ENQUEUE_SCRIPTS_STYLES' ) ) {
			define( 'SM_ENQUEUE_SCRIPTS_STYLES', true );
		}

		// default options
		$args = array(
			'display' => 'series',
			'order'   => 'ASC',
			'orderby' => 'name',
		);

		// for compatibility
		if ( ! empty( $atts['tax'] ) ) {
			$atts['display'] = $atts['tax'];
			unset( $atts['tax'] );
		}

		// for compatibility
		if ( ! empty( $atts['taxonomy'] ) ) {
			$atts['display'] = $atts['taxonomy'];
			unset( $atts['taxonomy'] );
		}

		// join default and user options
		$args = shortcode_atts( $args, $atts, 'list_sermons' );

		// check if we are using a SM taxonomy, and if we are, convert to valid taxonomy name
		if ( $this->convertTaxonomyName( $args['display'], true ) ) {
			$args['display'] = $this->convertTaxonomyName( $args['display'], false );
		} else if ( ! $this->convertTaxonomyName( $args['display'], false ) ) {
			return '<strong>Error: Invalid "list" parameter.</strong><br> Possible values are: "series", "preachers", "topics" and "books".<br> You entered: "<em>' . $args['display'] . '</em>"';
		}

		$query_args = array(
			'taxonomy' => $args['display'],
			'orderby'  => $args['orderby'],
			'order'    => $args['order'],
		);

		if ( $query_args['orderby'] === 'date' ) {
			$query_args['orderby']      = 'meta_value_num';
			$query_args['meta_key']     = 'sermon_date';
			$query_args['meta_compare'] = '<=';
			$query_args['meta_value_num']   = time();
		}

		// get items
		$terms = get_terms( $query_args );

		if ( count( $terms ) > 0 ) {
			// sort books by order
			if ( $args['display'] === 'wpfc_bible_book' && $args['orderby'] === 'book' ) {
				// book order
				$books = array(
					'Genesis',
					'Exodus',
					'Leviticus',
					'Numbers',
					'Deuteronomy',
					'Joshua',
					'Judges',
					'Ruth',
					'1 Samuel',
					'2 Samuel',
					'1 Kings',
					'2 Kings',
					'1 Chronicles',
					'2 Chronicles',
					'Ezra',
					'Nehemiah',
					'Esther',
					'Job',
					'Psalm',
					'Proverbs',
					'Ecclesiastes',
					'Song of Songs',
					'Isaiah',
					'Jeremiah',
					'Lamentations',
					'Ezekiel',
					'Daniel',
					'Hosea',
					'Joel',
					'Amos',
					'Obadiah',
					'Jonah',
					'Micah',
					'Nahum',
					'Habakkuk',
					'Zephaniah',
					'Haggai',
					'Zechariah',
					'Malachi',
					'Matthew',
					'Mark',
					'Luke',
					'John',
					'Acts',
					'Romans',
					'1 Corinthians',
					'2 Corinthians',
					'Galatians',
					'Ephesians',
					'Philippians',
					'Colossians',
					'1 Thessalonians',
					'2 Thessalonians',
					'1 Timothy',
					'2 Timothy',
					'Titus',
					'Philemon',
					'Hebrews',
					'James',
					'1 Peter',
					'2 Peter',
					'1 John',
					'2 John',
					'3 John',
					'Jude',
					'Revelation',
					'Topical',
				);

				// assign every book a number
				foreach ( $terms as $term ) {
					$ordered_terms[ array_search( $term->name, $books ) ] = $term;
				}

				// order the numbers (books)
				ksort( $ordered_terms );

				$terms = $ordered_terms;
			}

			$list = '<ul id="list-sermons">';
			foreach ( $terms as $term ) {
				$list .= '<li><a href="' . esc_url( get_term_link( $term, $term->taxonomy ) ) . '" title="' . $term->name . '">' . $term->name . '</a></li>';
			}
			$list .= '</ul>';

			return $list;
		} else {
			// if nothing has been found
			return 'No ' . $this->convertTaxonomyName( $args['display'], true ) . ' found.';
		}
	}

	/**
	 * Used to convert user friendly names to taxonomy names, i.e. "series" => "wpfc_sermon_series".
	 * Or taxonomy names to user friendly ones.
	 *
	 * @param string $name
	 * @param bool   $new_name Should it return user friendly name
	 *
	 * @return string|null null if nothing found, name otherwise
	 */
	public function convertTaxonomyName( $name, $new_name ) {
		$old_taxonomies = array(
			'wpfc_sermon_series',
			'wpfc_preacher',
			'wpfc_sermon_topics',
			'wpfc_bible_book',
			'wpfc_service_type'
		);
		$new_taxonomies = array( 'series', 'preachers', 'topics', 'books', 'service_types' );

		if ( $new_name ) {
			if ( in_array( $name, $old_taxonomies ) ) {
				return $new_taxonomies[ array_search( $name, $old_taxonomies ) ];
			}

			// return itself if it's already converted. try plural if (assumed) singular doesn't exist
			foreach ( array( $name, $name . 's' ) as $name_s ) {
				if ( in_array( $name_s, $new_taxonomies ) ) {
					return $name_s;
				}
			}
		} else {
			// try plural if (assumed) singular doesn't exist
			foreach ( array( $name, $name . 's' ) as $name_s ) {
				if ( in_array( $name_s, $new_taxonomies ) ) {
					return $old_taxonomies[ array_search( $name_s, $new_taxonomies ) ];
				}
			}

			// return itself if it's already converted
			if ( in_array( $name, $old_taxonomies ) ) {
				return $name;
			}
		}

		return null;
	}

	/**
	 * Display all series or speakers in a grid of images
	 *
	 * @param array $atts Shortcode parameters
	 *
	 * @type string $atts ['display'] The taxonomy, possible options: series, preachers
	 * @type string $atts ['order'] Sorting order, possible options: ASC, DESC
	 * @type string $atts ['ordrerby'] Possible options: id, count, name, slug, term_group, none
	 * @type string $atts ['size'] Possible options: sermon_small, sermon_medium, sermon_wide, thumbnail, medium,
	 *       large, full, or any size added with add_image_size()
	 * @type bool   $atts ['show_description'] Should we show the description, default false
	 *
	 * @return string Grid or error message.
	 */
	public function displayImages( $atts = array() ) {
		// enqueue scripts and styles
		if ( ! defined( 'SM_ENQUEUE_SCRIPTS_STYLES' ) ) {
			define( 'SM_ENQUEUE_SCRIPTS_STYLES', true );
		}

		// default args
		$args = array(
			'display'          => 'series',
			'order'            => 'DESC',
			'orderby'          => 'name',
			'size'             => 'sermon_medium',
			'show_description' => false,
		);

		// for compatibility
		if ( ! empty( $atts['tax'] ) ) {
			$atts['display'] = $atts['tax'];
			unset( $atts['tax'] );
		}

		// for compatibility
		if ( ! empty( $atts['show_desc'] ) ) {
			$atts['show_description'] = $atts['show_desc'];
			unset( $atts['show_desc'] );
		}

		// join default and user options
		$args = shortcode_atts( $args, $atts, 'sermon_images' );

		// convert to bool
		$args['show_description'] = boolval( $args['show_description'] );

		// check if we are using a SM taxonomy, and if we are, convert to valid taxonomy name
		if ( $this->convertTaxonomyName( $args['display'], true ) ) {
			$args['display'] = $this->convertTaxonomyName( $args['display'], false );
		} else if ( ! $this->convertTaxonomyName( $args['display'], false ) ) {
			return '<strong>Error: Invalid "list" parameter.</strong><br> Possible values are: "series", "preachers", "topics" and "books".<br> You entered: "<em>' . $args['display'] . '</em>"';
		}

		// get images
		$terms = apply_filters( 'sermon-images-get-terms', '', array(
			'taxonomy'  => $args['display'],
			'term_args' => array(
				'order'   => $args['order'],
				'orderby' => $args['orderby'],
			)
		) );

		// $terms will always return an array
		if ( ! empty( $terms ) ) {
			$list = '<ul id="wpfc_images_grid">';

			foreach ( (array) $terms as $term ) {
				$term_url = esc_url( get_term_link( $term, $term->taxonomy ) );

				$list .= '<li class="wpfc_grid_image">';
				$list .= '<a href="' . $term_url . '">' . wp_get_attachment_image( $term->image_id, $args['size'] ) . '</a>';
				$list .= '<h3 class="wpfc_grid_title"><a href="' . $term_url . '">' . $term->name . '</a></h3>';
				if ( $args['show_description'] === true ) {
					if ( ! empty( $term->description ) ) {
						$list .= '<div class="taxonomy-description">' . $term->description . '</div>';
					}
				}
				$list .= '</li>';
			}

			$list .= '</ul>';

			return $list;
		} else {
			// if nothing has been found
			return 'No ' . $this->convertTaxonomyName( $args['display'], true ) . ' images found.';
		}
	}

	/**
	 * Display the latest sermon series image (optional - by service type)
	 *
	 * @param array $atts Shortcode options
	 *
	 * @type string $atts ['image_class'] CSS class for image
	 * @type string $atts ['size'] Image size. Possible options: sermon_small, sermon_medium, sermon_wide, thumbnail,
	 *       medium, large, full, or any size added with add_image_size()
	 * @type bool   $atts ['show_title'] false to hide the series title (true is the default)
	 * @type string $atts ['title_wrapper'] Possible options: p, h1, h2, h3, h4, h5, h6, div
	 * @type string $atts ['title_class'] CSS class for title
	 * @type string $atts ['service_type'] Service type ID/slug/name. Used to get latest series from that service type.
	 * @type bool   $atts ['show_description'] true to show series description (false is the default)
	 * @type string $atts ['wrapper_class'] CSS class for wrapper
	 *
	 * @return string
	 */
	function displayLatestSeriesImage( $atts = array() ) {
		// enqueue scripts and styles
		if ( ! defined( 'SM_ENQUEUE_SCRIPTS_STYLES' ) ) {
			define( 'SM_ENQUEUE_SCRIPTS_STYLES', true );
		}

		// default options
		$args = array(
			'image_class'      => 'latest-series-image',
			'size'             => 'large',
			'show_title'       => true,
			'title_wrapper'    => 'h3',
			'title_class'      => 'latest-series-title',
			'service_type'     => '',
			'show_description' => false,
			'wrapper_class'    => 'latest-series',
		);

		// for compatibility
		if ( ! empty( $atts['show_desc'] ) ) {
			$atts['show_description'] = $atts['show_desc'];
			unset( $atts['show_desc'] );
		}

		// join default and user options
		$args = shortcode_atts( $args, $atts, 'latest_series' );

		// get latest series
		$latest_series = $this->getLatestSeries( 0, $args['service_type'] );

		// if for some reason we couldn't get latest series
		if ( $latest_series === null ) {
			return 'No latest series found.';
		}

		// Image ID
		$series_image_id = $this->getLatestSeriesImageId( $latest_series );

		// if for some reason we couldn't get latest series image
		if ( $series_image_id === null ) {
			return 'No latest series image found.';
		}

		// link to series
		$series_link = get_term_link( $latest_series, 'wpfc_sermon_series' );
		// image CSS class
		$image_class = sanitize_html_class( $args['image_class'] );
		// title wrapper tag name
		$wrapper_options = array( 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div' );
		if ( ! in_array( sanitize_text_field( $args['title_wrapper'] ), $wrapper_options ) ) {
			$args['title_wrapper'] = 'h3';
		}
		// title CSS class
		$title_class = sanitize_html_class( $args['title_class'] );

		$link_open  = '<a href="' . $series_link . '" title="' . $latest_series->name . '" alt="' . $latest_series->name . '">';
		$link_close = '</a>';

		$image = wp_get_attachment_image( $series_image_id, $args['size'], false, array( 'class' => $image_class ) );

		$title = $description = '';
		if ( boolval( $args['show_title'] ) === true ) {
			$title = $latest_series->name;
			$title = '<' . $args['title_wrapper'] . ' class="' . $title_class . '">' . $title . '</' . $args['title_wrapper'] . '>';
		}
		if ( boolval( $args['show_desc'] ) === true ) {
			$description = '<div class="latest-series-description">' . wpautop( $latest_series->description ) . '</div>';
		}

		$wrapper_class = sanitize_html_class( $args['wrapper_class'] );
		$before        = '<div class="' . $wrapper_class . '">';
		$after         = '</div>';

		$output = $before . $link_open . $image . $title . $link_close . $description . $after;

		return $output;
	}

	/**
	 * Get all sermon series as WP_Term object
	 *
	 * @param int $latest_sermon Optional. Latest sermon ID. If not provided, it will try to get it automatically.
	 * @param int $service_type  Optional. Service Type for getting latest sermon ID
	 *
	 * @return WP_Term|null
	 */
	public function getLatestSeries( $latest_sermon = 0, $service_type = 0 ) {
		if ( empty( $latest_sermon ) ) {
			$latest_sermon = $this->getLatestSermonId( $service_type );
		}

		$latest_series = get_the_terms( $latest_sermon, 'wpfc_sermon_series' );

		if ( is_array( $latest_series ) && ! empty( $latest_series ) ) {
			return $latest_series[0];
		}

		return null;
	}

	/**
	 * Get the latest sermon ID
	 *
	 * @param string|int $service_type Optional argument to get latest sermon from specified service type. Slug, name
	 *                                 and ID are accepted values.
	 *
	 * @return int|null Sermon ID on success, null on failure
	 */
	public function getLatestSermonId( $service_type = 0 ) {
		$args = array(
			'post_type'              => 'wpfc_sermon',
			'posts_per_page'         => 1,
			'post_status'            => 'publish',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false
		);

		// if service type is set
		if ( $service_type !== 0 ) {
			/*
			 * if it's not numeric, we will try to find it by slug;
			 * if not found by slug, we will try to find it by name.
			 */

			if ( ! is_numeric( $service_type ) ) {
				foreach ( array( 'slug', 'name' ) as $field ) {
					$service_type = get_term_by( $field, $service_type, 'wpfc_service_type' );

					if ( $service_type !== false ) {
						$service_type = intval( $service_type->term_id );
						break;
					}
				}
			} else {
				// convert to int, if string number is used
				$service_type = intval( $service_type );
			}

			if ( is_int( $service_type ) && term_exists( $service_type, 'wpfc_service_type' ) ) {
				$args['tax_query'] = array(
					'taxonomy' => 'wpfc_service_type',
					'terms'    => $service_type,
				);
			}
		}

		$latest_sermon = new WP_Query( $args );

		// if there is a post, return ID
		if ( ! empty( $latest_sermon->post_count ) ) {
			wp_reset_postdata();

			return $latest_sermon->post->ID;
		}

		return null;
	}

	/**
	 * Get the image ID of the specified series ID
	 * Will try to get image ID of latest series if $latest_series argument not set
	 *
	 * @param int $series Series to get the image of
	 *
	 * @return int|null
	 */
	function getLatestSeriesImageId( $series = 0 ) {
		if ( $series === 0 ) {
			$series = $this->getLatestSeries();

			if ( $series === null ) {
				return null;
			}
		}

		$associations = sermon_image_plugin_get_associations();
		$tt_id        = absint( $series->term_taxonomy_id );

		if ( array_key_exists( $tt_id, $associations ) ) {
			$ID = absint( $associations[ $tt_id ] );

			return $ID;
		}

		return null;
	}

	/**
	 * Renders sorting HTML.
	 *
	 * @param array $atts       Shortcode parameters.
	 *
	 * @type string $series     Force specific series to show. Slug only
	 * @type string $preachers  Force specific preacher to show. Slug only
	 * @type string $topics     Force specific topic to show. Slug only
	 * @type string $books      Force specific book to show. Slug only
	 * @type string $visibility 'none' to hide the forced fields, 'disable' to show them as disabled and 'suggest' to
	 *       just set the default value while allowing user to change it. Default 'suggest'
	 *
	 * @return string Sorting HTML
	 *
	 * @since 2.5.0 added shortcode parameters
	 */
	public function displaySermonSorting( $atts = array() ) {
		// enqueue scripts and styles
		if ( ! defined( 'SM_ENQUEUE_SCRIPTS_STYLES' ) ) {
			define( 'SM_ENQUEUE_SCRIPTS_STYLES', true );
		}

		// default shortcode options
		$args = array(
			'series'     => '',
			'preachers'  => '',
			'topics'     => '',
			'books'      => '',
			'visibility' => 'suggest',
		);

		// merge default and user options
		$args = shortcode_atts( $args, $atts, 'sermon_sort_fields' );

		return render_wpfc_sorting( $args );
	}

	/**
	 * Main sermon display code
	 *
	 * @param array $atts Shortcode parameters
	 *
	 * @type int    $atts ['per_page'] How many sermons per page.
	 * @type string $atts ['sermons'] Include only these sermons. Separate with comma (,) with no spaces. IDs only.
	 * @type string $atts ['order'] Sorting order, possible options: ASC, DESC
	 * @type string $atts ['orderby'] Sort by: date (default), none, ID, title, name, rand, comment_count
	 * @type bool   $atts ['hide_pagination'] true to hide the pagination (default false)
	 * @type bool   $atts ['image_size'] Image size. Possible values: sermon_small, sermon_medium, sermon_wide,
	 *       thumbnail, medium, large, full, or any size added with add_image_size(). (default is sermon_small)
	 * @type string $atts ['filter_by'] Filter by series, preacher, topic, book, service_type
	 * @type string $atts ['filter_value'] ID/slug of allowed filters
	 * @type int    $atts ['year'] 4 digit year (e.g. 2011)
	 * @type int    $atts ['month'] Month number (from 1 to 12)
	 * @type int    $atts ['week'] Week of the year (from 0 to 53)
	 * @type int    $atts ['day'] Day of the month (from 1 to 31)
	 * @type string $atts ['after'] Date to retrieve posts after. Accepts strtotime()-compatible string
	 * @type string $atts ['before'] Date to retrieve posts before. Accepts strtotime()-compatible string
	 *
	 *
	 * @return string
	 */
	function displaySermons( $atts = array() ) {
		// enqueue scripts and styles
		if ( ! defined( 'SM_ENQUEUE_SCRIPTS_STYLES' ) ) {
			define( 'SM_ENQUEUE_SCRIPTS_STYLES', true );
		}

		// default options
		$args = array(
			'per_page'        => '10',
			'sermons'         => false,
			'order'           => 'DESC',
			'orderby'         => 'date',
			'hide_pagination' => false,
			'image_size'      => 'sermon_small',
			'filter_by'       => '',
			'filter_value'    => '',
			'year'            => '',
			'month'           => '',
			'week'            => '',
			'day'             => '',
			'after'           => '',
			'before'          => '',
		);

		// legacy convert
		$old_options = array(
			'posts_per_page' => 'per_page',
			'id'             => 'sermons',
			'hide_nav'       => 'hide_pagination',
			'taxonomy'       => 'filter_by',
			'tax_term'       => 'filter_value'
		);

		foreach ( $old_options as $old_option => $new_option ) {
			if ( ! empty( $atts[ $old_option ] ) ) {
				$args[ $new_option ] = $atts[ $old_option ];
				unset( $atts[ $old_option ] );
			}
		}

		// merge default and user options
		$args = shortcode_atts( $args, $atts, 'sermons' );

		// set page
		if ( get_query_var( 'paged' ) ) {
			$my_page = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$my_page = get_query_var( 'page' );
		} else {
			global $paged;
			$paged = $my_page = 1;
			set_query_var( 'paged', 1 );
		}

		// set query args
		$query_args = array(
			'post_type'      => 'wpfc_sermon',
			'posts_per_page' => $args['per_page'],
			'order'          => $args['order'],
			'paged'          => $my_page,
			'year'           => $args['year'],
			'month'          => $args['month'],
			'week'           => $args['week'],
			'day'            => $args['day'],
			'after'          => $args['after'],
			'before'         => $args['before'],
			'meta_query'     => array(
				'relation' => 'OR',
				array( //check to see if date has been filled out
					'key'     => 'sermon_date',
					'compare' => '<=',
					'value'   => time()
				),
				array( //if no date has been added show these posts too
					'key'     => 'sermon_date',
					'value'   => time(),
					'compare' => 'NOT EXISTS'
				)
			),
		);

		// check if it's a valid ordering argument
		if ( ! in_array( strtolower( $args['orderby'] ), array(
			'date',
			'id',
			'none',
			'title',
			'name',
			'rand',
			'comment_count'
		) ) ) {
			$args['orderby'] = 'date';
		}

		$query_args['orderby'] = $args['orderby'];

		// if we should show just specific sermons
		if ( $args['sermons'] ) {
			$posts_in = explode( ',', $args['sermons'] );

			if ( ! empty( $posts_in ) ) {
				foreach ( $posts_in as &$post_in ) {
					// remove if it's not an ID
					if ( ! is_numeric( trim( $post_in ) ) ) {
						unset( $post_in );
						continue;
					}

					// convert to int
					$post_in = intval( trim( $post_in ) );
				}

				$query_args['post__in'] = $posts_in;
			}
		}

		// if we should filter by something
		if ( $args['filter_by'] && $args['filter_value'] ) {
			// Term string to array
			$terms = explode( ',', $args['filter_value'] );

			if ( ! empty( $terms ) ) {
				$field = 'slug';

				if ( is_numeric( $terms[0] ) ) {
					$field = 'id';
				}

				foreach ( $terms as &$term ) {
					$term = trim( $term );

					if ( $field === 'id' ) {
						// remove if it's not an ID
						if ( ! is_numeric( $term ) ) {
							unset( $term );
							continue;
						}

						// convert to int
						$term = intval( $term );
					}
				}

				$query_args['tax_query'] = array(
					array(
						'taxonomy' => $this->convertTaxonomyName( $args['filter_by'], false ),
						'field'    => 'slug',
						'terms'    => $terms,
					)
				);
			}
		}

		foreach ( array( 'wpfc_preacher', 'wpfc_sermon_series', 'wpfc_sermon_topics', 'wpfc_bible_book' ) as $filter ) {
			if ( ! empty( $_GET[ $filter ] ) ) {
				if ( empty( $query_args['tax_query']['custom'] ) || empty( $query_args['tax_query'] ) ) {
					$query_args['tax_query'] = array();
				}

				$query_args['tax_query'][0][] = array(
					'taxonomy' => $filter,
					'field'    => 'slug',
					'terms'    => sanitize_title_for_query( $_GET[ $filter ] ),
				);

				$query_args['tax_query']['custom'] = true;
			}
		}

		if ( ! empty( $query_args['tax_query'] ) && count( $query_args['tax_query'] ) > 1 && ! empty( $query_args['tax_query']['custom'] ) ) {
			unset( $query_args['tax_query']['custom'] );
		}

		$listing = new WP_Query( $query_args );

		// set image size
		add_filter( 'wpfc_sermon_excerpt_sermon_image_size', function () use ( $args ) {
			return $args['image_size'];
		} );

		if ( $listing->have_posts() ) {
			ob_start(); ?>
            <div id="wpfc_sermon">
                <div id="wpfc_loading">
					<?php while ( $listing->have_posts() ): ?>
						<?php $listing->the_post(); ?>
                        <div class="wpfc_sermon_wrap">
                            <h3 class="sermon-title">
                                <a href="<?php the_permalink(); ?>"
                                   title="<?php printf( esc_attr__( 'Permalink to %s', 'sermon-manager-for-wordpress' ), the_title_attribute( 'echo=0' ) ); ?>"
                                   rel="bookmark"><?php the_title(); ?></a></h3>
							<?php do_action( 'sermon_excerpt' ); ?>
                        </div>
					<?php endwhile; ?>

                    <div style="clear:both;"></div>

					<?php wp_reset_postdata(); ?>

					<?php if ( ! $args['hide_pagination'] ): ?>
                        <div id="sermon-navigation">
							<?php
							$big = 999999;
							echo paginate_links( array(
								'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
								'format'  => '?paged=%#%',
								'current' => max( 1, $query_args['paged'] ),
								'total'   => $listing->max_num_pages
							) );
							?>
                        </div>
					<?php endif; ?>
                    <div style="clear:both;"></div>
                </div>
            </div>
			<?php
			$buffer = ob_get_clean();

			return $buffer;
		} else {
			return 'No sermons found.';
		}
	}
}

$WPFC_Shortcodes = new WPFC_Shortcodes;
$WPFC_Shortcodes->init();