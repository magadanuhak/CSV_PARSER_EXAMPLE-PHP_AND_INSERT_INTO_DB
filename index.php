<?php
Class AirportParser {
  public $file = "airport-codes_csv.csv"; //adresa catre fisierul csv
  private $db = [
      'host'        => '',
      'username'    => '',
      'password'    => '',
      'db'          => ''
  ];
  private function csv_to_array($filename='', $delimiter=',')
  {
      if(!file_exists($filename) || !is_readable($filename))
          return FALSE;
      $header = NULL;
      $data = array();
      if (($handle = fopen($filename, 'r')) !== FALSE)
      {
          while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
          {
              if(!$header)
                  $header = $row;
              else
                  $data[] =  $row;
          }
          fclose($handle);
      }
      return $data;
  }
  public function insertAirports(){
    $con = mysqli_connect($this->db['host'], $this->db['username'], $this->db['password'], $this->db['db']);
    if (!$con) {
        die('Eșec la conectare: ' . mysqli_error());
    }
    echo 'Conectat cu succes';
    $airports = $this->csv_to_array($this->file);
    $query = "INSERT INTO `airport-codes_csv` (
                ident,
                type,
                name,
                elevation_ft,
                continent,
                iso_country,
                iso_region,
                municipality,
                gps_code,
                iata_code,
                local_code,
                coordinates
              ) VALUES  ";
    $values = [];
    $count = 0;
    foreach ($airports as $airport ){
        $name = str_replace('\'',' ',$airport[2]);
        $municipality = str_replace('\'',' ',$airport[7]);
        $values[] ="(
              '{$airport[0]}',
              '{$airport[1]}',
              '{$name}',
              '{$airport[3]}',
              '{$airport[4]}',
              '{$airport[5]}',
              '{$airport[6]}',
              '{$municipality}',
              '{$airport[8]}',
              '{$airport[9]}',
              '{$airport[10]}',
              '{$airport[11]}'
              )";
        if($count == 1000){
            $result = mysqli_query($con, $query . implode(',', $values));
            if($result == 1){
                $count = 0;
                $values = [];

            } else {
                die('eroare');
            }
        }
        $count++;
    }
    if($count > 1 && !empty($values))  {
      $result = mysqli_query($con, $query . implode(',', $values));
      if($result == 1){
          $count = 0;
          $values = [];
      } else {
          die('eroare');
      }
    }
  }
}
$airpot = new AirportParser;
$airpot->insertAirports();
