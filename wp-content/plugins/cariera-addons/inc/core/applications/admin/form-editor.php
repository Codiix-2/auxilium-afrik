<?php
namespace Cariera_Addons\Core\Applications\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Form_Editor {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'edit_form_after_title', [ $this, 'add_form_editor' ] );
		add_filter( 'get_user_option_screen_layout_job_application_form', [ $this, 'set_screen_layout' ] );
		add_filter( 'save_post_job_application_form', [ $this, 'save_actions' ] );
		add_filter( 'admin_action_job_application_form_reset', [ $this, 'reset_actions' ] );
		add_filter( 'post_updated_messages', [ $this, 'post_updated_messages' ] );
	}

	/**
	 * Render the form fields and e-mail editors.
	 *
	 * @since 0.9.3
	 */
	public function add_form_editor() {
		if ( \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION_FORM === get_post_type() ) {
			$post_id = get_the_ID();

			$form_data = \Cariera_Addons\Core\Applications\Application_Form::get_form_data( $post_id );

			$this->output( $form_data );
		}
	}

	/**
	 * Set screen to one column layout.
	 *
	 * @since 0.9.3
	 *
	 * @return int Number of columns to use.
	 */
	public function set_screen_layout() {
		return 1;
	}

	/**
	 * Handle save actions.
	 *
	 * @since 0.9.3
	 *
	 * @param int $post_id
	 */
	public function save_actions( $post_id ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce check.
		if ( empty( $post_id ) || empty( $_POST['_wpnonce_job_application_form'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce_job_application_form'] ), 'save-application-form' ) ) {
			return;
		}

		remove_filter( 'save_post_job_application_form', [ $this, 'save_actions' ] );

		$form_data = [
			'form_fields'              => $this->form_editor_save(),
			'candidate_email_template' => $this->email_save( 'candidate' ),
			'employer_email_template'  => $this->email_save( 'employer' ),
		];

		$form_data = apply_filters( 'job_manager_job_application_save_form_data', $form_data, $post_id );

		$form = new \Cariera_Addons\Core\Applications\Application_Form( $post_id );

		$form->set( $form_data );
		$form->save();
	}

	/**
	 * Handle reset actions.
	 *
	 * @since 0.9.3
	 *
	 * @return void
	 */
	public function reset_actions() {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce check.
		if ( empty( $_GET['post'] ) || empty( $_GET['reset-action'] ) || empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'job_application_form_reset' ) ) {
			return;
		}

		$post_id = absint( $_GET['post'] );
		$post    = get_post( $post_id );

		if ( empty( $post ) || \Cariera_Addons\Core\Applications\Post_Types::CPT_APPLICATION_FORM !== $post->post_type || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$message = '';

		$form = new \Cariera_Addons\Core\Applications\Application_Form( $post_id );

		$defaults = \Cariera_Addons\Core\Applications\Application_Form::get_default_data();

		switch ( sanitize_text_field( wp_unslash( $_GET['reset-action'] ) ) ) {
			case 'fields':
				$form->set( [ 'form_fields' => $defaults['form_fields'] ] );
				$message = 21;
				break;
			case 'employer-email':
				$form->set(
					[
						'employer_email_template' => $defaults['employer_email_template'],
					]
				);
				$message = 22;
				break;
			case 'candidate-email':
				$form->set(
					[
						'candidate_email_template' => $defaults['candidate_email_template'],
					]
				);
				$message = 22;
				break;
		}

		$form->save();

		wp_safe_redirect(
			add_query_arg(
				[
					'action'       => 'edit',
					'reset-action' => false,
					'_wpnonce'     => false,
					'message'      => $message,
				]
			)
		);
		exit;
	}

	/**
	 * Add feedback messages to be used after reset actions.
	 *
	 * @since 0.9.3
	 *
	 * @param array $messages Default messages.
	 */
	public function post_updated_messages( $messages ) {
		$messages['job_application_form'][21] = esc_html__( 'The fields were successfully reset.', 'cariera-addons' );
		$messages['job_application_form'][22] = esc_html__( 'The email was successfully reset.', 'cariera-addons' );
		return $messages;
	}

	/**
	 * Output form and email editors.
	 *
	 * @since   0.9.3
	 * @version 0.9.9
	 *
	 * @param array $form_data
	 */
	public function output( $form_data ) {

		/**
		 * Filters tabs that can extend the default tabs in the job application form page.
		 *
		 * @param array $tabs List of tabs to be displayed in the admin edit page. The format is: ID => label where ID is the same id of the element when outputing the markup.
		 */
		$tabs = apply_filters(
			'job_application_form_editor_tabs',
			[
				'tab-fields'                 => esc_html__( 'Form Fields', 'cariera-addons' ),
				'tab-employer-notification'  => esc_html__( 'Employer Notification', 'cariera-addons' ),
				'tab-candidate-notification' => esc_html__( 'Candidate Notification', 'cariera-addons' ),
			]
		);

		?>
		<div class="cariera-addons-applications-form-editor">
			<div class="wp-filter">
				<ul class="filter-links cariera-addons-applications-form-editor-tabs">
					<?php
					$tab_first = true;
					foreach ( $tabs as $key => $value ) {
						$active    = $tab_first ? 'current' : '';
						$tab_first = false;
						echo '<li><a class="tab-link ' . esc_attr( $active ) . '" href="#' . esc_attr( $key ) . '">' . esc_html( $value ) . '</a></li>';
					}
					?>
				</ul>
			</div>

		<div class="tab-content" id="tab-fields">
			<?php $this->form_editor( $form_data['form_fields'] ); ?>
		</div>

		<div class="tab-content" id="tab-employer-notification">
			<p><?php esc_html_e( 'Below you will find the email that is sent to an employer after a candidate submits an application.', 'cariera-addons' ); ?></p>
			<?php $this->email_editor( 'employer', $form_data ); ?>
		</div>

		<div class="tab-content" id="tab-candidate-notification">
			<p><?php esc_html_e( 'Below you will find the email that is sent to a candidate after submitting an application. Leave blank to disable.', 'cariera-addons' ); ?></p>
			<?php $this->email_editor( 'candidate', $form_data ); ?>
		</div>

		<?php
		/**
		 * Perform action when we are outputting tab content.
		 * Note that tab content must be wrapped in `<div class="tab-content" id="tab-ID"></div>`.
		 *
		 * @since 0.9.3
		 */
		do_action( 'job_application_form_editor_tab_content' );
		wp_nonce_field( 'save-application-form', '_wpnonce_job_application_form' );
		?>
		</div>
		<?php
	}

	/**
	 * Show a reset link for the tab, unless it's the 'Add Form' page.
	 *
	 * @since 0.9.3
	 *
	 * @param string $action Reset action - which tab to reset.
	 */
	private function reset_link( $action ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Check only.
		if ( ! isset( $_GET['post'] ) ) {
			return '';
		}

		$reset_link = wp_nonce_url(
			add_query_arg(
				[
					'action'       => 'job_application_form_reset',
					'reset-action' => $action,
				]
			),
			'job_application_form_reset'
		);

		return '<a href="' . esc_url( $reset_link ) . '" class="reset">' . esc_html__( 'Reset to defaults', 'cariera-addons' ) . '</a>';
	}

	/**
	 * Output the form editor
	 *
	 * @since 0.9.3
	 *
	 * @param array $fields
	 */
	private function form_editor( $fields ) {

		/**
		 * Filters rules that can only be used once per form.
		 *
		 * @since 0.9.3
		 *
		 * @param array $unique_rules Rule key values that should be unique.
		 */
		$unique_rules = apply_filters(
			'job_application_form_unique_field_rules',
			[
				'from_name',
				'from_email',
				'message',
			]
		);

		/**
		 * Returns the rules that can be used on application form fields.
		 *
		 * @since 0.9.3
		 *
		 * @param array $rules
		 */
		$field_rules = apply_filters(
			'job_application_form_field_rules',
			[
				esc_html__( 'Validation', 'cariera-addons' )    => [
					'required' => esc_html__( 'Required', 'cariera-addons' ),
					'email'    => esc_html__( 'Email', 'cariera-addons' ),
					'numeric'  => esc_html__( 'Numeric', 'cariera-addons' ),
				],
				esc_html__( 'Data Handling', 'cariera-addons' ) => [
					'from_name'  => esc_html__( 'From Name', 'cariera-addons' ),
					'from_email' => esc_html__( 'From Email', 'cariera-addons' ),
					'message'    => esc_html__( 'Message', 'cariera-addons' ),
					'attachment' => esc_html__( 'Attachment', 'cariera-addons' ),
				],
			]
		);

		/**
		 * Returns the field types that can be used on application forms.
		 *
		 * @sicne 0.9.3
		 *
		 * @param array $field_types
		 */
		$field_types = apply_filters(
			'job_application_form_field_types',
			[
				'text'           => esc_html__( 'Text', 'cariera-addons' ),
				'textarea'       => esc_html__( 'Textarea', 'cariera-addons' ),
				'file'           => esc_html__( 'File', 'cariera-addons' ),
				'select'         => esc_html__( 'Select', 'cariera-addons' ),
				'multiselect'    => esc_html__( 'Multiselect', 'cariera-addons' ),
				'checkbox'       => esc_html__( 'Checkbox', 'cariera-addons' ),
				'date'           => esc_html__( 'Date', 'cariera-addons' ),
				'resumes'        => esc_html__( 'Resume', 'cariera-addons' ),
				'output-content' => esc_html__( 'Output content', 'cariera-addons' ),
			]
		);

		if ( ! function_exists( 'get_resume_share_link' ) ) {
			unset( $field_types['resumes'] );
		}
		?>

		<table class="widefat">
			<thead>
			<tr>
				<th width="1%">&nbsp;</th>
				<th><?php esc_html_e( 'Field Label', 'cariera-addons' ); ?></th>
				<th width="1%"><?php esc_html_e( 'Type', 'cariera-addons' ); ?></th>
				<th><?php esc_html_e( 'Description', 'cariera-addons' ); ?></th>
				<th><?php esc_html_e( 'Placeholder / Options', 'cariera-addons' ); ?></th>
				<?php
				/**
				 * Output header for additional columns in the form field editor.
				 *
				 * @see job_application_form_field_editor_column_row to set the matching rows.
				 *
				 * @since 0.9.3
				 *
				 * @param array $fields
				 */
				do_action( 'job_application_form_field_editor_column_header', $fields );
				?>
				<th width="1%"><?php esc_html_e( 'Validation / Rules', 'cariera-addons' ); ?></th>
				<th width="1%" class="field-actions">&nbsp;</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th colspan="7">
					<div class="form-actions">
						<a class="button add-field" href="#"><?php esc_html_e( 'Add field', 'cariera-addons' ); ?></a>
					<?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in method.
					echo $this->reset_link( 'fields' );
					?>
					</div>
				</th>
			</tr>
			</tfoot>
			<tbody id="form-fields" data-field="
			<?php
			ob_start();
			$index     = - 1;
			$field_key = '';
			$field     = [
				'type'        => 'text',
				'label'       => '',
				'placeholder' => '',
			];

			include 'views/html-form-field-editor-row.php';
			echo esc_attr( ob_get_clean() );
			?>
			">
			<?php
			foreach ( $fields as $field_key => $field ) {
				++$index;
				include 'views/html-form-field-editor-row.php';
			}
			?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Save the form fields
	 *
	 * @since 0.9.3
	 */
	private function form_editor_save() {
		//phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce already verified in save_actions().
		$field_types          = ! empty( $_POST['field_type'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['field_type'] ) ) : [];
		$field_labels         = ! empty( $_POST['field_label'] ) ? array_map( 'wp_kses_post', wp_unslash( $_POST['field_label'] ) ) : [];
		$field_descriptions   = ! empty( $_POST['field_description'] ) ? array_map( 'wp_kses_post', wp_unslash( $_POST['field_description'] ) ) : [];
		$field_placeholder    = ! empty( $_POST['field_placeholder'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['field_placeholder'] ) ) : [];
		$field_options        = ! empty( $_POST['field_options'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['field_options'] ) ) : [];
		$field_multiple_files = ! empty( $_POST['field_multiple_files'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['field_multiple_files'] ) ) : [];
		$field_rules          = ! empty( $_POST['field_rules'] ) ? $this->sanitize_array( wp_unslash( $_POST['field_rules'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$new_fields           = [];
		$index                = 0;

		foreach ( $field_labels as $key => $field ) {
			if ( empty( $field ) ) {
				continue;
			}
			$field_name = sanitize_title( $field );
			$options    = ! empty( $field_options[ $key ] ) ? array_map( 'sanitize_text_field', explode( '|', $field_options[ $key ] ) ) : [];

			$new_field                = [];
			$new_field['label']       = $field;
			$new_field['type']        = $field_types[ $key ];
			$new_field['required']    = ! empty( $field_rules[ $key ] ) && in_array( 'required', $field_rules[ $key ], true );
			$new_field['options']     = $options ? array_combine( $options, $options ) : [];
			$new_field['placeholder'] = $field_placeholder[ $key ];
			$new_field['description'] = $field_descriptions[ $key ];
			$new_field['priority']    = $index++;
			$new_field['multiple']    = isset( $field_multiple_files[ $key ] );
			$new_field['rules']       = ! empty( $field_rules[ $key ] ) ? $field_rules[ $key ] : [];

			/**
			 * Filter form editor fields before saving.
			 *
			 * @since 0.9.3
			 *
			 * @param array $new_field Field data
			 * @param int   $key       Field array index
			 */
			$new_fields = apply_filters( 'job_application_form_field_editor_save', $new_fields, $key );

			if ( isset( $new_fields[ $field_name ] ) ) {
				// Generate a unique field name by appending a number to the existing field name.
				// Assumes no more than 100 fields with the same name would be needed? Otherwise it will override the field.
				$counter = 1;
				while ( $counter <= 100 ) {
					$candidate = $field_name . '-' . $counter;
					if ( ! isset( $new_fields[ $candidate ] ) ) {
						$field_name = $candidate;
						break;
					}
					++$counter;
				}
			}
			$new_fields[ $field_name ] = $new_field;
		}

		return $new_fields;
	}

	/**
	 * Sanitize a string or array of text fields.
	 *
	 * @since 0.9.3
	 *
	 * @param array|string $input Input array.
	 */
	private function sanitize_array( $input ) {
		if ( is_array( $input ) ) {
			foreach ( $input as $k => $v ) {
				$input[ $k ] = $this->sanitize_array( $v );
			}

			return $input;
		} else {
			return sanitize_text_field( $input );
		}
	}

	/**
	 * Employer notification email editor.
	 *
	 * @since 0.9.3
	 *
	 * @param array $form
	 */
	private function employer_notification_editor( $form ) {
	}

	/**
	 * Email template editor.
	 *
	 * @since   0.9.3
	 * @version 1.0.4
	 *
	 * @param string $email_type
	 * @param array  $form
	 */
	private function email_editor( $email_type, $form ) {
		$email   = $form[ $email_type . '_email_template' ];
		$form_id = $form['ID'] ?? null;

		$subject = $email['subject'];
		$content = $email['content'];

		$subject_input = $email_type . '-email-subject';
		$content_input = $email_type . '-email-content';
		?>

		<div class="cariera-addons-email-content-wrapper">
			<div class="cariera-addons-email-content">
				<p>
					<input type="text" name="<?php echo esc_attr( $subject_input ); ?>" value="<?php echo esc_attr( $subject ); ?>" placeholder="<?php esc_attr_e( 'Subject', 'cariera-addons' ); ?>" />
				</p>
				<p>
					<textarea name="<?php echo esc_attr( $content_input ); ?>" cols="71" rows="10"><?php echo esc_textarea( $content ); ?></textarea>
				</p>
			</div>
			<div class="cariera-addons-email-content-tags">
				<p><?php esc_html_e( 'The following tags can be used to add content dynamically:', 'cariera-addons' ); ?></p>
				<ul>
					<?php foreach ( get_job_application_email_tags( $form_id ) as $tag => $name ) { ?>
						<li><code>[<?php echo esc_html( $tag ); ?>]</code> - <?php echo wp_kses_post( $name ); ?></li>
					<?php } ?>
				</ul>
				<p>
					<?php
					printf(
						// translators: %s - HTML code.
						esc_html__( 'All tags can be passed a prefix and a suffix which is only output when the value is set e.g. %s', 'cariera-addons' ),
						'<code>[job_title prefix="Job Title: " suffix="."]</code>'
					);
					?>
				</p>
			</div>
		</div>
		<div class="form-actions">
			<?php echo $this->reset_link( $email_type . '-email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in method. ?>
		</div>
		<?php
	}

	/**
	 * Save employer notification email.
	 *
	 * @since 0.9.3
	 *
	 * @param string $email_type employer|candidate.
	 */
	private function email_save( $email_type ) {
		$subject_input = $email_type . '-email-subject';
		$content_input = $email_type . '-email-content';

		if ( ! isset( $_POST[ $subject_input ] ) || ! isset( $_POST[ $content_input ] ) ) {
			return null;
		}

		$email_content = wp_kses_post( wp_unslash( $_POST[ $content_input ] ) );
		$email_subject = sanitize_text_field( wp_unslash( $_POST[ $subject_input ] ) );

		return [
			'content' => $email_content,
			'subject' => $email_subject,
		];
	}
}
