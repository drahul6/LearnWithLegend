<?php

use v2\Security\TwoFactor;

@session_start();
class controller extends operations
{


	public static $guards = [
		'user' => [
			'model' => User::class,
			'guard' => 'user',
		],
		'admin' => [
			'model' => Admin::class,
			'guard' => 'admin',
		],
	];


	public function verify_2fa_only($auth = null)
	{
		if ($auth == null) {
			$auth = $this->auth();
		}


		$_2FA = new TwoFactor($auth);

		if (!$_2FA->hasLogin($_POST['code'])) {
			Session::putFlash('danger', "Invalid 2FA Code");
			Redirect::back();
		}
	}

	public function verify_2fa()
	{
		// return true;

		$auth = $this->auth();

		if ($auth->has_2fa_enabled()) {

			$this->verify_2fa_only();
		} else {

			print_r($_SESSION['twofa']);

			$this->validator()->check(Input::all(), array(

				'email_code' => [
					'required' => true,
					// 'equals'=> $_SESSION['twofa']['email_code'],
				]
			));


			if ($_SESSION['twofa']['email_code'] != trim($_POST['email_code'])) {
				Session::putFlash('danger', "Invalid Email Code");
				Redirect::back();
			}

			if (!$this->validator()->passed()) {
				Session::putFlash('danger', "Email Code is required");
				Redirect::back();
			}

			unset($_SESSION['twofa']);
		}
	}




	public function use_2fa()
	{

		if (!$this->auth()->has_2fa_enabled()) {
			return;
		}

		$form = <<<FORM
		  <div class="form-group">
		    <label>2FA Code</label>
		    <input type="" name="code" required="" placeholder="Enter Google 2FA 6 digit code" class="form-control">
		  </div>
FORM;
		return $form;
	}



	public function use_2fa_protection()
	{

		// return "";

		if ($this->auth()->has_2fa_enabled()) {

			$form = $this->use_2fa();
		} else {

			$form = $this->use_email_as_2fa();
		}

		return $form;
	}

	public function create_2fa_code()
	{
		$_SESSION['twofa']['expiry_time'] = $expiry_time = date("Y-m-d H:i:s", strtotime("+ 10 min"));
		$_SESSION['twofa']['email_code'] = MIS::random_string(6, 'numeric');
		$code = $_SESSION['twofa']['email_code'];

		return $code;
	}


	public function create_email_code()
	{
		$mailer = new Mailer;
		$auth = $this->auth();
		$to = $auth->email;
		$subject = "Authorization Email Code";


		if (isset($_SESSION['twofa']['expiry_time'])) {
			$expiry_time = $_SESSION['twofa']['expiry_time'];
			if (strtotime($expiry_time) <= time()) {
				$code =	$this->create_2fa_code();
			} else {
				$code = $_SESSION['twofa']['email_code'];
			}
		} else {
			$code = $this->create_2fa_code();
		}


		$content = "
				<p>Dear $auth->firstname,</p>

				<p>Kindly enter the code below to authorize the pending action.</p>
				<p>Email Code: $code </p>

	";


		$content = MIS::compile_email($content);

		//client
		$response = $mailer->sendMail(
			"{$to}",
			"$subject",
			$content,
			"{$auth->firstname}"
		);
		if ($response == true) {

			Session::putFlash("success", "Email Code sent to your email");
		} else {

			Session::putFlash("danger", "Email Code could not be sent. Please try again.");
		}
	}



	public function use_email_as_2fa()
	{

		$form = <<<FORM
		  <div class="form-group">
			    <label>2FA Code</label>
			<div class="input-group">
				<input type="text" name="email_code" required
				 class="form-control" placeholder="Enter email code sent to email" aria-describedby="button-addon2">
				<div class="input-group-append" id="button-addon2">
					<button onclick="send_email_code()" class="btn btn-outline-primary" type="button">Send Email code</button>
				</div>
			</div>
		  </div>
FORM;
		return $form;
	}


