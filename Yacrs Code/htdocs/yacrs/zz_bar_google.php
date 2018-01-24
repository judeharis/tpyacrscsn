<html>
<head>
<title>OMP Analysis prototype</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript" src="js/graph_google.js"></script>
<style>
body {
    background-color: FBF3E5;
}
table { table-layout: fixed;}
td { width: 50px;}

</style>
</head>
<body>
<table border=1 id="multiple-choice-respon">
<thead>
<tr>
<td>A</td><td>B</td><td>C</td><td>D</td>
<tr>
</thead>

<tbody>
<td>123</td><td>21</td><td>45</td><td>23</td>
</tbody>
</table>


<table border=1>
<tr>
<td><div id="PieChart_div"></div></td>			<td><div id="columnchart_chart_div"></div></td>
</tr>
<tr>
<td><a id='link' download='filename.png'>Save as Image</a><br /><img id="image"><img></td>
<td><a id='li_nk' download='filename.png'>Save as Image</a><br /><img id="ima_ge"></td>
</tr>
</table>
</body>
</html>