<?php
/**
	@file
	@brief Init file to be included on top
	
	This includes some libraries and sets up some general stuff.
*/
	if (!defined('NO_HACKING'))
	{
		die ('GO AWAY!');
	}
	
	// parse config file
	if (file_exists("./.my.script.cnf"))
	{
		$arrUserInfo['dir'] = './';
	}
	else
	{
		$arrUserInfo['dir'] = '../';
	}
	$arrMyCnf = parse_ini_file($arrUserInfo['dir'] . "/.my.script.cnf", true);
	
	//var_export($arrMyCnf);
	//die();

	// set timezone
	if (function_exists('date_default_timezone_set'))
	{
		date_default_timezone_set($arrMyCnf['dna']['proj_tz']);
	}
	else
	{
		die ('date_default_timezone_set does not exist!');
	}
	
	// base URL of DNA site
	$strBaseURL = empty($arrMyCnf['dna']['base_url']) ? '/dna/' : $arrMyCnf['dna']['base_url'];
	
	// init data manipulation
	if (!defined('NO_DB')) {
		require_once './lib/data.class.php';
		$arrSrcDb = $arrMyCnf[$arrMyCnf['dna']['srcdb']];
		$oData = new cMainData(
			$arrSrcDb['host'], $arrSrcDb['dbname'],
			$arrSrcDb['user'], $arrSrcDb['password']
		);
	}

	// include other classes
	require_once './lib/ticks.class.php';
	require_once './lib/cache.class.php';
	
	// init cache object
	$oCache = new cCache($arrMyCnf['dna']['cache_salt'], './cache/', true, '-');

	// error handling
	include './lib/fun_error.php';
	init_myErrorHandler();
?>