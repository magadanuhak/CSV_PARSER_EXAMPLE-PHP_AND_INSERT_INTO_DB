<?php
Class AirportParser {
    //Настройки базы данных
    private $db = [
        'host'        => '',
        'username'    => '',
        'password'    => '',
        'db'          => ''
    ];
    // Добавление информации из csv в базу данных
    //$filename = адрес файла csv
  function csvToDb($filename='', $delimiter=',')
  {
      $con = mysqli_connect($this->db['host'], $this->db['username'], $this->db['password'], $this->db['db']);
      if (!$con) {
           die('Ошибка подключения к базы данных ' . mysqli_error());
      } else {
          echo 'Подключение к базы данных успешно';
      }
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
                  mysqli_query($con, $query . implode(',', $values));
                  $values = [];
                  $count = 0;
              }
          }
          if(!empty($values)){
              //если в конце меньше 1000 строк то добавляем оставшийся данные в базу данных
              mysqli_query($con, $query . implode(',', $values));
          }
          fclose($handle);
      }
  }
}
$airpot = new AirportParser;
$airpot->csvToDb("airport-codes_csv.csv");