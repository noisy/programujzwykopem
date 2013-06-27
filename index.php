<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="css/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
        <?php 
         set_time_limit(15);
         ini_set('memory_limit', '256M');
         //ini_set('error_reporting', E_ALL);
         //ini_set('display_errors', 1);
         date_default_timezone_set('Europe/Warsaw');
         include_once './sztafetaClass.php';
         
         $sztafeta = new sztafeta();
         $kilometraz = $sztafeta->kilometrazWykres();
?>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
		<script type="text/javascript">
$(function () {
        $('#wykres').highcharts({
            chart: {
                type: 'column',
                margin: [ 50, 50, 100, 80]
            },
            title: {
                text: 'Dzienna suma dystansów'
            },
            xAxis: {
                categories: [
                    <?php
                    
                    
                    $i = 0;
                    
                    foreach($kilometraz as $value){
                         echo "'$value[dzien]'";
                         if($i+1<count($kilometraz)){
                              echo ', ';
                         }
                         $i++;
                    }
                    
                    ?>
                         
                        
                ],
                labels: {
                    rotation: -45,
                    align: 'right',
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Kilometry'
                }
            },
            legend: {
                enabled: false
            },
   
            series: [{
                name: 'Kilometrów',
                data: [
             <?php
             
                    
                    $i = 0;
                    
                    foreach($kilometraz as $value){
                         $dys = number_format($value['dystans'], 2);
                         echo $dys;
                         if($i+1<count($kilometraz)){
                              echo ', ';
                         }
                         $i++;
                    }
                    
                    ?>
            
                ],
                dataLabels: {
                    enabled: true,
                    rotation: -90,
                    color: '#FFFFFF',
                    align: 'right',
                    x: 4,
                    y: 10,
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            }]
        });
    });
    

		</script>
        <style>
            h3 {
                font-size:  21.5px;
            }
        </style>


        
    </head>
    <body>    
         <script src="charts/js/highcharts.js"></script>
          <script src="charts/js/modules/exporting.js"></script>
        <div class="container">
            <div class="page-header">
                <a href="http://www.wykop.pl"><img src="img/logo_wykop_250.png"></a><h1><a href="http://www.wykop.pl/tag/sztafeta/">#Sztafeta</a> <small><a href="http://www.endomondo.com/challenges/9351501" class="muted pull-right">Rywalizacja Endomondo</a></small></h1>
            </div>
            <?php
            $procent_dystansu = $sztafeta->przebiegnietyDystans();
            ?>
            <span class="badge badge-info" style="margin-left: <?php echo $procent_dystansu['procent_trasy']; ?>%"><?php echo number_format($procent_dystansu['procent_trasy'], 2); ?>%</span>
            <div class="progress progress-success">      
                <div class="bar" style="width: <?php echo $procent_dystansu['procent_trasy']; ?>%"></div>
            </div>

            <div class="row-fluid">
                <div class="span4">
                    <table class="table table-striped">
                        <caption><h3>TOP 5 DYSTANS</h3></caption>
                        <tr>
                            <th> # </th>
                            <th>Nick</th>
                            <th>Pokonany dystans</th>
                        </tr>
                        <?php
                        
                        $i = 1;
                        $top5dystans = $sztafeta->top5dystans();
                        foreach ($top5dystans as $value) {
                            echo '<tr>';
                            echo "<td>$i</td>";
                            echo "<td><a href=http://wykop.pl/ludzie/$value[nick]>$value[nick]</td>";
                            echo "<td>" . number_format($value['pokonany_dystans'], 2) . " km</td>";
                            echo '</tr>';
                            $i++;
                            if ($i > 5)
                                break;
                        }
                        ?>


                    </table>
                    <a href="ranking.php" class="btn btn-info pull-right">Pokaż pełen ranking</a>
                </div>
                <div class="span4">
                    <table class="table table-striped">
                        <caption><h3>NAJWIĘCEJ KM W TYGODNIU</h3></caption>
                        <tr>
                            <th> # </th>
                            <th>Nick</th>
                            <th>Pokonany dystans</th>
                        </tr>
                        <?php
                        $i = 1;
                        $top5tydzien = $sztafeta->top5tydzien();
                        foreach ($top5tydzien as $value) {
                            echo '<tr>';
                            echo "<td>$i</td>";
                            echo "<td><a href=http://wykop.pl/ludzie/$value[nick]>$value[nick]</td>";
                            echo "<td>" . number_format($value['pokonany_dystans'], 2) . " km</td>";
                            echo '</tr>';
                            $i++;
                            if ($i > 5)
                                break;
                        }
                        ?>


                    </table>
                     <a href="rankingTygodnia.php" class="btn btn-info pull-right">Pokaż pełen ranking</a>
                </div>


                <div class="span4">
                    <table class="table table-striped">
                        <caption><h3>OSTATNIE WPISY</h3></caption>
                        <tr>
                            <th> # </th>
                            <th>Nick</th>
                            <th>Pokonany dystans</th>
                        </tr>
                        <?php
                        $i = 1;
                        $ostatnioDodaneWpisy = $sztafeta->ostatnioDodaneWpisy();
                        foreach ($ostatnioDodaneWpisy as $value) {
                            echo '<tr>';
                            echo "<td>$i</td>";
                            echo "<td><a href=http://wykop.pl/ludzie/$value[nick]>$value[nick]</td>";
                            echo "<td><a href=http://wykop.pl/wpis/$value[id_wpisu]>" . number_format($value['dystans'], 2) . " km</a></td>";
                            echo '</tr>';
                            $i++;
                            if ($i > 5)
                                break;
                        }
                        ?>


                    </table>
                </div>

            </div> 

            
            <div class="row-fluid">
                
                 <div id="wykres" class="span8" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
                <div class="span4">
                    <form action="" method="POST">
                    <select name="nick">
                        <?php
                        $wszyscyZawodnicy = $sztafeta->wszyscyZawodnicy();
                        foreach ($wszyscyZawodnicy as $value) {
                            echo "<option value=$value[nick]>$value[nick]</option>";
                        }
                        ?>
                    </select>
                        <button type="submit" class="btn btn-primary">Pokaż</button>
                    </form>
                    
                    <?php
                    
                    if(isset($_POST)){
                        if(!empty($_POST['nick'])){
                            $nickZawodnika = htmlspecialchars($_POST['nick']);
                            $daneZawodnika = $sztafeta->daneZawodnika($nickZawodnika);
                            
                            if(count($daneZawodnika) > 0){
                                $i = 1;
                                ?>
                    <table class="table table-striped">
                                    <caption><h3><?php echo $daneZawodnika[0]['nick']; ?></h3></caption>
                                    <tr>
                                        <th>lp.</th>
                                        <th>Pokonany dystans</th>
                                        <th>Data</th>
                                    </tr>
                                    <?php
                                    $suma = 0;
                                     foreach ($daneZawodnika as $value) {
                                     echo '<tr>';
                                    echo "<td>$i</td>";
                                    $dystans = number_format($value['dystans'],2);
                                    echo "<td><a href=http://wykop.pl/wpis/$value[id_wpisu]>$dystans</td>";
                                    echo "<td>$value[data_wpisu]</td>";
                                    echo '</tr>';
                                    $i++;
                                    $suma += $dystans;
                                     }
                                     echo '<tr>';
                                     echo "<td>Razem:</td>";
                                     echo "<td>$suma km</td>";
                                     echo '</tr>';
                                    ?>
                    </table>
                               
       <?php
                            }
                        }
                    }
                    
       ?>
                    
                    
                </div>
               
            </div>



        </div>
        
        
        <script src="js/bootstrap.min.js"></script>

    </body>
</html>
