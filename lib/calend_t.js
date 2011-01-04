/* -------------------------------------------------------- *\
	Calendar
	
	version:	2.0.0
	copyright:	(C) 2004-2008 Maciej Jaros
	license:	GNU General Public License v2,
				http://opensource.org/licenses/gpl-license.php
\* -------------------------------------------------------- */

/* -------------------------------------------------------- *\
	Dodawnie funkcji (tak¿e wielu) na start
\* -------------------------------------------------------- */
if (typeof window.addOnloadHook != 'function')
{
	function addOnloadHook(fun)
	{
		if (window.addEventListener)
		{
			window.addEventListener('load', fun, false);
		}
		else if (window.attachEvent)
		{
			window.attachEvent('onload', fun);
		}
		else
		{
			window.onload=fun;
		}
	}
}

//
// obj - init
var ncalend = new Object();
/* -------------------------------------------------------- *\
    time en(de)coding + calendar - translator
\* -------------------------------------------------------- */
//
// dzien	1 - 31
// miesiac	0 - 11
// rok		-9999 - 9999
ncalend.zegarUpdateDMY = function (dzien,miesiac,rok)
{
	miesiac++;
	if (dzien<10)		dzien	= "0" + dzien;
	if (miesiac<10)	miesiac	= "0" + miesiac;
	
	document.getElementById('kal_val_dzien').value = dzien;
	document.getElementById('kal_val_miesiac').value = miesiac;
	document.getElementById('kal_val_rok').value = rok;
}

//
// for onChange event
//
// this is for an input that will containe time in format 33:33:33 or 33:33
// ":" is not important - might use "." or any else (then a number) e.g 33-33 33.33
ncalend.timeReg3 = /^(\d{1,2}).(\d{1,2}).(\d{1,2})$/;
ncalend.timeReg2 = /^(\d{1,2}).(\d{1,2})$/;
ncalend.timeChange = function ()
{
	var str = "" + document.getElementById('kal_time_field').value;
	var arr = str.match(ncalend.timeReg3);
	if (!arr)
	{
		arr = str.match(ncalend.timeReg2);
		if (arr)	arr[3] = 0
		;
	}
	
	if (arr)
	{
		// fix too big
		arr[1] %= 24;	// hours = 0 - 23
		arr[2] %= 60;	// minutes = 0 - 59
		arr[3] %= 60;	// seconds = 0 - 59

		// set
		document.getElementById('kal_val_godziny').value = arr[1];
		document.getElementById('kal_val_minuty').value = arr[2];
		document.getElementById('kal_val_sekundy').value = arr[3];

		// print it back on screen (with leading zero)
		arr[1] = parseInt(arr[1],10);
		arr[2] = parseInt(arr[2],10);
		arr[3] = parseInt(arr[3],10);
		if (arr[1]<10)	arr[1] = "0" + arr[1]
		;
		if (arr[2]<10)	arr[2] = "0" + arr[2]
		;
		if (arr[3]<10)	arr[3] = "0" + arr[3]
		;
		document.getElementById('kal_time_field').value = "" + arr[1] + ":" + arr[2] + ":" + arr[3];
	}
	else
	{
		alert(ncalend.err_parse_time);
		// document.getElementById('kal_time_field').focus(); // not working
	}
}

/* -------------------------------------------------------- *\
    time en(de)coding
\* -------------------------------------------------------- */
ncalend.initTimeToday = function ()
{
	var today = new Date();

	var godziny = today.getHours();
	var minuty = today.getMinutes();
	var sekundy = today.getSeconds();
	var dzien = today.getDate();
	var miesiac = today.getMonth()+1;
	var rok = today.getYear();
	if (rok<1000)	rok		+=1900;

	if (godziny<10)	godziny	= "0" + godziny;
	if (minuty<10)		minuty	= "0" + minuty;
	if (sekundy<10)	sekundy	= "0" + sekundy;
	if (dzien<10)		dzien	= "0" + dzien;
	if (miesiac<10)	miesiac	= "0" + miesiac;

	document.getElementById('kal_time_field').value = "" + godziny + ":" + minuty + ":" + sekundy;
	document.getElementById('kal_val_godziny').value = godziny;
	document.getElementById('kal_val_minuty').value = minuty;
	document.getElementById('kal_val_sekundy').value = sekundy;
	document.getElementById('kal_val_dzien').value = dzien;
	document.getElementById('kal_val_miesiac').value = miesiac;
	document.getElementById('kal_val_rok').value = rok;

	/*
	//
	// set Current time from the one set through PHP
	hours = parseInt(document.frm.hours.value,10);
	minutes = parseInt(document.frm.minutes.value,10);
	seconds = parseInt(document.frm.seconds.value,10);
	
	if (hours<10)		hours	= "0" + hours;
	if (minutes<10)	minutes	= "0" + minutes;
	if (seconds<10)	seconds	= "0" + seconds;


	document.frm.time.value = "" + hours + ":" + minutes + ":" + seconds;
	*/

	document.getElementById('kal_time_field').onchange = ncalend.timeChange;
}

