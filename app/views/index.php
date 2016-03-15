<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="img/favicon.png">

  <title>CollaMine PHP</title>

  <link href="css/bootstrap-theme.css" rel="stylesheet">
  <link href="css/elegant-icons-style.css" rel="stylesheet" />
  <link href="css/font-awesome.css" rel="stylesheet" />
  <link href="css/font-awesome.min.css" rel="stylesheet" />
  <link href="css/style.css" rel="stylesheet">
  <link href="css/style-responsive.css" rel="stylesheet" />

  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
  <script type="text/javascript" src="https://www.google.com/jsapi"></script>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

   <script type="text/javascript">
      google.charts.load('current', {packages: ['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function retrieve() {
        $.ajax({
            type: "GET",
            url: '/documents',
            success: function(data) {
              var trHTML = '';
              for(i = 0; i < data.length; i++) {
                trHTML += "<a href='http://172.20.131.150:9001/download/html/" + encodeURIComponent(data[i]['url']) + "'>" + data[i]['url'] + "</a><br />";
              }  
              //clear existing content first
              document.getElementById('records_table').innerHTML = "";

              //add the retrieved content to table
              $('#records_table').append(trHTML);
            } 
        });

        $.ajax({
            type: "GET",
            url: '/total',
            success: function(data) {
              var collamine = data[0];
              var original = data[1];

                $('#collamine_count').text(data[0]);
                $('#original_count').text(data[1]);
                $('#total_count').text(data[2]);
              }
        });
      }

      function drawPieChart() {
        $.ajax({
            type: "GET",
            url: '/total',
            success: function(data) {
              var data = google.visualization.arrayToDataTable([
                ['Source', 'Total'],
                ['Original', parseInt(data[1])],
                ['Collamine', parseInt(data[0])]
              ]);

              var options = {
                             sliceVisibilityThreshold: 0,
                             backgroundColor: 'transparent',
                             fontSize: 14,
                             width:750,
                             height:450
                            };

              var chart = new google.visualization.PieChart(document.getElementById('piechart'));
              chart.draw(data, options);
            }
        });
      }

      var dt;
      var options;
      var chart = null;

      function initLineChart() {
          dt = new google.visualization.DataTable();
          dt.addColumn('string', 'Time');
          dt.addColumn('number', 'Original');
          dt.addColumn('number', 'Collamine');
          
          options = {
            backgroundColor: 'transparent',
            width: 1200,
            height: 500,
            vAxis: { title: 'No of Downloads', viewWindow:{ min:0} },
            hAxis: { title: 'Crawled Time' },
            pointSize: 5,
            fontSize: 14,
          };
          drawLineChart();
      }

      function drawLineChart() {
          if (chart == null)
            chart = new google.visualization.LineChart(document.getElementById('line_chart'));
          chart.draw(dt, options);
      }

      function updateChart() {
         var today = new Date();
         var dd = ("0" + today.getDate().toString()).substr(-2);
         var mm = ("0" + (today.getMonth() + 1).toString()).substr(-2); //January is 0!
         var yyyy = today.getFullYear();

         today = yyyy + '-' + mm + '-' + dd + " " + (("0" + today.getHours()).slice(-2)) +":"+ (("0" + today.getMinutes()).slice(-2)) +":"+ (("0" + today.getSeconds()).slice(-2)); 

         $.ajax({
            type: "post",
            url: '/linechart',
            data: { "total" :  $('#total_count').text() },
            success: function(data) {
                dt.addRow([
                  today.substr(10), parseInt(data[0]), parseInt(data[1])
                ]);

                if (dt.getNumberOfRows() > 7)
                  dt.removeRow(0);
            } 
         }); 
      }

      function drawChart() {
            initLineChart();
            drawPieChart();
      }

      setInterval(function() {
          updateChart();
          drawLineChart();
          drawPieChart();
          retrieve();
      }, 10000); 
  </script>
  </head>

  <body>
    <!-- container section start -->
    <section id="container" class="">
      <header class="header dark-bg">
        <div class="toggle-nav">
          <div class="icon-reorder tooltips" data-orginal-title="Toggle Navigation" data-placement="bottom"></div>
        </div>
        <a href="index.html" class="logo">CollaMine</a>
        <a id="date_time" class="datetime"></a>
      </header>

      <!--main content start-->
      <section id="main-content">
        <section class="wrapper">            
          <!--overview start-->
          <div class="row">
            <div class="col-lg-12">
              <h3 class="page-header"><i class="fa fa-laptop"></i>Dashboard</h3>
            </div>
          </div>

          <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
              <div class="info-box blue-bg">
                <i class="fa fa-cloud-upload"></i>
                <div class="count">
                  <p id='total_count'></p>
                </div>
                <div class="title">Total Downloads</div>           
              </div><!--/.info-box-->     
            </div><!--/.col-->

            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
              <div class="info-box brown-bg">
                <i class="fa fa-cloud-download"></i>
                <div class="count">
                  <p id = "collamine_count"></p>
                </div>
                <div class="title">Downloads from CollaMine</div>           
              </div><!--/.info-box-->     
            </div><!--/.col-->  

            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
              <div class="info-box dark-bg">
                <i class="fa fa-cloud-download"></i>
                <div class="count">
                  <p id = "original_count"></p>
                </div>
                <div class="title">Downloads from Original</div>            
              </div><!--/.info-box-->     
            </div><!--/.col-->
          </div><!--/.row-->

          <div class="row">
            <div class="col-lg-8 col-md-12">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h2><img src="img/linechart-img.png" style="height:18px; margin-top:-2px;" />&nbsp;&nbsp;&nbsp;&nbsp;<strong>Line Chart</strong></h2>
                </div>
                <div class="panel-body text-center" style="height:440px">
                  <div id="line_chart" style="margin-top: -7%; margin-left: -5%; height: 300px; width:100%; "></div>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h2><img src="img/piechart-img.png" style="height:18px; margin-top:-2px;" />&nbsp;&nbsp;&nbsp;&nbsp;<strong>Pie Chart</strong></h2>
                </div>
                <div class="panel-body text-center" style="height:440px">
                  <br>
                  <div id = "piechart" style="margin-left: -5%; margin-top: -10%;"></div>
                </div>
              </div>
            </div>
          </div>  

          <!-- Recently Visited Documents -->
          <div class="row">
            <div class="col-lg-6 col-md-12">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h2><img src="img/globe-img.png" style="height:18px; margin-top:-2px;" />&nbsp;&nbsp;&nbsp;&nbsp;<strong>Recently Visited Documents</strong></h2>
                </div>
                <div>
                  <div height="250px" border-spacing="10px" id='records_table'></div>
                </div>
              </div>
            </div>

            <!-- Suggested Domains -->
            <div class="col-md-6">
              <div class="panel panel-default">
                <div class="panel-heading">
                  <h2><img src="img/history-img.png" style="height:18px; margin-top:-2px;" />&nbsp;&nbsp;&nbsp;&nbsp;<strong>Suggested Domains</strong></h2>
                </div>
                <div>
                  <table>
                    <tbody>
                      <?php
                        for ($i = 0; $i < count($domains); $i++)
                        {
                          echo  "<tr><td>" . 
                                      "<a href='http://" .  $domains[$i]['domain'] . "'>" . $domains[$i]['domain'] .
                                      "</a>" .
                                "</tr></td>";
                        } 
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div> 

        </section>
      </section>
      <!--main content end-->
    </section>
    <!-- container section end -->

    <!-- javascripts -->
    <script type="text/javascript" src="js/date_time.js"></script>
    <script type="text/javascript">
      function load() {
        retrieve();
        date_time('date_time');
      }
      window.onload = load();
    </script>
  </body>
  </html>