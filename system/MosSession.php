<?php

use OTPHP\TOTP;


/**
 * Tools to manage online visitor sessions. Including logging in, registering,
 * and 2FA OTP processing.
 */
class MosSession {
	private $_logged_in;
	private $_user_id;


	function __construct() {
		$this->_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == 'yes';
		$this->_user_id = $this->_logged_in && isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
	}


	/**
	 * Returns a bool indicating if the current user is logged in.
	 */
	public function logged_in() {
		return $this->_logged_in;
	}


	/**
	 * Get the current user.
	 */
	function current_user() {
		return $this->get_user($this->_user_id);
	}


	/**
	 * Perform a login.
	 */
	public function login(string $user_id, string $redirect = null) {
		global $app;
		$this->_logged_in = $_SESSION['logged_in'] = 'yes';
		$this->_user_id = $_SESSION['user_id'] = $user_id;
		if (false !== $redirect) {
			$app->redirect($redirect);
		}
	}
	

	/**
	 * Perform a logout.
	 */
	public function logout(string $redirect = null) {
		global $app;
		session_destroy();
		$this->_logged_in = false;
		$this->_user_id = null;
		if (!is_null($redirect)) {
			$app->redirect($redirect);
		}
	}


	/**
	 * Create a new user.
	 */
	public function create_user(string $username, string $email, string $password) {
		global $db;

		$otp = TOTP::create();
		$otp_secret = $otp->getSecret();
		$pwd_peppered = hash_hmac("sha256", $password, PASSWORD_SALT);

		$ins_query = "INSERT INTO users (username, email, pass_hash, otp_secret, date_created) VALUES (?,?,?,?,?)";
		$db->exec($db->prepare($ins_query, "sssss", array(
			$username,
			$email,
			password_hash($pwd_peppered, PASSWORD_ARGON2ID),
			$otp_secret,
			$db->now()
		)));

		return (object)array(
			'id' => $db->insert_id(),
			'otp_secret' => $otp_secret
		);
	}


	/**
	 * Get a user by it's id.
	 */
	function get_user(int $user_id) {
		global $db;
		return $db->select_row($db->prepare(
			'SELECT * FROM users WHERE id = ?', 'i', [$user_id]
		));
	}


	/**
	 * Get a user by the username.
	 */
	function get_user_by_username(string $username) {
		global $db;
		return $db->select_row($db->prepare(
			'SELECT * FROM users WHERE username = ?', 's', [$username]
		));
	}


	/**
	 * Get the (qr code) image url used for setting up the authenticator.
	 */
	function get_otp_image(int $user_id, string $app_name) {
		global $app;

		$user = $this->get_user($user_id);
		$otp = TOTP::create($user->otp_secret);
		$otp->setLabel($app_name);

		$code = $otp->getProvisioningUri();
		$image = $app->tools->GenerateQrImage($code);
		// $image = $otp->getQrCodeUri('https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=300x300&ecc=M', '[DATA]');

		return $image;
	}


	/**
	 * Check to see if a user/pass is correct.
	 */
	function validate_login(object $user, string $password) {
		if (is_null($user)) return false;
		$pwd_peppered = hash_hmac("sha256", $password, PASSWORD_SALT);
		return password_verify($pwd_peppered, $user->pass_hash);
	}


	/**
	 * Validate an otp code against a users otp secret.
	 */
	function validate_otp(object $user, string $code) {
		$otp = TOTP::create($user->otp_secret);
		return $otp->verify($code);
	}
}