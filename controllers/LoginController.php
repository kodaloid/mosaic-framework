<?php

class LoginController extends Controller {


	/**
	 * Show the login screen.
	 */
	function login(App $app) {
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
	function logout(App $app) {
		$redirect = $app->url('/login');
		$app->session->logout($redirect);
	}
}