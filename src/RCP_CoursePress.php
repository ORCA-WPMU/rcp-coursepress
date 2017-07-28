<?php
/**
 * Main RCP CoursePress class
 *
 * @package svbk-rcp-courspress
 * @author Brando Meniconi <b.meniconi@silverbackstudio.it>
 */

namespace Svbk\WP\Plugins\RCP\CoursePress;

use CoursePress_Data_Course;

/**
 * Main RCP CoursePress class
 */
class RCP_CoursePress {

	/**
	 * Prints the HTML fields in subscrioption's admin panel
	 *
	 * @param object $level Optional. The subscription level object.
	 *
	 * @return void
	 */
	public function admin_subscription_form( $level = null ) {
		global $rcp_levels_db;

		$defaults = array(
			'enroll_courses' => array(),
		);

		if ( ! empty( $level ) ) {
			$defaults['enroll_courses'] = $rcp_levels_db->get_meta( $level->id, 'enroll_courses', true );
		}
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="enroll_courses"><?php esc_html_e( 'Enroll courses', 'svbk-rcp-coursepress' ); ?></label>
			</th>
			<td>
				<?php
				$courses = get_posts(
					array(
						'post_type' => CoursePress_Data_Course::get_post_type_name(),
						'post_status' => 'publish',
					)
				);

				foreach ( $courses as $course ) : ?>
					<label for="enroll_courses_<?php echo esc_attr( $course->ID ); ?>"><?php echo esc_html( $course->post_title ); ?></label>
					<input type="checkbox" name="enroll_courses[]" id="enroll_courses_<?php echo esc_attr( $course->ID ); ?>" value="<?php echo esc_attr( $course->ID ); ?>" <?php if ( in_array( $course->ID, $defaults['enroll_courses'] ) ) { echo 'checked="checked"';} ?> />
					<?php endforeach;

				?>
				<p class="description"><?php esc_html_e( 'Which courses the user should enroll when buys this subscription', 'svbk-rcp-coursepress' ); ?></p>
			</td>
		</tr>
	<?php }

	/**
	 * Saves values from the subscription admin panel.
	 *
	 * @param int   $level_id The subscription level ID.
	 * @param array $args The submitted form filed values.
	 *
	 * @return void
	 */
	public function admin_subscription_form_save( $level_id, $args ) {

		global $rcp_levels_db;

		$defaults = array(
		'enroll_courses' => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		if ( current_filter() === 'rcp_add_subscription' ) {
			$rcp_levels_db->add_meta( $level_id, 'enroll_courses', (array) $args['enroll_courses'] );
		} elseif ( current_filter() === 'rcp_pre_edit_subscription_level' ) {
			$rcp_levels_db->update_meta( $level_id, 'enroll_courses', (array) $args['enroll_courses'] );
		}
	}

	/**
	 * Enrolls the specified user to the courses linked to thi subsctiption level.
	 *
	 * @param int        $subscription_id The subscription level ID.
	 * @param int        $user_id The user id.
	 * @param RCP_Member $member The RCP_Member object class.
	 *
	 * @return void
	 */
	public function enroll( $subscription_id, $user_id, $member ) {

		if ( ! class_exists( 'CoursePress_Data_Course' ) ) {
			return;
		}

		global $rcp_levels_db;

		$courses = $rcp_levels_db->get_meta( $subscription_id, 'enroll_courses', true );

		remove_filter( 'coursepress_enroll_student', array( $this, 'allow_enroll' ) , 10, 3 );

		foreach ( $courses as $course ) {
			CoursePress_Data_Course::enroll_student( $user_id, intval( $course ) );
		}

	}

	/**
	 * Disables enrollment if the user has no valid subscription
	 *
	 * @param bool $enroll_student Input value from filter.
	 * @param int  $student_id The student id.
	 * @param int  $course_id The course id.
	 *
	 * @return bool
	 */
	public function allow_enroll( $enroll_student, $student_id, $course_id ) {

		if ( function_exists( 'rcp_get_subscription' ) ) {
			return boolval( rcp_get_subscription_id( $student_id ) );
		}

		return $enroll_student;
	}

}
