<?php
/*!
	@mainpage Project description
	
	This project is aimed at getting user-centric article statistics for a specified date.
	@li DNA contest page:
	http://pl.wikipedia.org/wiki/WP:DNA
	@li General stats page:
	http://pl.wikipedia.org/wiki/Wikipedia:Dzie%C5%84_Nowego_Artyku%C5%82u/statystyki
	
	@author
		Copyright ©2010 Maciej Jaros (pl:User:Nux, en:User:Nux)
	@version
		0.1.6
	
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
	
	@subsection TODOP1 Most important
		\li User name from revision.rev_user_text (at least for IP's) - maybe save IP number as user id with some prefix e.g. 178.36.5.80 -> X.178.36.5.80
		\li section TODOP1_DONE
		
	@subsection TODOP2 Later
	@subsubsection day-ended
	\code
		if day eneded:
			do as we do (serve most from cache)
		else
			if page data is not fresh enough (more then 30 minutes old?):
				remove cache
				re-calculate (preferably from recent changes)
			else
				serve from cache
	\endcode
	@subsubsection invalidate
		invalidate user and page-extra data (helpfull when user or page name/title was changed)
	@subsubsection other
		section TODOP2_DONE
		
	@if TODOP2_DONE
		@subsection TODOP3 Maybe someday...
			\li Dopisać zliczarkę http://pl.wikipedia.org/wiki/Wikipedia:Liczba_artyku%C5%82%C3%B3w_polskiej_Wikipedii
			\li Filter out pages that were redirects at the end of the day? '/^#(?:REDIRECT|PRZEKIERUJ|TAM|PATRZ)/i'; Not really needed due to 2kB limit
	@endif
*/
?>