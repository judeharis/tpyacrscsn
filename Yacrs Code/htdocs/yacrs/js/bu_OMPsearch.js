function boyer_moore_horspool(haystack, needle) {
    var badMatchTable = {};
    var maxOffset = haystack.length - needle.length;
    var offset = 0;
    var last = needle.length - 1;
    var scan;
  
    if (last < 0) return false;

    // Generate the bad match table, which is the location of offsets
    // to jump forward when a comparison fails
    for (var i = 0; i < needle.length - 1; i++) {
        badMatchTable[needle[i]] = last - i;
    }

    // Now look for the needle
    while (offset <= maxOffset) {
        // Search right-to-left, checking to see if the current offset at 
        // needle and haystack match.  If they do, rewind 1, repeat, and if we 
        // eventually match the first character, return the offset.
        for (scan=last; needle[scan] === haystack[scan+offset]; scan--) {
            if (scan === 0) {
              //return offset; //return index number
			  return true;
            }
        }

        offset += badMatchTable[haystack[offset + last]] || last || 1;
    }

    return false;
}
var data=Array();
var showing=Array();
var not_showing=Array();
var time_searching=0;
var search_history=Array();
//onload, read all data should load into data;
//showing and not_showing only contain the index number, so that it can reduec memory
$(document).ready ( function(){
	/*  Loading data  */
	var column = $("#respon tbody tr td");
	for(var i = 3, ii=0; i < column.length; i+=4)
	{
		data[ii]={userid: column[i-3].innerHTML, name: column[i-2].innerHTML, response: column[i-1].innerHTML, time: column[i].innerHTML}
		ii=ii+1;
	}
	for(var i = 0; i < data.length; i++)
	{
		showing.push(i);
	}
	$("#num_respons").html(data.length);
	$("#Searchresult").html(data.length);
	/*  Loading End  */
	$("#SearchContain").click(function(){
		var keyword=$("#keyword").val();
		search_history.push([keyword, 0])
		produce_search_result(keyword, 0);
		refrush();
	});

	$("#SearchNotContain").click(function(){
		var keyword=$("#keyword").val();
		search_history.push([keyword, 1])
		produce_search_result(keyword, 1);
		refrush();
	});
	
	$("#OrContain").click(function(){
		var keyword=$("#keyword").val();
		search_history.push([keyword, 3])
		produce_search_result(keyword, 3);
		refrush();
	});
	
	$('div.history').on('click', 'div', function(event) {
		produce_recovery(parseInt(event.target.id));
	});
});

function produce_search_result(key, instruction){
	if(instruction==3){
		var new_not_showing=Array();
		while(not_showing.length!=0)
		{
			var temp=not_showing.pop();
			
			if(boyer_moore_horspool(data[temp].response, key))showing.push(temp);
			else new_not_showing.push(temp);
		}
		showing.sort((a,b) => a-b);
		not_showing=new_not_showing;
		display_history(key, "or contain: ");
		return 0;
	}
	var new_showing=Array();
	var new_not_showing=Array();
	for(var i = 0; i < showing.length; i++)
	{
		
		if(boyer_moore_horspool(data[showing[i]].response, key))new_showing.push(showing[i]);
		else new_not_showing.push(showing[i]);
	}
	switch (instruction) {
		case 0:
			display_history(key, "contain: ");
			showing= new_showing;
			not_showing= new_not_showing;
			break;
		case 1:
			display_history(key, "not contain: ");
			showing= new_not_showing;
			not_showing= new_showing;
			break;
	}
	
}


function produce_recovery(index){
	showing=Array();
	not_showing=Array();
	var new_search_history=Array();
	time_searching=0;
	for(var i = 0; i < data.length; i++)
	{
		showing.push(i);
	}
	for(var i = 0; i <= index; i++)
	{
		new_search_history.push([search_history[i][0], search_history[i][1]]);
		produce_search_result(search_history[i][0], search_history[i][1]);
		refrush();
	}
	search_history=new_search_history;
}

function display_history(key, instruction){
	if(time_searching==0)
	{
		$(".history").html("<div id='0'>"+instruction+key+"</div><br />");
		time_searching+=1;
	}
	else
	{
		$(".history").html($(".history").html()+"<div id='"+time_searching+"'>"+instruction+key+"</div><br />");
		time_searching+=1;
	}
}


function refrush(){
	var out = "";
	if(showing.length>0) {
		$(showing).each(function(index, value) {
			out+=adding_tr_td(data[value].userid, data[value].name, data[value].response, data[value].time);
		});
		$("#respon").css("display", "");
		$("#respon tbody").html(out);
		$("#Searchresult").html(showing.length);
	}
	else{
		out="Not respon match"
		$("#respon").html(out);
		$("#Searchresult").html("Not respon match");
		$("#respon").css("display", "none");
	}

}

function adding_tr_td(userid, name, response, time){
	return "<tr><td>"+userid+"</td><td>"+name+"</td><td>"+response+"</td><td>"+time+"</td></tr>"
}