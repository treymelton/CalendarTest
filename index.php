<?php
require_once('codeheader.php');
require_once('header.php');
echo '<h1>Calendar Competency Test Total time: 6 hours  <img src="images/test.gif" style="height:35px;" /></h1>';
$arrCalendarProperties = array('calendarid'=>'7');
//make our calendar
echo Calendar::Get()->MakeCalendar($arrCalendarProperties);
require_once('footer.php');
?>