<?php

/**
 * Class for adding a new tab to the application details screen.
 *
 * @package WPCD
 */

if (!defined('ABSPATH')) {
	exit;
}







class WPCD_WordPress_TABS_APP_SAMPLE extends WPCD_WORDPRESS_TABS
{

	public function handle_file_upload()
	{

		if (isset($_FILES['file'])) {
			$file = $_FILES['file'];

			// Define the upload directory
			$upload_dir = wp_upload_dir();

			// Generate a unique file name
			$file_name = sanitize_file_name($file['name']);

			// Move the uploaded file to the server
			$move_result = move_uploaded_file($file['tmp_name'], $upload_dir['path'] . '/' . $file_name);

			if ($move_result) {
				echo 'File uploaded successfully to ' . $upload_dir['url'] . '/' . $file_name;
			} else {
				echo 'Error uploading file.';
			}
			// Redirect back to the page after processing
			wp_redirect(wp_get_referer());
			exit;
		}
	}



	/**
	 * WPCD_WORDPRESS_TABS_PHP constructor.
	 */
	public function __construct()
	{

		parent::__construct();

		add_filter("wpcd_app_{$this->get_app_name()}_get_tabnames", array($this, 'get_tab'), 10, 1);
		add_filter("wpcd_app_{$this->get_app_name()}_get_tabs", array($this, 'get_tab_fields_sample'), 10, 2);
		add_filter("wpcd_app_{$this->get_app_name()}_tab_action", array($this, 'tab_action_sample'), 10, 3);

		add_action("wpcd_command_{$this->get_app_name()}_completed", array($this, 'command_completed_sample'), 10, 4);

		add_action('admin_post_handle_file_upload', array($this, 'handle_file_upload'), 10, 5);
		add_action('admin_post_nopriv_handle_file_upload', array($this, 'handle_file_upload'), 10, 6);

		// Filter to make sure we give the correct file path when merging contents.
		add_filter('wpcd_script_file_name', array($this, 'wpcd_script_file_name'), 10, 7);

		// Filter to handle script file tokens.
		add_filter('wpcd_wpapp_replace_script_tokens', array($this, 'wpcd_wpapp_replace_script_tokens'), 10, 8);
	}




	/**
	 * Called when a command completes.
	 *
	 * To see an example of how this would be used, see the
	 * \includes\core\apps\wordpress-app\tabs\clone-site.php
	 * file in the wpcd plugin.
	 *
	 * Action Hook: wpcd_command_{$this->get_app_name()}_completed
	 *
	 * @param int    $id     The postID of the server cpt.
	 * @param string $name   The name of the command.
	 */
	public function command_completed_sample($id, $name)
	{

		// remove the 'temporary' meta so that another attempt will run if necessary.
		delete_post_meta($id, "wpcd_app_{$this->get_app_name()}_action_status");
		delete_post_meta($id, "wpcd_app_{$this->get_app_name()}_action");
		delete_post_meta($id, "wpcd_app_{$this->get_app_name()}_action_args");
	}


	/**
	 * Populates the tab name.
	 *
	 * @param array $tabs The default value.
	 *
	 * @return array    $tabs The default value.
	 */
	public function get_tab($tabs)
	{
		$tabs['sample'] = array(
			'label' => __('Sample Add-on', 'wpcd'),
		);
		return $tabs;
	}


	/**
	 * Gets the fields to be shown in the Sample tab.
	 *
	 * Filter hook: wpcd_app_{$this->get_app_name()}_get_tabs
	 *
	 * @param array $fields list of existing fields.
	 * @param int   $id post id of app being worked with.
	 *
	 * @return array Array of actions, complying with the structure necessary by metabox.io fields.
	 */
	public function get_tab_fields_sample(array $fields, $id)
	{

		return $this->get_fields_for_tab($fields, $id, 'sample');
	}

