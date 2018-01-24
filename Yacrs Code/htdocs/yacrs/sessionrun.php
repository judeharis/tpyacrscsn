<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/forms.php');
require_once('lib/database.php');
require_once('lib/ajax.php');
require_once('lib/shared_funcs.php');
include_once('corelib/mobile.php');
$template = new templateMerge($TEMPLATE);
if($deviceType=='mobile')
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?mode=computer'>Use computer mode</a>";
else
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?mode=mobile'>Use mobile mode</a>";

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '<li><a href="index.php">YACRS</a></li>';
$template->pageData['breadcrumb'] .= '<li>Run a Session</li>';
$template->pageData['breadcrumb'] .= '</ul>';


$thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
//if(($uinfo==false)||($thisSession == false)||(!$thisSession->isStaffInSession($uinfo['uname'])))
if(!checkPermission($uinfo, $thisSession))
{
    header("Location: index.php");
    exit();
}
else
{
    session_start();
    CheckDaySelect();
    $template->pageData['afterContent'] = getAJAXScript();
	//$template->pageData['mainBody'] = '<pre>'.print_r($uinfo,1).'</pre>';
    if(isset($_REQUEST['activate'])){
        if($thisSession->questionMode == 0)
        {
            activateSingleQu($thisSession, $_REQUEST['activate']);
        }
        else
        {
	        $thisSession->extras[currentQuestions] = $_REQUEST['qiid'];
            $thisSession->update();
        }
        if(($thisSession->ublogRoom>0)&&($_REQUEST['activate']>0))
        {
	        $msg = new message();
	        $msg->user_id = 0;
	        $msg->posted = time();
	        $msg->message = "<a href='vote.php?sessionID={$thisSession->id}'>Question available.</a>";
	        $msg->session_id = $thisSession->id;
	        $msg->insert();
        }
    }elseif(isset($_REQUEST['deactivate'])){
	    $thisSession->extras[currentQuestions] = array();
        $thisSession->update();
    }



    $aqform = new addQuestion_formv2($thisSession->id);
    if($aqform->getStatus()==FORM_SUBMITTED_VALID){
        $questions = isset($_REQUEST['qz']) ? $_REQUEST['qz'] : false;
        if($questions){
            foreach ($questions as &$q) {
                $json_questions = json_decode($q);
                $theQu = new question();
                $theQu->title = $json_questions->question;
                if($json_questions->questiontype=="mcq"){
                    $definition="";
                    for($i=1; $i< ($json_questions->choicecounter)+1;$i++ ){
                        if($json_questions->{'choicechecked'.$i} =="true"){
                            $definition.="*";
                        };
                        $definition.= $json_questions->{'choice'.$i};
                        $definition.="\n";
                    }
                    $theQu->definition = new basicQuestion($theQu->title, true, $definition);
                    $theQu->id=1;
                }else{
                    $wordLimit = $json_questions->wordlimit;
                    $theQu->definition = new TextQuestion($theQu->title, true, $wordLimit);
                    $theQu->id=2;
                }
                $thisSession->addQuestion($theQu);
            }
        }
    }

    $eqform = new editQuestion_form($thisSession->id);

    if($eqform->getStatus()==FORM_SUBMITTED_VALID){
        echo "ll";
        $questions = isset($_REQUEST['qz']) ? $_REQUEST['qz'] : false;
        $qi = questionInstance::retrieve_questionInstance($_REQUEST['$qiID']);
        if($questions){
            foreach ($questions as &$q) {
                $json_questions = json_decode($q);
                $theQu = new question();
                $theQu->title = $json_questions->question;
                echo $json_questions->questiontype;
                if($json_questions->questiontype=="mcq"){
                    $definition="";
                    for($i=1; $i< ($json_questions->choicecounter)+1;$i++ ){
                        if($json_questions->{'choicechecked'.$i} =="true"){
                            $definition.="*";
                        };
                        $definition.= $json_questions->{'choice'.$i};
                        $definition.="\n";
                    }
                    $theQu->definition = new basicQuestion($theQu->title, true, $definition);
                    $theQu->id=1;
                }else{
                    $wordLimit = $json_questions->wordlimit;
                    $theQu->definition = new TextQuestion($theQu->title, true, $wordLimit);
                    $theQu->id=2;
                }
                $qi->theQuestion_id = $theQu->id;
                $qi->title = $theQu->title;
                $qi->qidefinition = $theQu->definition;
                $qi->update();
            }
        }
    }

    $template->pageData['mainBody'] = '<input type="hidden" name="sessID" value="'.$thisSession->id.'" />';
    $template->pageData['mainBody'] .= "<h1 style='text-align:center;'>Session ID: {$thisSession->id}</h1>";
    $userCount = sessionMember::count("session_id", $thisSession->id);
    $activeCount = sessionMember::countActive($thisSession->id);
    $template->pageData['mainBody'] .= "<p><a href='sessionmembers.php?sessionID={$thisSession->id}'>Active users (total users): $activeCount ($userCount)</a></p>";
    $template->pageData['mainBody'] .= DaySelectForm($thisSession->id);
    $template->pageData['mainBody'] .= "<h2>Session Questions</h2>";


    if(isset($_REQUEST['moveitem']))
    {
        performMove($thisSession);
    }
    elseif(isset($_REQUEST['delete']))
    {
        deleteQi($thisSession);
    }


    $quTitles = array();

    if(strlen(trim($thisSession->questions)))
    {

        $template->pageData['mainBody'].=sidebar($thisSession);
        // if($thisSession->questionMode == 0)
        // {
        //     $template->pageData['mainBody'] .= getQuestionTableSingleQu($thisSession, $quTitles, $_SESSION['showday']);
        //     echo "<script>console.log('0');</script>";
        // }
        // else
        // {
        //     $template->pageData['mainBody'] .= getQuestionTableMultipleQu($thisSession, $quTitles, $_SESSION['showday']);
        // }
    }
    else
        $template->pageData['mainBody'] .= "<p>No questions added yet.</p>";

    $template->pageData['mainBody'] .='<button id="showaddq" onclick="showaddq()"class="btn" type="button" value="">Make New Question</button>';
    $template->pageData['mainBody'] .='<div id="box" name="mainbit">';

    //$template->pageData['mainBody'] .= $aqform->getHtml();
    $template->pageData['mainBody'] .='</div>';
    if($thisSession->questionMode == 0){
        $template->pageData['mainBody'] .= "<div><a href='switchmode.php?sessionID={$thisSession->id}'>Close question and switch to student paced (multi-question) mode.</a></div>";
    }else{
        $template->pageData['mainBody'] .= "<div><a href='switchmode.php?sessionID={$thisSession->id}'>Close questions and switch to teacher paced (single question) mode.</a></div>";
    }


    if(sizeof($quTitles))
    {
        //$template->pageData['mainBody'] .= "<p><a href='export.php?sessionID={$thisSession->id}'>Export response data (CSV)</a></p>";
        $template->pageData['mainBody'] .= "<form action='export.php' method='POST' class='form-horizontal form-export-data'><input type='hidden' name='sessionID' value='{$thisSession->id}'/>";
        $template->pageData['mainBody'] .= "<div class='form-group'><label class='col-sm-4 control-label'>Export Response Data</label><div class='col-sm-8'>";
         $template->pageData['mainBody'] .= "<div class='row'><div class='col-sm-2'><label for='from' class='control-label'>From</label></div><div class='col-sm-10'><select name='from' id='from' class='form-control'>";
        $cday = '';
        foreach($quTitles as $qt)
        {
            if($qt['day']==$cday)
                $template->pageData['mainBody'] .= "\n<option value='{$qt['id']}'>{$qt['title']}</option>";
            else
            {
                $template->pageData['mainBody'] .= "\n<option value='{$qt['id']}'>{$qt['title']} ({$qt['day']})</option>";
                $cday = $qt['day'];
            }
        }
        $template->pageData['mainBody'] .= "</select></div></div><div class='row'><div class='col-sm-2'><label class='control-label' for='to'>To</label></div><div class='col-sm-10'>";
        $template->pageData['mainBody'] .= "<select name='to' id='to' class='form-control'><option value='".$quTitles[sizeof($quTitles)-1]['id']."'></option>";
        $cday = '';
        foreach($quTitles as $qt)
        {
            if($qt['day']==$cday)
                $template->pageData['mainBody'] .= "\n<option value='{$qt['id']}'>{$qt['title']}</option>";
            else
            {
                $template->pageData['mainBody'] .= "\n<option value='{$qt['id']}'>{$qt['title']} ({$qt['day']})</option>";
                $cday = $qt['day'];
            }
        }
        $template->pageData['mainBody'] .= "</select></div></div></div><div class='form-group'><label class='col-sm-4 control-label'>Include in Export</label><div class='col-sm-8'>";
        $template->pageData['mainBody'] .= "<div class='checkbox'><label><input type='checkbox' name='responses'  value='1' checked='checked'/> Responses</label></div>";
        $template->pageData['mainBody'] .= "<div class='checkbox'><label><input type='checkbox' name='scores'  value='1' checked='checked'/> Question Scores</label></div>";
        $template->pageData['mainBody'] .= "<div class='checkbox'><label><input type='checkbox' name='catsco'  value='1' checked='checked'/> Category Scores</label></div>";
        $template->pageData['mainBody'] .= "<div class='checkbox'><label><input type='checkbox' name='custrep'  value='1'/> Custom Report</label></div></div></div>";

        $template->pageData['mainBody'] .= "<div class='control-group'><div class='col-sm-8 col-sm-push-4'><input type='submit' class='btn btn-primary' value='Export'/></div></div></div></div></form>";

    }





	$template->pageData['logoutLink'] = loginBox($uinfo);
}
if(!isset($_REQUEST['ajax'])){
    echo'<script type="text/javascript" src="js/jquery-3.2.1.js"></script>';
    echo'<script src="js/script.js" type="text/javascript"></script>';
    echo $template->render();
}else{
    if(isset($_REQUEST['showaddq'])){
        echo $aqform->getHtml();
    }
    if(isset($_REQUEST['showquestion'])){
        $qi = questionInstance::retrieve_questionInstance($_REQUEST['$qiID']);
        $other = $qi->qidefinition;
        $qu ="";
        if ($qi->qidefinition->quType){
            $qu .= '{"questiontype":"mcq"';
            $qu .= ',"question":"' .$qi->title.'"';
            $length = count($qi->qidefinition->options);
            $choicecounter=0;
            for ($i = 0; $i < $length; $i++) {
                $choicecounter++;
                $qu.= ',"choice'.($i+1).'":"'.$qi->qidefinition->options[$i].'"';
                $qi->qidefinition->correct[$i] ==1 ? $qu .=',"choicechecked'.($i+1).'":"true"':$qu .=',"choicechecked'.($i+1).'":"false"';
            }
             $qu .=',"choicecounter":"'.$choicecounter.'"}';
        }else{
            $qu .='{"questiontype":"opm"';
            $qu .= ',"question":"' .$qi->title.'"';
            $qu .= ',"wordlimit":"' .$qi->qidefinition->wordLimit .'"}';
        }
        $data = array($qu,$eqform->getHtml(),$other);
        echo json_encode($data);

    }
    if(isset($_REQUEST['activateqs'])){
        echo $_REQUEST['qiactivatelist'];
        if($thisSession->questionMode == 0){
            activateSingleQu($thisSession, $_REQUEST['qiID']);
        }else{
            $thisSession->extras[currentQuestions] = $_REQUEST['qiactivatelist'];
            $thisSession->update();
        }

    }
    if(isset($_REQUEST['deactivateqs'])){
        echo $_REQUEST['qideactivatelist'];
        $thisSession->extras[currentQuestions] = array_merge(array_diff($thisSession->extras[currentQuestions], $_REQUEST['qideactivatelist']));
        $thisSession->update();
    }

}




