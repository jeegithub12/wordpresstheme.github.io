<?php

/**
 * The template for displaying course content within loops
 *
 * This template can be overridden by copying it to yourtheme/masteriyo/content-course.php.
 *
 * HOWEVER, on occasion Masteriyo will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @package Masteriyo\Templates
 * @version 1.5.9
 */

defined( 'ABSPATH' ) || exit;

global $course;

// Ensure visibility.
if ( empty( $course ) || ! $course->is_visible() ) {
	return;
}

$author     = masteriyo_get_user( $course->get_author_id() );
$difficulty = $course->get_difficulty();
$categories = $course->get_categories( 'name' );

?>
<div class="masteriyo-col">
	<div class="masteriyo-course-item--wrapper masteriyo-course--card">
		<div class="masteriyo-course--img-wrap">
		<a href="<?php echo esc_attr( $course->get_permalink() ); ?>">

			<!-- Difficulty Badge -->
			<?php if ( $difficulty ) : ?>
			<div class="difficulty-badge">
				<span class="masteriyo-badge <?php echo esc_attr( masteriyo_get_difficulty_badge_css_class( $difficulty['slug'] ) ); ?>"><?php echo esc_html( $difficulty['name'] ); ?></span>
			</div>
			<?php endif; ?>

			<!-- Featured Image -->
			<?php echo wp_kses( $course->get_image( 'masteriyo_thumbnail' ), 'masteriyo_image' ); ?>
		</a>
		</div>

		<div class="masteriyo-course--content">

			<!-- Course category -->
			<?php if ( ! empty( $categories ) ) : ?>
				<div class="masteriyo-course--content__category">
					<?php foreach ( $categories as $category ) : ?>
					<a href="<?php echo esc_attr( $category->get_permalink() ); ?>" class="masteriyo-course--content__category-items masteriyo-tag">
						<?php echo esc_html( $category->get_name() ); ?>
					</a>
				<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!-- Title of the course -->
			<h2 class="masteriyo-course--content__title">
				<?php
				printf(
					'<a href="%s" title="%s">%s</a>',
					esc_url( $course->get_permalink() ),
					esc_html( $course->get_title() ),
					esc_html( $course->get_title() )
				);
				?>
			</h2>

			<!-- Course author and course rating -->
			<div class="masteriyo-course--content__rt">
				<div class="masteriyo-course-author">
				<?php if ( $author ) : ?>
					<a href="<?php echo $author ? esc_attr( $author->get_course_archive_url() ) : '#'; ?>">
						<img src="<?php echo esc_attr( $author->get_avatar_url() ); ?>" alt="" srcset="">
						<span class="masteriyo-course-author--name"><?php echo esc_attr( $author->get_display_name() ); ?></span>
					</a>
				<?php endif; ?>

				<?php
				/**
				 * Fire after masteriyo course author.
				 *
				 * @since 1.5.10
				 *
				 * @param \Masteriyo\Models\Course $course Course object.
				 */
				do_action( 'masteriyo_after_course_author', $course );
				?>

				</div>
				<span class="masteriyo-icon-svg masteriyo-rating">
				<?php masteriyo_format_rating( $course->get_average_rating(), true ); ?> <?php echo esc_html( masteriyo_format_decimal( $course->get_average_rating(), 1, true ) ); ?> (<?php echo esc_html( $course->get_review_count() ); ?>)
				</span>
			</div>

			<!-- Course description -->
			<div class="masteriyo-course--content__description">
				<?php if ( empty( $course->get_highlights() ) || empty( trim( wp_strip_all_tags( $course->get_highlights(), true ) ) ) ) : ?>
					<?php echo wp_kses_post( $course->get_excerpt() ); ?>
				<?php else : ?>
					<?php echo wp_kses_post( masteriyo_trim_course_highlights( $course->get_highlights() ) ); ?>
				<?php endif; ?>
			</div>

			<!-- Four Column (Course duration, comments, student enrolled and curriculum) -->
			<div class="masteriyo-course--content__stats">
				<div class="masteriyo-course-stats-duration">
				<?php masteriyo_get_svg( 'time', true ); ?> <span><?php echo esc_html( masteriyo_minutes_to_time_length_string( $course->get_duration() ) ); ?></span>
				</div>
				<div class="masteriyo-course-stats-students">
				<?php masteriyo_get_svg( 'group', true ); ?> <span><?php echo esc_html( masteriyo_count_enrolled_users( $course->get_id() ) ); ?></span>
				</div>
				<div class="masteriyo-course-stats-curriculum">
				<?php masteriyo_get_svg( 'book', true ); ?> <span><?php echo esc_html( masteriyo_get_lessons_count( $course ) ); ?></span>
				</div>
			</div>

			<!-- Border -->
			<!-- Price and Enroll Now Button -->
			<div class="masteriyo-time-btn">
				<div class="masteriyo-course-price">
				<span class="current-amount"><?php echo wp_kses_post( masteriyo_price( $course->get_price() ) ); ?></span>
				</div>
				<?php
				/**
				 * Action hook for rendering enroll button template.
				 *
				 * @since 1.0.0
				 *
				 * @param \Masteriyo\Models\Course $course Course object.
				 */
				do_action( 'masteriyo_template_enroll_button', $course );
				?>
			</div>
		</div>
	</div>
</div>

<?php
