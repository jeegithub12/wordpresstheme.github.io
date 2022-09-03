<?php

/**
 * Template functions.
 *
 * @since 1.0.0
 */

use Masteriyo\Enums\OrderStatus;
use Masteriyo\Query\UserCourseQuery;
use Masteriyo\Query\CourseProgressQuery;
use Masteriyo\Enums\CourseProgressStatus;

if ( ! ( function_exists( 'add_action' ) && function_exists( 'add_filter' ) ) ) {
	return;
}

/**
 * Handle redirects before content is output - hooked into template_redirect so is_page works.
 *
 * @since 1.0.0
 */
function masteriyo_template_redirect() {
	global $wp_query, $wp;

	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	// When default permalinks are enabled, redirect courses list page to post type archive url.
	if ( ! empty( $_GET['page_id'] ) && '' === get_option( 'permalink_structure' )
		&& masteriyo_get_page_id( 'courses' ) === absint( $_GET['page_id'] )
		&& get_post_type_archive_link( 'mto-course' ) ) {
			wp_safe_redirect( get_post_type_archive_link( 'mto-course' ) );
			exit;
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	/**
	 * Filters boolean: true if it should be redirected to a different page if cart is empty.
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $bool true if it should be redirected to a different page if cart is empty.
	 */
	$redirect_empty_cart = apply_filters( 'masteriyo_checkout_redirect_empty_cart', true );

	// When on the checkout with an empty cart, redirect to courses list page.
	if (
		is_page( masteriyo_get_page_id( 'checkout' ) ) && masteriyo_get_page_id( 'checkout' ) !== masteriyo_get_page_id( 'cart' )
		&& masteriyo( 'cart' )->is_empty() && empty( $wp->query_vars['order-pay'] ) && ! isset( $wp->query_vars['order-received'] )
		&& ! is_customize_preview() && $redirect_empty_cart
	) {
		wp_safe_redirect( masteriyo_get_courses_url() );
		exit;
	}

	/**
	 * Filters boolean: true if it should be redirected to the found course's detail page, if search result has only one item.
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $bool true if it should be redirected to the found course's detail page, if search result has only one item.
	 */
	$redirect_single_search_result = apply_filters( 'masteriyo_redirect_single_search_result', true );

	// Redirect to the course page if we have a single course.
	if (
		is_search() && is_post_type_archive( 'mto-course' )
		&& $redirect_single_search_result && 1 === absint( $wp_query->found_posts )
	) {
		$course = masteriyo_get_course( $wp_query->post );

		if ( $course && $course->is_visible() ) {
			wp_safe_redirect( $course->get_permalink(), 302 );
			exit;
		}
	}
}
function_exists( 'add_action' ) && add_action( 'template_redirect', 'masteriyo_template_redirect' );

/**
 * Should the Masteriyo loop be displayed?
 *
 * This will return true if we have posts (courses) or if we have sub categories to display.
 *
 * @since 1.0.0
 * @return bool
 */
function masteriyo_course_loop() {
	return have_posts();
}

if ( ! function_exists( 'masteriyo_course_loop_start' ) ) {

	/**
	 * Output the start of a course loop. By default this is a UL.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo Should echo?.
	 * @return string
	 */
	function masteriyo_course_loop_start( $echo = true ) {
		ob_start();

		masteriyo_set_loop_prop( 'loop', 0 );

		masteriyo_get_template( 'loop/loop-start.php' );

		$loop_start = apply_filters( 'masteriyo_course_loop_start', ob_get_clean() );

		if ( $echo ) {
			echo wp_kses_post( $loop_start );
		} else {
			return $loop_start;
		}
	}
}

if ( ! function_exists( 'masteriyo_course_loop_end' ) ) {

	/**
	 * Output the end of a course loop. By default this is a UL.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $echo Should echo?.
	 * @return string
	 */
	function masteriyo_course_loop_end( $echo = true ) {
		ob_start();

		masteriyo_get_template( 'loop/loop-end.php' );

		/**
		 * Filters course loop end html.
		 *
		 * @since 1.0.0
		 *
		 * @param string $html The course loop end html.
		 */
		$loop_end = apply_filters( 'masteriyo_course_loop_end', ob_get_clean() );

		if ( $echo ) {
			echo wp_kses_post( $loop_end );
		} else {
			return $loop_end;
		}
	}
}

if ( ! function_exists( 'masteriyo_page_title' ) ) {

	/**
	 * Page Title function.
	 *
	 * @since 1.0.0
	 *
	 * @param  bool $echo Should echo title.
	 * @return string
	 */
	function masteriyo_page_title( $echo = true ) {
		if ( is_author() ) {
			// Queue the first post to know the archive author.
			the_post();

			$author = get_the_author();
			/* translators: %s: Author */
			$page_title = sprintf( __( 'Instructor | %s', 'masteriyo' ), esc_html( $author ) );

			// Since we called the_post() above, we need to rewind the loop back.
			rewind_posts();
		} elseif ( is_search() ) {
			/* translators: %s: search query */
			$page_title = sprintf( __( 'Search results: &ldquo;%s&rdquo;', 'masteriyo' ), get_search_query() );

			if ( get_query_var( 'paged' ) ) {
				/* translators: %s: page number */
				$page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'masteriyo' ), get_query_var( 'paged' ) );
			}
		} elseif ( is_tax() ) {
			$page_title = single_term_title( '', false );
		} else {
			$courses_page_id = masteriyo_get_page_id( 'courses' );
			$page_title      = get_the_title( $courses_page_id );
		}

		/**
		 * Filters current page title.
		 *
		 * @since 1.0.0
		 *
		 * @param string $page_title The current page title.
		 */
		$page_title = apply_filters( 'masteriyo_page_title', $page_title );

		if ( $echo ) {
			echo esc_html( $page_title );
		} else {
			return $page_title;
		}
	}
}

