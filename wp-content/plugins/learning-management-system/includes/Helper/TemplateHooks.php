<?php
/**
 * Masteriyo Template Hooks
 *
 * Action/filter hooks used for Masteriyo functions/templates.
 *
 * @package Masteriyo\Templates
 * @version 1.0.0
 */


if ( ! ( function_exists( 'add_filter' ) && function_exists( 'add_action' ) ) ) {
	return;
}

add_action( 'masteriyo_template_enroll_button', 'masteriyo_template_enroll_button' );
add_filter( 'admin_body_class', 'masteriyo_add_admin_body_class' );
add_filter( 'body_class', 'masteriyo_add_body_class', 10, 2 );

/**
 * Course category archive.
 */
add_action( 'masteriyo_after_course_category_main_content', 'masteriyo_archive_navigation' );
add_action( 'masteriyo_course_category_description', 'masteriyo_course_category_description' );

/**
 * Instructor archive.
 */
add_action( 'masteriyo_after_instructor_archive_main_content', 'masteriyo_archive_navigation' );

/**
 * Course Archive.
 */
add_action( 'masteriyo_after_main_content', 'masteriyo_archive_navigation' );

/**
 * Single course.
 */
add_action( 'masteriyo_single_course_content', 'masteriyo_single_course_featured_image', 10 );
add_action( 'masteriyo_single_course_content', 'masteriyo_single_course_categories', 20 );
add_action( 'masteriyo_single_course_content', 'masteriyo_single_course_title', 30 );
add_action( 'masteriyo_single_course_content', 'masteriyo_single_course_author_and_rating', 40 );
add_action( 'masteriyo_single_course_content', 'masteriyo_template_single_course_main_content', 50 );
add_action( 'masteriyo_single_course_main_content', 'masteriyo_single_course_tab_handles', 10 );
add_action( 'masteriyo_single_course_main_content', 'masteriyo_single_course_overview', 20 );
add_action( 'masteriyo_single_course_main_content', 'masteriyo_single_course_curriculum', 30 );
add_action( 'masteriyo_single_course_main_content', 'masteriyo_single_course_reviews', 40 );
add_action( 'masteriyo_after_single_course', 'masteriyo_single_course_modals', 10 );
add_action( 'masteriyo_after_single_course', 'masteriyo_template_single_course_related_courses', 20 );
add_action( 'masteriyo_template_course_review', 'masteriyo_template_course_review', 10, 2 );
add_action( 'masteriyo_template_course_review', 'masteriyo_template_single_course_review_replies', 20, 2 );
add_action( 'masteriyo_template_course_review_reply', 'masteriyo_template_course_review_reply' );
add_action( 'masteriyo_course_reviews_content', 'masteriyo_template_course_reviews_stats', 10, 3 );
add_action( 'masteriyo_course_reviews_content', 'masteriyo_template_course_reviews_list', 20, 3 );
add_action( 'masteriyo_course_reviews_content', 'masteriyo_template_single_course_see_more_reviews_button', 30, 3 );
add_action( 'masteriyo_course_reviews_content', 'masteriyo_single_course_review_form', 30, 3 );
add_action( 'masteriyo_single_course_sidebar_content', 'masteriyo_single_course_price_and_enroll_button', 10 );
add_action( 'masteriyo_single_course_sidebar_content', 'masteriyo_single_course_stats', 20 );
add_action( 'masteriyo_single_course_sidebar_content', 'masteriyo_single_course_highlights', 30 );

/**
 * Account page.
 */
add_action( 'masteriyo_account_sidebar_content', 'masteriyo_account_sidebar_content' );
add_action( 'masteriyo_account_main_content', 'masteriyo_account_main_content' );
add_action( 'masteriyo_account_edit-account_endpoint', 'masteriyo_account_edit_account_endpoint' );
add_action( 'masteriyo_account_view-account_endpoint', 'masteriyo_account_view_account_endpoint' );
add_action( 'masteriyo_account_courses_endpoint', 'masteriyo_account_courses_endpoint' );
add_action( 'masteriyo_account_order-history_endpoint', 'masteriyo_account_order_history_endpoint' );
add_action( 'masteriyo_account_view-order_endpoint', 'masteriyo_account_view_order_endpoint' );
/**
 * @since 1.3.0
 * @deprecated 1.5.0
 */
add_action( 'masteriyo_before_account_content', 'masteriyo_template_show_account_approved_notice' );

/**
 * Emails.
 */
add_action( 'masteriyo_email_header', 'masteriyo_email_header' );
add_action( 'masteriyo_email_footer', 'masteriyo_email_footer' );
add_action( 'masteriyo_email_order_details', 'masteriyo_email_order_details', 20 );
add_action( 'masteriyo_email_order_meta', 'masteriyo_email_order_meta', 10 );
add_action( 'masteriyo_email_customer_details', 'masteriyo_email_customer_addresses', 20 );


/**
 * Checkout form.
 */
add_action( 'masteriyo_checkout_summary', 'masteriyo_checkout_order_summary', 10 );
add_action( 'masteriyo_checkout_summary', 'masteriyo_checkout_payment', 20 );


/**
 * Course categories shortcode.
 *
 * @since 1.2.0
 */
add_action( 'masteriyo_template_shortcode_course_categories', 'masteriyo_template_shortcode_course_categories' );
add_action( 'masteriyo_template_shortcode_course_category', 'masteriyo_template_shortcode_course_category' );
