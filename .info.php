<?php
/*!
	@mainpage Project description
	
	This project is aimed at getting user-centric article statistics for a specified date.
	@li DNA contest page:
	http://pl.wikipedia.org/wiki/WP:DNA
	@li General stats page:
	http://pl.wikipedia.org/wiki/Wikipedia:Dzie%C5%84_Nowego_Artyku%C5%82u/statystyki
	
	@author
		Copyright ©2010-2012 Maciej Jaros (pl:User:Nux, en:User:Nux)
	@version
		1.2.0
	
	@section License
	
	You can use this program under the terms of any of the below licenses.
	Note that in any case you have to state the author.
	
	@note Logo used in the header was made by Artur Jan Fijałkowski (WarX)
	@note http://pl.wikipedia.org/wiki/Plik:Wikimedia_Community_Logo-Toolserver.svg
	
	@subsection GPL GNU GPL v2
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License as
	published by the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful, but
	WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
	General Public License for more details at:
	http://www.gnu.org/copyleft/gpl.html

	@subsection CCBYSA CC-BY-SA 3.0
	Creative Commons Attribution-ShareAlike 3.0 is described at:
	http://creativecommons.org/licenses/by-sa/3.0/
	
	Polska wersja licencji:
	http://creativecommons.org/licenses/by-sa/3.0/deed.pl
	
	@section TODO
	
	@subsection invalidate
		invalidate user and page-extra data (helpfull when user or page name/title was changed)

	@subsection TODOP3 Maybe someday...
		- Dopisać zliczarkę http://pl.wikipedia.org/wiki/Wikipedia:Liczba_artyku%C5%82%C3%B3w_polskiej_Wikipedii
		- Filter out pages that were redirects at the end of the day? '/^#(?:REDIRECT|PRZEKIERUJ|TAM|PATRZ)/i'; Not really needed due to 2kB limit
*/
?>