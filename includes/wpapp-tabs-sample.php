<?php

/**
 * Class for adding a new tab to the application details screen.
 *
 * @package WPCD
 */

if (!defined('ABSPATH')) {
	exit;
}




/**
 * Class for adding a new tab to the application details screen.
 */
class WPCD_WordPress_TABS_APP_SAMPLE extends WPCD_WORDPRESS_TABS
{


	/**
	 * WPCD_WORDPRESS_TABS_PHP constructor.
	 */
	public function __construct()
	{

		parent::__construct();

		add_filter("wpcd_app_{$this->get_app_name()}_get_tabnames", array($this, 'get_tab'), 10, 1);
		add_filter("wpcd_app_{$this->get_app_name()}_get_tabs", array($this, 'get_tab_fields_sample'), 10, 2);
		add_filter("wpcd_app_{$this->get_app_name()}_tab_action", array($this, 'tab_action_sample'), 10, 3);

		add_action("wpcd_command_{$this->get_app_name()}_completed", array($this, 'command_completed_sample'), 10, 2);


		// Filter to make sure we give the correct file path when merging contents.
		add_filter('wpcd_script_file_name', array($this, 'wpcd_script_file_name'), 10, 2);

		// Filter to handle script file tokens.
		add_filter('wpcd_wpapp_replace_script_tokens', array($this, 'wpcd_wpapp_replace_script_tokens'), 10, 7);
		//$this->get_html();
	}

	/*public function get_html()
	{
?>
		<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
			<input type="hidden" name="action" value="handle_text">
			<label for="textData">Text here:</label>
			<textarea name="textData" id="textData"></textarea>
			<input type="submit" value="Skicka Text">
		</form>
<?php
	} */


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
				/*case 'sample-action-a':
				$result = $this->sample_action_a($id, $action);
				break;
			case 'sample-action-b':
				$result = $this->sample_action_b($id, $action);
				break;
			case 'sample-action-c':
				$result = $this->sample_action_c($id, $action);
				break;
			case 'sample-action-d':
				$result = $this->sample_action_d($id, $action);
				break;  */

			case 'send-text':
				$result = $this->send_text($id, $action);
				break;


				return $result;
		}
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

		// Heading.
		$sample_desc  = __('transfer-file.txt', 'wpcd');
		$sample_desc .= '<br />';

		/*$actions['sample-action-ssl-file'] = array(
			'label'          => __('SSL-CERT', 'wpcd'),
			'raw_attributes' => array(
				'desc'           => __('Enter your ssl cert here.', 'wpcd'),
				'data-wpcd-name' => 'ssl cert',
			),
			'type'           => 'file',


		);
		$actions['sample-action-ssl'] = array(
			'label'          => __('Install SSL Certificate', 'wpcd'),
			'raw_attributes' => array(
				'std'                 => __('install SSL certificate', 'wpcd'),
				'desc'                => __('install SSL certificate and key for this domain.', 'wpcd'),
				// fields that contribute data for this action.
				'data-wpcd-fields'    => wp_json_encode(array('#wpcd_app_action_sample-action-field-01')),
				// make sure we give the user a confirmation prompt.
				'confirmation_prompt' => __('Are you sure you would like to install SSL certificate?', 'wpcd'),
				// Show the console.
				'log_console'         => true,
				// Initial console message.
				'console_message'     => __('Preparing to install SSL certificate...<br /> Please DO NOT EXIT this screen until you see a popup message indicating that the operation has completed or has errored.<br />This terminal should refresh every 60-90 seconds with updated progress information from the server. <br /> After the operation is complete the entire log can be viewed in the COMMAND LOG screen.', 'wpcd'),
			),

			'type'           => 'button',


		); */

		// handling text
		$actions['handle-ssl-certificate'] = array(
			'label'             => __('Add SSL certificate data: ', 'your_domain'),
			'raw_attributes'    => array(
				'desc'            => __('paste SSL certificate data', 'your_domain'),
				'data-wpcd-name'  => 'install_ssl_certificate',
			),
			'type'              => 'textarea',
			'name'              => 'textData',
		);

		// sending text
		$actions['install-ssl-certificate'] = array(
			'label'          => __('Install SSL Certificate', 'your_domain'),
			'raw_attributes' => array(
				'desc'       => __('Trigger the action to install SSL Certificate', 'your_domain'),
				'data-wpcd-fields'    => wp_json_encode(array('#wpcd_app_action_handle-ssl-certificate')),
				'confirmation_prompt' => __('Are you sure you would like to install SSL certificate?', 'wpcd'),
				'data-wpcd-name' => 'send_button',
				'log_console'         => true,
				// Initial console message.
				'console_message'     => __('Preparing to install SSL certificate...<br /> Please DO NOT EXIT this screen until you see a popup message indicating that the operation has completed or has errored.<br />This terminal should refresh every 60-90 seconds with updated progress information from the server. <br /> After the operation is complete the entire log can be viewed in the COMMAND LOG screen.', 'wpcd'),
			),

			'type'           => 'button',
			'name'           => 'sendButton',
		);


		return $actions;
	}



	private function install_ssl_certificate($id, $action)
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
			'transfer-file.txt',
			array_merge(
				$args,
				array(
					'command' => $command,
					'action'  => $action,
					'domain'  => $domain,
				)
			)
		);
		$return = $this->run_async_command_type_2($id, $command, $run_cmd, $instance, $action);
		return $return;
	}



	/*private function handle_text($id, $action)
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'handle_text') {
			if (isset($_POST['textData'])) {
				$textData = sanitize_text_field(wp_unslash($_POST['textData']));
				echo 'Text have been send: ' . $textData;
			} else {
				echo 'No text sended.';
			}
		}
	} */




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
		if ('transfer-file.txt' !== $script_name) {
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


		if ('transfer-file.txt' === $script_name) {
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
}

new WPCD_WordPress_TABS_APP_SAMPLE();
