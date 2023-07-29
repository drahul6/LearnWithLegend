<?php

/**
 * 
 */
class Validator
{
	private $_errors,
		$_passed = false;


	public function is_valid_btc_address($address)
	{
		$origbase58 = $address;
		$dec = "0";

		for ($i = 0; $i < strlen($address); $i++) {
			$dec = bcadd(bcmul($dec, "58", 0), strpos("123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz", substr($address, $i, 1)), 0);
		}

		$address = "";

		while (bccomp($dec, 0) == 1) {
			$dv = bcdiv($dec, "16", 0);
			$rem = (int)bcmod($dec, "16");
			$dec = $dv;
			$address = $address . substr("0123456789ABCDEF", $rem, 1);
		}

		$address = strrev($address);

		for ($i = 0; $i < strlen($origbase58) && substr($origbase58, $i, 1) == "1"; $i++) {
			$address = "00" . $address;
		}

		if (strlen($address) % 2 != 0) {
			$address = "0" . $address;
		}

		if (strlen($address) != 50) {
			return false;
		}

		if (hexdec(substr($address, 0, 2)) > 0) {
			return false;
		}

		return substr(strtoupper(hash("sha256", hash("sha256", pack("H*", substr($address, 0, strlen($address) - 8)), true))), 0, 8) == substr($address, strlen($address) - 8);
	}

	/**
	 * checks if a domain name is valid
	 * @param  string $domain_name 
	 * @return bool              
	 */
	public function is_domain_name_valid($domain_name)
	{
		//FILTER_VALIDATE_URL checks length but..why not? so we dont move forward with more expensive operations
		$domain_len = strlen($domain_name);
		if ($domain_len < 3 or $domain_len > 253)
			return FALSE;

		//getting rid of HTTP/S just in case was passed.
		if (stripos($domain_name, 'http://') === 0)
			$domain_name = substr($domain_name, 7);
		elseif (stripos($domain_name, 'https://') === 0)
			$domain_name = substr($domain_name, 8);

		//we dont need the www either                 
		if (stripos($domain_name, 'www.') === 0)
			$domain_name = substr($domain_name, 4);

		//Checking for a '.' at least, not in the beginning nor end, since http://.abcd. is reported valid
		if (strpos($domain_name, '.') === FALSE or $domain_name[strlen($domain_name) - 1] == '.' or $domain_name[0] == '.')
			return FALSE;

		//now we use the FILTER_VALIDATE_URL, concatenating http so we can use it, and return BOOL
		return (filter_var('http://' . $domain_name, FILTER_VALIDATE_URL) === FALSE) ? FALSE : TRUE;
	}


	public function get_value($array, $key)
	{
		$explode = explode('.', $key);
		$copy = $array;
		foreach ($explode as $key) {
			if (!isset($copy[$key])) {
				return null;
			}
			$copy = $copy[$key];
		}
		return $copy;
	}


