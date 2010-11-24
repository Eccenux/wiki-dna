<?
/*!
	@file
	@brief Functions for error-handling stuff
	
	Basic usage:
	
	For SQL
		\code
		trigger_error ('[sql]:'.$zapytanie, E_USER_ERROR);
		\endcode
	Other (DEBUG_MODE = 1)
		\code
		trigger_error ('tekst błędu', E_USER_ERROR);
		trigger_error ('tekst błędu', E_USER_WARNING);
		trigger_error ('tekst błędu', E_USER_NOTICE);
		\endcode
	Showing variables when doing various test
		\code
		trigger_error (myVarDump($zmienna), E_USER_NOTICE);
		or
		bug('zmienna');
		\endcode
			
	@todo parsowanie backtracka
	@todo zmiana sposobu wyświetlania śledzenia (pominięcie trigger_error)
*/

/*
*/

/*
	Stałe wewnętrzne
*/
//
// Parametry pliku do wyświetlania
define ('HANDLER_ERR_LOG', './.err.log');	// scieżka
define ('MAX_LOG_SIZE', 52428800);			// maksymalna wielkosc (tu 50MB)
//
// Tryb debugowania
define ('DEBUG_MODE', 1);	// 1 - ON, 0 - OFF
// Tryb pomocniczy ("chowa" komunikaty)
define ('SILENT_DEBUG_MODE', 1);	// 1 - ON, 0 - OFF
//
//define ('PHP_MANUAL_PATH', 'file:///home/ewa/Documents/prog/php-manual/');
ini_set('docref_root', 'http://pl.php.net/manual/pl/');
ini_set('docref_ext', '.php');

// cheat conv (not needed)
function win2utf8($str)
{
	return $str;
}

/* --------------------------------------------------------- *\
	Funkcja: myVarDump
	
	Zwraca lub wyswietla sformatowany HTML-owo 
	zrzut wartosci zmiennej (także tablicy)
	
	Parametry:
		$var - zmienna do wyswietlenia
		$return - domyslnie true
			true - kod HTML zostanie zwrócony jako wartosc
			false - kod HTML zostanie wyświetlony (zwykłe echo)
\* --------------------------------------------------------- */
function myVarDump($var, $return = true)
{
	$txt = '<div style="white-space: pre">'. wordwrap(htmlspecialchars(var_export($var,true)),90) .'</div>';
	if ($return)
		return $txt;
	else
		echo $txt;
}

/* --------------------------------------------------------- *\
	Funkcja: nuxDump
	
	szybki dump
	
	Parametry:
		$var - wartość zmiennej do wyświetlenia
\* --------------------------------------------------------- */
function nuxDump($var)
{
	trigger_error (myVarDump($var), E_USER_NOTICE);
}