	/**
	 * Called when an action needs to be performed on the tab.
	 *
	 * @param mixed  $result The default value of the result.
	 * @param string $action The action to be performed.
	 * @param int    $id The post ID of the app.
	 *
	 * @return mixed    $result The default value of the result.
	 */
	public function tab_action_sample($result, $action, $id)
	{

		switch ($action) {
			case 'ssl-cert':
				$result = $this->ssl_cert($id, $action);

				break;
			case 'send-ssl-key':
				$result = $this->create_text_file_on_publish($id, $action);
				break;

			case 'ssl-certificate-field':
				$result = $this->ssl_certificate_field($id, $action);
				break;
			case 'choose-file':
				$result = $this->choose_file($id, $action);
				break;
		}

		return $result;
	}

	/**
	 * Gets the actions to be shown in the Sample tab.
	 *
	 * @param int $id The post ID of the server.
	 * @return array Array of actions with key as the action slug and value complying with the structure necessary by metabox.io fields.
	 */
	public function get_actions($id)
	{

		return $this->get_server_fields_sample($id);
	}



	/**
	 * Gets the fields for the services to be shown in the Sample tab in the server details screen.
	 *
	 * @param int $id the post id of the app cpt record.
	 *
	 * @return array Array of actions with key as the action slug and value complying with the structure necessary by metabox.io fields.
	 */

	private function get_server_fields_sample($id)
	{


		// Set up metabox items.
		$actions = array();

		$actions['ssl-key-field'] = array(
			'label'          => __('Enter SSL-key', 'wpcd'),
			'type'           => 'text',
			'raw_attributes' => array(
				'desc'           => __('Enter your SSL key here.', 'wpcd'),
				// the key of the field (the key goes in the request).
				'data-wpcd-name' => 'ssl_key_field',
			),

		);


		$actions['send-ssl-key'] = array(
			'label'          => __('Create file with txt', 'wpcd'),
			'raw_attributes' => array(
				'std'                 => __('send SSL Key', 'wpcd'),
				'desc'                => __('send SSL Key to a local file on the server', 'wpcd'),
				// fields that contribute data for this action.
				'data-wpcd-fields'    => wp_json_encode(array('#wpcd_app_action_ssl-key-field')),
				// make sure we give the user a confirmation prompt.
				'confirmation_prompt' => __('Are you sure you want to create an ssl key with this data?', 'wpcd'),

			),
			'type'           => 'button',
		);
		$actions['ssl-cert-field'] = array(
			'label'          => __('Enter SSL-cert', 'wpcd'),
			'type'           => 'text',
			'raw_attributes' => array(
				'desc'           => __('Enter your SSL cert here.', 'wpcd'),
				// the key of the field (the key goes in the request).
				'data-wpcd-name' => 'ssl_cert_field',

			),
		);

		$actions['ssl-cert'] = array(
			'label'                 => __('Send SSL cert', 'your domain'),
			//'type'                  => 'text',
			'raw_attributes'        => array(
				'std'                 => __('Send SSL Cert', 'wpcd'),
				'desc'              => __('Send your ssl cert here', 'your domain'),
				'data-wpcd-name'    => 'ssl_cert',
				// fields that contribute data for this action.
				'data-wpcd-fields'    => wp_json_encode(array('#wpcd_app_action_ssl-cert-field')),
				// make sure we give the user a confirmation prompt.
				'confirmation_prompt' => __('Are you sure you want to create an ssl cert with this data?', 'wpcd'),

			),
			'type'         => 'button',

		);
		$actions['choose-file'] = array(
			'label'                 => __('choose File', 'your domain'),
			//'type'                  => 'text',
			'raw_attributes'        => array(
				'desc'              => __('choose your file', 'your domain'),
				'data-wpcd-name'    => 'choose_file',

			),
			'type'         => 'file',
			'name'       => 'file',
			'id'          => 'file',





		);
		$actions['handle-file-upload'] = array(
			'label'                 => __('choose File', 'your domain'),
			//'type'                  => 'text',
			'raw_attributes'        => array(
				'desc'              => __('choose your file', 'your domain'),
				'data-wpcd-name'    => 'handle_file_upload',

			),
			'type'         => 'submit',
			'value'       => 'Upload file',


		);


		return $actions;
	}






