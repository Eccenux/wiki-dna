<?php
	define('NO_HACKING', 1);
	require('./_top.php');

	// get an array of cached dates
	// dates are in miliseconds since 1970 (appropriate for JS)
	$d = dir("./cache/");
	$arrAvailableDates = array();
	while (false !== ($strEntry = $d->read()))
	{
		// is_file($d->path.$strEntry) && 
		if (preg_match('/pbase_==_([0-9\-]+)\.php/', $strEntry, $arrMatches))
		{
			$numDateTZ = date("Z", strtotime($arrMatches[1]))/3600;
			$arrAvailableDates[] = (strtotime($arrMatches[1].' GMT') - $numDateTZ*3600) * 1000;
		}
	}
	$d->close();
	sort($arrAvailableDates);
	
	// return JS
	header("Content-type: text/javascript; charset=utf-8");
?>
// TZ: <?=$numDateTZ?>

// min date
ncalend.dateScope.min = new Date(<?=$arrAvailableDates[0]?>);

// available dates object for the calendar
ncalend.dateScope.availableDates =
{'':''
	<?php foreach ($arrAvailableDates as $dt) { ?>
		,'<?=$dt?>' : true 
	<?php } ?>
};