<?php if (!empty($strDate2Check)) { ?>
<nav class="mini-menu">
	<ul>
		<li><a href="<?=$strBaseURL?>">Kalendarz</a></li>
		<li><a href="<?=$strBaseURL?><?=$strDate2Check?>">Hierarchiczna lista</a></li>
	</ul>
</nav>
<?php } ?>

<script type="text/javascript" src="./lib/sortable.js?v2"></script>

<table class="sortable">
	<tr>
		<th>Osoba</th>
		<th>Tytuł</th>
		<th title="wielkość strony na koniec dnia">Wielkość</th>
	</tr>
		<?php foreach ($arrDNAUserData as &$u) { ?>
			<?php foreach ($u['pages'] as &$p) { ?>
				<tr>
					<td>
						<?php if (!empty($u['user_name'])) { ?>
							<a href="<?=$strPageBaseURL?>User:<?=urlencode($u['user_name'])?>"><?=strtr($u['user_name'],'_',' ')?></a>
							<span class="extra_links">(<a href="<?=$strPageBaseURL?>Special:Contributions/<?=urlencode($u['user_name'])?>">wkład</a>)</span>
						<?php } else { ?>
							<i>Anonimowe edycje</i>
						<?php } ?>
					</td>
					<td>
						<a href="<?=$strPageBaseURL?><?=urlencode($p['page_title'])?>"><?=strtr($p['page_title'],'_',' ')?></a>
						<span class="extra_links">(<a href="<?=$strPageBaseURL?><?=urlencode($p['page_title'])?>?action=history">historia</a>)</span>
					</td>
					<td><?=$p['end_len']?></td>
				</tr>
			<?php } ?>
		<?php } ?>
</table>
<?php /*
<pre>
	<?php var_export($arrDNAUserData);?>
</pre>
*/?>