/* --------------------------------------------------------- *\
	Funkcja: bug
	
	szybki śledź ;)
	
	Parametry:
		//$var_name - nazwa zmiennej do "śledzenia"
		$var- nazwa zmiennej do "śledzenia"
\* --------------------------------------------------------- */
function bug($var='TRACE-ONLY')
{
	global $debug_msgtext;

	if (DEBUG_MODE)
	{
		$trace = debug_backtrace();
		$root = str_replace('/','\\',$_SERVER["DOCUMENT_ROOT"]).'\\';

		$errmsg ="<b>[DEBUG]</b><br/>\n";

		$call = $trace[0];
		$file = str_replace($root, '', $call['file']);
		$errmsg .= "\n<b>[{$call['line']}] {$file}</b>";
		if (!empty($call['args']))
		{
			$errmsg .= "\n<br/>value:\n<pre>";
			$errmsg .= htmlspecialchars(var_export($trace[0],true));
			$errmsg .= "</pre>\n";
		}

		//$errmsg .= myVarDump($trace);
		unset($trace[0]); // bieżąca funkcja
		foreach($trace as &$call)
		{
			$fun = empty($call['type']) ? $call['function'] : $call['class'].$call['type'].$call['function'];
			$file = str_replace($root, '', $call['file']);
			
			$errmsg .= "\n<center style='width:150px'> &#8593; &nbsp; &nbsp; &nbsp; &#8593; </center>";
			$errmsg .= "\n<b>[{$call['line']}] {$file}</b>";
			
			// funkcja i jej argumenty
			$errmsg .= "\n<br/>$fun():\n<pre>";
			foreach ($call['args'] as &$arg)
			{
				$errmsg .= htmlspecialchars(var_export($arg,true));
			}
			$errmsg .= "</pre>\n";
			// Bieżący obiekt z parametrami
			if (!empty($call['object']))
			{
				$errmsg .= "\nObj:\n<pre>";
				$errmsg .= htmlspecialchars(var_export($call['object'],true));
				$errmsg .= "</pre>\n";
			}
		}

//		$errmsg = win2utf8($errmsg);

		// zapis do zmiennej z błędami do wyświetlenia
		$debug_msgtext .= '<div>'.$errmsg.'</div>';

		// zapis do pliku
		if (@filesize(HANDLER_ERR_LOG) < MAX_LOG_SIZE)
		{
			$errmsg = html_entity_decode(strip_tags($errmsg));
			$log_debug_msgtext = "\n----------------------------------------------------\n ".date("Y-m-d H:i:s (T)")."\n$errmsg\n----------------------------------------------------";
			error_log ($log_debug_msgtext, 3, HANDLER_ERR_LOG);
		}

	}
}

/* --------------------------------------------------------- *\
	Funkcja: init_myErrorHandler
	
	Inicjowanie obsługi błędów
\* --------------------------------------------------------- */
function init_myErrorHandler()
{
	global $debug_msgtext;
	$debug_msgtext = '';
	error_reporting(E_ALL);
	set_error_handler('myErrorHandler');
}

/* --------------------------------------------------------- *\
	Funkcja: myErrorHandler
	
	Funkcja do obsługi błędów. Wywołanie opisane wczesniej.
	
	Opis ogólny i parametrów na stronie:
	http://pl.php.net/manual/pl/function.set-error-handler.php
	
	Parametry (globalne):
		$debug_msgtext - zmienna przechowująca dotychczasowe błędy 
			"zerowana" przy inicjalizacji (fun. init_myErrorHandler)
\* --------------------------------------------------------- */
function myErrorHandler($errno, $errmsg, $filename, $linenum)
{
	global $debug_msgtext;

	$done = false;
	//
	// Special errors handling
	//
	if ($errno == E_USER_ERROR ||
		$errno == E_USER_WARNING ||
		$errno == E_USER_NOTICE)
	{
		//
		// Check for prefix
		//
		if (preg_match ('/^((\[[a-z]+\])\s*([^:\n]*)\:)?((.|\n)*)$/', $errmsg, $matches))
		{
			switch ($matches[2])
			{
				case '[sql]':
					$errmsg = myErrorHandler_sql($matches[4], $matches[3], $errno, $filename, $linenum);
					if ($errno == E_USER_ERROR)
					{
						printout_html_msg ($errmsg);
					}
					else if ($errmsg!='db sql error')
					{
						$debug_msgtext .= $errmsg;
						$done = true;
					}
				break;
			}
		}
	}
	//
	// Standard error handling
	//
	if (DEBUG_MODE && !$done)
	{
		$new_err_msg = myErrorHandler_std($errno, win2utf8($errmsg), $filename, $linenum);

		$debug_msgtext .= $new_err_msg;
	}
}