	public function check($data, $items)
	{
		// echo "<pre>";

		foreach ($items as $item => $rules) {


			$item_key = $item;
			$item_name = $item;


			switch ($item_key) {
				case 'composite_unique':
					$model = $rules['model'];
					$name = $rules['name'];
					$query = $model::where($rules['columns_value']);

					if (isset($rules['primary_key'])) {
						$query->where($rules['primary_key'], '!=', $rules['find_key']);
					}


					$count = $query->count();

					if ($count > 0) {
						$this->addError("$name", "Entry already exist.");
					}
					break;

				case 'files_exists':

					$name = $rules['name'];
					foreach ($rules['files'] as $key => $file) {
						if (!file_exists($file)) {
							$this->addError("$name", "Resource not fully available.");
							break;
						}
					}

					break;

				default:
					# code...
					break;
			}






			foreach ($rules as $rule => $rule_value) {

				// echo "$item must be $rule $rule_value<br>";
				//rule definitions of

				// $value = trim($data["$item_key"] ?? '');
				$value = trim($this->get_value($data, "$item_key") ?? '');

				//rename the item if set
				if (array_key_exists('name', $rules)) {
					$item_name = $rules['name'];
				}


				if ($rule === 'required' && empty($value)) {

					$this->addError($item_name, "$item_name is required");
				} else if (!empty($value)) {


					switch ($rule) {

						case 'min':

							(strlen($value) < $rule_value) ? $this->addError($item_name, "$item_name cannot be less than $rule_value characters.") : '';


							break;
						case 'max':

							(strlen($value) > $rule_value) ? $this->addError($item_name, "$item_name cannot be more than $rule_value characters.") : '';
							break;

						case 'min_age':
							$birth_year = date("Y", strtotime($value));
							$diff = date("Y") - $birth_year;
							(($diff) < $rule_value) ? $this->addError($item_name, "You must be at least $rule_value years.") : '';




							break;

						case 'min_value':

							(($value) < $rule_value) ? $this->addError($item_name, "$item_name cannot be less than $rule_value.") : '';


							break;

						case 'max_value':

							(($value) > $rule_value) ? $this->addError($item_name, "$item_name cannot be more than $rule_value.") : '';


							break;

						case 'equals':

							(($value) != $rule_value) ? $this->addError($item_name, "$item_name does not match records.") : '';

							break;

						case 'in':
							$list = implode(",", $rule_value);
							(!in_array($value, $rule_value)) ? $this->addError($item_name, "$item_name must be in '$list'.") : '';

							break;

						case 'step':

							(($value % $rule_value) !== 0) ? $this->addError($item_name, "$item_name should be in steps of $rule_value.") : '';


							break;


						case 'no_special_character':

							if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $value)) {
								// one or more of the 'special characters' found in $string
								$this->addError($item_name, "$item_name cannot contain special characters.");
							}

							break;


						case 'one_word':
							$number_of_word = str_word_count($value);
							if ($number_of_word > 1) {
								$this->addError($item_name, "$item_name must be one word.");
							}

							break;

						case 'no_of_words':
							$number_of_word = str_word_count($value);
							if ($number_of_word != $rule_value) {
								$this->addError($item_name, "$item_name must contain $rule_value word(s).");
							}
							break;

						case 'min_no_of_words':
							$number_of_word = str_word_count($value);
							if ($number_of_word < $rule_value) {
								$this->addError($item_name, "$item_name cannot be less than $rule_value word(s).");
							}
							break;

						case 'max_no_of_words':
							$number_of_word = str_word_count($value);
							if ($number_of_word > $rule_value) {
								$this->addError($item_name, "$item_name cannot be more than $rule_value word(s).");
							}
							break;


						case 'email':

							if (!filter_var($value, FILTER_VALIDATE_EMAIL) === true) {

								$this->addError($item_name, "$item_name is not valid.");
							}

							break;



						case 'date':
							$d = DateTime::createFromFormat($rule_value, $value);
							// The Y ( 4 digits year ) returns TRUE for any integer with any number 
							// of digits so changing the comparison from == to === fixes the issue.

							if ($d && ($d->format($rule_value) === $value)) {
							} else {
								$this->addError($item_name, "$item_name is not valid.");
							}


							break;

						case 'url':

							if (!filter_var($value, FILTER_VALIDATE_URL) === true) {

								$this->addError($item_name, "$item_name is not valid.");
							}

							break;
						case 'numeric':

							if (!ctype_digit($value)) {

								$this->addError($item_name, "$item_name must be numeric.");
							}

							break;

						case 'positive':

							if ($value < 0) {

								$this->addError($item_name, "$item_name must be greater than 0.");
							}

							break;

						case 'unique':

							$model  = explode('|', $rule_value)[0];
							$column  = explode('|', $rule_value)[1] ?? $item;

							if ($model::where($column, $value)->first()) {
								$this->addError($item_name, "$item_name is already taken.");
							}

							break;


						case 'domain':

							$passed = $this->is_domain_name_valid($value);

							if (!$passed) {
								$this->addError($item_name, "$item_name is not valid.");
							}

							break;




						case 'exist':

							$model  = explode('|', $rule_value)[0];
							$column  = explode('|', $rule_value)[1];

							if ($model::where($column, $value)->first() == null) {
								$this->addError($item_name, "$item_name does not exist.");
							}
							break;


						case 'unique_collection':

							$unique  = array_unique(collect($rule_value)->pluck('value')->toArray());

							$text = '';
							foreach ($rule_value as $key => $value) {
								$text .= $value['name'] ?? $item;
								$text .= ", ";
							}

							if (count($unique) != count($rule_value)) {
								$this->addError($item_name, "$text should be different.");
							}


							break;




						case 'replaceable':

							$model  = explode('|', $rule_value)[0];
							$id  = explode('|', $rule_value)[1];
							$column  = explode('|', $rule_value)[2] ?? $item;


							if ($model::where($column, $value)->where('id', '!=', $id)->first()) {
								$this->addError($item_name, "$item_name is already taken.");
							}


							break;
						case 'matches':

							if ($value !== $data["$rule_value"]) {

								$this->addError("$rule_value", "{$rule_value}s do not match.");
							}

							break;

						case 'not match':

							if ($value === $data["$rule_value"]) {

								$this->addError("$item", "{$item} cannot match {$rule_value}.");
							}

							break;

						default:
							# code...
							break;
					}
				}
			}
		}

		if ($this->_errors == '') {

			$this->_passed = true;
		}

		return false;
	}




	public function addError($field, $error)
	{
		$this->_errors["$field"][] = $error;
		Session::put('inputs-errors', $this->_errors);
	}


	public function passed()
	{

		if ($this->_errors == '') {

			return  true;
		}

		return false;
	}


	public function errors()
	{
		return $this->_errors;
	}
}
