<?php

class LoginController extends Controller {


	/**
	 * Show the login screen.
	 */
	function login($app) {
		if ($app->request->is_post) {
			$username = $app->request->post('user');
			$pass     = $app->request->post('pass');
			$code		 = $app->request->post('code');
			$user		 = $app->session->get_user_by_username($username);

			if ($app->session->validate_login($user, $pass)) {
				if ($app->session->validate_otp($user, $code)) {
					$app->session->login($user->id);
					return $this->view('login', ['error' => 'Login Valid']);	
				}
				return $this->view('login', ['error' => 'Invalid Login']);
			}
			else {
				return $this->view('login', ['error' => 'Invalid Login']);
			}
		}
		
		return $this->view('login', ['error' => '']);
	}


	/**
	 * Logout and redirect to the login screen
	 */
	function logout($app) {
		$redirect = $app->url('/login');
		$app->session->logout($redirect);
	}


	/**
	 * Handle the registration page.
	 */
	function register($app) {
		global $db;

		// create the user.
		$res = $app->session->create_user('admin', 'test@localhost', 'test123');
		$user_id = $res->id;

		// login without redirect.
		$app->session->login();

		// return a logged-in view, with otp_image generated.
		return $this->view('registered', array(
			'otp_image' => $app->session->get_otp_image($user_id)
		));
	}
}