	public function inputErrors()
	{
		if (Input::errors()) {


			$output = ' <div class="list-group" style="text-align:center;">';


			foreach (Input::errors() as $field => $errors) {


				$field = ucfirst(str_replace('_', ' ', $field));

				$output .=  ' <a class="list-group-item list-group-item-danger">
		         <strong class="list-group-item-heading">' . $field . '</strong>';

				foreach ($errors as $error) {

					$error = ucfirst(str_replace('_', ' ', $error));

					$output .= '<p class="list-group-item-text">' . $error . '</p>';
				}

				$output .= '</a>';
			}

			$output .= '</div>';
		}


		return $output;
	}




	public function inputError($field)
	{

		$output = '  <span role="alert">';

		if (Input::errors($field)) {
			foreach (Input::errors($field) as $error) {
				$error = ucfirst(str_replace('_', ' ', $error));
				$output .= $error . ' ';
			}

			$output .= '</span>';
			return $output;
		}
	}

	public function validator()
	{

		if (isset($this->validator)) {
			return $this->validator;
		}
		return	$this->validator = new Validator;
	}




	public function getsitecredit()
	{
		$date = '2020-06-17';
		$now = date("Y-m-d");
		$diff = (int) ((time() - strtotime($date)) / (24 * 60 * 60));
		if ($diff >= 30) {
			return  "<span class='float-right'> Developed by <a target='_blank' href='http://gitstardigital.com'> Gitstar Digital</a> </span>";
		}
	}



	public function getController($route)
	{
		$router = require '../app/core/router.php';
		$class = $router[$route];
		require "../app/controllers/$class.php";
		$controller = new $class;
		return $controller;
	}


	public function csrf_field($csrf_field = "")
	{
		if ($csrf_field == "") {
			$csrf_field = "new" . time();
		}

		$csrf_prefix  = Token::$csrf_prefix;
		$csrf_field = "{$csrf_prefix}$csrf_field";
		echo '<input type="hidden" name="' . $csrf_field . '" value="' . Token::set_csrf($csrf_field) . '">';
	}

	public function money_format($string)
	{
		return number_format("$string", 2);
	}