	/**
	 * Sample Action "A": updates all plugins on the site.
	 *
	 * @param int    $id         The postID of the server cpt.
	 * @param string $action     The action to be performed (this matches the string required in the bash scripts if bash scripts are used).
	 *
	 * @return boolean success/failure/other
	 */
	private function sample_action_a($id, $action)
	{

		// Get the instance details.
		$instance = $this->get_app_instance_details($id);

		if (is_wp_error($instance)) {
			/* translators: %s is replaced with the name of the action being executed */
			return new \WP_Error(sprintf(__('Unable to execute this request because we cannot get the instance details for action %s', 'wpcd'), $action));
		}

		// Get the domain we're working on.
		$domain = $this->get_domain_name($id);

		// Construct a simple command.
		// This command is three bash commands chanined by "&&".
		// First it changes the folder to the WordPress folder (which is the same name as the domain).
		// Then it lists the folder name (this is unnecessary but included here just to show how bash chaining works if you're not familiar with it.
		// Finally it runs the wp-cli plugin update command.
		// The full command will look like this: 'cd /var/www/my.domain.com/html && pwd && sudo -u my.domain.com wp plugin update --all'.
		$command = "cd /var/www/$domain/html && pwd && sudo -u $domain wp plugin update --all";

		// Send the command and wait for a reply.
		// This command needs to complete within the limits of the PHP Execution timeout.
		$result = $this->execute_ssh('generic', $instance, array('commands' => $command));

		// Check output string to make sure we don't have an error...
		if (!(strpos($result, 'Success: Updated')) && !(strpos($result, 'Success: Plugin already updated.'))) {
			return new \WP_Error(__('An error was encounered during the updates. Please check the SSH logs for more information.', 'wpcd'));
		}

		// If you got here, success!
		// @todo: you can do cool things by parsing the result string to check for number of plugins updated and reporting that back to the user.
		$success_msg = __('Command was a success - plugins updated!', 'wpcd');
		$result      = array(
			'msg'     => $success_msg,
			'refresh' => 'yes',
		);

		return $result;
	}

	/**
	 * Sample Action "B": updates all themes on the site.
	 *
	 * This one is the same process as "A" with a different command.
	 *
	 * @param int    $id         The postID of the server cpt.
	 * @param string $action     The action to be performed (this matches the string required in the bash scripts if bash scripts are used).
	 *
	 * @return boolean success/failure/other
	 */
	private function sample_action_b($id, $action)
	{

		// Get the instance details.
		$instance = $this->get_app_instance_details($id);

		if (is_wp_error($instance)) {
			/* translators: %s is replaced with the name of the action being executed */
			return new \WP_Error(sprintf(__('Unable to execute this request because we cannot get the instance details for action %s', 'wpcd'), $action));
		}

		// Get the domain we're working on.
		$domain = $this->get_domain_name($id);

		// Construct a simple command.
		// This command is three bash commands chanined by "&&".
		// First it changes the folder to the WordPress folder (which is the same name as the domain).
		// Then it lists the folder name (this is unnecessary but included here just to show how bash chaining works if you're not familiar with it.
		// Finally it runs the wp-cli plugin update command.
		// The full command will look like this: 'cd /var/www/my.domain.com/html && pwd && sudo -u my.domain.com wp plugin update --all'.
		$command = "cd /var/www/$domain/html && pwd && sudo -u $domain wp theme update --all";

		// Send the command and wait for a reply.
		// This command needs to complete within the limits of the PHP Execution timeout.
		$result = $this->execute_ssh('generic', $instance, array('commands' => $command));

		// Check output string to make sure we don't have an error...
		if (!(strpos($result, 'Success: Updated')) && !(strpos($result, 'Success: Theme already updated.'))) {
			return new \WP_Error(__('An error was encounered during the updates. Please check the SSH logs for more information.', 'wpcd'));
		}

		// If you got here, success!
		// @todo: you can do cool things by parsing the result string to check for number of plugins updated and reporting that back to the user.
		$success_msg = __('Command was a success - themes updated!', 'wpcd');
		$result      = array(
			'msg'     => $success_msg,
			'refresh' => 'yes',
		);

		return $result;
	}