/**
 * Sets up the masteriyo_loop global from the passed args or from the main query.
 *
 * @since 1.0.0
 * @param array $args Args to pass into the global.
 */
function masteriyo_setup_loop( $args = array() ) {
	$default_args = array(
		'loop'         => 0,
		'columns'      => masteriyo_get_default_courses_per_row(),
		'name'         => '',
		'is_shortcode' => false,
		'is_paginated' => true,
		'is_search'    => false,
		'is_filtered'  => false,
		'total'        => 0,
		'total_pages'  => 0,
		'per_page'     => 0,
		'current_page' => 1,
	);

	// If this is a main Masteriyo query, use global args as defaults.
	if ( $GLOBALS['wp_query']->get( 'masteriyo_query' ) ) {
		$default_args = array_merge(
			$default_args,
			array(
				'is_search'    => $GLOBALS['wp_query']->is_search(),
				'is_filtered'  => masteriyo_is_filtered(),
				'total'        => $GLOBALS['wp_query']->found_posts,
				'total_pages'  => $GLOBALS['wp_query']->max_num_pages,
				'per_page'     => $GLOBALS['wp_query']->get( 'posts_per_page' ),
				'current_page' => max( 1, $GLOBALS['wp_query']->get( 'paged', 1 ) ),
			)
		);
	}

	// Merge any existing values.
	if ( isset( $GLOBALS['masteriyo_loop'] ) ) {
		$default_args = array_merge( $default_args, $GLOBALS['masteriyo_loop'] );
	}

	$GLOBALS['masteriyo_loop'] = wp_parse_args( $args, $default_args );
}
function_exists( 'add_action' ) && add_action( 'masteriyo_before_courses_loop', 'masteriyo_setup_loop' );

/**
 * Resets the masteriyo_loop global.
 *
 * @since 1.0.6
 */
function masteriyo_reset_loop() {
	unset( $GLOBALS['masteriyo_loop'] );
}

/**
 * Get the default columns setting - this is how many courses will be shown per row in loops.
 *
 * @since 1.0.0
 *
 * @return int
 */
function masteriyo_get_default_courses_per_row() {
	$columns     = masteriyo_get_setting( 'course_archive.display.per_row' );
	$course_grid = masteriyo_get_theme_support( 'course_grid' );
	$min_columns = isset( $course_grid['min_columns'] ) ? absint( $course_grid['min_columns'] ) : 0;
	$max_columns = isset( $course_grid['max_columns'] ) ? absint( $course_grid['max_columns'] ) : 0;

	if ( $min_columns && $columns < $min_columns ) {
		$columns = $min_columns;
		update_option( 'masteriyo.courses.per_row', $columns );
	} elseif ( $max_columns && $columns > $max_columns ) {
		$columns = $max_columns;
		update_option( 'masteriyo.courses.per_row', $columns );
	}

	$columns = max( 1, absint( $columns ) );

	/**
	 * Filters number of columns for a course loop.
	 *
	 * @since 1.0.0
	 *
	 * @param integer $columns The number of columns for a course loop.
	 */
	return apply_filters( 'masteriyo_loop_courses_columns', $columns );
}

/**
 * Get the default rows setting - this is how many course rows will be shown in loops.
 *
 * @since 1.0.0
 * @return int
 */
function masteriyo_get_default_course_rows_per_page() {
	$rows        = masteriyo_get_setting( 'course_archive.display.per_page' );
	$course_grid = masteriyo_get_theme_support( 'course_grid' );
	$min_rows    = isset( $course_grid['min_rows'] ) ? absint( $course_grid['min_rows'] ) : 0;
	$max_rows    = isset( $course_grid['max_rows'] ) ? absint( $course_grid['max_rows'] ) : 0;

	if ( $min_rows && $rows < $min_rows ) {
		$rows = $min_rows;
		update_option( 'masteriyo.courses.per_page', $rows );
	} elseif ( $max_rows && $rows > $max_rows ) {
		$rows = $max_rows;
		update_option( 'masteriyo.courses.per_page', $rows );
	}

	return $rows;
}

/**
 * Sets a property in the masteriyo_loop global.
 *
 * @since 1.0.0
 * @param string $prop Prop to set.
 * @param string $value Value to set.
 */