	public function load_email_verification()
	{
		ob_start();
		if ($this->auth()->email_verification != 1) {
			require_once 'app/others/email_verification.php';
		}

		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	public function load_confirmation_dialog()
	{
		ob_start();
		require_once 'app/others/confirmation_dialog.php';

		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}




	public function load_phone_verification()
	{
		ob_start();
		require_once 'app/others/phone_verification.php';
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}




	public function allow_contenteditable($ngmodel_name)
	{
		if ($this->admin()) {

			return " contenteditable='true'  ng-model='$ngmodel_name' ";
		}
		return " contenteditable='false'  ng-model='$ngmodel_name'  ";
	}





	public function buildView($view, $data = [], $multiple = false, $light = false)
	{


		ob_start();
		$this->view($view, $data, $multiple, $light);
		$output = ob_get_contents();
		ob_end_clean();


		return $output;
	}


	protected function logout()
	{
		session_destroy();
		return true;
	}

	public function directly_authenticate($user_id, $guard = 'user')
	{
		Session::put($this->auth_user($guard), $user_id);
		$model = self::$guards[$guard]['model'];

		$this->remember_user($model::find($user_id));
	}

	protected function authenticate_with($id, $password, $remember_me = false, $guard = 'user')
	{


		/* Session::putFlash("info", "System is undergoing maintenance. kindly check back");
		return false; */
		$model = self::$guards[$guard]['model'];
		$session_key = self::$guards[$guard]['guard'];

		$user = $model::where('id', $id)->where('blocked_on', null)->first();

		$hash = $user->password;
		if (!password_verify($password, $hash)) {
			return false;
		}

		Session::put($this->auth_user($session_key), $user->id);
		if (!$remember_me) {
			return $user;
		}

		//create remeber me cookie if remember_me
		$this->remember_user($user);
		return $user;
	}

	public function logout_user($guard)
	{
		$this->forget_user($guard);
		unset($_SESSION[$this->auth_user($guard)]);
	}

	public function forget_user($guard)
	{
		$cookie_name = $this->remember_cookiename($guard);
		$expires = time() - 3000000;
		$response = setcookie($cookie_name, "remember_token", $expires, '/', null, false, true);
	}

	public function remember_cookiename($guard = null)
	{
		$guard = $guard ?? 'user';
		$project_name = Config::project_name();
		$cookie_name = "$project_name" . "r_$guard";
		$cookie_name = str_replace(" ", "_", $cookie_name);
		return $cookie_name;
	}

	public function remember_user($user, $guard = null)
	{
		$guard = $guard ?? 'user';
		$rememberance =  RememberAuth::remember($user);


		$options = [
			'expires' => strtotime($rememberance->expires_at), //7months
			'path' => '/',
			// 'domain' => '.example.com', // leading dot for compatibility or use subdomain
			// 'secure' => true,     // or false
			'httponly' => true,    // or false
			'samesite' => 'None' // None || Lax  || Strict
		];

		$cookie_name = $this->remember_cookiename($guard);
		setcookie($cookie_name, $rememberance->token, $options['expires'], '/', null, false, true);
	}

	public function auth_user($key = null)
	{
		$key = $key ?? 'user';
		return Config::project_name() . $key;
	}

	public function admin()
	{
		return $this->auth('admin');
	}

	public function auth($guard = null)
	{
		// return false;

		$guard = $guard ?? 'user';

		$model = self::$guards[$guard]['model'];
		$session_key = self::$guards[$guard]['guard'];

		$session_id = Session::get($this->auth_user($session_key));
		$user = $model::where('id', $session_id)->first();

		$remember_cookiename = $this->remember_cookiename($guard);
		$remember_token = $_COOKIE[$remember_cookiename] ?? 'empty';

		if ($user == null) {
			$can_remember =  RememberAuth::recallUser($remember_token);

			if (!$can_remember) {
				return false;
			}

			$this->directly_authenticate($can_remember->user_id, $guard);
			$user = $can_remember->user;
		}

		if ($user->is_blocked()) {
			Session::putFlash('danger', '<br>You Have Been Blocked!');
			return false;
		}

		return $user;
	}




	public function model($model)
	{
		require_once 'app/models/' . $model . '.php';
		return new $model;
	}



	public function view($view, $data = [], $multiple = false, $light = false)
	{
		foreach ($data as $key => $value) {
			$$key = $value;
		}
		$view_path = explode('/', $view);
		array_pop($view_path);
		$view_folder = '';
		foreach ($view_path as $key => $folder) {

			$view_folder .= $folder . '/';
		}
		$view_folder = rtrim($view_folder, '/');


		$host			= Config::host();
		$currency		= Config::currency('html_code');
		$project_name	= Config::project_name();
		$domain			= Config::domain();
		$asset 			= $domain . "/template/" . Config::views_template() . "/app-assets";
		$general_asset 			= $domain . "/template/" . Config::views_template() . "/system_assets";
		$logo 			= Config::logo();
		$fav_icon 			=	$logo;
		$this_folder	= $domain . "/template/" . Config::views_template() . "/$view_folder";
		$websocket_url	= "$host:3000";
		$template	=   'template/' . Config::views_template();


		$auth =  $this->auth();
		$admin =  $this->admin();

		$page_author =  "";

		// define("$this_folder", 	$this_folder, 	true);


		if (!defined('domain')) {

			define("domain", 	$domain, 	false);
		}
		if (!defined('project_name')) {

			define("project_name", 	$project_name, 	false);
		}
		if (!defined('asset')) {

			define("asset", 	$asset, 	false);
		}
		if (!defined('general_asset')) {

			define("general_asset", 	$general_asset, 	false);
		}
		if (!defined('logo')) {

			define("logo", 	$logo, 	false);
		}


		if (!defined('fav_icon')) {

			define("fav_icon", 	$fav_icon, 	false);
		}
		if (!defined('websocket_url')) {

			define("websocket_url", $websocket_url, 	false);
		}



		if ($multiple == false) {
			require_once "template/" . Config::views_template() . "/{$view}.php";
		} else {
			require "template/" . Config::views_template() . "/{$view}.php";
		}


		if ($light == false) {
			require_once "template/" . Config::views_template() . "/others/confirmation_dialog.php";
			require_once "template/" . Config::views_template() . "/others/show_notifications.php";
		}

		// Session::delete('inputs-errors');
	}


	public function middleware($middleware)
	{

		require_once '../app/middlewares/' . $middleware . '.php';
		return new $middleware;
	}
}
