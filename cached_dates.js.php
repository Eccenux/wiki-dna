<?php
	define('NO_HACKING', 1);
	require('./_top.php');

	// get an array of cached dates
	// dates are in miliseconds since 1970 (appropriate for JS)
	$d = dir("./cache/");
	$arrAvailableDates = array();
	while (false !== ($strEntry = $d->read()))
	{
		if (is_dir($d->path.$strEntry) && preg_match('/([0-9]+)/', $strEntry))
		{
			$sd1 = dir($d->path.$strEntry.'/');
			while (false !== ($strEntryS1 = $sd1->read()))
			{
				if (is_dir($sd1->path.$strEntryS1) && preg_match('/([0-9]+)/', $strEntryS1))
				{
					$sd2 = dir($sd1->path.$strEntryS1.'/');
					while (false !== ($strEntryS2 = $sd2->read()))
					{
						if (is_dir($sd2->path.$strEntryS2) && preg_match('/([0-9]+)/', $strEntryS2))
						{
							$strDate = $strEntry.'-'.$strEntryS1.'-'.$strEntryS2;
							$numDateTZ = date("Z", strtotime($strDate))/3600;
							$arrAvailableDates[] = (strtotime($strDate.' GMT') - $numDateTZ*3600) * 1000;
						}
					}
					$sd2->close();
				}
			}
			$sd1->close();
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