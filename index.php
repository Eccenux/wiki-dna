<?php
/**
	@file
	@brief Main file

	@todo OMG, refactor this someday maybe... :]
	
	@see \a .info.php (Main page) for details about the license and description of this project.
	@see \a index.getdata.php Main script controling the flow of gathering data
*/
define('NO_HACKING', 1);
//header("Content-type: text/plain; charset=utf-8");
require('./_top.php');

// setup performance check
$oTicks = new cTicks();

// time limit
set_time_limit(600);

//
// CLI refresh mode
//
$textOutput = false;
if(php_sapi_name() == "cli" && isset($argc)) {
	$textOutput = true;
	$_GET = array(
		'allow_slow' => 1,
		'clear_cache' => 1,
		'override_sec' => md5($arrMyCnf['dna']['cache_salt']),
	);
	if (isset($argv[1])) {
		$_GET['D'] = $argv[1];
	} else {
		$_GET['D'] = date('Y-m-d', strtotime('-3 days'));
	}
}

//
// Preformat some variables
//
$strDate2Check = empty($_GET['D']) ? '' : $_GET['D'];
if (empty($strDate2Check) && !empty($_GET['Dy']) && isset($_GET['Dm'],$_GET['Dd']))
{
	$strDate2Check = $_GET['Dy'].'-'.$_GET['Dm'].'-'.$_GET['Dd'];
}
$oData->allowSlow = empty($_GET['allow_slow']) ? false : true;
//$numDateTZ = $arrMyCnf['dna']['tz'];
$numMinEndSize = $arrMyCnf['dna']['min_len'];
$strPageTitle = 'DNA';
$strDieMessage = '';
$strTplFile = empty($_GET['flat']) ? 'data' : 'data_flat';
$strPageBaseURL = $arrSrcDb['page_base_url'];
$isAccuracyNeeded=false;
// use "secret" word to bypass some checks and optimizations...
$strSecretStr = md5($arrMyCnf['dna']['cache_salt']);
$strSecretUserStr = empty($_GET['override_sec']) ? '' : $_GET['override_sec'];
//$strSecretUserStr=$strSecretStr;
if ($strSecretUserStr==$strSecretStr)
{
	$isAccuracyNeeded = true;
}

//
// Check date format, get timezone
//
$numDateTZ = date("Z",strtotime($strDate2Check))/3600;
$oTicks->pf_insTick('RC min-max check');
$arrLocalMaxMinRC = $oData->pf_getLocalMaxMinRC($numDateTZ);
$oTicks->pf_endTick('RC min-max check');
if (empty($strDate2Check))
{
	$strTplFile = 'index';
}
else if (!preg_match('#^[0-9]{4}-[0-9]{2}-[0-9]{2}$#', $strDate2Check))
{
	$strDieMessage = 'Nieprawidłowy format daty. Prawidłowy format to: RRRR-MM-DD.';
}
else if ($strSecretUserStr!=$strSecretStr	// not a secret bypass
	&& !$oCache->pf_isInCache($arrMyCnf['dna']['cache_page_basics_name'], $strDate2Check)	// not already cached
)
{
	if (!$oData->pf_isDateInRC($strDate2Check, $numDateTZ))	// not in RC table?
	{
		$strDieMessage = 'Data jest zbyt stara lub za bardzo w przyszłości ;-) (poza tabelą ostatnich zmian). Stare daty mogą być obliczane na życzenie (<a href="http://pl.wikipedia.org/wiki/User_talk:Nux">skontaktuj się ze mną przez Wiki</a>).';
	}
}

//
// Clearing (refreshing) cache
//
$isCacheClear = false;
// secret cache clear
if ($strSecretUserStr==$strSecretStr && !empty($_GET['clear_cache']))
{
	$isCacheClear = true;
}

// check if cache need to be cleared
if (!$isCacheClear)
{
	$dtCache = $oCache->pf_getCacheTime($arrMyCnf['dna']['cache_page_basics_name'], $strDate2Check);
	$dtToBeChecked = strtotime($strDate2Check);	// no need to change TZ - both SHOULD be in the same TZ
	$dtDiff = abs($dtCache - $dtToBeChecked);
	$dtDiffNow = abs($dtCache - time());
	if ($dtDiffNow>60*5 && $dtDiff<=24*3600)	// current day cached for 5 min
	{
		$isCacheClear = true;
	}
}

// clear cache
if ($isCacheClear)
{
	$oCache->pf_delFromCache($arrMyCnf['dna']['cache_page_basics_name'], $strDate2Check);
	$oCache->pf_delFromCache($arrMyCnf['dna']['cache_last_len_name'], $strDate2Check);
	$oCache->pf_delFromCache($arrMyCnf['dna']['cache_users_name'], $strDate2Check);	// why did I kept cache names in cnf?
	$oCache->pf_delFromCache('actors', $strDate2Check);
	$oCache->pf_delFromCache($arrMyCnf['dna']['cache_page_extra_name'], $strDate2Check);
}

//
// Deny requests for calculating unfinished date
//
/*
if (!empty($strDate2Check) && empty($strDieMessage))
{
	$dtEnd = strtotime("{$strDate2Check} -$numDateTZ hours")+24*3600;
	$dtNow = time();
	if ($dtNow<=$dtEnd)
	{
		//$strDieMessage = 'Skrypt umożliwia obliczanie danych tylko dla dat z przeszłości.';
		// disable cache
		$oCache->isDisabled = true;
	}
}
*/

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
if (!$textOutput) {
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
} else {
	echo "date: $strDate2Check\n";
	if (!empty($strDieMessage)) {
		echo $strDieMessage;
	} else {
		echo 'Ticks [s]:';
		foreach ($arrTicks as $strTickName=>$intDurtation) {
			echo sprintf("\n%s: %.4f", $strTickName, $intDurtation);
		}
	}
}
?>