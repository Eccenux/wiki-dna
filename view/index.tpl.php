<form action="index.php" method="get">
	<div id="kal_shown_with_js_holder" style="display:none">
		<div class="js_kalendarz_holder">
			<table cellspacing="1" cellpadding="0" border="0">
				<caption><span
					class="kal_back_menu"><span
					class="clickable" id="kal_fbckwrd" onclick="ncalend.decYear()">&#171;</span>&nbsp;<span
					class="clickable" id="kal_bckwrd" onclick="ncalend.decMonth()">&#139;</span></span
					><span 
					class="kal_fwd_menu"><span 
					class="clickable" id="kal_fwd" onclick="ncalend.incMonth()">&#155;</span>&nbsp;<span
					class="clickable" id="kal_ffwd" onclick="ncalend.incYear()">&#187;</span></span
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