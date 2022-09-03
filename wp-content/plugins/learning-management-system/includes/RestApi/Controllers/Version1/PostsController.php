<?php
/**
 * Abstract Rest Posts Controller Class
 *
 * @class PostsController
 * @package Masteriyo/RestApi
 */

namespace Masteriyo\RestApi\Controllers\Version1;

use Masteriyo\Enums\CourseAccessMode;
use Masteriyo\Enums\PostStatus;

defined( 'ABSPATH' ) || exit;

/**
 * PostsController
 *
 * @package Masteriyo/RestApi
 * @version  1.0.0
 */
abstract class PostsController extends CrudController {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = '';

	/**
	 * Checks if a given request has access to get a specific item.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return boolean|WP_Error True if the request has read access for the item, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		if ( is_null( $this->permission ) ) {
			return new \WP_Error(
				'masteriyo_null_permission',
				__( 'Sorry, the permission object for this resource is null.', 'masteriyo' )
			);
		}

		$post = get_post( (int) $request['id'] );

		// Allow to get the items for open courses.
		if ( $post && in_array( $post->post_type, array( 'mto-lesson', 'mto-quiz', 'mto-section', 'mto-question' ), true ) ) {
			$course_id = get_post_meta( $post->ID, '_course_id', true );
			$course    = masteriyo_get_course( $course_id );

			if ( $course && CourseAccessMode::OPEN === $course->get_access_mode() ) {
				return true;
			}
		}

		if ( $post && ! $this->permission->rest_check_post_permissions( $this->post_type, 'read', $request['id'] ) ) {
			return new \WP_Error(
				'masteriyo_rest_cannot_read',
				__( 'Sorry, you are not allowed to read resources.', 'masteriyo' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		return true;
	}

	/**
	 * Check if a given request has access to read items.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( is_null( $this->permission ) ) {
			return new \WP_Error(
				'masteriyo_null_permission',
				__( 'Sorry, the permission object for this resource is null.', 'masteriyo' )
			);
		}

		if ( ! $this->permission->rest_check_post_permissions( $this->post_type, 'read' ) ) {
			return new \WP_Error(
				'masteriyo_rest_cannot_read',
				__( 'Sorry, you cannot list resources.', 'masteriyo' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		return true;
	}

	/**
	 * Check if a given request has access to create an item.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {
		if ( is_null( $this->permission ) ) {
			return new \WP_Error(
				'masteriyo_null_permission',
				__( 'Sorry, the permission object for this resource is null.', 'masteriyo' )
			);
		}

		$instructor = masteriyo_get_current_instructor();
		if ( $instructor && ! $instructor->is_active() ) {
			return new \WP_Error(
				'masteriyo_rest_user_not_approved',
				__( 'Sorry, you are not approved by the manager.', 'masteriyo' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		if ( ! $this->permission->rest_check_post_permissions( $this->post_type, 'create' ) ) {
			return new \WP_Error(
				'masteriyo_rest_cannot_create',
				__( 'Sorry, you are not allowed to create resources.', 'masteriyo' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		return true;
	}

	/**
	 * Check if a given request has access to delete an item.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function delete_item_permissions_check( $request ) {
		if ( is_null( $this->permission ) ) {
			return new \WP_Error(
				'masteriyo_null_permission',
				__( 'Sorry, the permission object for this resource is null.', 'masteriyo' )
			);
		}

		$instructor = masteriyo_get_current_instructor();
		if ( $instructor && ! $instructor->is_active() ) {
			return new \WP_Error(
				'masteriyo_rest_user_not_approved',
				__( 'Sorry, you are not approved by the manager.', 'masteriyo' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		$post = get_post( (int) $request['id'] );

		if ( $post && ! $this->permission->rest_check_post_permissions( $this->post_type, 'delete', $post->ID ) ) {
			return new \WP_Error(
				'masteriyo_rest_cannot_delete',
				__( 'Sorry, you are not allowed to delete resources.', 'masteriyo' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		return true;
	}

	/**
	 * Check if a given request has access to update an item.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {
		if ( is_null( $this->permission ) ) {
			return new \WP_Error(
				'masteriyo_null_permission',
				__( 'Sorry, the permission object for this resource is null.', 'masteriyo' )
			);
		}

		$instructor = masteriyo_get_current_instructor();
		if ( $instructor && ! $instructor->is_active() ) {
			return new \WP_Error(
				'masteriyo_rest_user_not_approved',
				__( 'Sorry, you are not approved by the manager.', 'masteriyo' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		$post = get_post( (int) $request['id'] );

		if ( $post && ! $this->permission->rest_check_post_permissions( $this->post_type, 'update', $post->ID ) ) {
			return new \WP_Error(
				'masteriyo_rest_cannot_update',
				__( 'Sorry, you are not allowed to update resources.', 'masteriyo' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		return true;
	}


		/**
	 * Get previous and next links for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param Model           $object  Object data.
	 * @return array                   Links for the given post.
	 */
	protected function get_navigation_objects( $object ) {
		$previous = '';
		$next     = '';

		$query = new \WP_Query(
			array(
				'post_type'      => array( 'mto-lesson', 'mto-quiz' ),
				'post_status'    => PostStatus::PUBLISH,
				'posts_per_page' => -1,
				'meta_key'       => '_course_id',
				'meta_compare'   => '=',
				'meta_value'     => $object->get_course_id(),
				'orderby'        => array(
					'parent'     => 'ASC',
					'menu_order' => 'ASC',
				),
			)
		);

		try {
			foreach ( $query->posts as $index => $post ) {
				if ( $post->ID === $object->get_id() ) {
					$previous = ( $index - 1 ) > -1 ? $query->posts[ $index - 1 ] : '';
					$next     = ( $index + 1 ) < $query->found_posts ? $query->posts[ $index + 1 ] : '';
				}
			}
		} catch ( \Exception $error ) {
			// TODO Error log
			$error = $error->getErrorMessage();
		}

		return array(
			'previous' => $previous,
			'next'     => $next,
		);
	}