function sidebar(&$thisSession){
    $out="";
    $out .= '<div>';
    $quTitles = array();
    $qiIDs = explode(',',$thisSession->questions);
    $qunum=0;

    foreach($qiIDs as $qiID){
        $qunum++;
        $qi = questionInstance::retrieve_questionInstance($qiID);
        if ($thisSession->extras[currentQuestions] != null && in_array($qiID, $thisSession->extras[currentQuestions]) || $thisSession->currentQuestion == $qiID) {
            $out.='<button id="showquestion" style="width:30%;background-color: #4CAF50;"class="btn" type="button" value="'.$qiID.'">Q'.$qunum.':'.$qi->title.'</button>';
            $out.='<button id="editbtn"  style=" background-color: white;color: black;border: 2px solid #4CAF50;" class="btn" type="button" value="'.$qiID.'">Edit</button>';
        }else{
            $out.='<button id="showquestion" style="width:30%;" class="btn" type="button" value="'.$qiID.'">Q'.$qunum.':'.$qi->title.'</button>';
            $out.='<button id="editbtn"  style=" background-color: white;color: black;border: 2px solid;" class="btn" type="button" value="'.$qiID.'">Edit</button>';
        }

        // $out.='<button id="editbtn"  style=" background-color: white;color: black;border: 2px solid #4CAF50;" class="btn" type="button" value="'.$qiID.'">Edit</button>';
        if($thisSession->questionMode == 1){
            $out.= '<input  type="checkbox" name="qactivate" value="'.$qiID.'"><br>';
        }else{
            $out.='<button id="makeqactive"  style=" background-color: white;color: black;border: 2px solid;" class="btn" type="button" value="'.$qiID.'">Activate</button>';
        }
    }
    if($thisSession->questionMode == 1){
        $out .= '<button id="makeqactive" class="btn" type="button" value="">Activate Selected</button>';
        $out .= '<button id="qdeactivate" class="btn" type="button" value="">Deactivate Selected</button>';
    }
    $out .= '</div>';
    return $out;

}





