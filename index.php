<?php
/*!
	@file
	@brief Main file
	
	@see \a .info.php (Main page) for details about the license and description of this project.
	@see \a index.getdata.php for details about the license and description of this project.
*/
define('NO_HACKING', 1);
header("Content-type: text/plain; charset=utf-8");
require('./_top.php');

//
// Preformat some variables
//
$strDate2Check = empty($_GET['D']) ? '' : $_GET['D'];
if (!preg_match('#^[0-9]{4}-[0-9]{2}-[0-9]{2}$#', $strDate2Check))
{
	die ('Sorry, see you later!');
}
$numDateTZ = $arrMyCnf['dna']['tz'];
$numMinEndSize = $arrMyCnf['dna']['min_len'];

//
// Get \a $arrDNAUserData and \a $oTicks
//
require('./index.getdata.php');

//
// Output
//
echo "\n\n==arrDNAUserData==\n";	var_export($arrDNAUserData);

$arrTicks = $oTicks->pf_getDurations();
echo "\n\n==arrTicks==\n";	var_export($arrTicks);

?>