	/**
	 * Get previous and next links for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param Model           $object  Object data.
	 * @return array                   Links for the given post.
	 */
	protected function get_navigation_links( $object ) {
		$navigation = $this->get_navigation_objects( $object );

		$links['previous'] = array(
			'href' => $this->get_navigation_link( $navigation['previous'] ),
		);

		$links['next'] = array(
			'href' => $this->get_navigation_link( $navigation['next'] ),
		);

		return $links;
	}

	/**
	 * Get navigation link.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $object Post object.
	 * @return string
	 */
	protected function get_navigation_link( $object ) {
		if ( empty( $object ) ) {
			return '';
		}

		$object_type = str_replace( 'masteriyo-', '', $object->post_type );
		$object_rest = masteriyo( "{$object_type}.rest" );
		$link        = rest_url( sprintf( '/%s/%s/%d', $object_rest->namespace, $object_rest->rest_base, $object->ID ) );

		return $link;
	}

	/**
	 * Get navigation items.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $model WP_Post object.
	 * @param string $context Request context.
	 *                        Options: 'view' and 'edit'.
	 *
	 * @return array
	 */
	protected function get_navigation_items( $object, $context = 'view' ) {
		$navigation = $this->get_navigation_objects( $object );

		return array(
			'previous' => $this->get_navigation_item( $navigation['previous'] ),
			'next'     => $this->get_navigation_item( $navigation['next'] ),
		);
	}

	/**
	 * Get navigation item.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $object WP_Post object.
	 * @param string $context Request context.
	 *                        Options: 'view' and 'edit'.
	 *
	 * @return array
	 */
	protected function get_navigation_item( $object, $context = 'view' ) {
		if ( empty( $object ) ) {
			return '';
		}

		$previous_parent = get_post( $object->post_parent );
		$video           = get_post_meta( $object->ID, '_video_source_url', true );

		$previous = array(
			'id'     => $object->ID,
			'name'   => wp_specialchars_decode( $object->post_title ),
			'type'   => str_replace( 'mto-', '', $object->post_type ),
			'video'  => ! empty( trim( $video ) ),
			'parent' => is_null( $previous_parent ) ? null : array(
				'id'   => $previous_parent->ID,
				'name' => $previous_parent->post_title,
			),
		);

		return $previous;
	}

	/**
	 * Prepare objects query.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {
		$args = parent::prepare_objects_query( $request );

		/**
		 * Filters list of object types to hide from instructor.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $object_types The list of object types to hide from instructor.
		 */
		$object_types = apply_filters(
			'masteriyo_rest_hide_object_types_from_instructor',
			array(
				'course',
				'lesson',
				'quiz',
				'section',
				'question',
			)
		);

		if ( masteriyo_is_current_user_instructor() && in_array( $this->object_type, $object_types, true ) ) {
			$args = array_merge( $args, array( 'author' => get_current_user_id() ) );
		}

		return $args;

	}

	/**
	 * Get posts count by status.
	 *
	 * @since 1.4.12
	 * @since 1.5.0 Filter post counts by post authors.
	 *
	 * @return array
	 */
	protected function get_posts_count() {
		if ( masteriyo_is_current_user_admin() || masteriyo_is_current_user_manager() ) {
			$post_count = (array) wp_count_posts( $this->post_type );
		} else {
			$post_count = (array) masteriyo_count_posts( $this->post_type, get_current_user_id() );
		}

		$post_count        = array_map( 'absint', $post_count );
		$post_count['any'] = array_sum( masteriyo_array_except( $post_count, PostStatus::TRASH ) );

		/**
		 * Filters the post counts.
		 *
		 * @since 1.5.12
		 *
		 * @param array $post_count Posts count.
		 * @param \Masteriyo\RestApi\Controllers\Version1\PostsController $controller Posts Controller.
		 */
		return apply_filters( "masteriyo_rest_{$this->object_type}_count", $post_count, $this );
	}
}