/* -------------------------------------------------------- *\
    Calendar
\* -------------------------------------------------------- */
ncalend.data = new Date();
/*
ncalend.days_holders;
ncalend.days_holders_bgcolor;
*/

//
// for onMouseOver event
ncalend.MouseOver = function ()
{
	if (this.style.backgroundColor == ncalend.days_holders_bgcolor)
		this.style.backgroundColor = ncalend.days_holders_bgcolor_hover;
}
//
// for onMouseOver event
ncalend.MouseOut = function ()
{
	if (this.style.backgroundColor == ncalend.days_holders_bgcolor_hover)
		this.style.backgroundColor = ncalend.days_holders_bgcolor;
}

//
// for onClick event
ncalend.Click = function ()
{
	var dzien = this.innerHTML;
	var miesiac = ncalend.data.getMonth();
	var rok = ncalend.data.getYear();

	if (dzien<1)		dzien	= 1;
	if (rok<1000)		rok		+=1900;
	ncalend.data.setDate(dzien);
	ncalend.printDate(miesiac,rok);
	
	ncalend.zegarUpdateDMY(dzien,miesiac,rok);

	for (i=ncalend.days_holders.length-1; i>=0; i--)
	{
		ncalend.days_holders.item(i).style.backgroundColor = ncalend.days_holders_bgcolor;
	}
	if (this.innerHTML == dzien)	// not a blank field
	{
		this.style.backgroundColor = ncalend.days_holders_bgcolor_mark;
	}
}

// Selecting the current day
ncalend.SelectClick = function ()
{
	var fake_this;
	var dzien = ncalend.data.getDate();

	for (i=ncalend.days_holders.length-1; i>=0; i--)
	{
		ncalend.days_holders.item(i).style.backgroundColor = ncalend.days_holders_bgcolor;
		if (ncalend.days_holders.item(i).innerHTML==dzien)
		{
			fake_this = ncalend.days_holders.item(i);
		}
	}
	fake_this.style.backgroundColor = ncalend.days_holders_bgcolor_mark;
}

//
// init the Calendar (inners and the header)
ncalend.initKalendarz = function ()
{
	//
	// Show the js version, hide the other one
	//
	document.getElementById('kal_shown_with_js_holder').style.display = 'block';
	document.getElementById('kal_shown_without_js_holder').style.display = 'none';

	/*
	//
	// Set date form user's PHP time
	//
	var today = new Date(lastvis_year, lastvis_month, lastvis_day);
	data.setTime(today.getTime());
	*/

	ncalend.days_holders = document.getElementById("kal_days").getElementsByTagName('td');

	//
	// init the bgcolors vars
	//
	// get the default, reinit hover, reinit mark, reset to default
	// reinit is done to avoid different names for colors
	ncalend.days_holders_bgcolor = ncalend.days_holders.item(0).style.backgroundColor;
	ncalend.days_holders.item(0).style.backgroundColor = ncalend.days_holders_bgcolor_hover;
	ncalend.days_holders_bgcolor_hover = ncalend.days_holders.item(0).style.backgroundColor;
	ncalend.days_holders.item(0).style.backgroundColor = ncalend.days_holders_bgcolor_mark;
	ncalend.days_holders_bgcolor_mark = ncalend.days_holders.item(0).style.backgroundColor;
	ncalend.days_holders.item(0).style.backgroundColor = ncalend.days_holders_bgcolor;
	
	rok = ncalend.data.getYear();
	if (rok<1000)	rok+=1900;
	ncalend.printDate(ncalend.data.getMonth(),rok);
	ncalend.fillKalendarz();

	for (i=ncalend.days_holders.length-1; i>=0; i--)
	{
		ncalend.days_holders.item(i).onclick = ncalend.Click;
		ncalend.days_holders.item(i).onmouseover = ncalend.MouseOver;
		ncalend.days_holders.item(i).onmouseout = ncalend.MouseOut;
	}
}

