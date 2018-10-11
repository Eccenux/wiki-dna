<script type="text/javascript">
	/**
	 * Push/replace history for shorter URL.
	 *
	 * @note requires mod_rewrite or host exclusion
	 *
	 * lighthttpd config:
	 * url.rewrite-once += ( "/([0-9]{4}-[0-9]{2}-[0-9]{2})" => "/index.php?D=$1" )
	 *
	 * @note browsers automatically add script if you specify '?' in the beginning of the string.
	 */
	(function (){
		var reData = /\?Dy=([0-9]+)&Dm=([0-9]+)&Dd=([0-9]+)&submit/;
		if ('replaceState' in history)
		{
			location.href.replace(reData, function(a, Dy, Dm, Dd)
			{
				var isoDate = Dy+'-'+Dm+'-'+Dd;
				if (location.host != 'localhost') {	// exclude localhost
					history.replaceState(null, null, isoDate);
				} else {
					history.replaceState(null, null, '?D='+isoDate);
				}
				return a;
			});
		}
	})();
</script>
<table>
	<tr>
		<th rowspan="2">l.p.</th>
		<th rowspan="2">Osoba</th>
		<th colspan="3">Razem</th>
		<th colspan="3">Strony</th>
	</tr>
	<tr>
		<th title="liczba stron powyżej ustalonego progu">dobre</th>
		<th title="liczba stron poniżej progu">za małe</th>
		<th title="suma wielkości wszystkich stron na koniec dnia">wielkość</th>
		
		<th>tytuł</th>
		<th title="wielkość strony przy utworzeniu">wlk. początkowa</th>
		<th title="wielkość strony na koniec dnia">wlk. końcowa</th>
	</tr>
	<!--
	<tr>
		<td>[[User:Coś|Coś]]</td>
		
		<td>1</td>
		<td>2</td>
		<td>3</td>
		
		<td>Tytuł</td>
		<td>5</td>
		<td>6</td>
	</tr>
	-->
	<tr>
		<?php $intLP = 0; ?>
		<?php foreach ($arrDNAUserData as &$u) { ?>
			<?php $intRowSpan = count($u['pages']) ?>
			<?php $intLP++; ?>
			<td rowspan="<?=$intRowSpan?>"><?=$intLP?></td>
			<td rowspan="<?=$intRowSpan?>">
				<?php if (!empty($u['user_name'])) { ?>
					<a href="<?=$strPageBaseURL?>User:<?=$u['user_name']?>"><?=strtr($u['user_name'],'_',' ')?></a>
					<span class="extra_links">(<a href="<?=$strPageBaseURL?>Special:Contributions/<?=$u['user_name']?>">wkład</a>)</span>
				<?php } else { ?>
					<i>Anonimowe edycje</i>
				<?php } ?>
			</td>
			
			<td rowspan="<?=$intRowSpan?>"><?=$u['total_ok']?></td>
			<td rowspan="<?=$intRowSpan?>"><?=$u['total_nonok']?></td>
			<td rowspan="<?=$intRowSpan?>"><?=$u['total_len']?></td>
			
			<?php $isFirst=true ?>
			<?php foreach ($u['pages'] as &$p) { ?>
				<?=($isFirst?'':'<tr>')?>
					<?php $strClass=($p['is_ok']?'page_ok':'page_small')?>
					
					<td class="<?=$strClass?>">
						<a href="<?=$strPageBaseURL?><?=$p['page_title']?>"><?=strtr($p['page_title'],'_',' ')?></a>
						<span class="extra_links">(<a href="<?=$strPageBaseURL?><?=$p['page_title']?>?action=history">historia</a>)</span>
					</td>
					<td class="<?=$strClass?>"><?=$p['start_len']?></td>
					<td class="<?=$strClass?>"><?=$p['end_len']?></td>
					
					<?php $isFirst=false ?>
				</tr>
			<?php } ?>
		<?php } ?>
</table>
<?php /*
<pre>
	<?php var_export($arrDNAUserData);?>
</pre>
*/?>