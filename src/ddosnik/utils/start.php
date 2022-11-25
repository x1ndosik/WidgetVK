<?php

	start();

	function prepare(){
		define('WIDGET_PATH', realpath(getcwd()) . DIRECTORY_SEPARATOR);
		define('START_TIME', time());
	}

	function start() {
		prepare();
		if(!class_exists("ClassLoader", false)){
			if(!is_file(WIDGET_PATH . "src/spl/ClassLoader.php")){
				echo "[CRITICAL] Unable to find the SPL library." . PHP_EOL;
				echo "[CRITICAL] Please use provided builds or clone the repository recursively." . PHP_EOL;
				exit(1);
			}
			require_once(WIDGET_PATH . "src/spl/ClassLoader.php");
			require_once(WIDGET_PATH . "src/spl/BaseClassLoader.php");
			$autoloader = new \BaseClassLoader();
			$autoloader->addPath(WIDGET_PATH . "src");
			$autoloader->addPath(WIDGET_PATH . "src" . DIRECTORY_SEPARATOR . "spl");
			$autoloader->register(true);

			new \ddosnik\Widget($autoloader);
		}
	}
?>
