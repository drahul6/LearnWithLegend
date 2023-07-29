<?php

/**
 * this class contains all configurations for our project
 * some values are fetched from database while others are hardcoded
 */
class Config
{
	public static $global_settings = [
		'set_currency'
	];



	public function __construct()
	{
	}

	public static function updateSettings()
	{
		foreach (self::$global_settings as $key => $setting) {
			if (isset($_GET[$setting]) && $_GET[$setting] != '') {
				self::$setting($_GET[$setting]);
			}
		}
	}

	public static function set_currency($setting)
	{
		$_SESSION['global_settings']['currency'] = $setting;
	}


	public static function currency($property = null)
	{

		return "USD";

		$currency = $_SESSION['global_settings']['currency'] ?? 'USD';
		$model = AvailableCurrency::fetch_currency($currency);
		return	$property == null ? $model : $model->$property;
	}


	public static function default_profile_pix()
	{
		$domain = self::domain();
		return	 "/template/default/app-assets/images/logo/user.png";
	}


	public static function logo()
	{
		$domain = self::domain();
		return	 "$domain/template/default/app-assets/images/logo/logo.png";
	}



	/**
	 * returns the url to access admin panel.
	 * @return string
	 */
	public  static function admin_url()
	{
		return	 'admin_panel';
	}


	/**
	 * returns the domain of this project
	 * @return string
	 */
	public  static function domain()
	{
		$add = $_ENV['APP_URL'] == null ? '' : "/" . $_ENV['APP_URL'];
		return	self::host() . $add;
	}



	public  static function main_domain()
	{
	}


	/**
	 * returns the host of this project
	 * @return string
	 */
	public  static function host()
	{

		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			// SSL connection
			return	"https://" . $_SERVER['HTTP_HOST'];
		} else {


			return	"http://" . $_SERVER['HTTP_HOST'];
		}
	}

	public static function cookie_name()
	{
		$project_name = Config::project_name();
		$cookie_name = "$project_name-referral";
		$cookie_name = str_replace(" ", "_", $cookie_name);
		return $cookie_name;
	}


	/**
	 * this returns the name of this project
	 * @return string
	 */
	public  static function project_name()
	{
		return	 $_ENV['APP_NAME'];
	}


	/**
	 *returns the current template for the views
	 *@return string
	 */
	public  static function views_template()
	{
		return	'default';
	}

	public static function countries()
	{
		$countries = 'Afghanistan, Albania, Algeria, American Samoa, Andorra, Angola, Anguilla, Antigua & Barbuda, Argentina, Armenia, Aruba, Australia, Austria, Azerbaijan, Bahamas, Bahrain, Bangladesh, Barbados, Belarus, Belgium, Belize, Benin, Bermuda, Bhutan, Bolivia, Bonaire, Bosnia & Herzegovina, Botswana, Brazil, British Indian Ocean Ter, Brunei, Bulgaria, Burkina Faso, Burundi, Cambodia, Cameroon, Canada, Canary Islands, Cape Verde, Cayman Islands, Central African Republic, Chad, Channel Islands, Chile, China, Christmas Island, Cocos Island, Colombia, Comoros, Congo, Cook Islands, Costa Rica, Cote D\'Ivoire, Croatia, Cuba, Curacao, Cyprus, Czech Republic, Denmark, Djibouti, Dominica, Dominican Republic, East Timor, Ecuador, Egypt, El Salvador, Equatorial Guinea, Eritrea, Estonia, Ethiopia, Falkland Islands, Faroe Islands, Fiji, Finland, France, French Guiana, French Polynesia, French Southern Ter, Gabon, Gambia, Georgia, Germany, Ghana, Gibraltar, Great Britain, Greece, Greenland, Grenada, Guadeloupe, Guam, Guatemala, GuGuinea, Guyana, Haiti, Hawaii, Honduras, Hong Kong, Hungary, Iceland, India, Indonesia, Iran, Iraq, Ireland, Isle of Man, Israel, Italy, Jamaica, Japan, Jordan, Kazakhstan, Kenya, Kiribati, Korea North, Korea South, Kuwait, Kyrgyzstan, Laos, Latvia, Lebanon, Lesotho, Liberia, Libya, Liechtenstein, Lithuania, Luxembourg, Macau, Macedonia, Madagascar, Malaysia, Malawi, Maldives, Mali, Malta, Marshall Islands, Martinique, Mauritania, Mauritius, Mayotte, Mexico, Midway Islands, Moldova, Monaco, Mongolia, Montserrat, Morocco, Mozambique, Myanmar, Nambia, Nauru, Nepal, Netherland Antilles, Netherlands (Holland - Europe), Nevis, New Caledonia, New Zealand, Nicaragua, Niger, Nigeria, Niue, Norfolk Island, Norway, Oman, Pakistan, Palau Island, Palestine, Panama, Papua New Guinea, Paraguay, Peru, Philippines, Pitcairn Island, Poland, Portugal, Puerto Rico, Qatar, Republic of Montenegro, Republic of Serbia, Reunion, Romania, Russia, Rwanda, St Barthelemy, St Eustatius, St Helena, St Kitts-Nevis, St Lucia, St Maarten, St Pierre & Miquelon, St Vincent & Grenadines, Saipan, Samoa, Samoa American, San Marino, Sao Tome & Principe, Saudi Arabia, Senegal, Serbia, Seychelles, Sierra Leone, Singapore, Slovakia, Slovenia, Solomon Islands, Somalia, South Africa, Spain, Sri Lanka, Sudan, Suriname, Swaziland, Sweden, Switzerland, Syria, Tahiti, Taiwan, Tajikistan, Tanzania, Thailand, Togo, Tokelau, Tonga, Trinidad & Tobago, Tunisia, Turkey, Turkmenistan, Turks & Caicos Is, Tuvalu, Uganda, Ukraine, United Arab Emirates, United Kingdom, United States of America, Uruguay, Uzbekistan, Vanuatu, Vatican City State, Venezuela, Vietnam, Virgin Islands (Brit), Virgin Islands (USA), Wake Island, Wallis & Futana Is Yemen, Zaire,Zambia, Zimbabwe';
		$countries  = explode(',', $countries);

		return $countries;
	}
}
