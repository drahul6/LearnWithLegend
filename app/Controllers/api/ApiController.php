<?php


/**
 *
 */
class ApiController extends controller
{

	public function __construct()
	{
		header("Access-Control-Allow-Origin: *");

		$headers = getallheaders();
		$authorization =  $headers['Authorization'];


		$auth = str_replace("Basic ", "", $authorization);

		print_r($headers);

		// echo base64_decode($auth);
		// echo $authorization;

	}

	public function user()
	{
		print_r($_SERVER['REQUEST_METHOD']);

		print_r($_REQUEST);
	}


    public function index()
    {

        echo "foldr gome";

    }


}


?>