<?
/*!
	@file
	@brief Init file to be included on top
	
	This includes some libraries and sets up some general stuff.
*/
	if (!defined('NO_HACKING'))
	{
		die ('GO AWAY!');
	}
	
	// set timezone
	//! @todo check if this won't mess with time...
	if (function_exists('date_default_timezone_set'))
	{
		date_default_timezone_set('Europe/Paris');
	}
	
	// parse config file
	if (function_exists('posix_getuid'))
	{
		$arrUserInfo = posix_getpwuid(posix_getuid());
	}
	else
	{
		$arrUserInfo['dir'] = '.';
	}
	$arrMyCnf = parse_ini_file($arrUserInfo['dir'] . "/.my.cnf", true);

	// init data manipulation
	require_once './lib/data.class.php';
	$arrSrcDb = $arrMyCnf[$arrMyCnf['dna']['srcdb']];
	$oData = new cMainData(
		$arrSrcDb['host'], $arrSrcDb['dbname'],
		$arrSrcDb['user'], $arrSrcDb['password']
	);

	// include other classes
	require_once './lib/ticks.class.php';
	require_once './lib/cache.class.php';

	// error handling
	include './lib/fun_error.php';
	init_myErrorHandler();
?>