<?php
$destination_page = preg_replace('/&?status=[^&]*/', '', $_SERVER["REQUEST_URI"]);
setcookie("destination_page", $destination_page, ["expires" => '', "path" => '/', "domain" => "", "secure" => false, "httponly" => true]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Add / Edit Events</title>
<meta name="viewport" content="user-scalable=yes, initial-scale=1, width=device-width">
<meta name="referrer" content="no-referrer">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link rel="icon" href="data:;base64,iVBORw0KGgo=">
<link rel="stylesheet" href="styles.css">
</head>
<body onload="StartTimers()">
<div id="timer"></div>
<div class="sidebar">
<h2>Control Panel</h2>
<nav id="mainnav" itemscope itemtype="http://schema.org/SiteNavigationElement">
<a itemprop="url" href="index.php" class="active"><span itemprop="name">Events</span></a>
</nav>
</div>
<div id="header">
<div id="alignleft">Add / Edit Events</div><div id="alignright"></div>
</div>
<div class="content">
<?php
/**
* @author  Xu Ding
* @website https://www.StarTutorial.com
* @revised by Alessandro Marinuzzi
* @website https://www.alecos.it/
* @revised 10.17.2017
**/
$db = new PDO('sqlite:db/Calendar.db');
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
class Calendar {
/**
** Constructor
**/
public function __construct() {
$this->naviHref = htmlentities($_SERVER['PHP_SELF']);
}
/********************* PROPERTY ********************/
private $dayLabels = array("MON", "TUE", "WED", "THU", "FRI", "SAT", "SUN");
private $currentYear = 0;
private $currentMonth = 0;
private $currentDay = 0;
private $currentDate = null;
private $daysInMonth = 0;
private $naviHref = null;
/********************* PUBLIC **********************/
/**
** Print out the calendar
**/
public function show() {
$year = null;
$month = null;
if (null == $year && isset($_GET['year'])) {
$year = htmlentities($_GET['year'], ENT_QUOTES);
if(!preg_match("/^[0-9-+.]{1,}$/", $year))
{
exit("SQL Injection");
}
} elseif (null == $year) {
$year = date("Y", time());
}
if ((!is_numeric($year)) || ($year == "")) {
$year = date("Y", time());
}
if (null == $month && isset($_GET['month'])) {
$month = htmlentities($_GET['month'], ENT_QUOTES);
if(!preg_match("/^[0-9-+.]{1,}$/", $month))
{
exit("SQL Injection");
}
} elseif (null == $month) {
$month = date("m", time());
}
if ((!is_numeric($month)) || ($month == "")) {
$month = date("m", time());
}
$this->currentYear = $year;
$this->currentMonth = $month;
$this->daysInMonth = $this->_daysInMonth($month, $year);
$content = '<div class="pagination_style">' . "\r\n" . $this->_createNavi() . "\r\n" . '</div>' . "\r\n";
$content .= '<table>' . "\r\n" . '<tr>' . "\r\n" . $this->_createLabels();
$content .= '</tr>' . "\r\n";
$weeksInMonth = $this->_weeksInMonth($month, $year);
// Create weeks in a month
for ($i = 0; $i < $weeksInMonth; $i++) {
// Create days in a week
$content .= '<tr>' . "\r\n";
for ($j = 1; $j <= 7; $j++) {
$content .= $this->_showDay($i * 7 + $j);
}
$content .= '</tr>' . "\r\n";
}
$content .= '</table>' . "\r\n";
return $content;
}
/********************* PRIVATE **********************/
/**
** Create the calendar days
**/
private function _showDay($cellNumber) {
if ($this->currentDay == 0) {
$firstDayOfTheWeek = date('N', strtotime($this->currentYear . '-' . $this->currentMonth . '-01'));
if (intval($cellNumber) == intval($firstDayOfTheWeek)) {
$this->currentDay = 1;
}
}
if (($this->currentDay != 0) && ($this->currentDay <= $this->daysInMonth)) {
$this->currentDate = date('Y-m-d', strtotime($this->currentYear . '-' . $this->currentMonth . '-' . ($this->currentDay)));
$cellContent = $this->currentDay;
$this->currentDay++;
} else {
$this->currentDate = null;
$cellContent = null;
}
$today_day = date("d");
$today_mon = date("m");
$today_yea = date("Y");
global $db;

$class_day = ($cellContent == $today_day && $this->currentMonth == $today_mon && $this->currentYear == $today_yea ? "day today" : "day");
$statement = $db->prepare("SELECT EVENT FROM Calendar WHERE TimeStamp = :TimeStamp LIMIT 1");
$currentyear = $this->currentYear;
$currentmonth = $this->currentMonth;
$fulltimestamp = $currentyear . "-" . $currentmonth . "-" . str_pad($cellContent, 2, '0', STR_PAD_LEFT);
$statement->bindParam(':TimeStamp', $fulltimestamp);
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
if(!empty($result[0]['EVENT']))
{
if ($today_yea.$today_mon.$today_day <= $currentyear.$currentmonth.str_pad($result[0]['EVENT'], 2, '0', STR_PAD_LEFT))
{
$event = '<span class="event" style="word-break: break-all;">' . $result[0]['EVENT'] . '<span><br><a href="events-edit.php?year='.$currentyear.'&month='.$currentmonth.'&day='.$cellContent.'" style="color:#FFF;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M18.363 8.464l1.433 1.431-12.67 12.669-7.125 1.436 1.439-7.127 12.665-12.668 1.431 1.431-12.255 12.224-.726 3.584 3.584-.723 12.224-12.257zm-.056-8.464l-2.815 2.817 5.691 5.692 2.817-2.821-5.693-5.688zm-12.318 18.718l11.313-11.316-.705-.707-11.313 11.314.705.709z" class="svg-add" /></svg></a> &emsp;<a href="events-delete_form.php?year='.$currentyear.'&month='.$currentmonth.'&day='.$cellContent.'" style="color:#FFF;" onclick="javascript:return confirm(\'Delete permanently?\')"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd"><path d="M19 24h-14c-1.104 0-2-.896-2-2v-16h18v16c0 1.104-.896 2-2 2zm-7-10.414l3.293-3.293 1.414 1.414-3.293 3.293 3.293 3.293-1.414 1.414-3.293-3.293-3.293 3.293-1.414-1.414 3.293-3.293-3.293-3.293 1.414-1.414 3.293 3.293zm10-8.586h-20v-2h6v-1.5c0-.827.673-1.5 1.5-1.5h5c.825 0 1.5.671 1.5 1.5v1.5h6v2zm-8-3h-4v1h4v-1z" class="svg-add" /></svg></a>';
}
else
{
$event = '<span class="event" style="word-break: break-all;">' . $result[0]['EVENT'] . '<span><br><a href="events-delete_form.php?year='.$currentyear.'&month='.$currentmonth.'&day='.$cellContent.'" style="color:#FFF;" onclick="javascript:return confirm(\'Delete permanently?\')"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd"><path d="M19 24h-14c-1.104 0-2-.896-2-2v-16h18v16c0 1.104-.896 2-2 2zm-7-10.414l3.293-3.293 1.414 1.414-3.293 3.293 3.293 3.293-1.414 1.414-3.293-3.293-3.293 3.293-1.414-1.414 3.293-3.293-3.293-3.293 1.414-1.414 3.293 3.293zm10-8.586h-20v-2h6v-1.5c0-.827.673-1.5 1.5-1.5h5c.825 0 1.5.671 1.5 1.5v1.5h6v2zm-8-3h-4v1h4v-1z" class="svg-add" /></svg></a>';
}
}
elseif(!empty($cellContent) && $today_yea.$today_mon.$today_day <= $currentyear.$currentmonth.str_pad($cellContent, 2, '0', STR_PAD_LEFT))
{
$event = '<br><a href="events-add.php?year='.$currentyear.'&month='.$currentmonth.'&day='.$cellContent.'" style="color:#FFF;"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm6 13h-5v5h-2v-5h-5v-2h5v-5h2v5h5v2z" class="svg-add" /></svg></a>';
}
else{
$event = "";
}

return '<td class="' . $class_day . '"><span class="number">' . $cellContent . $event . '</span></td>';
}
/**
** Create navigation
**/
private function _createNavi() {
$nextMonth = $this->currentMonth == 12 ? 1 : intval($this->currentMonth)+1;
$nextYear = $this->currentMonth == 12 ? intval($this->currentYear)+1 : $this->currentYear;
$preMonth = $this->currentMonth == 1 ? 12 : intval($this->currentMonth)-1;
$preYear = $this->currentMonth == 1 ? intval($this->currentYear)-1 : $this->currentYear;
return '<span class="pagination-prev"><a href="' . $this->naviHref . '?month=' . sprintf('%02d', $preMonth) . '&amp;year=' . $preYear.'" class="pagination-button left" rel="nofollow">&#x276E; Prev</a></span>' . "" . '<a class="pagination-button-middle middle">' . date('M Y', strtotime($this->currentYear . '-' . $this->currentMonth . '-1')) . '</a>' . "" . '<span class="pagination-next"><a href="' . $this->naviHref . '?month=' . sprintf("%02d", $nextMonth) . '&amp;year=' . $nextYear . '" class="pagination-button right" rel="nofollow">Next &#x276F;</a></span>';
}
/**
** Create calendar week labels
**/
private function _createLabels() {
$content = '';
foreach ($this->dayLabels as $index => $label) {
$content .= '<th class="day-name">' . $label . '</th>' . "\r\n";
}
return $content;
}
/**
** Calculate number of weeks in a particular month
**/
private function _weeksInMonth($month = null, $year = null) {
if (null == ($year)) {
$year = date("Y", time());
}
if (null == ($month)) {
$month = date("m", time());
}
// Find number of days in this month
$daysInMonths = $this->_daysInMonth($month, $year);
$numOfweeks = ($daysInMonths % 7 == 0 ? 0 : 1) + intval($daysInMonths / 7);
$monthEndingDay = date('N',strtotime($year . '-' . $month . '-' . $daysInMonths));
$monthStartDay = date('N',strtotime($year . '-' . $month . '-01'));
if ($monthEndingDay < $monthStartDay) {
$numOfweeks++;
}
return $numOfweeks;
}
/**
** Calculate number of days in a particular month
**/
private function _daysInMonth($month = null, $year = null) {
if (null == ($year)) $year = date("Y",time());
if (null == ($month)) $month = date("m",time());
return date('t', strtotime($year . '-' . $month . '-01'));
}
}
$calendar = new Calendar();
echo $calendar->show();
?>
</div>
<?php
include "toast-code.php";
?>
</body>
</html>