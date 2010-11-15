<?php
	/*!
		@brief Artykuły z danego dnia
		
		@todo:
			\li https://wiki.toolserver.org/view/Subversion
			\li https://svn.toolserver.org/svnroot/eccenux/
			\li https://wiki.toolserver.org/view/New-style_Subversion_repositories

		@todo 
		
		Dla danej daty:
		spr. czy jest to data dla zakończonego dnia i jeśli tak:
			spr. czy już obliczono dane i jeśli tak:
				zaserwuj dane z cache
			else
				oblicz dane
				zapisz dane
		else
			spr. czy dane obliczono dawniej niż X temu (X=15 minut?) i jeśli tak:
				oblicz dane
				zapisz dane
			else
				zaserwuj dane z cache
				
		oblicz dane:
			1. get (rc_user AS) user_id, (rc_cur_id AS) page_id, (rc_new_len AS) start_len FROM recentchanges - można by cachować sobie kolejne cząstki po dacie lub rc_id
			2. get user_name FROM user dla każdego user_id
			3. get page_title FROM page dla każdego page_id
			4. get max(rev_len) z revision dla każdego page_id o danej dacie
				SELECT * FROM page LEFT JOIN revision ON (page_id = rev_page)
				
			2 i 3 można by robić ekstra (na życzenie) także żeby odświeżyć archiwalne zliczarki
	*/
	define('NO_HACKING', 1);
	header("Content-type: text/plain; charset=utf-8");
	require('./_top.php');
	
	//$strDate2Check = '2010-10-10';
	$strDate2Check = '2010-11-08';
	$numDateTZ = $arrMyCnf['dna']['tz'];
	$numMinStartSize = $arrMyCnf['dna']['min_len'];
	
	// ticks
	$oTicks = new cTicks();
	
	//
	// 1. get user_id, page_id, start_len
	//
	//! @todo: run timer before any testing on TS
	//! @todo: cache basic data - only to be refreshed upon bug
	$oTicks->pf_insTick('basic page data');
	$arrPages = $oData->pf_getPagesBasics($strDate2Check, $numDateTZ);
	$oTicks->pf_endTick('basic page data');

	//
	// 2. get user_name FROM user dla każdego user_id
	//
	$oTicks->pf_insTick('user info');
	$arrUsers = $oData->pf_getUserInfo($arrPages);
	$oTicks->pf_endTick('user info');
	
	//
	// 3. get page_title FROM page dla każdego page_id
	//
	$oTicks->pf_insTick('page titles');
	$arrPageTitle = $oData->pf_getPageTitles($arrPages);
	$oTicks->pf_endTick('page titles');
	
	//
	// 4. get last(rev_len) z revision dla każdego page_id o danej dacie
	//
	//! @todo: cache along with the basic data - only to be refreshed upon bug or if pages for the given date were not complete
	$oTicks->pf_insTick('page last lens');
	$arrPageLastLen = $oData->pf_getPageLastLens($arrPages, $strDate2Check, $numDateTZ);
	$oTicks->pf_endTick('page last lens');
	
	//! @todo Filter out pages that were redirects on the end of the day? '/^#(?:REDIRECT|PRZEKIERUJ|TAM|PATRZ)/i'
	
	//
	// @todo TEST output (to be removed)
	//
	echo "\n";	var_export($arrPages);
	echo "\n";	var_export($arrPageTitle);
	echo "\n";	var_export($arrPageLastLen);
	echo "\n";	var_export($arrUsers);
	
	$arrTicks = $oTicks->pf_getDurations();
	echo "\n";	var_export($arrTicks);

?>