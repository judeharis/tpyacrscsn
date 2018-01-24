
var choice;
var type ="0";
var questions=[];
var mcquestion="";
var opquestion="";
var choices= [];
var choicechecked=[];
var editq=false;
var editqnumber;
var wordlimit=0;
var refresh=true;
$(document).ready(function() {

    choice=1;


    $('[name="newsessionform"]').hide();

    $('body').on('click', '#choiceincrease', function () {
        mcquestion = $('#mcqquestion').val()
        for (i = 0; i < window.choice; i++) {
            counter = i+1;
            choices[counter] =  $('#choice'+counter+'').val();
            if($('#choice'+counter+'correct').is(":checked")){
                choicechecked[counter] = "true";
                console.log(counter);
            }
        }
        if(choice <5){
            choice+=1;
        }
        mcq();

    });

    $('body').on('click', '#choicedecrease', function () {
        mcquestion = $('#mcqquestion').val()
        choices[window.choice]="";
        choicechecked[window.choice]="false";
        if(choice >1){
            choice-=1;
        }
        mcq();
    });

    $('body').on('change', '#qu', function (){
        type = this.value;
        if(type==="0"){
            opquestion=$('#opmquestion').val()
            mcq();
        }else{
            mcquestion = $('#mcqquestion').val()
            for (i = 0; i < window.choice; i++) {
                counter = i+1;
                choices[counter] =  $('#choice'+counter+'').val();
                choicechecked[counter] = $('#choice'+counter+'correct').is(":checked");
            }
            opm();
        }
    });

    checktype()

    $('body').on('click', '[value="Create"]', function () {
        event.preventDefault();
        var titleval= $("#title").val();
        var allowGuestsval = $("#allowGuests").is(":checked")? 1:0;
        var defaultQuActiveSecsval =isNaN(parseInt($("#defaultQuActiveSecs").val()))? 0:parseInt($("#defaultQuActiveSecs").val());
        console.log(defaultQuActiveSecsval);
        $.post("index.php",
        {
            qz :questions,
            ajax:true,
            editSession_form_code:"73e4b27a947d6e4f3a1c38c04af1a20f",
            title:titleval,
            allowGuests:allowGuestsval,
            visible:1,
            questionMode:0,
            defaultQuActiveSecs:defaultQuActiveSecsval,
            allowQuReview:1,
            allowFullReview:1,
            customScoring:"",
            ublogRoom:0,
            maxMessagelength:0,
            allowTeacherQu:0,
            teachers:""
        },function(data){
            console.log(data);
            window.location.replace("index.php");
        });
    });

    $('body').on('click', '[id^="editq"]', function(){
        var obj = JSON.parse(questions[this.value]);
        //console.info(JSON.stringify(questions));
        editq=true;
        editqnumber=this.value;
        if(obj.questiontype =="mcq"){
            type="0";
            $("#qu").val(0);
            window.choice = parseInt(obj.choicecounter);
            mcquestion=obj.question;
            for (i = 1; i <= window.choice; i++) {
                choices[i] =obj["choice"+i];
                choicechecked[i] =obj["choicechecked"+i];
            }
        }else{
            type="1";
            $("#qu").val(1);
            opquestion=obj.question;
            wordlimit = parseInt(obj.wordlimit);
        }
        $("[name='addquestion']").html("Edit Question");
        checktype();
    });

    $('body').on('click', '#addqonly', function(){
        refresh=false;
        addq();
        event.preventDefault();
        var sessionIDval = $('[name="sessID"]').val();
        $.post("sessionrun.php?sessionID="+sessionIDval,
        {
            qz :questions,
            ajax:true,
            addQuestion_form_code:"35a1a89b39ca94b6ca60eeaa5e965edb",
            sessionID:sessionIDval
        },function(data){
            questions=[];
            console.log(data);
            window.location.replace("sessionrun.php?sessionID="+sessionIDval);
        });
    });

    $('body').on('click', '#editoneq', function(){
        var qiIDval = this.value;
        refresh=false;
        addq(false);
        var sessionIDval = $('[name="sessID"]').val();
        $.post("sessionrun.php?sessionID="+sessionIDval,
        {
            qz :questions,
            editoneq:true,
            $qiID:qiIDval,
            ajax:true,
            editQuestion_form_code:"8acab4c527c7ff2adb0898459f63c1bd",
            sessionID:sessionIDval
        },function(data){
            questions=[];
            window.location.replace("sessionrun.php?sessionID="+sessionIDval);
        });
    });

    $('body').on('click','#editbtn',function(){
        cleardata()
        var sessionIDval = $('[name="sessID"]').val();
        var qiIDval = this.value;
        $.post("sessionrun.php?sessionID="+sessionIDval,
        {
            $qiID:qiIDval,
            showquestion:true,
            ajax:true
        },function(data){

            var obj =JSON.parse(data);
            questions[0]=obj[0];
            $('[name="mainbit"]').html(obj[1]);
            type =JSON.parse(obj[0]).questiontype;
            var question = JSON.parse(questions[0]);
            editq=true;
            editqnumber=0;
            if(question.questiontype =="mcq"){
                type="0";
                $("#qu").val(0);
                window.choice = parseInt(question.choicecounter);
                mcquestion=question.question;
                for (i = 0; i <= window.choice; i++) {
                    choices[i] =question["choice"+i];
                    choicechecked[i] =question["choicechecked"+i];
                }
            }else{
                type="1";
                $("#qu").val(1);
                opquestion=question.question;
                wordlimit = parseInt(question.wordlimit);
            }
            $("[name='addquestion']").html("Edit Question");
            $("[name='addquestion']").attr('id', 'editoneq');
            $("[name='addquestion']").val(qiIDval);
            checktype();
        });
    });

    $('body').on('click','#makeqactive', function(){
        var qiactivatelistval=[];
        var sessionIDval = $('[name="sessID"]').val();
        var qiIDval = $(this).attr('value');
        $('[name="qactivate"]').each(function(){
            if ($(this).is(":checked")){
                qiactivatelistval.push(this.value);
            }
        });

        $.post("sessionrun.php?sessionID="+sessionIDval,
        {
            qiactivatelist:qiactivatelistval,
            qiID:qiIDval,
            activateqs:true,
            ajax:true
        },function(data){
            window.location.replace("sessionrun.php?sessionID="+sessionIDval);
        });

    });


    $('body').on('click','#qdeactivate', function(){
        var qideactivatelistval=[];
        var sessionIDval = $('[name="sessID"]').val();
        $('[name="qactivate"]').each(function(){
            if ($(this).is(":checked")){
                qideactivatelistval.push(this.value);
            }
        });

        $.post("sessionrun.php?sessionID="+sessionIDval,
        {
            qideactivatelist:qideactivatelistval,
            deactivateqs:true,
            ajax:true
        },function(data){
            window.location.replace("sessionrun.php?sessionID="+sessionIDval);
        });

    });

});