function masteriyo_set_loop_prop( $prop, $value = '' ) {
	if ( ! isset( $GLOBALS['masteriyo_loop'] ) ) {
		masteriyo_setup_loop();
	}
	$GLOBALS['masteriyo_loop'][ $prop ] = $value;
}

/**
 * Gets a property from the masteriyo_loop global.
 *
 * @since 1.0.0
 * @param string $prop Prop to get.
 * @param string $default Default if the prop does not exist.
 * @return mixed
 */
function masteriyo_get_loop_prop( $prop, $default = '' ) {
	masteriyo_setup_loop(); // Ensure courses loop is setup.

	return isset( $GLOBALS['masteriyo_loop'], $GLOBALS['masteriyo_loop'][ $prop ] ) ? $GLOBALS['masteriyo_loop'][ $prop ] : $default;
}

/**
 * When the_post is called, put course data into a global.
 *
 * @param mixed $post Post Object.
 * @return Masteriyo\Models\Course
 */
function masteriyo_setup_course_data( $post ) {
	unset( $GLOBALS['course'] );

	if ( is_int( $post ) ) {
		$post = get_post( $post );
	}

	if ( empty( $post->post_type ) || ! in_array( $post->post_type, array( 'mto-course' ), true ) ) {
		return;
	}

	$GLOBALS['course'] = masteriyo_get_course( $post );

	return $GLOBALS['course'];
}
function_exists( 'add_action' ) && add_action( 'the_post', 'masteriyo_setup_course_data' );

/**
 * Add class to the body tag.
 *
 * @since 1.0.0
 *
 * @param string[] $classes An array of body class names.
 * @param string[] $class   An array of additional class names added to the body.
 * @return string[]
 */

function masteriyo_add_body_class( $classes, $class ) {
	global $post;

	if ( masteriyo_is_archive_course_page() ) {
		$classes[] = 'masteriyo masteriyo-courses-page';
	} elseif ( masteriyo_is_learn_page() ) {
		$classes[] = 'masteriyo notranslate masteriyo-interactive-page';
	} elseif ( masteriyo_is_account_page() ) {
		$classes[] = 'masteriyo masteriyo-account-page';
	} elseif ( is_tax( 'course_cat' ) ) {
		$classes[] = 'masteriyo masteriyo-course-category-page';
	}

	if ( $post ) {
		$tags = masteriyo_get_shortcode_tags();

		foreach ( $tags as $tag ) {
			if ( has_shortcode( $post->post_content, $tag ) ) {
				$classes[] = 'masteriyo';
				break;
			}
		}

		$blocks = masteriyo_get_blocks();
		foreach ( $blocks as $block ) {
			if ( has_block( $block, $post->post_content ) ) {
				$classes[] = 'masteriyo';
				break;
			}
		}
	}

	return $classes;
}

if ( ! function_exists( 'masteriyo_add_admin_body_class' ) ) {
	/**
	 * Add class to the body tag in admin page.
	 *
	 * @since 1.3.0
	 *
	 * @param string $classes An string of body class names.
	 *
	 * @return string
	 */
	function masteriyo_add_admin_body_class( $classes ) {
		if ( masteriyo_is_post_edit_page() && masteriyo_is_block_editor() ) {
			$classes .= ' masteriyo';
		}

		return $classes;
	}
}