//
// month	0 - 11
// year		-9999 - 9999
ncalend.maxDayMY = function (month,year)
{
	var day;
	
	// till july
	if (month<7)	// month from 0
	{
		// even (note: january=0 and is even)
		if (month%2==0)
			day = 31;
		// odd, but not february
		else if (month!=1)
			day = 30;
		// february in leap year
		else if (year%4==0 && year%100!=0 || year%400==0)
			day = 29;
		// february in normal year
		else
			day = 28;
	}
	// from april
	else
	{
		// even
		if (month%2==0)
			day = 30;
		// odd
		else if (month!=1)
			day = 31;
	}
	
	return day;
}

//
// month	0 - 11
// year		-9999 - 9999
ncalend.printDate = function (month,year)
{
	document.getElementById("kal_head_MonYear").innerHTML = "" + ncalend.data.getDate() + "&nbsp;" + ncalend.months[month] + "&nbsp;" + year;
}

//
// Fill out the inners of the calendar
ncalend.fillKalendarz = function ()
{
	var temp = new Date(ncalend.data);
	temp.setDate(1);
	var fstday = temp.getDay();
	if (fstday==0)
		fstday=7;

	temp.setDate(31);	// ostatni maksimum
	var lstday = temp.getDate();
	if (lstday<20)			// jesli przekrecil sie licznik, to
		lstday=31-lstday;	// ostatni = max - przekret
	lstday = lstday+fstday-1;
	//
	// oczyszczanie
	//
	// przedpola
	for (i=0; i<fstday-1; i++)
	{
		ncalend.days_holders.item(i).innerHTML = "";
	}
	// zapola
	for (i=ncalend.days_holders.length-1; i>=lstday; i--)
	{
		ncalend.days_holders.item(i).innerHTML = "";
	}

	//
	// wypisanie dni
	//
	for (day=0, i=fstday-1; i<lstday; i++)
	{
		day++;
		ncalend.days_holders.item(i).innerHTML = day;
	}

	ncalend.SelectClick();
}

//
// decrease year - update the header and the clock
ncalend.decYear = function ()
{
	// get updated year
	rok = ncalend.data.getYear()-1;
	if (rok<1000)	rok+=1900;
	// get month
	miesiac = ncalend.data.getMonth();

	// correct day if needed
	dzien = ncalend.data.getDate();
	if (dzien>28)	// lowest max
	{
		tmp = ncalend.maxDayMY(miesiac,rok);
		if (tmp<dzien)
		{
			dzien = tmp;
			ncalend.data.setDate(dzien);
		}
	}
	
	// set year
	ncalend.data.setYear(rok);

	// update
	ncalend.fillKalendarz();
	ncalend.printDate(miesiac,rok);
	ncalend.zegarUpdateDMY(dzien,miesiac,rok);
}

//
// increase year - update the header and the clock
ncalend.incYear = function ()
{
	// get updated year
	rok = ncalend.data.getYear()+1;
	if (rok<1000)	rok+=1900;
	// get month
	miesiac = ncalend.data.getMonth();

	// correct day if needed
	dzien = ncalend.data.getDate();
	if (dzien>28)	// lowest max
	{
		tmp = ncalend.maxDayMY(miesiac,rok);
		if (tmp<dzien)
		{
			dzien = tmp;
			ncalend.data.setDate(dzien);
		}
	}
	
	// set year
	ncalend.data.setYear(rok);

	// update
	ncalend.fillKalendarz();
	ncalend.printDate(miesiac,rok);
	ncalend.zegarUpdateDMY(dzien,miesiac,rok);
}

