<?php
/**
 * Class WPCF7R_Actions
 * A helper class for managing form actions
 *
 * @package WPCF7Redirect
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WPCF7R_Actions
 * A helper class for managing form actions
 *
 * @package WPCF7Redirect
 * @since 1.0.0
 * @version 1.0.0
 */
class WPCF7R_Actions {

	/**
	 * The post type of the action
	 *
	 * @var [string]
	 */
	public $post_type;

	/**
	 * The post id of the form
	 *
	 * @var [int]
	 */
	public $wpcf7_post_id;

	/**
	 * The actions that are relevant to this form
	 *
	 * @var [array]
	 */
	public $actions;

	/**
	 * The html helper class
	 *
	 * @var [type]
	 */
	public $html;

	/**
	 * Class constructor
	 *
	 * @param [int]    $post_id - the post id of the form.
	 * @param [object] $wpcf7r_form - the form object.
	 */
	public function __construct( $post_id, $wpcf7r_form ) {
		$this->post_type     = 'wpcf7r_action';
		$this->wpcf7_post_id = $post_id;
		$this->html          = new WPCF7R_Html( WPCF7R_Form::$mail_tags );
	}

	/**
	 * Get all actions that are relevant to this form
	 *
	 * @param [int]     $rule_id - the rule id.
	 * @param [integer] $count - the number of actions to return.
	 * @param [boolean] $active - whether to return only active actions.
	 * @param [array]   $args - extra arguments to pass to the query.
	 */
	public function get_actions( $rule_id, $count = -1, $active = false, $args = array() ) {
		$this->actions = array();
		$actions       = array();

		$actions_posts = $this->get_action_posts( $rule_id, $count, $active, $args );

		if ( $actions_posts && is_array( $actions_posts ) ) {
			$counter = 0;
			foreach ( $actions_posts as $action_post ) {
				$action = WPCF7R_Action::get_action( $action_post );

				if ( is_object( $action ) && ! is_wp_error( $action ) ) {
					$actions[ $action->priority . '_' . $counter ] = $action;
					++$counter;
				}
			}
		}

		ksort( $actions );
		$this->actions = $actions;

		return $this->actions;
	}

	/**
	 * Get and return the posts that are used as actions
	 *
	 * @param int     $rule_id - the rule id.
	 * @param integer $count - the number of actions to return.
	 * @param boolean $active - whether to return only active actions.
	 * @param array   $extra_args - extra arguments to pass to the query.
	 */
	public function get_action_posts( $rule_id, $count = -1, $active = false, $extra_args = array() ) {

		$post_type = $this->post_type;
		$post_id   = $this->wpcf7_post_id;

		$actions = wpcf7r_get_actions( $post_type, $count, $post_id, $rule_id, $extra_args, $active );

		return $actions;
	}

	/**
	 * Echo the templates used for the javascript process
	 */
	public function html_fregments() {
		if ( ! isset( $this->wpcf7_post_id ) ) {
			return;
		}

		$action = new WPCF7R_Action();

		$new_block = array(
			'block_title' => __( 'New Block', 'wpcf7-redirect' ),
			'groups'      => $action->get_groups(),
			'block_key'   => 'new_block',

		);

		$default_group               = $action->get_group_fields();
		$prefix                      = '[actions][action_id]';
		$fields                      = $this->get_plugin_default_fields_values();
		$row_template                = $this->html->get_conditional_row_template( $new_block['block_key'], 'new_group', 'new_row', reset( $default_group ), $prefix );
		$options['row_html']         = $row_template;
		$options['group_html']       = $this->html->group_display( 'new_block', 'new_group', reset( $new_block['groups'] ), $prefix );
		$options['block_html']       = $this->html->get_block_html( 'new_block', $new_block, false, false, $prefix );
		$options['block_title_html'] = $this->html->get_block_title( 'new_block', $new_block, false, false, $prefix );
		$options['mail_tags']        = WPCF7R_Form::$mail_tags;

		echo '<script>';
			echo 'var wpcfr_template = ' . wp_json_encode( $options );
		echo '</script>';
	}

	/**
	 * Get form values
	 */
	public function get_plugin_default_fields_values() {

		$fields = WPCF7r_Form_Helper::get_plugin_default_fields();

		foreach ( $fields as $field ) {
			$values[ $field['name'] ] = '';
		}
		return $values;
	}
}
