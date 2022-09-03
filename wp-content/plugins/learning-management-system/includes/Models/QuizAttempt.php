<?php
/**
 * Quiz attempt model.
 *
 * @since 1.3.2
 *
 * @package Masteriyo\Models;
 */

namespace Masteriyo\Models;

use Masteriyo\Database\Model;
use Masteriyo\Repository\QuizAttemptRepository;
use Masteriyo\Helper\Utils;
use Masteriyo\Cache\CacheInterface;

defined( 'ABSPATH' ) || exit;

/**
 * QuizAttempt Model.
 *
 * @since 1.3.2
 */
class QuizAttempt extends Model {

	/**
	 * This is the name of this object type.
	 *
	 * @since 1.3.2
	 *
	 * @var string
	 */
	protected $object_type = 'quiz_attempt';

	/**
	 * Cache group.
	 *
	 * @since 1.3.2
	 *
	 * @var string
	 */
	protected $cache_group = 'mto_quiz_attempts';


	/**
	 * Stores quiz attempts data.
	 *
	 * @since 1.3.2
	 *
	 * @var array
	 */
	protected $data = array(
		'course_id'                => 0,
		'quiz_id'                  => 0,
		'user_id'                  => 0,
		'total_questions'          => 0,
		'total_answered_questions' => 0,
		'total_marks'              => '',
		'total_attempts'           => 0,
		'total_correct_answers'    => 0,
		'total_incorrect_answers'  => 0,
		'earned_marks'             => '',
		'answers'                  => array(),
		'attempt_status'           => '',
		'attempt_started_at'       => null,
		'attempt_ended_at'         => null,
	);

