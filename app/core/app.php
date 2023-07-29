<?php

namespace App\core;

use FastRoute;

ob_start();
class app
{
	public function __construct()
	{
		$this->index();
	}


	public function index()
	{

		$dispatcher = require_once("router.php");

		// Fetch method and URI from somewhere
		$httpMethod = $_SERVER['REQUEST_METHOD'];
		$uri = $_SERVER['REQUEST_URI'];
		$uri = @"/{$_GET['url']}";
		$uri  = rtrim($uri, "/");

		// Strip query string (?foo=bar) and decode URI
		if (false !== $pos = strpos($uri, '?')) {
			$uri = substr($uri, 0, $pos);
		}
		$uri = rawurldecode($uri);

		$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

		switch ($routeInfo[0]) {
			case FastRoute\Dispatcher::NOT_FOUND:
				// ... 404 Not Found
				echo "not found 404";
				break;
			case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
				$allowedMethods = $routeInfo[1];
				// ... 405 Method Not Allowed
				echo "not allowed 405";
				break;
			case FastRoute\Dispatcher::FOUND:

				if (gettype($routeInfo[1]) == 'array') {
					$this->runHandlerAsArray($routeInfo);
				}

				if (gettype($routeInfo[1]) == 'string') {
					$this->runHandlerAsString($routeInfo);
				}

				if (gettype($routeInfo[1]) == 'object') {
					// $this->runHandlerAsString($routeInfo);
				}

				break;
		}
	}


	public function runHandlerAsString($routeInfo)
	{

		$controller_path = "../app/Controllers/{$routeInfo[1]}.php";
		if (file_exists($controller_path)) {

			require_once "$controller_path";

			$explode = explode("/", $routeInfo[1]);
			$controller_class = end($explode);
			$controller = new $controller_class;
		} else {
			// $controller = new ;
		}



		$explode = explode("/", $routeInfo[2]['path'] ?? "");

		$method = str_replace("-", "_", $explode[0]);

		if (method_exists($controller, $method ?? '')) {
			unset($explode[0]);
		} else {

			$method = "index";
		}



		$vars = array_values($explode);


		$data = call_user_func_array([$controller, $method], $vars);
	}

	public function runHandlerAsArray($routeInfo)
	{

		$handler = $routeInfo[1];

		$vars = $routeInfo[2];
		// ... call $handler with $vars
		$controller = new $handler[0];

		$method = method_exists($controller, $handler[1] ?? '') ? $handler[1] : "index";
		$data = call_user_func_array([$controller, $method], $vars);
	}
}
