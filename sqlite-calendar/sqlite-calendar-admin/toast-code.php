<?php
if (isset($_GET['status'])) {
$msgcode[] = $_GET['status'];
}
if(isset($msgcode))
{
$codes=array(
1 => array('No message to display.', '#ff2424'),
2 => array('SQL injection detected in input.', '#ff2424'),
3 => array('Invalid ID detected', '#333'),
4 => array('Link cannot be empty', '#ff2424'),
5 => array('Description cannot be empty', '#ff2424'),
6 => array('Invalid URL detected', '#ff2424'),
7 => array('Main content cannot be empty', '#ff2424'),
8 => array('Invalid IP Address detected', '#ff2424'),
9 => array('Invalid characters in input', '#ff2424'),
10 => array('Input is too short, minimum is 10 characters (160 max)', '#ff2424'),
11 => array('Input is too long, maximum is 160 characters (10 min)', '#ff2424'),
12 => array('Mobile version of this web site is not supported', '#ff2424'),
13 => array('Input is too short, minimum is 5 characters (100 max)', '#ff2424'),
14 => array('Input is too long, maximum is 100 characters (5 min)', '#ff2424'),
15 => array('Success! Content has been saved successfully.', '#009000'),
16 => array('Success! Event has been successfully deleted.', '#009000'),
);
foreach($msgcode as $toastcode)
{
$message = $codes[$toastcode][0];
$messagecolor = $codes[$toastcode][1];
echo <<<EOD
<script src="js/toastify.js"></script>
<script>
var myToast = Toastify({text: "$message", duration: 5000, gravity: "bottom", position: 'center', close: false, backgroundColor: "$messagecolor",}); myToast.showToast();
</script>

EOD;
}
}
?>