	/**
	 * Sample Action "C": Export the database.
	 *
	 * This action is going to be handled with a console shown on the screen.  Which is different from the "A" and "B" actions.
	 * This is an example of a long running action.
	 * Note: You cannot use long-running actions in this style on the server tabs! If you want to connect a long-running action
	 * for a server level command that is not tied to a WP site, you should still initiate it from a WP site.
	 *
	 * @param int    $id         The postID of the server cpt.
	 * @param string $action     The action to be performed (this matches the string required in the bash scripts if bash scripts are used).
	 *
	 * @return boolean success/failure/other
	 */
	private function sample_action_c($id, $action)
	{

		// Get the instance details.
		$instance = $this->get_app_instance_details($id);

		if (is_wp_error($instance)) {
			/* translators: %s is replaced with the name of the action being executed */
			return new \WP_Error(sprintf(__('Unable to execute this request because we cannot get the instance details for action %s', 'wpcd'), $action));
		}

		// We're going to collect any arguments sent.
		// But we're not using them. Only including them here so that you can see how we do basic sanitization.
		// You can also use the FILTER_INPUT function if you like as well.
		$args = wp_parse_args(sanitize_text_field(wp_unslash($_POST['params'])));

		// Get the domain we're working on.
		$domain = $this->get_domain_name($id);

		// we want to make sure this command runs only once in a "swatch beat" for a domain.
		// e.g. 2 manual backups cannot run for the same domain at the same time (time = swatch beat)
		// although technically only one command can run per domain (e.g. backup and restore cannot run at the same time).
		// we are appending the Swatch beat to the command name because this command can be run multiple times
		// over the app's lifetime.
		// but within a swatch beat, it can only be run once.
		$command             = sprintf('%s---%s---%d', $action, $domain, gmdate('B'));
		$instance['command'] = $command;
		$instance['app_id']  = $id;

		// Construct a run command.
		// 'exportdb.txt' is the file that contains the commands we'll be sending to the server.
		// We will be using a filter later to add a pathname to it.
		$run_cmd = $this->turn_script_into_command(
			$instance,
			'exportdb.txt',
			array_merge(
				$args,
				array(
					'command' => $command,
				)
			)
		);


		/**
		 * Run the constructed commmand .
		 * Check out the write up about the different aysnc methods we use
		 * here: https://wpclouddeploy.com/documentation/wpcloud-deploy-dev-notes/ssh-execution-models/
		 */
		$return = $this->run_async_command_type_2($id, $command, $run_cmd, $instance, $action);

		return $return;
	}


	private function create_text_file_on_publish($id, $action)
	{

		$text_content = wp_parse_args(sanitize_text_field(wp_unslash($_POST['params'])));
		$file_path = get_template_directory() . '/key.txt';

		// Create or overwrite the text file
		file_put_contents($file_path, $text_content);

		// Optionally, you can also set file permissions
		chmod($file_path, 0644);
		$return = true;

		return $return;
		//file_put_contents($file_path, $text_content);
	}
	private function ssl_cert($id, $action)
	{

		$text_content = wp_parse_args(sanitize_text_field(wp_unslash($_POST['params'])));
		$file_path = get_template_directory() . '/cert.txt';

		// Create or overwrite the text file
		file_put_contents($file_path, $text_content);

		// Optionally, you can also set file permissions
		chmod($file_path, 0644);
		$return = true;

		return $return;
		//file_put_contents($file_path, $text_content);
	}

