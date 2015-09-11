<?php 

	// inclui a classe
	require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "psyDuck.php";

	// define the default storage folder for the app
	if (!defined('STORAGE_FOLDER'))
		define( 'STORAGE_FOLDER', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'storage_folder' .DIRECTORY_SEPARATOR );