//
// decrease month - update the header and the clock
ncalend.decMonth = function ()
{
	// get year
	rok = ncalend.data.getYear();
	if (rok<1000)	rok+=1900;
	// get updated month
	miesiac = ncalend.data.getMonth()-1;

	// correct year (and month) if needed
	// the day should stay the same (border months have 31 days)
	if (miesiac<0)		// styczen -> grudzien
	{
		miesiac=11;
		rok--;
		ncalend.data.setYear(rok);
	}

	// correct day if needed
	dzien = ncalend.data.getDate();
	if (dzien>28)	// lowest max
	{
		tmp = ncalend.maxDayMY(miesiac,rok);
		if (tmp<dzien)
		{
			dzien = tmp;
			ncalend.data.setDate(dzien);
		}
	}
	
	// set month
	ncalend.data.setMonth(miesiac);

	// update
	ncalend.fillKalendarz();
	ncalend.printDate(miesiac,rok);
	ncalend.zegarUpdateDMY(dzien,miesiac,rok);
}
//
// increase month - update the header and the clock
ncalend.incMonth = function ()
{
	// get year
	rok = ncalend.data.getYear();
	if (rok<1000)	rok+=1900;
	// get updated month
	miesiac = ncalend.data.getMonth()+1;

	// correct year (and month) if needed
	// the day should stay the same (border months have 31 days)
	if (miesiac>11)	// grudzien -> styczen
	{
		miesiac=0;
		rok++;
		ncalend.data.setYear(rok);
	}

	// correct day if needed
	dzien = ncalend.data.getDate();
	if (dzien>28)	// lowest max
	{
		tmp = ncalend.maxDayMY(miesiac,rok);
		if (tmp<dzien)
		{
			dzien = tmp;
			ncalend.data.setDate(dzien);
		}
	}
	
	// set month
	ncalend.data.setMonth(miesiac);

	// update
	ncalend.fillKalendarz();
	ncalend.printDate(miesiac,rok);
	ncalend.zegarUpdateDMY(dzien,miesiac,rok);
}

/* -------------------------------------------------------- *\
	Init calendar and time controls
\* -------------------------------------------------------- */
addOnloadHook(ncalend.initKalendarz);
addOnloadHook(ncalend.initTimeToday);

/* -------------------------------------------------------- *\
	Extras
\* -------------------------------------------------------- */
//
// Calendar time 2 PHP timestamp
//
ncalend.getDTFromCalend = function ()
{
	var godziny = document.getElementById('kal_val_godziny').value;
	var minuty = document.getElementById('kal_val_minuty').value;
	var sekundy = document.getElementById('kal_val_sekundy').value;
	var dzien = document.getElementById('kal_val_dzien').value;
	var miesiac = document.getElementById('kal_val_miesiac').value;
	var rok = document.getElementById('kal_val_rok').value;
	if (rok<1000)	rok+=1900;

	var dt = new Date();
	dt.setHours(godziny);
	dt.setMinutes(minuty);
	dt.setSeconds(sekundy);
	dt.setDate(dzien);
	dt.setMonth(miesiac-1);
	dt.setYear(rok);
	
	return dt;
}

//
// Calendar time 2 PHP timestamp
//
ncalend.Cal2PHPtime = function (out_el_id)
{
	var dt = ncalend.getDTFromCalend();
	
	//php time
	document.getElementById(out_el_id).value = Math.floor(dt.getTime()/1000);
}
//
// PHP timestamp 2 simple readable format
//
ncalend.PHPtime2readable = function (php_el_id, out_el_id)
{
	var today = new Date();
	var rok_teraz = today.getYear();
	if (rok_teraz<1000)	rok_teraz+=1900;
	
	today.setTime(document.getElementById(php_el_id).value * 1000);
	var godziny = today.getHours();
	var minuty = today.getMinutes();
	var sekundy = today.getSeconds();
	var dzien = today.getDate();
	var miesiac = today.getMonth()+1;
	var rok = today.getYear();
	
	var out_el = document.getElementById(out_el_id);
	out_el.value = "";
	if (godziny<10)		out_el.value = "0";
	out_el.value += godziny + ":";
	if (minuty<10)		out_el.value += "0";
	out_el.value += minuty + ":";
	if (sekundy<10)		out_el.value += "0";
	out_el.value += sekundy + "  ";
	if (dzien<10)		out_el.value += "0";
	out_el.value += dzien + ".";
	if (miesiac<10)		out_el.value += "0";
	out_el.value += miesiac + ".";
	if (rok<1000)		rok+=1900;
	out_el.value += rok + "r.";
}

//
// Calendar time 2 Java like timetext
//
ncalend.Cal2Javatime = function (out_el_id)
{
	var dt = ncalend.getDTFromCalend();

	//Java-like time
	txt = dt.toString();
	txt = txt.replace(/(.*?[0-9]{2}) ([0-9]{4}) ([0-9:]+) (.*)/,
		function (a, start, y, t, tzorig)
		{
			var tztxt;
			var tz=-dt.getTimezoneOffset()/60
			switch (tz)
			{
				case 1:
					tztxt = 'CET';
				break;
				case 2:
					tztxt = 'CEST';
				break;
				default:
					tztxt = tzorig;
				break;
			}
			return start + " " +t+ " " +tztxt+ " " + y;
		}
	);

	document.getElementById(out_el_id).value = txt;
}