function checktype(){
    if(type==="0"){
        mcq();
    }else{
        opm();
    }
}

function addq(){
    if (type === "0"){
        var input= '{"questiontype":"mcq",';
        input+=  '"question":"' +$('#mcqquestion').val()+ '"';
        var choicecounter =0;
        for (i = 0; i < window.choice; i++) {
            var counter= i+1;
            var answer = $('#choice'+counter+'').val();
            if (answer != ""){
                choicecounter+=1;
                input+= ',"choice'+choicecounter+'":"' + answer+ '",';
                if ($('#choice'+counter+'correct').is(":checked")){
                    input+= '"choicechecked'+choicecounter+'":"true"';
                }else{
                    input+= '"choicechecked'+choicecounter+'":"false"';
                }
            }
        }
        input +=',"choicecounter":"'+choicecounter+'"}';
        if (!editq){
            questions[questions.length] =input;
        }else{
            questions[editqnumber] =input;
            editq=false;
            $("[name='addquestion']").html("Add Question");
        }
    }else{
        var input= '{"questiontype":"opm",';
        input+=  '"question":"' +$('#opmquestion').val()+ '"';
        if($('#opmlimit').val() != 0){
            input += ',"wordlimit":"'+ $('#opmlimit').val()+'"';
        }else{
            input += ',"wordlimit":"140"';
        }
        input +='}';
        if (!editq){
            questions[questions.length] =input;
        }else{
            questions[editqnumber] =input;
            editq=false;
            $("[name='addquestion']").html("Add Question");
        }
    }


    cleardata();
    if (refresh === true){
        checktype();
    }else{
        refresh=true;
    }

}

