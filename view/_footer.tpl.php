
</div>
<div id="footer">
	<p>Copyright &copy;2010-2023 Maciej Jaros (pl:User:Nux, en:User:Nux)</p>
	<?php if (!empty($arrTicks)) { ?>
		<div id="ticks">
			Czasy wykonania [s]:
			<ul>
				<?php foreach ($arrTicks as $strTickName=>$intDurtation) { ?>
					<li><?=sprintf("<em>%s</em> %.4f", $strTickName, $intDurtation)?></li>
				<?php } ?>
			</ul>
		</div>
	<?php } ?>
</div>
</body>
</html>