<?php
$REQUIRED_MASK = 25165896;
$txt['tea_getchar'] = 'Get Characters';

require_once("Sources/TEAC.php");
$teac = new TEAC;
$details = $teac->get_api_details($_GET['userid'], $_GET['api']);
if(is_null($details["mask"])){
	echo 'API key not found<Br><select name="tea_char"><option value="-">-</option>';
} elseif($details["type"] != "Account") {
	echo 'API key must Account type.<br/><select name="tea_char"><option value="-">-</option>';
} elseif((intval($details["mask"]) & $REQUIRED_MASK) != $REQUIRED_MASK) {
	echo 'API key does not include all of the required access. Use <a href="https://support.eveonline.com/api/Key/CreatePredefined/'.$REQUIRED_MASK.'">this</a>.<br/><select name="tea_char"><option value="-">-</option>';
} else {

	$chars = $teac -> get_api_characters($_GET['userid'], $_GET['api']);

	if(!empty($chars))
	{
		echo '<select name="tea_charid" id="tea_charid" >';
		foreach($chars as $char)
		{
			echo '<option value="'.$char['charid'].'">'.$char['name'].'</option>';
		}
	}
	else
	{	
		$error = $teac -> get_error($teac -> data);
		echo 'Error '.$error[0].' ('.$error[1].')<Br><select name="tea_char"><option value="-">-</option>';
	}
}
echo '</select> <button type="button" onclick="javascript: getchars()">'.$txt['tea_getchar'].'</button>';
?>
