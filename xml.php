<?php
/*

Squad XML Generator by Tier1 Operations (http://tier1ops.eu) v0.2

This work is licenced under the Creative Commons by-nc-sa 3.0 Licence
https://creativecommons.org/licenses/by-nc-sa/3.0/deed.en

*/



if (!isCli()) {
// Send the headers
header('Content-type: text/xml');
header('Pragma: public');
header('Cache-control: private');
header('Expires: -1');
  if (isset($_GET["rank"])) {
    $rank = htmlspecialchars(strtolower($_GET["rank"]));
  } else {
    $rank = "grunt";
  }
} else {
  if (isset($argv[1])) {
    $rank = $argv[1];
  } else {
    $rank = "grunt";
  }
}

echo "<?xml version=\"1.0\"?>\n";
echo "<!DOCTYPE squad SYSTEM \"squad.dtd\">\n";
echo "<?xml-stylesheet href=\"".strtolower($rank).".xsl?\" type=\"text/xsl\"?>\n";

require_once('config.inc');

$request = "
SELECT `".$db_prefix."_users`.user_id,
`".$db_prefix."_users`.username,
`".$db_prefix."_profile_fields_data`.user_id,
".$db_prefix."_profile_fields_data.pf_xml_remark,
".$db_prefix."_profile_fields_data.pf_arma_player_id
FROM
`".$db_prefix."_users`,".$db_prefix."_profile_fields_data
WHERE `".$db_prefix."_users`.group_id = '". ranktogroupid($rank) ."' and `".$db_prefix."_profile_fields_data`.user_id = `".$db_prefix."_users`.user_id
ORDER BY cast(username as char) asc, binary username desc";

$dblink   = mysql_connect ($dbserver, $dbuser, $dbpass);
mysql_select_db($database, $dblink);
$userlist = mysql_query($request, $dblink);

echo '<squad nick="'.$squad_tag.' '. ucfirst($rank) .'">\n';
echo '  <name>'.$squad_name.'</name>\n';
echo '  <email>'.$squad_contact_mail.'</email>\n';
echo '  <web>'.$squad_web.'</web>\n';
echo '  <picture>'. strtolower($rank) .'.paa</picture>\n';
echo '  <title>'.$squad_info.'</title>\n';


# Debug
# echo "rank: $rank - Group_ID: ". ranktogroupid($rank) ."\n";
# echo "Numbers: ". mysql_num_rows($userlist) ."\n";
# End Debug

while ($user = mysql_fetch_array($userlist, MYSQL_ASSOC)) {
  $name   = $user["username"];
  $remark = $user["pf_xml_remark"];
  $aID    = $user["pf_arma_player_id"];
  echo "   <member id=\"". $aID ."\" nick=\"". $name ."\">\n";
  echo "      <name>". $name ."</name>\n";
  echo "      <email>".$squad_contact_mail."</email>\n";
  echo "      <icq>N/A</icq>\n";
  echo '      <remark>';
  echo $remark;
  echo '</remark>'. "\n";
  echo "   </member>\n";
}


echo "</squad>\n";

mysql_close($dblink);


function ranktogroupid($rank) {
 $out=9; # Default to Recruits
 switch (ucfirst($rank)) {
  case 'PRCT':
    $out = 8;
    break;
  case 'Recruit':
    $out = 9;
    break;
  case 'Grunt';
    $out = 10;
    break;
  case 'Regular';
    $out = 11;
    break;
  case 'Specialist';
    $out = 12;
    break;
  case 'Corporal';
    $out = 13;
    break;
 }
 return $out;
}


# From: http://www.codediesel.com/php/quick-way-to-determine-if-php-is-running-at-the-command-line/
function isCli() {
 
     if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
          return true;
     } else {
          return false;
     }
}
?>
