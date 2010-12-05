<?php
/*!
	@file
	@brief This script is a semi-function that returns \a $arrDNAUserData and \a $oTicks
	
	@par Input Variables
	@li \a $strDate2Check Date to be check (str 'YYYY-mm-dd')
	@li \a $numDateTZ Time zone (int in hours from GMT)
	@li \a $numMinEndSize Min length of articles at the end of the day that is OK (int in bytes)

	@par Return data
	@li \a $oTicks Ticks object that gathers some info on times
	@li \a $arrDNAUserData Output data (user-centric)
	\code
	array
	(
		user_id (int) => array
		(
			'user_name' => str,
			'total_ok' => int,
			'total_nonok' => int,
			'total_len' => int (in bytes),
			'pages' => array
			(
				[0..n] => array
				(
					'page_id' => int,
					'page_title' => str,
					'start_len' => int (in bytes),
					'end_len' => int (in bytes),
					'is_ok' => true/false,
				)
			)
		)
	)
	\endcode
*/

if (!defined('NO_HACKING'))
{
	die ('GO AWAY!');
}

//
// 0. ticks and cache init
//
$oTicks = new cTicks();
//$oCache = new cCache($arrMyCnf['dna']['cache_salt']);	// moved to top

//
// 1. get arrPages (user_id, page_id, start_len)
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
// 2. get last(rev_len) z revision dla każdego page_id o danej dacie
//
$arrPageLastLen = $oCache->pf_readFromCache($arrMyCnf['dna']['cache_last_len_name'], $strDate2Check);
if ($arrPageLastLen===false)
{
	$oTicks->pf_insTick('page last lens');
	$arrPageLastLen = $oData->pf_getPageLastLens($arrPages, $strDate2Check, $numDateTZ);
	$oTicks->pf_endTick('page last lens');

	// only to be refreshed upon bug or if the day was not over when pages data was gathered
	$oCache->pf_writeToCache($arrMyCnf['dna']['cache_last_len_name'], $strDate2Check, $arrPageLastLen);
}

//
// 3. get user_name FROM user for each user_id
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
// 4. get page_title, page_namespace FROM page for each page_id
//
$arrPageExtra = $oCache->pf_readFromCache($arrMyCnf['dna']['cache_page_extra_name'], $strDate2Check);
if ($arrPageExtra===false)
{
	$oTicks->pf_insTick('page extra');
	$arrPageExtra = $oData->pf_getPageTitles($arrPages);
	$oTicks->pf_endTick('page extra');

	$oCache->pf_writeToCache($arrMyCnf['dna']['cache_page_extra_name'], $strDate2Check, $arrPageExtra);
}

/**/
//
// 5. Transform data to be user-centric
//
$arrDNAUserData = array();
foreach ($arrPages as $p)
{
	if ($arrPageExtra[$p['page_id']]['page_namespace']!='0')
	{
		continue;
	}
	$uid = intval($p['user_id']);
	if (empty($arrDNAUserData[$uid]))
	{
		$arrDNAUserData[$uid] = array
		(
			'user_name' => isset($arrUsers[$uid])?$arrUsers[$uid]:'',
			'total_ok' => 0,
			'total_nonok' => 0,
			'total_len' => 0,
			'pages' => array(),
		);
	}
	$arrDNAUserData[$uid]['pages'][] = array
	(
		'page_id' => intval($p['page_id']),
		'page_title' => $arrPageExtra[$p['page_id']]['page_title'],
		'start_len' => intval($p['start_len']),
		'end_len' => intval($arrPageLastLen[$p['page_id']]),
		'is_ok' => ($arrPageLastLen[$p['page_id']]>=$numMinEndSize),
	);
}
/**/

//
// Remove used data
//
unset($arrPages, $arrPageExtra, $arrPageLastLen, $arrUsers);

//
// Sort functions
//
function pf_cmpUsers($b, $a)
{
    if ($a['total_ok'] == $b['total_ok'])
	{
        return ($a['total_len'] - $b['total_len']);
    }
    return ($a['total_ok'] - $b['total_ok']);
}
function pf_cmpPages($b, $a)
{
    if ($a['end_len'] == $b['end_len'])
	{
        return ($a['start_len'] - $b['start_len']);
    }
    return ($a['end_len'] - $b['end_len']);
}

/**/
//
// 6. Count stuff
//
foreach ($arrDNAUserData as &$u)
{
	foreach ($u['pages'] as &$p)
	{
		if ($p['is_ok'])
		{
			$u['total_ok']++;
		}
		else
		{
			$u['total_nonok']++;
		}
		$u['total_len']+=$p['end_len'];
	}
	usort($u['pages'], "pf_cmpPages");
}
/**/
usort($arrDNAUserData, "pf_cmpUsers");

?>