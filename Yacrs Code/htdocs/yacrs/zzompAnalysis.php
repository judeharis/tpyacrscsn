<?php
require_once('config.php');
require_once('lib/database.php');
require_once('lib/shared_funcs.php');

require_once('lib/forms.php');
require_once('lib/questionTypes.php');

$template = new templateMerge('ompAna_template.html');

$thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
$uinfo = checkLoggedInUser();
if(!checkPermission($uinfo, $thisSession))
{
	$out .= '<script language="javascript">;';
	$out .= 'alert("Sorry, Permission Error");';
	$out .= 'window.location = "index.php";';
	$out .= '</script>';
}
else
{
	$responses = response::retrieve_response_matching('question_id', $_REQUEST['qiID']);
	$out .= "<table id='respon' border='1'>". "\n"."<thead>". "\n"."<tr><th>User</th><th>Name</th><th>Response</th><th>respon time</th>". "\n"."</thead>". "\n"."<tbody>". "\n";
	foreach($responses as $r){
		$member = sessionMember::retrieve_sessionMember($r->user_id);
		$out .= "<tr>". "\n"."<td>{$member->userID}</td>". "\n"."<td>{$member->name}</td>". "\n"."<td>{$r->value}</td>". "\n"."<td>".date("Y-m-d H:i:s",$r->time)."</td></tr>". "\n";
	}
	$out .= "</table>". "\n";
}
$template->pageData['table'] = $out;
echo $template->render();
?>