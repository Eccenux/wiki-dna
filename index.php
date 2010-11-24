<?php
/*!
	@file
	@brief Main file
	
	@see Basic information page (.info.php) for details about the license and project description.

	@todo Filter out pages that are not in the main namespace (any way to check in which namespace they were?)

	@todo Form data to be user-centric
	\code
	user_id => array (
		'user_name' =>,
		'total_ok' =>,
		'total_nonok' =>,
		'total_len' =>,
		'pages' => array(
			'page_id' =>,
			'page_title' =>,
			'start_len' =>,
			'end_len' =>,
		)
	)
	\endcode

	@todo Test output - to be removed
*/
define('NO_HACKING', 1);
header("Content-type: text/plain; charset=utf-8");
require('./_top.php');

//
// 0. Preformat data
//
$strDate2Check = empty($_GET['D']) ? '' : $_GET['D'];
if (!preg_match('#^[0-9]{4}-[0-9]{2}-[0-9]{2}$#', $strDate2Check))
{
	die ('Sorry, see you later!');
}
$numDateTZ = $arrMyCnf['dna']['tz'];
$numMinStartSize = $arrMyCnf['dna']['min_len'];

// ticks and cache init
$oTicks = new cTicks();
$oCache = new cCache($arrMyCnf['dna']['cache_salt']);

//
// 1. get user_id, page_id, start_len
//

// attempt to read from cache
$arrPages = $oCache->pf_readFromCache($arrMyCnf['dna']['cache_page_basics_name'], $strDate2Check);
if ($arrPages===false)
{
	$oTicks->pf_insTick('basic page data');
	$arrPages = $oData->pf_getPagesBasics($strDate2Check, $numDateTZ);
	$oTicks->pf_endTick('basic page data');

	// only to be refreshed upon bug
	$oCache->pf_writeToCache($arrMyCnf['dna']['cache_page_basics_name'], $strDate2Check, $arrPages);
}

//
// 4. get last(rev_len) z revision dla każdego page_id o danej dacie
//
$arrPageLastLen = $oCache->pf_readFromCache($arrMyCnf['dna']['cache_last_len_name'], $strDate2Check);
if ($arrPageLastLen===false)
{
	$oTicks->pf_insTick('page last lens');
	$arrPageLastLen = $oData->pf_getPageLastLens($arrPages, $strDate2Check, $numDateTZ);
	$oTicks->pf_endTick('page last lens');

	// only to be refreshed upon bug or if pages for the given date were not complete
	//! @todo figure out what did I mean by "complete" :-)
	$oCache->pf_writeToCache($arrMyCnf['dna']['cache_last_len_name'], $strDate2Check, $arrPageLastLen);
}

//
// 2. get user_name FROM user for each user_id
//
$arrUsers = $oCache->pf_readFromCache($arrMyCnf['dna']['cache_users_name'], $strDate2Check);
if ($arrUsers===false)
{
	$oTicks->pf_insTick('user info');
	$arrUsers = $oData->pf_getUserInfo($arrPages);
	$oTicks->pf_endTick('user info');

	$oCache->pf_writeToCache($arrMyCnf['dna']['cache_users_name'], $strDate2Check, $arrUsers);
}

//
// 3. get page_title, page_namespace FROM page for each page_id
//
$arrPageExtra = $oCache->pf_readFromCache($arrMyCnf['dna']['cache_page_extra_name'], $strDate2Check);
if ($arrPageExtra===false)
{
	$oTicks->pf_insTick('page extra');
	$arrPageExtra = $oData->pf_getPageTitles($arrPages);
	$oTicks->pf_endTick('page extra');

	$oCache->pf_writeToCache($arrMyCnf['dna']['cache_page_extra_name'], $strDate2Check, $arrPageExtra);
}

//
// Output
//
echo "\n";	var_export($arrPages);
echo "\n";	var_export($arrPageExtra);
echo "\n";	var_export($arrPageLastLen);
echo "\n";	var_export($arrUsers);

$arrTicks = $oTicks->pf_getDurations();
echo "\n";	var_export($arrTicks);

?>