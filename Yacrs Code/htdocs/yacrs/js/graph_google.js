
var respon_data = Array();//for google.charts
$( document ).ready(function() {
	/*  Loading data  */
	var selection_column = $("#multiple-choice-respon thead tr td");
	var respon_column = $("#multiple-choice-respon tbody tr td");
	respon_data.push(['Choices', 'respon', { role: 'style' }]);
	for(var i=0; i < selection_column.length; i+=1)
	{
		respon_data.push([selection_column[i].innerHTML, parseInt(respon_column[i].innerHTML), ((i % 2 == 0)?'gold':'silver')]);
	}

	
	
	google.charts.load('current', {'packages':['corechart']});
	// Set a callback to run when the Google Visualization API is loaded.
	google.charts.setOnLoadCallback(drawChart);
	// Callback that creates and populates a data table,
	// instantiates the pie chart, passes in the data and
	// draws it.
	
});

function drawChart() {
	// Create the data table.
    var data = google.visualization.arrayToDataTable(respon_data);
	

	// Set chart options
	var PieChart_options = {
		title: "q name",
		'pieSliceText': 'value-and-percentage',
		width: 600,
		height: 500,
		chartArea:{
			left:20,
			top: 20,
			width: 580,
			height: 480,
		},
		animation:
           {
               "startup": true,
               duration: 2000,
               easing: 'out'
           }
		
	};

	// Instantiate and draw our chart, passing in some options.
	
	var PieChart_chart = new google.visualization.PieChart(document.getElementById('PieChart_div'));
	PieChart_chart.draw(data, PieChart_options);
	
	image.src=PieChart_chart.getImageURI();
	link.href = PieChart_chart.getImageURI();
	
	
	var view = new google.visualization.DataView(data);
      view.setColumns([0, 1,
                       { calc: "stringify",
                         sourceColumn: 1,
                         type: "string",
                         role: "annotation" },
                       2]);

	var columnchart_options = {
        title: "q name",
        width: 600,
        height: 500,
        bar: {groupWidth: "95%"},
        legend: 'none',
		hAxis: {
          title: 'Choices'
        },
		vAxis: {
          title: 'Respons'
		},
		animation:
           {
               "startup": true,
               duration: 2000,
               easing: 'out'
           }
     };
	var columnchart_chart = new google.visualization.ColumnChart(document.getElementById("columnchart_chart_div"));
    columnchart_chart.draw(view, columnchart_options);
	ima_ge.src=columnchart_chart.getImageURI();
	li_nk.href = columnchart_chart.getImageURI();
}