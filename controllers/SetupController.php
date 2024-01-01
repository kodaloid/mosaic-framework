<?php

/**
 * Handles the setup routine when first installing Mosaic.
 */
class SetupController extends Controller {


	/**
	 * Validate the setup form.
	 */
	private function validate_setup(object $vars) :FormErrors {
		$errors = new FormErrors();
		
		if (empty($vars->site_name)) {
			$errors->add('site_name', 'Site Name can not be empty.');
		}

		if (empty($vars->site_url) || !filter_var($vars->site_url, FILTER_VALIDATE_URL)) {
			$errors->add('site_url', 'Site URL "'.$vars->site_url.'" is invalid.');
		}

		if ($vars->disable_login == false) {
			if (empty($vars->admin_user)) {
				$errors->add('admin_user', 'Admin Username can not be empty.');
			}

			if (empty($vars->admin_email)) {
				$errors->add('admin_email', 'Admin Email is invalid.');
			}

			if (empty($vars->admin_pass) || strlen($vars->admin_pass) < 5) {
				$errors->add('admin_pass', 'Admin Password must be longer than 5 characters.');
			}
		}

		if (empty($vars->db_host)) {
			$errors->add('db_host', 'Database Host can not be empty.');
		}

		if (empty($vars->db_name)) {
			$errors->add('db_name', 'Database Name can not be empty.');
		}

		$conn = @new mysqli($vars->db_host, $vars->db_user, $vars->db_pass, $vars->db_name);
		if ($conn->connect_errno) {
			$errors->add('db', 'Failed to connect to database, ' . $conn->connect_error);
		}

		return $errors;
	}


	/**
	 * Insert the various vars into the config.php template.
	 */
	private function compile_template(object $vars, string $password_salt) :string {
		$template = $this->get_template();
		$code = str_replace('[site_name]', $vars->site_name, $template);
		$code = str_replace('[site_url]', $vars->site_url, $code);
		$code = str_replace('[time_zone]', $vars->time_zone, $code);

		if ($vars->show_errors) {
			$code_err = "ini_set('display_errors', 1);\n" .
							"ini_set('display_startup_errors', 1);\n" .
							"error_reporting(E_ALL);\n";
		}
		else {
			$code_err = "// ini_set('display_errors', 1);\n" .
							"// ini_set('display_startup_errors', 1);\n" .
							"// error_reporting(E_ALL);\n";
		}

		$code = str_replace('[errors]', $code_err, $code);
		
		$code = str_replace('[db_host]', $vars->db_host, $code);
		$code = str_replace('[db_name]', $vars->db_name, $code);
		$code = str_replace('[db_user]', $vars->db_user, $code);
		$code = str_replace('[db_pass]', $vars->db_pass, $code);

		$code = str_replace('[login_enabled]', $vars->disable_login ? 'false' : 'true', $code);
		$code = str_replace('[login_salt]', $password_salt, $code);

		return $code;
	}
	

	/**
	 * Return a string template for config.php
	 */
	private function get_template() {
		$code = "<?php

		// Localization.
		date_default_timezone_set('[time_zone]');

		// PHP Settings (comment these out to disable verbose errors).
		[errors]
				
		// Global Variables.
		define('SITE_URL', '[site_url]');
		define('SITE_NAME', '[site_name]');
				
		// Database.
		define('DB_HOST', '[db_host]');
		define('DB_NAME', '[db_name]');
		define('DB_USER', '[db_user]');
		define('DB_PASS', '[db_pass]');
		define('DB_CHARSET', 'utf8mb4');
		define('DB_DATE_FORMAT', 'Y-m-d H:i:s');
		define('ARRAY_A', 0);
		define('OBJECT', 1);
				
		// Login System
		define('LOGIN_ENABLED', [login_enabled]);
		define('PASSWORD_SALT', '[login_salt]');
				
		// Timing.
		define('ONE_DAY', 86400);";
		return str_replace("\t", '', $code);
	}


	/**
	 * The endpoint for the setup sequence.
	 */
	function index(App $app) {
		$current_url = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

		// defaults.
		$vars = (object)array(
			'site_name'		=> '',
			'site_url'		=> $current_url,
			'time_zone'		=> 'Europe/London',
			'show_errors'	=> true,
			'disable_login'=> false,
			'admin_user'	=> '',
			'admin_email'	=> '',
			'admin_pass'	=> '',
			'db_host'		=> 'localhost',
			'db_name'		=> '',
			'db_port'		=> '',
			'db_user'		=> '',
			'db_pass'		=> ''
		);

		if ($app->request->is_post) {
			$vars = (object)array(
				'site_name'		=> $app->request->post('site_name'),
				'site_url'		=> $app->request->post('site_url'),
				'time_zone'		=> $app->request->post('time_zone'),
				'show_errors'	=> $app->request->post('show_errors') == 'yes',
				'disable_login'=> $app->request->post('disable_login') == 'yes',
				'admin_user'	=> $app->request->post('admin_user'),
				'admin_email'	=> $app->request->post('admin_email'),
				'admin_pass'	=> $app->request->post('admin_pass'),
				'db_host'		=> $app->request->post('db_host'),
				'db_name'		=> $app->request->post('db_name'),
				'db_port'		=> $app->request->post('db_port'),
				'db_user'		=> $app->request->post('db_user'),
				'db_pass'		=> $app->request->post('db_pass')
			);

			$errors = $this->validate_setup($vars);
			if ($errors->count > 0) {
				return $this->view('setup/setup', array(
					'old' => $vars,
					'current_url' => $current_url,
					'errors' => $errors->toArray(),
					'time_zones' => $app->tools->GenerateTimezoneList()
				));
			}

			// setup some new missing constants before using the database.
			$salt = base64_encode(random_bytes(12));
			$salt = rtrim($salt, '==');
			$has_login = $vars->disable_login == false;
			$otp_image = '';


			define('DB_CHARSET', 'utf8mb4');
			define('PASSWORD_SALT', $salt);
			define('DB_DATE_FORMAT', 'Y-m-d H:i:s');
			define('OBJECT', 1);
			define('SITE_URL', $current_url);

			if ($has_login) {
				// setup db connection so we can create the admin.
				$GLOBALS['db'] = $db = new MosDatabase;
				$db->connect_manual($vars->db_host, $vars->db_name, $vars->db_user, $vars->db_pass);

				// create the user.
				$user_res = $app->session->create_user($vars->admin_user, $vars->admin_email, $vars->admin_pass);
				$user_id = $user_res->id;

				$otp_image = $app->session->get_otp_image($user_res->id, $vars->site_name);
			}

			// generate the config.php file.
			$code = $this->compile_template($vars, PASSWORD_SALT);
			file_put_contents(__APP__ . '/config.php', $code);

			// finally show view asking admin to configure 2fa before login.
			return $this->view('setup/setup-complete', array(
				'has_login' => $has_login,
				'otp_image' => $otp_image
			));
		}

		return $this->view('setup/setup', array(
			'old' => $vars,
			'current_url' => $current_url,
			'errors' => [],
			'time_zones' => $app->tools->GenerateTimezoneList()
		));
	}
}