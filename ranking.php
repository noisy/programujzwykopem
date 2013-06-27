<!DOCTYPE html>
<html>
     <head>
          <title></title>
          <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
          <link href="css/bootstrap-responsive.min.css" rel="stylesheet" media="screen">
          <style>
               h3 {
                    font-size:  21.5px;
               }
          </style>

          <?php
          set_time_limit(15);
          ini_set('memory_limit', '256M');
          //ini_set('error_reporting', E_ALL);
          //ini_set('display_errors', 1);
          date_default_timezone_set('Europe/Warsaw');
          include_once './sztafetaClass.php';

          $sztafeta = new sztafeta();
          ?>

     </head>
     <body>    
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
                    <?php
                    $top5dystans = $sztafeta->top5dystans();
                    //$sztafeta->mVarDump($top5dystans);

                    $i = 0;
                    $y=1;
                    foreach ($top5dystans as $value) {
                         
                         if ($i == 0 or ($i % 20) == 0) {
                              if($i % 60 == 0){
                                   
                                   ?>
               </div><div class="row-fluid">
                    <?php
                              }
                              ?>
                              <div class="span4">
                                   <table class="table table-striped">
                                        <caption><h3><?php if($i == 0) {$top=20; } else { $top = $i+20;}echo "TOP ".$top; ?></h3></caption>
                                        <tr>
                                             <th> # </th>
                                             <th>Nick</th>
                                             <th>Dystans</th>
                                        </tr>
                                        <?php
                                   }

                                   echo '<tr>';
                                   echo "<td>$y</td>";
                                   echo "<td><a href=http://wykop.pl/ludzie/$value[nick]>$value[nick]</a></td>";
                                   echo "<td>" . number_format($value['pokonany_dystans'], 2) . " km</td>";
                                   echo '</tr>';
                                    $i++;
                                   if ($i % 20 == 0) {
                                        ?>
                                   </table>
                              </div>
                              <?php
                         }
                         
                       $y++;
                      
                         
                    }
                    ?>

               </div>
          </div>
               <script src="js/bootstrap.min.js"></script>
     </body>
</html>