if ( ! function_exists( 'masteriyo_template_enroll_button' ) ) {
	/**
	 * Show enroll now button.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_template_enroll_button( $course ) {
		$query = new CourseProgressQuery(
			array(
				'course_id' => $course->get_id(),
				'user_id'   => get_current_user_id(),
			)
		);

		$progress = current( $query->get_course_progress() );

		$class = array(
			'masteriyo-btn',
			'masteriyo-btn-primary',
			'masteriyo-single-course--btn',
			'mb-0',
		);

		if ( masteriyo_can_start_course( $course ) ) {
			if ( $progress && CourseProgressStatus::COMPLETED === $progress->get_status() ) {
				$class[] = 'masteriyo-btn-complete';
			} elseif ( $progress && CourseProgressStatus::COMPLETED === $progress->get_status() ) {
				$class[] = 'masteriyo-btn-continue';
			} else {
				$class[] = 'masteriyo-btn-start-course';
			}
		} else {
			$class[] = 'masteriyo-course--btn';
		}

		/**
		 * Filters enroll button class.
		 *
		 * @since 1.5.12
		 *
		 * @param string[] $class An array of class names.
		 * @param \Masteriyo\Models\Course $course Course object.
		 * @param \Masteriyo\Models\CourseProgress $progress Course progress object.
		 */
		$class = apply_filters( 'masteriyo_enroll_button_class', $class, $course, $progress );

		masteriyo_get_template(
			'enroll-button.php',
			array(
				'course'   => $course,
				'progress' => $progress,
				'class'    => join( ' ', $class ),
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_account_sidebar_content' ) ) {
	/**
	 * Show sidebar on account page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_account_sidebar_content() {
		$data = array(
			'menu_items'       => masteriyo_get_account_menu_items(),
			'user'             => masteriyo_get_current_user(),
			'current_endpoint' => masteriyo_get_current_account_endpoint(),
		);

		masteriyo_get_template( 'account/sidebar-content.php', $data );
	}
}

if ( ! function_exists( 'masteriyo_account_courses_endpoint' ) ) {
	/**
	 * Show courses on account page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_account_courses_endpoint() {
		$query = new UserCourseQuery(
			array(
				'user_id' => get_current_user_id(),
				'limit'   => -1,
			)
		);

		$user_courses = $query->get_user_courses();
		$all_courses  = array_filter(
			array_map(
				function( $user_course ) {
					$course = masteriyo_get_course( $user_course->get_course_id() );

					if ( is_null( $course ) ) {
						return null;
					}

					return $course;
				},
				$user_courses
			)
		);

		$active_courses = masteriyo_get_active_courses( get_current_user_id() );

		masteriyo_get_template(
			'account/courses.php',
			array(
				'active_courses' => $active_courses,
				'all_courses'    => $all_courses,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_account_edit_account_endpoint' ) ) {
	/**
	 * Edit account on account page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_account_edit_account_endpoint() {
		$data = array(
			'user' => masteriyo_get_current_user(),
		);

		masteriyo_get_template( 'account/edit-account.php', $data );
	}
}

if ( ! function_exists( 'masteriyo_account_view_account_endpoint' ) ) {
	/**
	 * View profile on account page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_account_view_account_endpoint() {
		$data = array(
			'user' => masteriyo_get_current_user(),
		);

		masteriyo_get_template( 'account/view-account.php', $data );
	}
}

if ( ! function_exists( 'masteriyo_account_order_history_endpoint' ) ) {
	/**
	 * Show order history on account page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_account_order_history_endpoint() {
		$orders = masteriyo_get_orders(
			array(
				'customer_id' => get_current_user_id(),
			)
		);

		masteriyo_get_template(
			'account/order-history.php',
			array(
				'orders' => $orders,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_account_view_order_endpoint' ) ) {
	/**
	 * Show order detail on account page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_account_view_order_endpoint( $order_id ) {
		$order       = masteriyo_get_order( $order_id );
		$customer_id = $order->get_customer_id();

		if ( ! masteriyo_is_current_user_admin() && ! masteriyo_is_current_user_manager() && get_current_user_id() !== $customer_id ) {
			esc_html_e( 'You are not allowed to view this content.', 'masteriyo' );
			return;
		}

		$notes       = $order->get_customer_order_notes();
		$order_items = $order->get_items( 'course' );

		/**
		 * Filters order status list for showing purchase note when they exist.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $statuses The order status list for showing purchase note when they exist.
		 */
		$show_purchase_note = $order->has_status( apply_filters( 'masteriyo_purchase_note_order_statuses', array( OrderStatus::COMPLETED, OrderStatus::PROCESSING ) ) );

		$show_customer_details = masteriyo_is_current_user_admin() || ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() );

		masteriyo_get_template(
			'account/view-order.php',
			array(
				'order'                 => $order,
				'notes'                 => $notes,
				'order_items'           => $order_items,
				'show_purchase_note'    => $show_purchase_note,
				'show_customer_details' => $show_customer_details,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_account_main_content' ) ) {
	/**
	 * Handle account page's main content.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_account_main_content() {
		$endpoint         = masteriyo_get_current_account_endpoint();
		$current_endpoint = $endpoint['endpoint'];

		if ( has_action( 'masteriyo_account_' . $current_endpoint . '_endpoint' ) ) {
			/**
			 * Action for rendering content of an endpoint in account page.
			 *
			 * @since 1.0.0
			 *
			 * @param string $arg Endpoint argument.
			 */
			do_action( 'masteriyo_account_' . $current_endpoint . '_endpoint', $endpoint['arg'] );
			return;
		}

		$endpoint['user']           = masteriyo_get_current_user();
		$endpoint['active_courses'] = masteriyo_get_active_courses( get_current_user_id() );

		// No endpoint found? Default to dashboard.
		masteriyo_get_template( 'account/dashboard.php', $endpoint );
	}
}

if ( ! function_exists( 'masteriyo_email_header' ) ) {
	/**
	 * Get the email header.
	 *
	 * @param mixed $email_heading Heading for the email.
	 */
	function masteriyo_email_header( $email_heading ) {
		masteriyo_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
	}
}

if ( ! function_exists( 'masteriyo_email_footer' ) ) {
	/**
	 * Get the email footer.
	 */
	function masteriyo_email_footer() {
		masteriyo_get_template( 'emails/email-footer.php' );
	}
}

if ( ! function_exists( 'masteriyo_single_course_featured_image' ) ) {
	/**
	 * Show course featured image.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_single_course_featured_image( $course ) {
		masteriyo_get_template(
			'single-course/featured-image.php',
			array(
				'course' => $course,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_single_course_categories' ) ) {
	/**
	 * Show course categories.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_single_course_categories( $course ) {
		if ( empty( $course->get_categories() ) ) {
			return;
		}

		masteriyo_get_template(
			'single-course/categories.php',
			array(
				'course' => $course,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_single_course_title' ) ) {
	/**
	 * Show course title.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_single_course_title( $course ) {
		masteriyo_get_template(
			'single-course/title.php',
			array(
				'course' => $course,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_single_course_author_and_rating' ) ) {
	/**
	 * Show course author and rating.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_single_course_author_and_rating( $course ) {
		masteriyo_get_template(
			'single-course/author-and-rating.php',
			array(
				'course' => $course,
				'author' => masteriyo_get_user( $course->get_author_id() ),
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_single_course_price_and_enroll_button' ) ) {
	/**
	 * Show course price and enroll button.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_single_course_price_and_enroll_button( $course ) {
		masteriyo_get_template(
			'single-course/price-and-enroll-button.php',
			array(
				'course' => $course,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_single_course_stats' ) ) {
	/**
	 * Show course stats in single course page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_single_course_stats( $course ) {
		$comments_count = masteriyo_count_course_comments( $course );

		masteriyo_get_template(
			'single-course/course-stats.php',
			array(
				'course'               => $course,
				'comments_count'       => $comments_count,
				'enrolled_users_count' => masteriyo_count_enrolled_users( $course->get_id() ),
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_single_course_highlights' ) ) {
	/**
	 * Show course highlights in single course page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_single_course_highlights( $course ) {
		masteriyo_get_template(
			'single-course/highlights.php',
			array(
				'course' => $course,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_template_single_course_main_content' ) ) {
	/**
	 * Show course main content in single course page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_template_single_course_main_content( $course ) {
		masteriyo_get_template(
			'single-course/main-content.php',
			array(
				'course' => $course,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_single_course_tab_handles' ) ) {
	/**
	 * Show tab handles in single course page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_single_course_tab_handles( $course ) {
		masteriyo_get_template(
			'single-course/tab-handles.php',
			array(
				'course' => $course,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_single_course_overview' ) ) {
	/**
	 * Show course overview in single course page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_single_course_overview( $course ) {
		masteriyo_get_template(
			'single-course/overview.php',
			array(
				'course' => $course,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_single_course_curriculum' ) ) {
	/**
	 * Show course curriculum in single course page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_single_course_curriculum( $course ) {
		if ( $course->get_show_curriculum() || masteriyo_can_start_course( $course ) ) {
			$structure     = masteriyo_get_course_structure( $course->get_id() );
			$lessons_count = 0;
			$quiz_count    = 0;

			foreach ( $structure as $section ) {
				if ( empty( $section ) || ! is_array( $section['contents'] ) ) {
					continue;
				}
				$lessons_count += count(
					array_filter(
						$section['contents'],
						function( $content ) {
							return 'lesson' === $content['type'];
						}
					)
				);
				$quiz_count    += count(
					array_filter(
						$section['contents'],
						function( $content ) {
							return 'quiz' === $content['type'];
						}
					)
				);
			}

			masteriyo_get_template(
				'single-course/curriculum.php',
				array(
					'course'           => $course,
					'course_structure' => $structure,
					'sections_count'   => count( $structure ),
					'lessons_count'    => $lessons_count,
					'quiz_count'       => $quiz_count,
				)
			);
		}

	}
}

if ( ! function_exists( 'masteriyo_single_course_reviews' ) ) {
	/**
	 * Show course reviews in single course page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_single_course_reviews( $course ) {
		if ( masteriyo_get_setting( 'single_course.display.enable_review' ) ) {

			$reviews_and_replies = masteriyo_get_course_reviews_and_replies( $course );

			masteriyo_get_template(
				'single-course/reviews.php',
				array(
					'course'         => $course,
					'course_reviews' => $reviews_and_replies['reviews'],
					'replies'        => $reviews_and_replies['replies'],
				)
			);
		}
	}
}

if ( ! function_exists( 'masteriyo_single_course_review_form' ) ) {
	/**
	 * Show course review form in single course page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_single_course_review_form( $course ) {
		masteriyo_get_template(
			'single-course/course-review-form.php',
			array(
				'course' => $course,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_checkout_order_summary' ) ) {
	/**
	 * Display billing form.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_checkout_order_summary() {
		$cart = masteriyo( 'cart' );
		if ( is_null( $cart ) ) {
			return;
		}

		$courses = array();

		if ( ! $cart->is_empty() ) {
			foreach ( $cart->get_cart_contents() as $cart_content ) {
				if ( ! isset( $cart_content['course_id'] ) ) {
					continue;
				}

				$course = masteriyo_get_course( $cart_content['course_id'] );
				if ( is_null( $course ) ) {
					continue;
				}

				$courses[] = $course;
			}
		}

		$data = array(
			'cart'    => $cart,
			'courses' => $courses,
		);

		masteriyo_get_template( 'checkout/order-summary.php', $data );
	}
}

if ( ! function_exists( 'masteriyo_checkout_payment' ) ) {
	/**
	 * Display checkout form payment.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_checkout_payment() {
		$available_gateways = array();

		if ( masteriyo( 'cart' )->needs_payment() ) {
			$available_gateways = masteriyo( 'payment-gateways' )->get_available_payment_gateways();
		}

		/**
		 * Filters order button text.
		 *
		 * @since 1.0.0
		 *
		 * @param string $text The order button text.
		 */
		// translators: Cart total order
		$order_button_text = apply_filters( 'masteriyo_order_button_text', __( 'Confirm Payment', 'masteriyo' ) );

		masteriyo_get_template(
			'checkout/payment.php',
			array(
				'checkout'           => masteriyo( 'checkout' ),
				'available_gateways' => $available_gateways,
				'order_button_text'  => $order_button_text,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_display_item_meta' ) ) {
	/**
	 * Display item meta data.
	 *
	 * @since  1.0.0
	 * @param  OrderItem $item Order Item.
	 * @param  array         $args Arguments.
	 * @return string|void
	 */
	function masteriyo_display_item_meta( $item, $args = array() ) {
		$strings = array();
		$html    = '';
		$args    = wp_parse_args(
			$args,
			array(
				'before'       => '<ul class="masteriyo-item-meta"><li>',
				'after'        => '</li></ul>',
				'separator'    => '</li><li>',
				'echo'         => true,
				'autop'        => false,
				'label_before' => '<strong class="masteriyo-item-meta-label">',
				'label_after'  => ':</strong> ',
			)
		);

		foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
			$value     = $args['autop'] ? wp_kses_post( $meta->display_value ) : wp_kses_post( make_clickable( trim( $meta->display_value ) ) );
			$strings[] = $args['label_before'] . wp_kses_post( $meta->display_key ) . $args['label_after'] . $value;
		}

		if ( $strings ) {
			$html = $args['before'] . implode( $args['separator'], $strings ) . $args['after'];
		}

		/**
		 * Filters order item meta html.
		 *
		 * @since 1.0.0
		 *
		 * @param string $html The order item meta html.
		 * @param object $order_item Order item object.
		 * @param array $args Arguments.
		 */
		$html = apply_filters( 'masteriyo_display_item_meta', $html, $item, $args );

		if ( $args['echo'] ) {
			echo wp_kses_post( $html );
		} else {
			return $html;
		}
	}
}

if ( ! function_exists( 'masteriyo_archive_navigation' ) ) {

	/**
	 * Display course archive navigation.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_archive_navigation() {
		masteriyo_get_template( 'course-pagination.php' );
	}
}

if ( ! function_exists( 'masteriyo_get_course_search_form' ) ) {

	/**
	 * Display course search form.
	 *
	 * Will first attempt to locate the course-searchform.php file in either the child or.
	 * the parent, then load it. If it doesn't exist, then the default search form.
	 * will be displayed.
	 *
	 * The default searchform uses html5.
	 *
	 * @since 1.0.0
	 * @param bool $echo (default: true).
	 * @return string
	 */
	function masteriyo_get_course_search_form( $echo = true ) {
		global $course_search_form_index;

		ob_start();

		if ( empty( $course_search_form_index ) ) {
			$course_search_form_index = 0;
		}

		/**
		 * Fires before rendering course search form.
		 *
		 * @since 1.0.0
		 */
		do_action( 'before_masteriyo_get_course_search_form' );

		masteriyo_get_template(
			'course-searchform.php',
			array(
				'index' => $course_search_form_index++,
			)
		);

		/**
		 * Filters course search form html.
		 *
		 * @since 1.0.0
		 *
		 * @param string $html The course search form html.
		 */
		$search_form = apply_filters( 'masteriyo_get_course_search_search_form', ob_get_clean() );

		if ( ! $echo ) {
			return $search_form;
		}

		echo $search_form; // phpcs:ignore
	}
}

if ( ! function_exists( 'masteriyo_course_search_form' ) ) {

	/**
	 * Course Search Form.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_course_search_form() {
		$show_search_box = masteriyo_get_setting( 'course_archive.display.enable_search' );

		if ( true !== $show_search_box ) {
			return;
		}

		?>
		<div class="masteriyo-search">
			<?php masteriyo_get_course_search_form(); ?>
		</div>
		<?php
	}
	function_exists( 'add_action' ) && add_action( 'masteriyo_before_main_content', 'masteriyo_course_search_form' );
}

if ( ! function_exists( 'masteriyo_email_order_details' ) ) {
	/**
	 * Show order details.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_email_order_details( $order, $email = null ) {
		masteriyo_get_template(
			'emails/order-details.php',
			array(
				'order' => $order,
				'email' => $email,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_email_order_meta' ) ) {
	/**
	 * Show order metas.
	 *
	 * @since 1.0.0
	 *
	 * @param false|\Masteriyo\Models\Order $order Order object.
	 */
	function masteriyo_email_order_meta( $order = false ) {
		/**
		 * Filters order email meta fields.
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields Meta fields.
		 * @param false|\Masteriyo\Models\Order $order Order object.
		 */
		$fields = apply_filters( 'masteriyo_email_order_meta_fields', array(), $order );

		/**
		 * Deprecated masteriyo_email_order_meta_keys filter.
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields Meta fields.
		 */
		$_fields = apply_filters( 'masteriyo_email_order_meta_keys', array() );

		if ( $_fields ) {
			foreach ( $_fields as $key => $field ) {
				if ( is_numeric( $key ) ) {
					$key = $field;
				}

				$fields[ $key ] = array(
					'label' => wptexturize( $key ),
					'value' => wptexturize( get_post_meta( $order->get_id(), $field, true ) ),
				);
			}
		}

		if ( $fields ) {
			foreach ( $fields as $field ) {
				if ( isset( $field['label'] ) && isset( $field['value'] ) && $field['value'] ) {
					echo wp_kses_post( '<p><strong>' . $field['label'] . ':</strong> ' . $field['value'] . '</p>' );
				}
			}
		}
	}
}

if ( ! function_exists( 'masteriyo_email_customer_addresses' ) ) {
	/**
	 * Show order metas.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_email_customer_addresses( $order, $email = null ) {
		masteriyo_get_template(
			'emails/customer-addresses.php',
			array(
				'order' => $order,
				'email' => $email,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_get_email_order_items' ) ) {
	/**
	 * Get HTML for the order items to be shown in emails.
	 *
	 * @param Masteriyo\Models\Order\Order $order Order object.
	 * @param array $args Arguments.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function masteriyo_get_email_order_items( $order, $args = array() ) {
		ob_start();

		$defaults = array(
			'show_sku'   => false,
			'show_image' => false,
			'image_size' => array( 32, 32 ),
		);

		$args = wp_parse_args( $args, $defaults );

		masteriyo_get_template(
			'emails/order-items.php',
			/**
			 * Filters order item args for email.
			 *
			 * @since 1.0.0
			 *
			 * @param array $args Order item args.
			 */
			apply_filters(
				'masteriyo_email_order_items_args',
				array(
					'order'              => $order,
					'items'              => $order->get_items(),
					'show_sku'           => $args['show_sku'],
					'show_purchase_note' => $order->is_paid(),
					'show_image'         => $args['show_image'],
					'image_size'         => $args['image_size'],
				)
			)
		);

		/**
		 * Filters order items table html for email.
		 *
		 * @since 1.0.0
		 *
		 * @param string $html
		 * @param Masteriyo\Models\Order\Order $order Order object.
		 */
		return apply_filters( 'masteriyo_email_order_items_table', ob_get_clean(), $order );
	}
}

