<?php
/*!
	@page maininfo Basic information
	
	Articles stats for a specified date [[:pl:WP:DNA]].
	
	General stats:
	http://pl.wikipedia.org/wiki/Wikipedia:Dzie%C5%84_Nowego_Artyku%C5%82u/statystyki
	
	@author
		Copyright ©2010 Maciej Jaros (pl:User:Nux, en:User:Nux)
	@version
		0.0.3
	
	@section License
	
	You can use this program under the terms of any of the below licenses.
	Note that in any case you have to state the author.
	
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
		\li Deny request for calculating future and present dates
		\li Filter out pages that are not in the main namespace (any way to check in which namespace they were?)
		\li Form data to be user-centric
		\li Header and footer template (include tick in the footer)
		\li Display data template
	@subsection TODOP2 Later
	@subsubsection day-ended
		if day eneded:
			do as we do (serve most from cache)
		else
			if page data is not fresh enough (more then 30 minutes old?):
				remove cache
				re-calculate
			else
				serve from cache
	@subsubsection invalidate
		invalidate user and page-extra data (helpfull when user or page name/title was changed)
		
	@subsection TODOP3 Maybe someday...
		Filter out pages that were redirects at the end of the day? '/^#(?:REDIRECT|PRZEKIERUJ|TAM|PATRZ)/i'
		Not really needed due to 2kB limit
*/
?>