	/**
	 * Get the course review if ID.
	 *
	 * @since 1.3.2
	 *
	 * @param QuizAttemptRepository $course_review_repository Course Review Repository.
	 */
	public function __construct( QuizAttemptRepository $course_review_repository ) {
		$this->repository = $course_review_repository;
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get course_id.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_course_id( $context = 'view' ) {
		return $this->get_prop( 'course_id', $context );
	}

	/**
	 * Get quiz_id.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_quiz_id( $context = 'view' ) {
		return $this->get_prop( 'quiz_id', $context );
	}

	/**
	 * Get user_id.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_user_id( $context = 'view' ) {
		return $this->get_prop( 'user_id', $context );
	}

	/**
	 * Get total_questions.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_total_questions( $context = 'view' ) {
		return $this->get_prop( 'total_questions', $context );
	}

	/**
	 * Get total_answered_questions.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_total_answered_questions( $context = 'view' ) {
		return $this->get_prop( 'total_answered_questions', $context );
	}

	/**
	 * Get total_marks.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_total_marks( $context = 'view' ) {
		return $this->get_prop( 'total_marks', $context );
	}

	/**
	 * Get total_attempts.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_total_attempts( $context = 'view' ) {
		return $this->get_prop( 'total_attempts', $context );
	}

	/**
	 * Get total_correct_answers.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_total_correct_answers( $context = 'view' ) {
		return $this->get_prop( 'total_correct_answers', $context );
	}

	/**
	 * Get total_incorrect_answers.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_total_incorrect_answers( $context = 'view' ) {
		return $this->get_prop( 'total_incorrect_answers', $context );
	}

	/**
	 * Get earned_marks.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_earned_marks( $context = 'view' ) {
		return $this->get_prop( 'earned_marks', $context );
	}

	/**
	 * Get answers.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_answers( $context = 'view' ) {
		return $this->get_prop( 'answers', $context );
	}

	/**
	 * Get attempt_status.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_attempt_status( $context = 'view' ) {
		return $this->get_prop( 'attempt_status', $context );
	}

	/**
	 * Get attempt_started_at.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_attempt_started_at( $context = 'view' ) {
		return $this->get_prop( 'attempt_started_at', $context );
	}

	/**
	 * Get attempt_ended_at.
	 *
	 * @since  1.0.6
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_attempt_ended_at( $context = 'view' ) {
		return $this->get_prop( 'attempt_ended_at', $context );
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Set course_id.
	 *
	 * @since 1.3.2
	 *
	 * @param int $course_id course_id.
	 */
	public function set_course_id( $course_id ) {
		$this->set_prop( 'course_id', absint( $course_id ) );
	}

	/**
	 * Set quiz_id.
	 *
	 * @since 1.3.2
	 *
	 * @param int $quiz_id Quiz ID.
	 */
	public function set_quiz_id( $quiz_id ) {
		$this->set_prop( 'quiz_id', absint( $quiz_id ) );
	}

	/**
	 * Set user_id.
	 *
	 * @since 1.3.2
	 *
	 * @param int $user_id User ID.
	 */
	public function set_user_id( $user_id ) {
		$this->set_prop( 'user_id', $user_id );
	}

	/**
	 * Set total_questions.
	 *
	 * @since 1.3.2
	 *
	 * @param int $total_questions Total number of questions.
	 */
	public function set_total_questions( $total_questions ) {
		$this->set_prop( 'total_questions', absint( $total_questions ) );
	}

	/**
	 * Set total_answered_questions.
	 *
	 * @since 1.3.2
	 *
	 * @param int $total_answered_questions Total answered questions.
	 */
	public function set_total_answered_questions( $total_answered_questions ) {
		$this->set_prop( 'total_answered_questions', absint( $total_answered_questions ) );
	}

	/**
	 * Set total_marks.
	 *
	 * @since 1.3.2
	 *
	 * @param string $total_marks Total marks of quiz.
	 */
	public function set_total_marks( $total_marks ) {
		$this->set_prop( 'total_marks', $total_marks );
	}

	/**
	 * Set total_attempts.
	 *
	 * @since 1.3.2
	 *
	 * @param int $total_attempts Total quiz attempts.
	 */
	public function set_total_attempts( $total_attempts ) {
		$this->set_prop( 'total_attempts', absint( $total_attempts ) );
	}

	/**
	 * Set total_correct_answers.
	 *
	 * @since 1.3.2
	 *
	 * @param int $total_correct_answers Total number of correct answers.
	 */
	public function set_total_correct_answers( $total_correct_answers ) {
		$this->set_prop( 'total_correct_answers', absint( $total_correct_answers ) );
	}

	/**
	 * Set total_incorrect_answers.
	 *
	 * @since 1.3.2
	 *
	 * @param int $total_incorrect_answers Total incorrect answers.
	 */
	public function set_total_incorrect_answers( $total_incorrect_answers ) {
		$this->set_prop( 'total_incorrect_answers', absint( $total_incorrect_answers ) );
	}

	/**
	 * Set earned_marks.
	 *
	 * @since 1.3.2
	 *
	 * @param string $earned_marks Total earned quiz marks.
	 */
	public function set_earned_marks( $earned_marks ) {
		$this->set_prop( 'earned_marks', $earned_marks );
	}

	/**
	 * Set answers.
	 *
	 * @since 1.3.2
	 *
	 * @param array $answers Answers of students.
	 */
	public function set_answers( $answers ) {
		$this->set_prop( 'answers', $answers );
	}

	/**
	 * Set attempt_status.
	 *
	 * @since 1.3.2
	 *
	 * @param string $attempt_status Quiz attempt status.
	 */
	public function set_attempt_status( $attempt_status ) {
		$this->set_prop( 'attempt_status', $attempt_status );
	}

	/**
	 * Set attempt_started_at.
	 *
	 * @since 1.3.2
	 *
	 * @param int $attempt_started_at Start time of quiz attempt.
	 */
	public function set_attempt_started_at( $attempt_started_at ) {
		$this->set_date_prop( 'attempt_started_at', $attempt_started_at );
	}

	/**
	 * Set attempt_ended_at.
	 *
	 * @since 1.3.2
	 *
	 * @param int $attempt_ended_at End time of quiz attempt.
	 */
	public function set_attempt_ended_at( $attempt_ended_at ) {
		$this->set_date_prop( 'attempt_ended_at', $attempt_ended_at );
	}
}