if ( ! function_exists( 'masteriyo_the_email_order_items' ) ) {
	/**
	 * Get HTML for the order items to be shown in emails.
	 *
	 * @param Order $order Order object.
	 * @param array $args Arguments.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function masteriyo_the_email_order_items( $order, $args = array() ) {
		$html = masteriyo_get_email_order_items( $order, $args );
		echo wp_kses_post( $html );
	}
}

if ( ! function_exists( 'masteriyo_display_item_meta' ) ) {
	/**
	 * Display item meta data.
	 *
	 * @since  1.0.0
	 * @param  OrderItem $item Order Item.
	 * @param  array         $args Arguments.
	 * @return string|void
	 */
	function masteriyo_display_item_meta( $item, $args = array() ) {
		$strings = array();
		$html    = '';
		$args    = wp_parse_args(
			$args,
			array(
				'before'       => '<ul class="masteriyo-item-meta"><li>',
				'after'        => '</li></ul>',
				'separator'    => '</li><li>',
				'echo'         => true,
				'autop'        => false,
				'label_before' => '<strong class="masteriyo-item-meta-label">',
				'label_after'  => ':</strong> ',
			)
		);

		foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
			$value     = $args['autop'] ? wp_kses_post( $meta->display_value ) : wp_kses_post( make_clickable( trim( $meta->display_value ) ) );
			$strings[] = $args['label_before'] . wp_kses_post( $meta->display_key ) . $args['label_after'] . $value;
		}

		if ( $strings ) {
			$html = $args['before'] . implode( $args['separator'], $strings ) . $args['after'];
		}

		/**
		 * Filters html to display order item meta.
		 *
		 * @since 1.0.0
		 *
		 * @param string $html The html to display order item meta.
		 * @param object $order_item Order item object.
		 * @param array $args Arguments.
		 */
		$html = apply_filters( 'masteriyo_display_item_meta', $html, $item, $args );

		if ( $args['echo'] ) {
			echo wp_kses_post( $html );
		} else {
			return $html;
		}
	}
}

