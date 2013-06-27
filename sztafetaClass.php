<?php

include_once 'wykopAPI.php';

class sztafeta {

         
    //dane dla wykopAPI
    private $key = '';
    private $secret = '';
    private $userAccountKey = '';
    //dane do bazy danych PDO
    private $dbCfg = 'mysql:host=localhost;dbname=';
    private $dbUser = '';
    private $dbPassword = '';
    
    
    public $tag = '#sztafeta';
    
   
    public function pobierzNajnowszeWpisy(){
        $uzytkownicy = array();
        $strona = 1;
        
        $wykop = new libs_Wapi($this->key, $this->secret);
        
        
            
            $result = $wykop->doRequest("search/entries/page/$strona", array('q' => $this->tag));
             foreach ($result as $r) {
                
                  if(!preg_match('@(#sztafetaraport)@', $r['body'])){
                       
                    $r['body'] = str_replace(array('km', 'KM','\\', ' '), '', $r['body']);
                    $r['body'] = preg_replace('@\([^)]*\)@', '', $r['body']);
                    $r['body'] = str_replace(array(','), '.', $r['body']);

                    if(preg_match_all("@(?<dane>[0-9]{1,5}[\.]{0,1}[0-9]{0,3}(?:-[0-9]{1,5}[\.]{0,1}[0-9]{0,3}){1,}=[0-9]{1,5}[\.]{0,1}[0-9]{0,3})@", $r['body'],$dane)){
                        $zm = preg_split('/[-=]/', $dane['dane'][0]);
                        $zm = array_map('floatval', $zm);

                        $uzytkownicy[] = array('idWpisu' => $r['id'], 'author' => $r['author'], 'dystans' => $this->sumujDystans($zm), 'data_wpisu' => $r['date']);

                    }
                  }
                  
            }
        
        return $uzytkownicy;
    }
    
    public function pobierzWszystkieWpisy(){
        $uzytkownicy = array();
        $strona = 1;
        $wyskocz = 0;
        $wykop = new libs_Wapi($this->key, $this->secret);
        
        do {

            $result = $wykop->doRequest("search/entries/page/$strona", array('q' => $this->tag));
             foreach ($result as $r) {
                if(strtotime($r['date']) < strtotime('2013-04-06 15:30:45')){ $wyskocz = 1; } //jeżeli wpis jest starszy niż data -> wyskocz z pętli
                    $wyjatek = preg_match('/(\#ksiezycowyspacer|\#rowerowyrownik)/', $r['body']); 
                    $r['body'] = str_replace(array('km', 'KM','\\', ' '), '', $r['body']);
                    $r['body'] = preg_replace('@\([^)]*\)@', '', $r['body']);
                    $r['body'] = str_replace(array(','), '.', $r['body']);

                    if(preg_match_all("@(?<dane>[0-9]{1,5}[\.]{0,1}[0-9]{0,3}(?:-[0-9]{1,5}[\.]{0,1}[0-9]{0,3}){1,}=[0-9]{1,5}[\.]{0,1}[0-9]{0,3})@", $r['body'],$dane)){
                        $zm = preg_split('/[-=]/', $dane['dane'][0]);
                        $zm = array_map('floatval', $zm);

                        $uzytkownicy[] = array('idWpisu' => $r['id'], 'author' => $r['author'], 'dystans' => $this->sumujDystans($zm), 'data_wpisu' => $r['date']);

                    }
                  
            }
            if($wyskocz == 1) break;
            $strona++;
        } while (true);
        
        return $uzytkownicy;
    }
    
    public function dodajNowegoZawodnika($array){
         try {
             $polaczenie = new PDO($this->dbCfg, $this->dbUser, $this->dbPassword);
             $polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             foreach ($array as $value) {
                 $zap = $polaczenie->prepare('SELECT nick FROM `zawodnicy` WHERE `nick` = :nick');
                 $zap->bindValue(':nick', $value['author']);
                 $zap->execute();
                 $wynikZapytnia = $zap->fetch(PDO::FETCH_ASSOC);
                 
                 if($wynikZapytnia == FALSE){
                     try {
                         $polaczenie->beginTransaction();
                         $zap = $polaczenie->prepare('INSERT INTO `zawodnicy` (`nick`, `data_dolaczenia`) VALUES (:nick, CURRENT_DATE( ));');
                         $zap->bindValue(':nick', $value['author']);
                         $zap->execute();
                         $polaczenie->commit();
                         echo "Dodano: $value[author]<br>";
                         
                     } catch (PDOException $exc) {
                         $polaczenie->rollBack();
                         echo $exc->getMessage();
                     }
                  }
                 
             }
         } catch (PDOException $exc){
             echo $exc->getMessage();
         }
    }
        
