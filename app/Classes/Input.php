<?php


/**
 * 
 */
class Input

{

	public static function inputErrors()
	{
		if (Input::errors()) {


			$output = ' <div class="list-group" style="text-align:center;">';


			foreach (Input::errors() as $field => $errors) {


				$field = ucfirst(str_replace('_', ' ', $field));

				$output .=  ' <a class="list-group-item list-group-item-danger" style="padding:0px;">
		         <strong class="list-group-item-heading">' . $field . '</strong>';

				foreach ($errors as $error) {

					$error = ucfirst(str_replace('_', ' ', $error));

					$output .= '<p class="list-group-item-text" style="margin:0px;">' . $error . '</p>';
				}

				$output .= '</a>';
			}

			$output .= '</div>';
		}


		return $output;
	}




	public static function inputError($field)
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



	public static function exists($csrf_field = null)
	{
		$_SESSION["inputs"] = $_POST;
		$status = null;

		if ($csrf_field != null) {
			$key = Token::$csrf_prefix . $csrf_field;

			$status = (isset($_REQUEST[$key])) ?  $_REQUEST[$key] == Token::csrf_field($key) : false;
		} else {



			foreach ($_REQUEST as $key => $value) {
				$csrf_prefix = Token::$csrf_prefix;

				if (strpos($key, $csrf_prefix) !== false) {

					$status =  $_REQUEST[$key] == Token::csrf_field($key);
				}
			}
		}

		if (($status == true)) {
			return true;
		}

		return false;
	}

	public static function get($item)
	{
		if (isset($_POST[$item])) {
			return trim($_POST[$item]);
		} elseif (isset($_GET[$item])) {
			return trim($_GET[$item]);
		}

		return '';
	}

	public static function all()
	{
		if (isset($_POST)) {
			self::exists();
			return $_POST;
		} elseif (isset($_GET)) {
			return $_GET;
		}

		return '';
	}

	public static function old($item)
	{
		return Session::get('inputs')[$item] ?? '';
	}

	public static function errors($fieldError = '')
	{
		if ($fieldError != '') {

			return Session::get('inputs-errors')[$fieldError] ?? '';
		}
		return Session::get('inputs-errors');
	}
}