if ( ! function_exists( 'masteriyo_single_course_modals' ) ) {
	/**
	 * Include modals for the single course page.
	 *
	 * @since 1.0.0
	 */
	function masteriyo_single_course_modals() {
		masteriyo_get_template( 'single-course/modals.php' );
	}
}

if ( ! function_exists( 'masteriyo_template_course_review' ) ) {
	/**
	 * Print course review item.
	 *
	 * @since 1.0.5
	 *
	 * @param \Masteriyo\Models\CourseReview $course_review Course review object.
	 */
	function masteriyo_template_course_review( $course_review ) {
		if ( 'trash' === $course_review->get_status() ) {
			masteriyo_get_template( 'notices/review-deleted.php' );
			return;
		}

		masteriyo_get_template(
			'single-course/course-review.php',
			array(
				'pp_placeholder' => masteriyo_get_course_review_author_pp_placeholder(),
				'course_review'  => $course_review,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_template_course_review_reply' ) ) {
	/**
	 * Print course review reply.
	 *
	 * @since 1.0.5
	 */
	function masteriyo_template_course_review_reply( $args = array() ) {
		$args['pp_placeholder'] = masteriyo_get_course_review_author_pp_placeholder();

		masteriyo_get_template( 'single-course/course-review-reply.php', $args );
	}
}

if ( ! function_exists( 'masteriyo_template_course_reviews_stats' ) ) {
	/**
	 * Print course reviews stats.
	 *
	 * @since 1.0.5
	 */
	function masteriyo_template_course_reviews_stats( $course, $course_reviews, $replies ) {
		masteriyo_get_template( 'single-course/reviews-stats.php', compact( 'course', 'course_reviews', 'replies' ) );
	}
}

if ( ! function_exists( 'masteriyo_template_course_reviews_list' ) ) {
	/**
	 * Print course reviews list.
	 *
	 * @since 1.0.5
	 *
	 * @param \Masteriyo\Models\Course $course Course object.
	 * @param \Masteriyo\Models\CourseReview[] $course_reviews Course reviews.
	 * @param \Masteriyo\Models\CourseReview[] $replies Replies to the course review.
	 */
	function masteriyo_template_course_reviews_list( $course, $course_reviews, $replies ) {
		masteriyo_get_template(
			'single-course/reviews-list.php',
			array(
				'course'         => $course,
				'course_reviews' => $course_reviews,
				'replies'        => $replies,
			)
		);
	}
}

if ( ! function_exists( 'masteriyo_template_shortcode_course_categories' ) ) {
	/**
	 * Print course categories.
	 *
	 * @since 1.2.0
	 */
	function masteriyo_template_shortcode_course_categories( $attrs ) {
		masteriyo_get_template( 'shortcodes/course-categories/list.php', $attrs );
	}
}

if ( ! function_exists( 'masteriyo_template_shortcode_course_category' ) ) {
	/**
	 * Print course category.
	 *
	 * @since 1.2.0
	 */
	function masteriyo_template_shortcode_course_category( $attrs ) {
		masteriyo_get_template( 'shortcodes/course-categories/list-item.php', $attrs );
	}
}

if ( ! function_exists( 'masteriyo_template_show_account_approved_notice' ) ) {
	/**
	 * Display account approved notice if the user is instructor and is not approved.
	 *
	 * @since 1.3.0
	 * @deprecated 1.5.0
	 */
	function masteriyo_template_show_account_approved_notice() {
		masteriyo_deprecated_function( 'masteriyo_template_show_account_approved_notice', '1.5.0' );
	}
}

if ( ! function_exists( 'masteriyo_course_category_description' ) ) {
	/**
	 * Display course category description in course category archive page.
	 *
	 * @since 1.4.3
	 */
	function masteriyo_course_category_description() {
		$term = get_queried_object();

		if ( ! is_object( $term ) || ! isset( $term->description ) ) {
			return;
		}

		$html  = '<p class="masteriyo-courses-header__description">';
		$html .= wp_kses_post( $term->description );
		$html .= '</p>';

		echo wp_kses_post( $html );
	}
}

if ( ! function_exists( 'masteriyo_template_single_course_review_replies' ) ) {
	/**
	 * Display review replies in single course page's reviews list section.
	 *
	 * @since 1.5.9
	 *
	 * @param \Masteriyo\Models\CourseReview $course_review Course review object.
	 * @param \Masteriyo\Models\CourseReview[] $replies Replies to the course review.
	 */
	function masteriyo_template_single_course_review_replies( $course_review, $replies ) {
		if ( ! empty( $replies ) ) {
			masteriyo_get_template(
				'single-course/review-replies.php',
				array(
					'course_review' => $course_review,
					'replies'       => $replies,
				)
			);
		}
	}
}

if ( ! function_exists( 'masteriyo_template_single_course_see_more_reviews_button' ) ) {
	/**
	 * Display see more reviews button in single course page's reviews list section.
	 *
	 * @since 1.5.9
	 *
	 * @param \Masteriyo\Models\Course $course Course object.
	 */
	function masteriyo_template_single_course_see_more_reviews_button( $course ) {
		if ( masteriyo_get_course_reviews_infinite_loading_pages_count( $course ) > 1 ) {
			masteriyo_get_template(
				'single-course/see-more-reviews-button.php',
				array(
					'course' => $course,
				)
			);
		}
	}
}

if ( ! function_exists( 'masteriyo_template_single_course_related_courses' ) ) {

	/**
	 * Display related courses in single course page.
	 *
	 * @since 1.5.9
	 */
	function masteriyo_template_single_course_related_courses() {
		if ( masteriyo_get_setting( 'single_course.related_courses.enable' ) ) {
			masteriyo_get_template_part( 'content', 'related-posts' );
		}
	}
}
