<?php
define('NO_HACKING', 1);
define('NO_DB', 1);
require('./_top.php');

// setup performance check
$oTicks = new cTicks();

// get an array of cached dates
// dates are in miliseconds since 1970 (appropriate for JS)
$oTicks->pf_insTick('glob', true);
$dirs = glob("./cache/*/*/*", GLOB_ONLYDIR);

$oTicks->pf_insTick('loop', true);
$arrAvailableDates = array();
foreach($dirs as $path) {
	$strDate = preg_replace('#.+/(\d{4})/(\d{2})/(\d{2})#', '$1-$2-$3', $path);
	$numDateTZ = date("Z", strtotime($strDate))/3600;
	$arrAvailableDates[] = (strtotime($strDate.' GMT') - $numDateTZ*3600) * 1000;
}

$oTicks->pf_insTick('sort', true);
sort($arrAvailableDates);
$oTicks->pf_endTick('sort');
	
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

<?php
//
// Debug info
//
echo "\n/*";
echo "\nGenerated: ".date('c');

// dump performance info
$arrTicks = $oTicks->pf_getDurations();
echo "\n\nTicks [s]:";
foreach ($arrTicks as $strTickName=>$intDurtation) {
	echo sprintf("\n%s: %.4f", $strTickName, $intDurtation);
}

echo "\n*/";