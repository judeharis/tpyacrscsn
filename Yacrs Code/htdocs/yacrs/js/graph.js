$( document ).ready(function() {
	var ctx = document.getElementById('myChart').getContext('2d');
	chart = new Chart(ctx, {
		// The type of chart we want to create
		type: 'bar',

		// The data for our dataset
		data: {
			labels: ["A", "B", "C", "D"],
			datasets: [{
				label: "My First dataset",
				backgroundColor: [getRandomColor(),getRandomColor(),getRandomColor(),getRandomColor()],
				borderColor: 'rgb(255, 99, 132)',
				data: [50, 10, 5, 2],
			}]
		},
		// Configuration options go here

		plugins: [{
		beforeDraw: function(chartInstance) {
			var ctx = chartInstance.chart.ctx;
			ctx.fillStyle = "white";
			ctx.fillRect(0, 0, chartInstance.chart.width, chartInstance.chart.height);
		},
		}],
		options: {
			legend: {display: false},
			title: {
				display: true,
				text: 'Custom Chart Title'
			},
			tooltips: {enabled: false},
			
			hover: {mode: null},
			
			animation: {
				duration: 1000,
				easing: 'easeInQuint',
				onComplete: function () {
					
				var chartInstance = this.chart;
				
				ctx = chartInstance.ctx;
				ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
				ctx.textAlign = 'center';
				ctx.textBaseline = 'bottom';

				this.data.datasets.forEach(function (dataset, i) {
					var meta = chartInstance.controller.getDatasetMeta(i);
					meta.data.forEach(function (bar, index) {
						var data = dataset.data[index];                            
						ctx.fillText(data, bar._model.x, bar._model.y - 5);
					});
				});
				save_graph();
				}
			}
	}

		
	});

	function Random_numString_150_200() {
		return (Math.floor(Math.random()*56)+150).toString()
	}
	function getRandomColor() {
		return "rgba("+Random_numString_150_200()+","+Random_numString_150_200()+","+Random_numString_150_200()+","+"0.6";
	}
	
	
	function save_graph(){
		var url=chart.toBase64Image();
		link.href = url;
		document.getElementById("url").src=url;
		
		
};
});



