<!DOCTYPE html>
<html>
<head>
<title>PHP Sqlite Calendar</title>
<meta name="viewport" content="user-scalable=yes, initial-scale=1, width=device-width">
<link rel="icon" href="data:;base64,iVBORw0KGgo=">
<link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>
<div id="header">
<div id="innerheader">
<h1>PHP Sqlite Calendar</h1>
</div>
</div>
<div id="outer" style="margin-top:50px;">
<div id="inner" style="margin-top:50px;text-align:center;">
<div class="maincontent">
<?php
/**
* @author  Xu Ding
* @website https://www.StarTutorial.com
* @revised by Alessandro Marinuzzi
* @website https://www.alecos.it/
* @revised 10.17.2017
**/
$db = new PDO('sqlite:sqlite-calendar-admin/db/Calendar.db');
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
			if(!preg_match("/^[0-9]{1,4}$/", $year))
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
			if(!preg_match("/^[0-9]{1,2}$/", $month))
				{
					exit("SQL Injection");
				}
	} elseif (null == $month) {
		$month = date("m", time());
	}
	if ((!is_numeric($month)) || ($month == "")) {
		$month = date("m", time());
	}
	$input_date = $year . "-" . $month;
	//$valid_date = date('Y-m');
	$max_date = date('Y-m', strtotime("+3 months", strtotime(date('Y-m'))));
	$min_date = date('Y-m', strtotime("-3 months", strtotime(date('Y-m'))));
	if($input_date > $max_date OR $input_date < $min_date)
	{
		header("location: /events.php");
		exit;
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
	$currentyear = $this->currentYear;
	$currentmonth = $this->currentMonth;
	$fulltimestamp = $currentyear . "-" . $currentmonth . "-" . str_pad($cellContent, 2, '0', STR_PAD_LEFT);
	$result = $db->query("SELECT EVENT FROM Calendar WHERE TimeStamp = '$fulltimestamp' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
	if(empty($result['EVENT']))
	{
	$event = '';
	}
	else
	{
		$event = '<span class="event">' . $result['EVENT'] . '<span>';
	}
	return '<td class="' . $class_day . '"><span class="number">' . $cellContent . $event . '</span></td>
';
	}
	/**
	** Create navigation
	**/
	private function _createNavi() {
	$nextMonth = $this->currentMonth == 12 ? 1 : intval($this->currentMonth)+1;
	$nextYear = $this->currentMonth == 12 ? intval($this->currentYear)+1 : $this->currentYear;
	$preMonth = $this->currentMonth == 1 ? 12 : intval($this->currentMonth)-1;
	$preYear = $this->currentMonth == 1 ? intval($this->currentYear)-1 : $this->currentYear;
	return '<span class="pagination-prev"><a href="' . $this->naviHref . '?month=' . sprintf('%02d', $preMonth) . '&amp;year=' . $preYear.'" class="pagination-button pagination-left" rel="nofollow"><svg width="8" height="8" viewBox="0 0 24 24"><path d="M16.67 0l2.83 2.829-9.339 9.175 9.339 9.167-2.83 2.829-12.17-11.996z"></svg> Prev</a></span>' . "" . '<a class="pagination-button-middle middle">' . date('F Y', strtotime($this->currentYear . '-' . $this->currentMonth . '-1')) . '</a>' . "" . '<span class="pagination-next"><a href="' . $this->naviHref . '?month=' . sprintf("%02d", $nextMonth) . '&amp;year=' . $nextYear . '" class="pagination-button pagination-right" rel="nofollow">Next <svg width="8" height="8" viewBox="0 0 24 24"><path d="M7.33 24l-2.83-2.829 9.339-9.175-9.339-9.167 2.83-2.829 12.17 11.996z"></svg></a></span>';
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
</div>
</div>
<!-- Designed and maintained by Saud Iqbal www.saudiqbal.com -->
</body>
</html>