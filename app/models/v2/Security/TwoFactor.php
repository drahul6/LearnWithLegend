<?php
declare(strict_types=1);

namespace v2\Security;
use PHPGangsta_GoogleAuthenticator, Config;

/**
 * 
 */
class TwoFactor 
{
	private $ga;


	private $user;

	private $secret;

	
	function __construct($user)
	{



		/*
		$ga = new PHPGangsta_GoogleAuthenticator();
		$secret = $ga->createSecret();
		echo "Secret is: ".$secret."\n\n";

		// $_SESSION['secret'] = $secret;

		// $secret = $_SESSION['secret'] ?? 'S4KOYDYTP2AZVUO4';

		$app_name = Config::domain();

		$qrCodeUrl = $ga->getQRCodeGoogleUrl('tee', $secret, $app_name );
		echo "Google Charts URL for the QR-Code: ".$qrCodeUrl."\n\n";

		$oneCode = $ga->getCode($secret);

		echo "Checking Code '$oneCode' and Secret '$secret':\n";

		$checkResult = $ga->verifyCode($secret, $oneCode, 2);    // 2 = 2*30sec clock tolerance
		if ($checkResult) {
		    echo 'OK';
		} else {
		    echo 'FAILED';
		}

		$img = <<<EL
		<img src="$qrCodeUrl">
		EL;

		echo "$img";
		*/




		    $this->user = $user;


		    $this->ga = new PHPGangsta_GoogleAuthenticator();

		    $this->existing_settings = $this->user->SettingsArray;
		    $this->app_name = Config::domain();


		    $this->secret = $this->existing_settings['2fa']['secret'] ?? $this->ga->createSecret();


		    $this->existing_settings['2fa'] = [
		    	'secret'=> $this->secret,
		    	'email'=> $this->user->email,
		    ];


		    $this->user->save_settings($this->existing_settings);

	}


	public function hasLogin($code)
	{
		$checkResult = $this->ga->verifyCode($this->secret, $code, 2);    // 2 = 2*30sec clock tolerance

		return $checkResult;
	}

	public function getQrCode()
	{
		    $qrCodeUrl = $this->ga->getQRCodeGoogleUrl($this->user->email, $this->secret, $this->app_name);

		    return $qrCodeUrl;
	}

}