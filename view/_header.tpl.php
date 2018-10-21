<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="pl">
<head>
	<title><?=$strPageTitle?> <?php if (!empty($strDate2Check)) { echo $strDate2Check; } ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

	<meta name="author" content="Maciej Jaros" />
	<meta name="copyright" content="Maciej Jaros" />

	<link rel="stylesheet" type="text/css" href="view/main.css?2327" />

	<!-- time en(de)coding & Calendar -->
	<link href="view/calend_t.css?0111" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="./lib/calend_t.js?1337"></script>
	<script type="text/javascript">
	<!--
	//
	//  Kalendarz - ustawienia
	//
	ncalend.days_holders_bgcolor_hover = '#BBB';
	ncalend.days_holders_bgcolor_mark = '#088';
	ncalend.months = ["sty","lut","mar","kwi","maj","cze","lip","sie","wrz","pa&#378;","lis","gru"];
	ncalend.err_parse_time = 'Nieprawid\u0142owy format czasu!\nSpr\u00F3buj 13:10, albo 14:57:29';
	// -->
	</script>
</head>
<body>
<div id="header">
	<p><?=$strPageTitle?></p>
</div>
<div id="container">
<?php /** ?>
<pre>
<?var_export($_GET)?>
<?var_export($_SERVER)?>
</pre>
<?php /**/ ?>

<?php if (!empty($strDate2Check)) { ?>
<nav class="mini-menu">
	<ul>
		<li><a href="<?=$strBaseURL?><?=date("Y-m-d", strtotime("-1 day", strtotime($strDate2Check)))?>">Poprzedni</a></li>
		<li><?=$strDate2Check?></li>
		<?php if ($strDate2Check != date("Y-m-d")) { ?>
			<li><a href="<?=$strBaseURL?><?=date("Y-m-d", strtotime("+1 day", strtotime($strDate2Check)))?>">Następny</a></li>
		<?php } ?>
	</ul>
</nav>
<?php } ?>