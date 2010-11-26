<?php
/*!
	@file
	@brief Main file
	
	@see \a .info.php (Main page) for details about the license and description of this project.
	@see \a index.getdata.php for details about the license and description of this project.
*/
define('NO_HACKING', 1);
//header("Content-type: text/plain; charset=utf-8");
require('./_top.php');

// time limit
set_time_limit(600); 

//
// Preformat some variables
//
$strDate2Check = empty($_GET['D']) ? '' : $_GET['D'];
//$numDateTZ = $arrMyCnf['dna']['tz'];
$numMinEndSize = $arrMyCnf['dna']['min_len'];
$strPageTitle = 'DNA';
$strDieMessage = '';
$strTplFile = 'data';
$strPageBaseURL = $arrSrcDb['page_base_url'];

//
// Check date format, get timezone
//
if (empty($strDate2Check))
{
	$strTplFile = 'index';
}
else if (!preg_match('#^[0-9]{4}-[0-9]{2}-[0-9]{2}$#', $strDate2Check))
{
	$strDieMessage = 'Nieprawidłowy format daty. Prawidłowy format to: RRRR-MM-DD.';
}
else if (!preg_match('#^2010-([0-9]{2})-\1$#', $strDate2Check))
{
	$strDieMessage = 'Obecnie dopuszczalne są tylko daty z roku 2010 dotyczące DNA. Obliczanie innych dat na życzenie (skontaktuj się ze mną przez Wiki).';
}
else
{
	$numDateTZ = date("Z",strtotime($strDate2Check))/3600;
}

//
// Deny request for calculating unfinished date
//
if (!empty($strDate2Check) && empty($strDieMessage))
{
	$dtEnd = strtotime("{$strDate2Check} -$numDateTZ hours")+24*3600;
	$dtNow = time();
	if ($dtNow<=$dtEnd)
	{
		$strDieMessage = 'Skrypt umożliwia obliczanie danych tylko dla dat z przeszłości.';
	}
}

//
// Get \a $arrDNAUserData and \a $oTicks
//
if (!empty($strDate2Check) && empty($strDieMessage))
{
	require('./index.getdata.php');
}

//
// Form ticks
//
if (!empty($oTicks))
{
	$arrTicks = $oTicks->pf_getDurations();
}

//
// Output
//
include('./view/_header.tpl.php');
if (empty($strDieMessage))
{
	include("./view/$strTplFile.tpl.php");
}
else
{
	echo $strDieMessage;
}
include('./view/_footer.tpl.php');
?>