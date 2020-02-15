<?php
$today_day = date("d");
$today_mon = date("m");
$today_yea = date("Y");
if (isset($_GET['year']) && isset($_GET['month']) && isset($_GET['day']))
{
if(isset($_GET['year']))
{
$year = $_GET['year'];
if(preg_match('/[^0-9]/i', $year))
{
$formerror = 1;
$msgcode[] = "2";
}
}
if(isset($_GET['month']))
{
$month = $_GET['month'];
if(preg_match('/[^0-9]/i', $month))
{
$formerror = 1;
$msgcode[] = "2";
}
}
if(isset($_GET['day']))
{
$day = $_GET['day'];
if(preg_match('/[^0-9]/i', $day))
{
$formerror = 1;
$msgcode[] = "2";
}
}
if($year.$month.str_pad($day, 2, '0', STR_PAD_LEFT) < $today_yea.$today_mon.$today_day)
{
header("Location: index.php?status=2");
exit;
}
}
elseif (isset($_POST['Submit'])){
$formerror = 0;
if(isset($_POST['year']))
{
$year = $_POST['year'];
if(preg_match('/[^0-9]/i', $year))
{
$formerror = 1;
$msgcode[] = "2";
}
}
else
{
$formerror = 1;
$msgcode[] = "7";
}
if(isset($_POST['month']))
{
$month = $_POST['month'];
if(preg_match('/[^0-9]/i', $month))
{
$formerror = 1;
$msgcode[] = "2";
}
}
else
{
$formerror = 1;
$msgcode[] = "7";
}
if(isset($_POST['day']))
{
$day = $_POST['day'];
if(preg_match('/[^0-9]/i', $day))
{
$formerror = 1;
$msgcode[] = "2";
}
}
else
{
$formerror = 1;
$msgcode[] = "7";
}
$content = $_POST['content'];
if(!empty($content))
{
$content = trim($content, " \t\n\r\0\x0B");
}
if(empty($content))
{
$formerror = 1;
$msgcode[] = "7";
}
if(!preg_match('/^[\w\-\s\.\,\:\-\@]+$/', $content)) {
$formerror = 1;
$msgcode[] = "9";
}
if (strlen($content) < 5)
{
$formerror = 1;
$msgcode[] = "13";
}
elseif(strlen($content) > 100)
{
$formerror = 1;
$msgcode[] = "14";
}
if(isset($msgcode) && in_array('9', $msgcode) && in_array('7', $msgcode))
{
	$msgcode = [];
	$msgcode[] = "7";
}
elseif(isset($msgcode) && in_array('9', $msgcode) && in_array('7', $msgcode))
{
	$msgcode = [];
	$msgcode[] = "7";
}
elseif(isset($msgcode) && in_array('9', $msgcode) && in_array('13', $msgcode))
{
	$msgcode = [];
	$msgcode[] = "9";
}


if($formerror != 1){
$db = new PDO('sqlite:db/Calendar.db');
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $db->prepare("UPDATE Calendar SET EVENT = :Content WHERE TimeStamp = :TimeStamp");
$fulltimestamp = $year . "-" . $month . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
$stmt->bindParam(':TimeStamp', $fulltimestamp);
$stmt->bindParam(':Content', $content);
$stmt->execute();
if(!empty($_COOKIE['destination_page']))
{
if (strpos($_COOKIE['destination_page'], '?') !== false) {
$redirectstatus = "&status=15";
}
else
{
$redirectstatus = "?status=15";
}
header("Location: " . $_COOKIE['destination_page'] . $redirectstatus);
$_COOKIE['destination_page'] = "";
}
else
{
header("Location: index.php?status=15");
}
exit();
}
}
else
{
header("Location: index.php?status=2");
exit;
}
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
<br>
<?php
$db = new PDO('sqlite:db/Calendar.db');
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$statement = $db->prepare("SELECT EVENT FROM Calendar WHERE TimeStamp = :TimeStamp LIMIT 1");
$fulltimestamp = $year . "-" . $month . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
$statement->bindParam(':TimeStamp', $fulltimestamp);
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
  
?>
Edit event for <?php echo dateName($year. "-" . $month . "-" . $day);
function dateName($date)
{
$result = "";
$convert_date = strtotime($date);
$month = date('F',$convert_date);
$year = date('Y',$convert_date);
$name_day = date('l',$convert_date);
$day = date('j',$convert_date);
$result = $name_day . ", " . $month . " " . $day . ", " . $year;
return $result;
}
?>

<form action="events-edit.php" method="POST">
<input type="hidden" name="year" value="<?php echo $year; ?>">
<input type="hidden" name="month" value="<?php echo $month; ?>">
<input type="hidden" name="day" value="<?php echo $day; ?>">
<textarea cols="120" rows="3" id="textbox" name="content" maxlength="100" autofocus>
<?php
if (isset($_POST['Submit'])){
echo $_POST['content'];
}
else
{
echo $result[0]['EVENT'];
}
?>
</textarea>
<br>
<input type="submit" value="Submit" id="submit" class="formbutton" name="Submit">
</form>
</div>
<?php
include "toast-code.php";
?>
</body>
</html>