	/**
	 * Install Commercial and Key SSL Certificate.
	 *
	 * @param int    $id                 The postID of the server cpt.
	 * @param string $action             The action to be performed (this matches the string required in the bash scripts if bash scripts are used).
	 *
	 * @return boolean success/failure/other
	 */
	private function sample_action_d($id, $action)
	{
		// Get the instance details.
		$instance = $this->get_app_instance_details($id);

		if (is_wp_error($instance)) {
			/* translators: %s is replaced with the name of the action being executed */
			return new \WP_Error(sprintf(__('Unable to execute this request because we cannot get the instance details for action %s', 'wpcd'), $action));
		}

		// Get the domain we're working on.
		$domain = $this->get_domain_name($id);

		// Construct a command to update SSL certificates.
		// This command changes the folder to the WordPress folder (which is the same name as the domain)
		// and then runs the wp-cli command to update SSL certificates.

		// Replace these paths with actual paths to your commercial SSL certificate and key files.
		$sslCertificatePath = '/path/to/commercial_certificate.crt';
		$sslKeyPath = '/path/to/commercial_private_key.key';

		// Call your function to install the commercial SSL certificate
		$result = $this->execute_ssh('generic', $instance, ['commands' => $command]);

		if ($result) {
			$success_msg = __('Command was a success - commercial and key SSL installed!', 'wpcd');
			$result = [
				'msg' => $success_msg,
				'refresh' => 'yes',
			];
		} else {
			$result = new \WP_Error(__('Failed to install commercial and key SSL certificate.', 'wpcd'));
		}

		// Constructing the async command
		$run_cmd = $this->turn_script_into_command(
			$instance,
			'ssl-file.txt',
			array_merge(
				$args,
				[
					'command' => $command,
					'action' => $action,
					'domain' => $domain,
				]
			)
		);

		// Running the async command
		$return = $this->run_async_command_type_2($id, $command, $run_cmd, $instance, $action);

		return $result;
	}

	private function choose_file($id, $action)
	{

		if (isset($_FILES['file'])) {
			$file = $_FILES['file'];

			// Define the upload directory
			$upload_dir = wp_upload_dir();

			// Generate a unique file name
			$file_name = sanitize_file_name($file['name']);

			// Move the uploaded file to the server
			$move_result = move_uploaded_file($file['tmp_name'], $upload_dir['path'] . '/' . $file_name);

			if ($move_result) {
				echo 'File uploaded successfully to ' . $upload_dir['url'] . '/' . $file_name;
			} else {
				echo 'Error uploading file.';
			}
			// Redirect back to the page after processing
			wp_redirect(wp_get_referer());
			exit;
		}

		// Get the instance details.
		/*
		$instance = $this->get_app_instance_details($id);

		if (is_wp_error($instance)) {
			/* translators: %s is replaced with the name of the action being executed */
		//return new \WP_Error(sprintf(__('Unable to execute this request because we cannot get the instance details for action %s', 'wpcd'), $action));
		//} 

		// Get the domain we're working on.
		//$domain = $this->get_domain_name($id);

		// Construct a command to update SSL certificates.
		// This command changes the folder to the WordPress folder (which is the same name as the domain)
		// and then runs the wp-cli command to update SSL certificates.



		// Call your function to install the commercial SSL certificate
		/*$result = $this->execute_ssh('generic', $instance, ['commands' => $command]);

		if ($result) {
			$success_msg = __('Command was a success - commercial and key SSL installed!', 'wpcd');
			$result = [
				'msg' => $success_msg,
				'refresh' => 'yes',
			];
		} else {
			$result = new \WP_Error(__('Failed to install commercial and key SSL certificate.', 'wpcd'));
		}

		// Constructing the async command
		$run_cmd = $this->turn_script_into_command(
			$instance,
			'ssl-file.txt',
			array_merge(
				$args,
				[
					'command' => $command,
					'action' => $action,
					'domain' => $domain,
				]
			)
		);

		// Running the async command
		$return = $this->run_async_command_type_2($id, $command, $run_cmd, $instance, $action);
          
		return $result; */
	}



