<?php
Class Database{
    //Настройки базы данных
    public $con;
    private $db = [
        'host'        => '',
        'username'    => '',
        'password'    => '',
        'db'          => ''
    ];
    function open(){
        $this->con = mysqli_connect($this->db['host'], $this->db['username'], $this->db['password'], $this->db['db']);
        if (!$this->con) {
            return 'Ошибка подключения к базы данных ' . mysqli_error();
        } else return $this->con;
    }
    function insert($query){
        if(!empty($this->con)) {
            mysqli_query($this->con,$query);
        }
    }
    function close(){
        if(!empty($this->con)) {
            mysqli_close($this->con);
        }
    }
}
Class AirportParser {

    // Добавление информации из csv в базу данных
    //$filename = адрес файла csv
  function csvToDb($filename='', $delimiter=',')
  {
      $database = new Database;
      //здесь первая часть запоса к которой прикрепляем все данные
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
      if(!file_exists($filename) || !is_readable($filename)) {
          return FALSE;
      }
      $count = 0;
      $header = NULL;
      if (($handle = fopen($filename, 'r')) !== FALSE)
      {   // 1000 это длина строки
          $database->open();
          while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
              if($count < 1000){
                  if (!$header){
                      $header = $row;
                  }
                  else {
                      $name = str_replace('\'',' ',$row[2]);
                      $municipality = str_replace('\'',' ',$row[7]);
                      //здесь формируеться масив для запроса
                      $values[] ="(
                          '{$row[0]}',
                          '{$row[1]}',
                          '{$name}',
                          '{$row[3]}',
                          '{$row[4]}',
                          '{$row[5]}',
                          '{$row[6]}',
                          '{$municipality}',
                          '{$row[8]}',
                          '{$row[9]}',
                          '{$row[10]}',
                          '{$row[11]}'
                          )";
                      $count++;
                  }
              } else{
                  //Запись 1000 строк в базу данных
                  $database->insert($query . implode(',', $values));
                  $values = [];
                  $count = 0;
              }
          }
          if(!empty($values)){
              //если в конце меньше 1000 строк то добавляем оставшийся данные в базу данных
              $database->insert($query . implode(',', $values));
          }
          fclose($handle);
          $database->close();
      }
  }
}
$airpot = new AirportParser;
$airpot->csvToDb("airport-codes_csv.csv");