function getAJAXScript()
{
	return "<script lang=\"JavaScript\">
    function httpGet(theUrl)
    {
        var xmlHttp = null;

    	if (window.XMLHttpRequest)
    	  {// code for IE7+, Firefox, Chrome, Opera, Safari
    	  xmlHttp=new XMLHttpRequest();
    	  }
    	else
    	  {// code for IE6, IE5
    	  xmlHttp=new ActiveXObject(\"Microsoft.XMLHTTP\");
    	  }
        xmlHttp.open( \"GET\", theUrl, false );
        xmlHttp.send( null );
        return xmlHttp.responseText;
    }

    function EditTitle(id)
    {
        name = \"title\"+id;
        document.getElementById(name).innerHTML = \"<input type='text' id='edt' size='60' maxlength='80' value='\"+document.getElementById('title'+id+'_txt').innerHTML+\"'/><a OnClick='UpdateTitle(\\\"\"+id+\"\\\");'>Update</a>\";
        document.getElementById('edt').onkeydown = function(e)
        {
            if(e.keyCode == 13)
            {
                UpdateTitle(id);
            }
        };
        return false;
    }

    function UpdateTitle(id)
    {
        var updateURL = 'updateTitle.php?qiID='+id+'&text='+encodeURIComponent(document.getElementById('edt').value);
        var text = httpGet(updateURL);
        var name = \"title\"+id;
        document.getElementById(name).innerHTML = \"<span id='title\"+id+\"_txt'>\"+text + \"</span>&nbsp;<a OnClick='EditTitle(\\\"\"+id+\"\\\");'>(Edit)</a></td></tr></table>\";
    }

    function toggle(checked)
    {
      checkboxes = document.getElementsByName('qiid[]');
      for(var i=0; i<checkboxes.length; i++)
      {
          checkboxes[i].checked = checked;
      }
    }
    </script>";
}

function getMonitorResponsesJS($sessionID, $qiID)
{
	return "<script lang=\"JavaScript\">
	function refreshResponseCount()
	{
	    document.getElementById('rc{$qiID}').innerHTML = httpGet(\"responseCounter.php?sessionID={$sessionID}&qiID={$qiID}\");
	    var refresher = setTimeout(\"refreshResponseCount()\", 1000);
	}
	refreshResponseCount();</script>";
}

function performMove(&$thisSession)
{
    $moveID = $_REQUEST['moveitem'];
    $qiIDs = explode(',',$thisSession->questions);
    $qiIndexes = array_flip($qiIDs);
    array_splice($qiIDs, $qiIndexes[$moveID],1);
    $qiIndexes = array_flip($qiIDs);
    if(isset($_REQUEST['before']))
    {
        array_splice($qiIDs, $qiIndexes[$_REQUEST['before']],0,array($moveID));
    }
    elseif(isset($_REQUEST['after']))
    {
        array_splice($qiIDs, $qiIndexes[$_REQUEST['after']]+1,0,array($moveID));
    }
    else
    {
        // should never happen, bale out before touching the database
        return false;
    }
    $thisSession->questions = implode(',',$qiIDs);
    $thisSession->update();
    return true;
}

function deleteQi(&$thisSession)
{
    $deleteID = $_REQUEST['delete'];
    $qiIDs = explode(',',$thisSession->questions);
    $qiIndexes = array_flip($qiIDs);
    array_splice($qiIDs, $qiIndexes[$deleteID],1);
    $thisSession->questions = implode(',',$qiIDs);
    if($thisSession->currentQuestion == $deleteID)
        $thisSession->currentQuestion = 0;
    $thisSession->update();
    // clean up database
    questionInstance::deleteInstance($deleteID);
    return true;
}

function activateSingleQu(&$thisSession, $activate)
{
    if($thisSession->currentQuestion > 0)
    {
    	$cqi = questionInstance::retrieve_questionInstance($thisSession->currentQuestion);
        $cqi->endtime = time();
        $cqi->update();
    }
    $thisSession->currentQuestion = $activate;
    if($thisSession->currentQuestion > 0)
    {
    	$cqi = questionInstance::retrieve_questionInstance($thisSession->currentQuestion);
        $cqi->starttime = time();
        $cqi->update();
    }
    $thisSession->update();
}

function getQuestionTableMultipleQu($thisSession, &$quTitles, $showday)
{
    $out .= "<form method='POST' action='{$_SERVER['PHP_SELF']}'>";
    $out .= "<input type='hidden' name='sessionID' value='{$thisSession->id}'/>";
        $out .= '<table class="table table-striped"><thead><tr><th>#</th><th>Question</th><th>Used</th><th>Control</th><th>Responses</th><th>Actions</th></tr></thead><tbody>';

        $qiIDs = explode(',',$thisSession->questions);
        if(!isset($thisSession->extras[currentQuestions]))
            $thisSession->extras[currentQuestions] = array();

        $qunum = 0;
        if(isset($_REQUEST['move']))
            $moveMode = 'before';
        foreach($qiIDs as $qiID)
        {
            $qunum++;
            $qi = questionInstance::retrieve_questionInstance($qiID);
            if(($showday == 0)||(($qi->endtime >= $showday)&&($qi->endtime < $showday+3600*24)))
            {
            $day = strftime("%a %d %b %Y", ((floor($qi->endtime / (3600*24)) * 3600 * 24)+3600));
            $quTitles[] = array('id'=>$qi->id, 'title'=>$qi->title, 'day'=>$day);
            $qu = question::retrieve_question($qi->theQuestion_id);
            if($qu)
            {
                if(in_array($qiID, $thisSession->extras[currentQuestions]))
	                $out .= "\n<tr style='background-color: palegreen;'><td>$qunum.</td><td id='title{$qiID}'><span id='title{$qiID}_txt'>{$qi->title}</span>&nbsp;<a OnClick='EditTitle(\"{$qiID}\");'>(Edit title)</a></td>";
                else
	                $out .= "\n<tr><td>$qunum.</td><td id='title{$qiID}'><span id='title{$qiID}_txt'>{$qi->title}</span>&nbsp;<a OnClick='EditTitle(\"{$qiID}\");'>(Edit title)</a></td>";
                if($qi->endtime > 0)
                {
	                $out .= "<td>".strftime("%d %b %H:%M", $qi->endtime)."</td>";
                }
                else
                {
	                $out .= "<td>&nbsp;</td>";
                }
                $out .= "<td>Make active <input type='checkbox' name='qiid[]' value='$qiID'";
                if(in_array($qiID, $thisSession->extras[currentQuestions]))
                    $out .= " checked='checked'";
                $out .= "/></td>";
                $count = response::countCompleted($qi->id);
                if(($count == 0)&&(!in_array($qiID, $thisSession->extras[currentQuestions])))
	            	$out .= "<td>No responses</td>";
                else
		            $out .= "<td><a href='responses.php?sessionID={$thisSession->id}&qiID=$qiID'><span id='rc$qiID'>$count</span> response".s($count)."</a></td>";
                if(sizeof($thisSession->extras[currentQuestions])==0)
                {
	                if(isset($_REQUEST['move']))
	                {
	                    if($_REQUEST['move'] == $qiID)
	                    {
	            			$moveMode = 'after';
	            		    $out .= "<td><i><a href='sessionrun.php?sessionID={$thisSession->id}'>(Cancel move)</a></i></td>";
	                    }
	                    else
	                    {
	            		    $out .= "<td><a href='sessionrun.php?sessionID={$thisSession->id}&moveitem={$_REQUEST['move']}&$moveMode=$qiID'>(To $moveMode this)</a> ";
	                    }
	                }
	                else
	                {
	            		$out .= "<td><span class='feature-links'><a href='sessionrun.php?sessionID={$thisSession->id}&move=$qiID'><i class='fa fa-arrows'></i> Move</a> ";
	                    $out .= "<a href='sessionrun.php?sessionID={$thisSession->id}&delete=$qiID'><i class='fa fa-trash-o'></i> Delete</a></span></td>";
	                }
                }
                else
                {
                    $out .= "<td>&nbsp;</td>";
                }
	            $out .= "</tr>";
            }
        }
        }
        $out .= "<tr><td colspan='3'><a href='#' OnClick='toggle(1);'>Select all</a> <a href='#' OnClick='toggle(0);'>Select none</a></td><td colspan='3'><input type='submit' name='activate' value='Update active questions'/>";
        if(sizeof($thisSession->extras[currentQuestions])>0)
        {
            $out .= " <input type='submit' name='deactivate' value='Close all'/>";
        }
        $out .= "</td></tr>";
        $out .= "</table></form>";
        return $out;
}


function getQuestionTableSingleQu($thisSession, &$quTitles, $showday)
{
        $out = '<table class="table table-striped"><thead><tr><th>#</th><th>Question</th><th>Used</th><th>Control</th><th>Responses</th><th>Actions</th></tr></thead><tbody>';

        $qiIDs = explode(',',$thisSession->questions);
        // check current is valid, display make active stuff otherwise
	    if(!in_array($thisSession->currentQuestion, $qiIDs))
        {
	        $thisSession->currentQuestion = 0;
            $thisSession->update();
        }

        $qunum = 0;
        if(isset($_REQUEST['move']))
            $moveMode = 'before';
        foreach($qiIDs as $qiID)
        {
            $qunum++;
            $qi = questionInstance::retrieve_questionInstance($qiID);
            if(($showday == 0)||(($qi->endtime >= $showday)&&($qi->endtime < $showday+3600*24)))
            {
            $day = strftime("%a %d %b %Y", ((floor($qi->endtime / (3600*24)) * 3600 * 24)+3600));
            $quTitles[] = array('id'=>$qi->id, 'title'=>$qi->title, 'day'=>$day);
            $qu = question::retrieve_question($qi->theQuestion_id);
            if($qu)
            {
                if($thisSession->currentQuestion == $qiID)
	                $out .= "\n<tr style='background-color: palegreen;'><td>$qunum.</td><td id='title{$qiID}'><span id='title{$qiID}_txt'>{$qi->title}</span>&nbsp;<a OnClick='EditTitle(\"{$qiID}\");'>(Edit title)</a></td>";
                else
	                $out .= "\n<tr><td>$qunum.</td><td id='title{$qiID}'><span id='title{$qiID}_txt'>{$qi->title}</span>&nbsp;<a OnClick='EditTitle(\"{$qiID}\");'>(Edit title)</a></td>";
                if($qi->endtime > 0)
                {
	                $out .= "<td>".strftime("%d %b %H:%M", $qi->endtime)."</td>";
                }
                else
                {
	                $out .= "<td>&nbsp;</td>";
                }
                if($thisSession->currentQuestion == 0)
                {
	            	$out .= "<td><a href='sessionrun.php?sessionID={$thisSession->id}&activate=$qiID'>Make active</a></td>";
                }
                elseif($thisSession->currentQuestion == $qiID)
                {
	            	$out .= "<td><a href='sessionrun.php?sessionID={$thisSession->id}&activate=0'>Close</a><br/><a href='liveresponses.php?sessionID={$thisSession->id}' target='_live'>View Live</a></td>";
                    $template->pageData['afterContent'] .= getMonitorResponsesJS($thisSession->id, $qiID);
                }
                else
                {
	            	$out .= "<td>&nbsp;</td>";
                }
                //DEBUG
	                //$out.="<td>{$qi->id}</td>";
                $count = response::countCompleted($qi->id);
                if(($count == 0)&&($thisSession->currentQuestion != $qiID))
	            	$out .= "<td>No responses</td>";
                else
		            $out .= "<td><a href='responses.php?sessionID={$thisSession->id}&qiID=$qiID'><span id='rc$qiID'>$count</span> response".s($count)."</a></td>";
                if(isset($_REQUEST['move']))
                {
                    if($_REQUEST['move'] == $qiID)
                    {
            			$moveMode = 'after';
            		    $out .= "<td><span class='feature-links'><a href='sessionrun.php?sessionID={$thisSession->id}'><i class='fa fa-ban'></i> Cancel move</a></td>";
                    }
                    else
                    {
            		    $out .= "<td><span class='feature-links'><a href='sessionrun.php?sessionID={$thisSession->id}&moveitem={$_REQUEST['move']}&$moveMode=$qiID'><i class='fa fa-arrows'></i> Move $moveMode this</a> ";
                    }
                }
                else
                {
            		$out .= "<td><span class='feature-links'><a href='sessionrun.php?sessionID={$thisSession->id}&move=$qiID'><i class='fa fa-arrows'></i> Move</a> ";
                    $out .= "<a href='sessionrun.php?sessionID={$thisSession->id}&delete=$qiID'><i class='fa fa-trash-o'></i> Delete</a></span></td>";
                }
	            $out .= "</tr>";
            }
        }
        }
        $out .= "</table>";
        return $out;
}



?>