/* --------------------------------------------------------- *\
	Funkcja: myErrorHandler_sql
	
	Zwraca sformatowany HTML-owo kod błędu i zapisuje 
	poufne dane do pliku (stała: HANDLER_ERR_LOG).
	
	Parametry:
		$sql - kod zapytania SQL (które wywołało bład)
		$err_info - informacja o błędzie (zwykle z jakiej funkcji - exec | prep)
		$errno - num. typu błędu (E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE)
		$err_file_name - nazwa pliku, w którym wystapił bład
		$err_line_num - numer linii pliku, w miejscu wystapienia błędu
\* --------------------------------------------------------- */
function myErrorHandler_sql ($sql, $err_info, $errno, $err_file_name, $err_line_num)
{
	global $db;
	
	$ret_err_msg = 'db sql error';

	$err_log_file = HANDLER_ERR_LOG;
	if (@filesize($err_log_file)<1024*1024)
	{
		$sql_errno = $db->errno();
		$sql_error = win2utf8($db->error());
		
		$log_msg = "\n--------------------------------\n ". date('D d.m.Y H:i:s (T)')
			. "\n $err_line_num - $err_file_name\n--------------------------------\n";
		
		if (!empty($err_info))
		{
			$log_msg .= "[" . $err_info . "] ";
		}
		
		if ($errno != E_USER_NOTICE && !empty($sql_error))
		{
			if ($sql_errno != -1)	// connection error
			{
				$err_msg = str_replace('Something is wrong in your syntax', 'Błąd w składni', $sql_error);
				
				$log_msg .= "nieprawidłowe zapytanie: \n". $sql;
				$log_msg .= "\nBłąd (" . $sql_errno . "): $err_msg\n";
			}
			else
			{
				$ret_err_msg = 'db connection error';

				$log_msg .= "\nBłąd (" . $sql_errno . "): $sql_error\n";
			}
		}
		else
		{
			$log_msg .= "zapytanie info: \n". $sql;
		}
		@error_log ($log_msg, 3, $err_log_file);
	}

	if (DEBUG_MODE)
	{
		return '<div><pre>' .htmlspecialchars($log_msg). '</pre></div>';
	}
	else
	{
		return '<div class="mymsgdie"><b>Wystąpił błąd bazy danych!</b><br />Jeśli to się powtórzy, to prosimy o kontakt przez e-mail.</div>';
	}
}

/* --------------------------------------------------------- *\
	Funkcja: myErrorHandler_std
	
	Zwraca sformatowany HTML-owo kod błędu i zapisuje 
	poufne dane do pliku (stała: HANDLER_ERR_LOG).
	
	Parametry:
		jak w fun. myErrorHandler
\* --------------------------------------------------------- */
function myErrorHandler_std ($errno, $errmsg, $err_file_name, $err_line_num)
{
	//
	// translation array
	//	
	$errortype = array (
		E_WARNING		=> 'Warning',
		E_NOTICE		=> 'Notice',
		E_USER_ERROR	=> 'User Error',
		E_USER_WARNING	=> 'User Warning',
		E_USER_NOTICE	=> 'User Notice',
	);
	if (!isset($errortype[$errno]))
	{
		$errortype[$errno] = 'Unknown';
	}

	//
	// debug_msgtext
	//
	$file_name = basename($err_file_name);
	$dir_name = dirname($err_file_name);
	$debug_msgtext = "<div>
		<b>{$errortype[$errno]}</b> ($errno): $errmsg<br/>
		In [$dir_name/<b>$file_name</b>] at line ($err_line_num)";
	
	/***
	// get file's lines
	$handle = fopen($err_file_name, 'r');
	$i = 0;
	$file_lines = '';
	while (!feof($handle)) {
		$line = fgets($handle, 4096);
		$i++;
		switch($err_line_num-$i)
		{
			case -1:
			case 1:
				$file_lines .= $line;
			break;
			case 0:
				$file_lines .= rtrim($line). "// ($err_line_num)\n";
			break;
		}
	}
	$debug_msgtext .= '<br/>'.highlight_string ($file_lines, TRUE);
	fclose($handle);
	/**/
	
	// close debug div
	$debug_msgtext .=  "</div>\n\n";

	/**/
	if (@filesize(HANDLER_ERR_LOG) < MAX_LOG_SIZE)
	{
		$log_debug_msgtext = "\n----------------------------------------------------\n ".date("Y-m-d H:i:s (T)")."\n {$errortype[$errno]}($errno): $errmsg\n In [$err_file_name] at line ($err_line_num) \n----------------------------------------------------";
		error_log ($log_debug_msgtext, 3, HANDLER_ERR_LOG);
	}
	/**/
	
	return $debug_msgtext;
}

?>