    public function dodajNowyWpis($array){
        try { 
            $polaczenie = new PDO($this->dbCfg, $this->dbUser, $this->dbPassword);
            $polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            foreach ($array as $value) {
                 
                $zap = $polaczenie->prepare('SELECT `data_wpisu` FROM `wpisy` ORDER BY `data_wpisu` DESC LIMIT 1');
                $zap->execute();
                $wynikZapytnia = $zap->fetch(PDO::FETCH_ASSOC);
                  
                //jeżeli pojawił się nowszy wpis 
                if(strtotime($wynikZapytnia['data_wpisu']) < strtotime($value['data_wpisu'])){
                    try {
                        $polaczenie->beginTransaction();
                        $zap = $polaczenie->prepare('SELECT `id` FROM `zawodnicy` WHERE `nick` = :nick');
                        $zap->bindValue(':nick', $value['author']);
                        $zap->execute();
                        $wynikZap = $zap->fetch(PDO::FETCH_ASSOC);
                        
                        $zap2 = $polaczenie->prepare('INSERT INTO `wpisy` VALUES (:id_wpisu, :id_zawodnika, :dystans, :data_wpisu)');
                        $zap2->bindValue(':id_wpisu', $value['idWpisu']);
                        $zap2->bindValue(':id_zawodnika', $wynikZap['id']);
                        $zap2->bindValue(':dystans', $value['dystans']);
                        $zap2->bindValue(':data_wpisu', $value['data_wpisu']);
                        $zap2->execute();
                        $polaczenie->commit();
                        $this->polubWpis($value['idWpisu']);
                         echo "$value[idWpisu] $wynikZap[id] $value[dystans] $value[data_wpisu] <br>";
                    } catch (PDOException $exc) {
                        $polaczenie->rollBack();
                        echo $exc->getMessage(); echo '<br>';
                        
                    }
                            }
            }
        } catch (PDOException $exc) {
            echo $exc->getMessage();
        }
    }
  
    public function przebiegnietyDystans(){
         try {
            $polaczenie = new PDO($this->dbCfg, $this->dbUser, $this->dbPassword);
            $polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $zap = $polaczenie->prepare('SELECT SUM(`dystans`) as `pokonany_dystans` FROM `wpisy`');
            $zap->execute();
            $wynikZapytania = $zap->fetch(PDO::FETCH_ASSOC);
            $wynikZapytania['procent_trasy'] = $wynikZapytania['pokonany_dystans'] / 24540  * 100;
        } catch (PDOException $exc) {
            echo $exc->getMessage();
        }
        return $wynikZapytania;
    }
    
    public function top5($nick){
        try {
            $polaczenie = new PDO($this->dbCfg, $this->dbUser, $this->dbPassword);
            $polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $zap = $polaczenie->prepare('SELECT * FROM `wpisy` JOIN `zawodnicy` ON `id` = `id_zawodnika` WHERE `nick` = :nick');
            $zap->bindValue(':nick', $nick, PDO::PARAM_STR);
            $zap->execute();
            $wynikZapytania = $zap->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $exc) {
            echo $exc->getMessage();
        }
        return $wynikZapytania;
        }
        
        
        public function top5dystans(){
        try {
            $polaczenie = new PDO($this->dbCfg, $this->dbUser, $this->dbPassword);
            $polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $zap = $polaczenie->prepare('SELECT SUM(  `dystans` ) AS  \'pokonany_dystans\',  `nick` FROM  `wpisy` JOIN  `zawodnicy` ON  `id` =  `id_zawodnika` GROUP BY  `nick` ORDER BY  `pokonany_dystans` DESC ');
            $zap->execute();
            $wynikZapytania = $zap->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $exc) {
            echo $exc->getMessage();
        }
        return $wynikZapytania;
        }
        
        
        public function top5tydzien(){
        try {
            $polaczenie = new PDO($this->dbCfg, $this->dbUser, $this->dbPassword);
            $polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $zap = $polaczenie->prepare("SELECT SUM(  `dystans` ) AS  'pokonany_dystans',  `nick` FROM  `wpisy` JOIN  `zawodnicy` ON  `id` =  `id_zawodnika` WHERE `data_wpisu` between date_sub(now(),INTERVAL 1 WEEK) and now() GROUP BY  `nick` ORDER BY  `pokonany_dystans` DESC");
            $zap->execute();
            $wynikZapytania = $zap->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $exc) {
            echo $exc->getMessage();
        }
        return $wynikZapytania;
        }
        
        
        
