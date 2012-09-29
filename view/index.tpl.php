<script type="text/javascript" src="cached_dates.js.php"></script>
<form action="index.php" method="get">
	<div id="kal_shown_with_js_holder" style="display:none">
		<div class="js_kalendarz_holder">
			<table cellspacing="1" cellpadding="0" border="0">
				<caption><span
					class="kal_back_menu"><span
					unselectable="on" class="clickable" id="kal_fbckwrd" onclick="ncalend.decYear()">&#171;</span>&nbsp;<span
					unselectable="on" class="clickable" id="kal_bckwrd" onclick="ncalend.decMonth()">&#139;</span></span
					><span 
					class="kal_fwd_menu"><span 
					unselectable="on" class="clickable" id="kal_fwd" onclick="ncalend.incMonth()">&#155;</span>&nbsp;<span
					unselectable="on" class="clickable" id="kal_ffwd" onclick="ncalend.incYear()">&#187;</span></span
					>&nbsp;<span
					id="kal_head_MonYear">data</span>&nbsp;</caption>
				<thead>
					<!-- weekdays - shortcuts -->
					<tr><th>Pon</th><th>Wto</th><th>&#346;ro</th><th>Czw</th><th>Pi&#261;</th><th>Sob</th><th>Nie</th></tr>
					<!--
					<tr><th>Pn</th><th>Wt</th><th>&#346;r</th><th>Cz</th><th>Pi</th><th>So</th><th>Ni</th></tr>
					-->
				</thead>
				<tbody id="kal_days">
					<!-- 7x6 -->
					<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
					<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
					<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
					<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
					<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
					<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
				</tbody>
			</table>
			<div class="time_holder" style="display:none"><input id="kal_time_field" type="text" size="7" /></div>
		</div>
	</div>
	<div id="kal_shown_without_js_holder">
		<label>Data</label>: 
		<input id="kal_val_godziny"	type="hidden" size="2" />
		<input id="kal_val_minuty"	type="hidden" size="2" />
		<input id="kal_val_sekundy"	type="hidden" size="2" />
			
		<input id="kal_val_rok"		name="Dy" type="text" size="4" />
		<input id="kal_val_miesiac"	name="Dm" type="text" size="2" />
		<input id="kal_val_dzien"	name="Dd" type="text" size="2" />
	</div> 
	<input type="submit" name="submit" />
</form>

<p>Ze względu na kosztowność obliczeń, dostępność statystyk archiwalnych jest ograniczona. Na bieżąco można wyliczać statystyki w zakresie ostatnich zmian. Statystyki wyliczone wcześniej są oznaczone jasnozielonym kolorem i pogrubieniem.</p>

<h2>Zakres bieżących statystyk</h2>

<p>Bieżący zakres dostępnych „ostatnich zmian” (w tym zakresie można wyliczać niedostępne statystyki)</p>
<ul>
	<li>Początek: <?=$arrLocalMaxMinRC['min']?></li>
	<li>Koniec:   <?=$arrLocalMaxMinRC['max']?></li>
</ul>

<p>Zwykle tabela ostatnich zmian sięga około miesiąc wstecz aż w okolice bieżącej godziny. Czasem zdarzają się jednak awarie lub przestoje, które powodują, że zaległości są większe. Jeśli zaległości są większe niż parę godzin, to poinformuj o problemach <a href="https://jira.toolserver.org/">administratorów Toolservera</a>. Zobacz też <a href="http://toolserver.org/~bryan/stats/replag/?cluster=s2#s2-weekly" title="powyżej 4*3.600 wymaga specjalnych działań, powyżej 10.000 jest niepokojące">statystyki replikacji (w sekundach)</a>.</p>

<h2>Statystyki archiwalne</h2>

<p>Priorytetem są tzw. urodziny miesiąca (01-01, 02-02), czyli statystyki z okazji <a href="http://pl.wikipedia.org/wiki/WP:DNA">Dnia Nowego Artykułu</a> &ndash; dlatego też poniżej 2011 roku właściwie tylko takie są dostępne. Natomiast w okolicach 2011-03 została uruchomiona usługa bieżącego przeliczania statystyk i od tego czasu nie powinno być już żadnych dziur.</p>

<p>Prośby wyliczania statystyk dla starych dat proszę zgłaszać przez <a href="http://pl.wikipedia.org/wiki/User_talk:Nux">moją stronę dyskusji</a>.</p>