	/**
	 * Make sure that we return the full path name of the script file if the script filename is one of ours.
	 *
	 * For the purposes of this demo we are only checking for one script file name - 'exportdb.txt'.
	 * It is also expected that the $script_name is globally unique to the plugin and all addons.
	 * So make sure that you choose this name wisely!
	 *
	 * Filter Hook: wpcd_script_file_name
	 *
	 * @param string $script_name     The script file name.
	 *
	 * @return boolean success/failure/other
	 */
	public function wpcd_script_file_name($script_name)
	{

		// shortcut and return if not something we should handle.
		if ('exportdb.txt' !== $script_name && 'ssl-file.txt' !== $script_name) {
			return $script_name;
		}

		return WPCDSAMPLE_PATH . 'includes/scripts/' . $script_name;
	}

	/**
	 * Different scripts needs different placeholders/handling.
	 *
	 * Filter Hook: wpcd_script_placeholders_{$this->get_app_name()}
	 *
	 * @param array  $new_array          Existing array of placeholder data.
	 * @param array  $array              The original array of data passed into the core 'script_placeholders' function.
	 * @param string $script_name        The name of the script being processed.
	 * @param string $script_version     The version of script to be used.
	 * @param array  $instance           Various pieces of data about the server or app being used. It can use the following keys:
	 *      post_id: the ID of the post.
	 * @param string $command            The command being constructed.
	 * @param array  $additional         An array of any additional data we might need. It can use the following keys (non-exhaustive list):
	 *    command: The command to use (a script may have multiple commands)
	 *    domain: The domain of the site
	 *    user: The user to action.
	 *    email: The email to use.
	 *    public_key: The path to the public key
	 *    password: The password of the user.
	 */
	public function wpcd_wpapp_replace_script_tokens($new_array, $array, $script_name, $script_version, $instance, $command, $additional)
	{

		if ('exportdb.txt' === $script_name) {
			$command_name = $additional['command'];
			$new_array    = array_merge(
				array(
					'SCRIPT_LOGS'  => "{$this->get_app_name()}_{$command_name}",
					'CALLBACK_URL' => $this->get_command_url($instance['app_id'], $command_name, 'completed'),
				),
				$additional
			);
		}
		if ('ssl-file.txt' === $script_name) {
			$command_name = $additional['command'];
			$new_array    = array_merge(
				array(
					'SCRIPT_LOGS'  => "{$this->get_app_name()}_{$command_name}",
					'CALLBACK_URL' => $this->get_command_url($instance['app_id'], $command_name, 'completed'),
				),
				$additional
			);
		}

		return $new_array;
	}

	public function ssl_certificate_field($id, $action)
	{
		$instance = $this->get_app_instance_details($id);

		if (is_wp_error($instance)) {
			/* translators: %s is replaced with the name of the action being executed */
			return new \WP_Error(sprintf(__('Unable to execute this request because we cannot get the instance details for action %s', 'wpcd'), $action));
		}

		// Get the domain we're working on.
		$domain = $this->get_domain_name($id);

		// Construct a command to update SSL certificates.
		// This command changes the folder to the WordPress folder (which is the same name as the domain)
		// and then runs the wp-cli command to update SSL certificates.

		// Replace these paths with actual paths to your commercial SSL certificate and key files.
		$sslCertificatePath = '/path/to/commercial_certificate.crt';
		$sslKeyPath = '/path/to/commercial_private_key.key';

		// Call your function to install the commercial SSL certificate
		$result = $this->execute_ssh('generic', $instance, ['commands' => $command]);

		if ($result) {
			$success_msg = __('Command was a success - commercial and key SSL installed!', 'wpcd');
			$result = [
				'msg' => $success_msg,
				'refresh' => 'yes',
			];
		} else {
			$result = new \WP_Error(__('Failed to install commercial and key SSL certificate.', 'wpcd'));
		}

		// Constructing the async command
		$run_cmd = $this->turn_script_into_command(
			$instance,
			'ssl-file.txt',
			array_merge(
				$args,
				[
					'command' => $command,
					'action' => $action,
					'domain' => $domain,
				]
			)
		);

		// Running the async command
		$return = $this->run_async_command_type_2($id, $command, $run_cmd, $instance, $action);

		return $result;
	}
}

new WPCD_WordPress_TABS_APP_SAMPLE();