        public function ostatnioDodaneWpisy(){
        try {
            $polaczenie = new PDO($this->dbCfg, $this->dbUser, $this->dbPassword);
            $polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $zap = $polaczenie->prepare('SELECT * FROM wpisy JOIN zawodnicy ON id = id_zawodnika ORDER BY data_wpisu DESC');
            $zap->execute();
            $wynikZapytania = $zap->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $exc) {
            echo $exc->getMessage();
        }
        return $wynikZapytania;
        }
        
        
        public function dziennyKolometraz(){
        try {
            $polaczenie = new PDO($this->dbCfg, $this->dbUser, $this->dbPassword);
            $polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $zap = $polaczenie->prepare('SELECT SUM(dystans) as dystans, DAY(data_wpisu) as dzien FROM (SELECT * FROM wpisy WHERE data_wpisu BETWEEN date_sub(now(),INTERVAL 2 WEEK) and now()) as tmp GROUP By DAY(data_wpisu) ASC');
            $zap->execute();
            $wynikZapytania = $zap->fetchAll(PDO::FETCH_ASSOC);
            
            
        } catch (PDOException $exc) {
            echo $exc->getMessage();
        }
        return $wynikZapytania;
        }
        
        public function kilometrazWykres(){
        try {
            $polaczenie = new PDO($this->dbCfg, $this->dbUser, $this->dbPassword);
            $polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $zap = $polaczenie->prepare('SELECT DATE(data_wpisu) as dzien, SUM(dystans) as dystans FROM wpisy GROUP BY DATE(data_wpisu) ORDER BY dzien ASC');
            $zap->execute();
            $wynikZapytania = $zap->fetchAll(PDO::FETCH_ASSOC);
            
            
        } catch (PDOException $exc) {
            echo $exc->getMessage();
        }
        return $wynikZapytania;
        }
        
        
         public function wszyscyZawodnicy(){
        try {
            $polaczenie = new PDO($this->dbCfg, $this->dbUser, $this->dbPassword);
            $polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $zap = $polaczenie->prepare('SELECT nick FROM zawodnicy ORDER BY nick ASC');
            $zap->execute();
            $wynikZapytania = $zap->fetchAll(PDO::FETCH_ASSOC);
            
            
        } catch (PDOException $exc) {
            echo $exc->getMessage();
        }
        return $wynikZapytania;
        }
        
         public function daneZawodnika($zawodnik){
        try {
            $polaczenie = new PDO($this->dbCfg, $this->dbUser, $this->dbPassword);
            $polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $zap = $polaczenie->prepare('SELECT * FROM zawodnicy JOIN wpisy ON id = id_zawodnika WHERE nick = :nick ORDER BY data_wpisu DESC');
            $zap->bindValue(':nick', $zawodnik, PDO::PARAM_STR);
            $zap->execute();
            $wynikZapytania = $zap->fetchAll(PDO::FETCH_ASSOC);
            
            
        } catch (PDOException $exc) {
            echo $exc->getMessage();
        }
        return $wynikZapytania;
        }
        