function displayquestions(){
    var output='<div class="form-group">';
    var counter=0;
    for (q of questions){
        output+='<div class="form-group">';
        var obj = JSON.parse(q);
        output+='<label class="col-sm-4" >Question: '+obj.question+'</label>';
        output+='<button id="editq'+counter+'" value="'+counter+'" class="btn"  type="button" name="editquestion">Edit</button>';
        output+='</div>';
        counter+=1;
    }
    output+='</div>';
    $("#0").html(output);
}


function cleardata(){
    window.choice=1;
    wordlimit=0;
    mcquestion="";
    opquestion="";
    choices= [];
    choicechecked=[];
}

function mcq(){
    var choicehtml= '<div class="form-group"><label class="col-sm-4 control-label" >Question:</label><div class="col-sm-8"><input class="form-control" id="mcqquestion" type="text" name="mcqquestion" value="'+mcquestion+'">';
    for (i = 0; i < window.choice; i++) {
        var counter= i+1;
        var value;
        if (choices[counter] == undefined){
            value ="";
        }else{
            value =choices[counter];
        }
        choicehtml +=  '<label class="col-sm-4 control-label" >Choice '+ counter + ':</label>';
        choicehtml += '<input class="textinputbox" id="choice'+ counter +'" type="text" name="choice'+ counter +'" value ="'+value+'">';
        if(choicechecked[counter]==="true"){
            choicehtml += '<input class="togglebtn" id="choice'+ counter +'correct" checked type="checkbox" data-toggle="toggle">';
        }else{
            choicehtml += '<input class="togglebtn" id="choice'+ counter +'correct" type="checkbox" data-toggle="toggle">';
        }
    }
    choicehtml+='<button id="choiceincrease" class="submit btn btn-success"  type="button" name="choiceincrease">Add Choice</button>'
    choicehtml+='<button id="choicedecrease" class="submit btn btn-success"  type="button" name="choicedecrease">Remove Choice</button></div></div>'
    $("#1").html(choicehtml);
}

function opm(){
    var opmhtml= '<div class="form-group">';
    opmhtml +='<label class="col-sm-4 control-label" >Question:</label><div class="col-sm-8">';
    opmhtml += '<input class="form-control" id="opmquestion" type="text" name="opmquestion" value="'+ opquestion+'">';
    opmhtml +='<label class="col-sm-4 control-label" >Word Limit:</label><div class="col-sm-8">';
    opmhtml += '<input class="form-control" id="opmlimit" value="'+wordlimit+'"type="number" name="opmlimit"></div></div>';
    $("#1").html(opmhtml);
}

function showform(){
    $("#showform").hide();
    $('[name="newsessionform"]').show();
}

function showaddq(){
    var sessionIDval = $('[name="sessID"]').val();
    console.log(sessionIDval);
    $.post("sessionrun.php?sessionID="+sessionIDval,
    {
        ajax:true,
        showaddq:true
    },function(data){
        $('[name="mainbit"]').html(data);
        cleardata();
        checktype();
        //window.location.replace("sessionrun.php?sessionID="+sessionIDval);
    });
}
