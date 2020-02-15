<?php
if(isset($_GET['year']))
{
$year = $_GET['year'];
if(preg_match('/[^0-9]/i', $year))
{
header("Location: index.php?status=2");
exit();
}
}
else
{
header("Location: index.php?status=7");
exit();
}
if(isset($_GET['month']))
{
$month = $_GET['month'];
if(preg_match('/[^0-9]/i', $month))
{
header("Location: index.php?status=2");
exit();
}
}
else
{
header("Location: index.php?status=7");
exit();
}
if(isset($_GET['day']))
{
$day = $_GET['day'];
if(preg_match('/[^0-9]/i', $day))
{
header("Location: index.php?status=2");
exit();
}
}
else
{
header("Location: index.php?status=7");
exit();
}
$db = new PDO('sqlite:db/Calendar.db');
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $db->prepare("DELETE FROM Calendar WHERE TimeStamp = :TimeStamp");
$fulltimestamp = $year . "-" . $month . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
$stmt->bindParam(':TimeStamp', $fulltimestamp);
$stmt->execute();
if(!empty($_COOKIE['destination_page']))
{
if (strpos($_COOKIE['destination_page'], '?') !== false) {
$redirectstatus = "&status=16";
}
else
{
$redirectstatus = "?status=16";
}
header("Location: " . $_COOKIE['destination_page'] . $redirectstatus);
$_COOKIE['destination_page'] = "";
}
else
{
header("Location: index.php?status=16");
}
exit();
?>