        private function polubWpis($idWpisu) {
        
        $wykop = new libs_Wapi($this->key, $this->secret);
        $apiResult = $wykop->doRequest("user/login/appkey,$this->key,", array('accountkey' => $this->userAccountKey));

        if (!empty($apiResult['userkey'])) {
            $apiResult = $wykop->doRequest("entries/vote/entry/$idWpisu/appkey,$this->key,userkey," . $apiResult['userkey']);
        } else {
            echo 'Wystąpił błąd API : ' . $wykop->getError();
        }
    }
        
        
         public function dziennyRaport(){
            try {
            $polaczenie = new PDO($this->dbCfg, $this->dbUser, $this->dbPassword);
            $polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            
            $zap = $polaczenie->prepare('SELECT SUM(dystans) as dystans FROM wpisy WHERE date(data_wpisu) = curdate()-1');
            $zap->execute();
            $dystansDnia = $zap->fetch(PDO::FETCH_ASSOC);
            
            $zap = $polaczenie->prepare('SELECT nick, SUM(dystans) as dystans FROM wpisy JOIN zawodnicy ON id = id_zawodnika WHERE date(data_wpisu) = curdate()-1 GROUP BY nick ORDER BY dystans DESC');
            $zap->execute();
            $najwiecejKmWDniu = $zap->fetch(PDO::FETCH_ASSOC);
            
            $zap = $polaczenie->prepare('SELECT nick FROM zawodnicy WHERE data_dolaczenia = curdate()-1');
            $zap->execute();
            $dolaczyli = $zap->fetchAll(PDO::FETCH_ASSOC);
            
            $pokonanyDystans =  $this->przebiegnietyDystans();
            
            $dzien = number_format(((time()-strtotime('2013-04-06 15:30:45'))/3600/24),0);
            $dystans = number_format($pokonanyDystans['pokonany_dystans'],2);
            $procent = number_format($pokonanyDystans['procent_trasy'],2);
            $dzienDystans = number_format($dystansDnia['dystans'],2);
            $najwiecejKmWDniuNick = $najwiecejKmWDniu['nick'];
            $najwiecejKmWDniuDystans = number_format($najwiecejKmWDniu['dystans'],2);

            // TODO jeżeli 2 lub więcej osób zrobiło taki sam dystans 
            $wiadomosc = "Sztafeta trwa $dzien dni, do tej pory przebiegliśmy: $dystans km - $procent%.\n
                        W dniu dzisiejszym przebiegliśmy $dzienDystans km.";
                        if($dzienDystans > $this->rekordDystansu()){
                             $wiadomosc .= " Dziś przebiegliśmy najwięcej kilometrów w jednym dniu. BRAWO!\n";
                        }
                        $wiadomosc .= "\nNajwięcej przebiegł(a) @$najwiecejKmWDniuNick - $najwiecejKmWDniuDystans km.";
            
             if(count($dolaczyli)>0){
                 if(count($dolaczyli) == 1) $wiadomosc .= "\n\nDo sztafety dołączył(a): "; else $wiadomosc .= "\n\nDo sztafety dołączyli: "; 
                
                foreach ($dolaczyli as $value) {
                    $wiadomosc .= '@'.$value['nick']." ";
                }
                $wiadomosc .= "\nWitamy!";
            }
            
            $wiadomosc .= "\nUwaga dotycząca dodawania wpisów.
                 \nWynik zamieszczaj w pierwszej lini wpisu. Ułatwisz odejmowanie innym biegaczom oraz skryptowi.
                 \nSchemat odejmowania: [Wynik z poprzedniego wpisu] - [Twój przebyty dystans] - [Twój przebyty dystans 2] - [Twój przebyty dystans 3] = [Wynik odejmowania]
                 \nnp. 15375.59 km - 4 km - 6 km - 21,07 km= 15344,52 km
                 \nnastępnie opcjonalnie zdjęcia, opis itp.
                 \nna końcu tagi.";
            $wiadomosc .= "\n\nZnalazłeś błąd? Masz pomysł na dodatkową funkcjonalność? Napisz do mnie. Więcej statystyk na http://sztafeta.w0lny.pl\n#sztafeta #sztafetaraport ";
            
            
            $wykop = new libs_Wapi($this->key, $this->secret);
            $apiResult = $wykop->doRequest("user/login/appkey,$this->key,", array('accountkey' => $this->userAccountKey));

            if (!empty($apiResult['userkey'])) {
                $apiResult = $wykop->doRequest("entries/add/appkey,$this->key,userkey," . $apiResult['userkey'], array('body' => $wiadomosc));
            } else {
                Die('Wystąpił błąd API : ' . $apiResult['error']['message']);
            }
            
        } catch (PDOException $exc) {
            echo $exc->getMessage();
        }
        return nl2br($wiadomosc);
    }

        public function mVarDump($array){
            echo '<pre>';
            var_dump($array);
            echo '</pre>';
        }
        
       
    public function sumujDystans($tablica) {
        
        $suma = 0;
        for ($index = 1; $index < count($tablica)-1; $index++) {
            $suma += $tablica[$index];
        }
        return $suma;
    }
    
    private function rekordDystansu() {
          $polaczenie = new PDO($this->dbCfg, $this->dbUser, $this->dbPassword);
          $polaczenie->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


          $zap = $polaczenie->prepare('SELECT SUM(dystans) as dystans FROM wpisy WHERE data_wpisu < curdate()-2 GROUP by date(data_wpisu) ORDER BY dystans DESC;');
          $zap->execute();
          $dystans = $zap->fetch(PDO::FETCH_ASSOC);
          
          return $dystans['dystans'];
     }

    private function dateDiff($d1, $d2) {


        return round(abs(strtotime($d1) - strtotime($d2)) / 86400);
    }

}
?>
