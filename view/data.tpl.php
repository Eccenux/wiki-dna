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
		<th title="wielkość strony nakoniec dnia">wlk. końcowa</th>
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
		<? $intLP = 0; ?>
		<? foreach ($arrDNAUserData as &$u) { ?>
			<? $intRowSpan = count($u['pages']) ?>
			<? $intLP++; ?>
			<td rowspan="<?=$intRowSpan?>"><?=$intLP?></td>
			<td rowspan="<?=$intRowSpan?>">
				<? if (!empty($u['user_name'])) { ?>
					<a href="<?=$strPageBaseURL?>User:<?=$u['user_name']?>"><?=strtr($u['user_name'],'_',' ')?></a>
					<span class="extra_links">(<a href="<?=$strPageBaseURL?>Special:Contributions/<?=$u['user_name']?>">wkład</a>)</span>
				<? } else { ?>
					<i>Anonimowe edycje</i>
				<? } ?>
			</td>
			
			<td rowspan="<?=$intRowSpan?>"><?=$u['total_ok']?></td>
			<td rowspan="<?=$intRowSpan?>"><?=$u['total_nonok']?></td>
			<td rowspan="<?=$intRowSpan?>"><?=$u['total_len']?></td>
			
			<? $isFirst=true ?>
			<? foreach ($u['pages'] as &$p) { ?>
				<?=($isFirst?'':'<tr>')?>
					<?$strClass=($p['is_ok']?'page_ok':'page_small')?>
					
					<td class="<?=$strClass?>">
						<a href="<?=$strPageBaseURL?><?=$p['page_title']?>"><?=strtr($p['page_title'],'_',' ')?></a>
						<span class="extra_links">(<a href="<?=$strPageBaseURL?><?=$p['page_title']?>?action=history">historia</a>)</span>
					</td>
					<td class="<?=$strClass?>"><?=$p['start_len']?></td>
					<td class="<?=$strClass?>"><?=$p['end_len']?></td>
					
					<? $isFirst=false ?>
				</tr>
			<? } ?>
		<? } ?>
</table>
<?/*
<pre>
	<?var_export($arrDNAUserData);?>
</pre>
*/?>