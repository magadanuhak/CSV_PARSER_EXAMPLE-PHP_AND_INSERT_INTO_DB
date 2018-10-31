<?php
class Database
{
    //Настройки базы данных
    public $con;
    private $db = [
        'host'        => '',
        'username'    => '',
        'password'    => '',
        'db'          => ''
    ];
    //Функция открытия соединения с базой данных
    function open()
    {
        $this->con = mysqli_connect($this->db['host'], $this->db['username'], $this->db['password'], $this->db['db']);
        if (!$this->con) {
            return 'Ошибка подключения к базы данных ' . mysqli_error();
        } else {
            return $this->con;
        }
    }
    //функция всакки данных в базу данных
    function insert($query)
    {
        if (!empty($this->con)) {
            $result = mysqli_query($this->con,$query);
            if (empty($result)) {
                return "Ошибка при добавлении данных в БД - добавлено 0 строк";
            } else {
                return $result;
            }
        }
    }
    //Функция закрытия соединения с базой данных
    function close()
    {
        if (!empty($this->con)) {
            $result = mysqli_close($this->con);
            if (empty($result)) {
                return "Не смог закрыть соединение к БД";
            } else {
                return $result;
            }
        }
    }
}
Class AirportParser
{
    //Метод добавления информации из csv в базу данных
    //$filename = адрес файла csv
    public $database;
    public function __construct()
    {
        $this->database = new Database;
    }

    function csvToDb($filename='', $delimiter=',')
    {

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
      if (!file_exists($filename) || !is_readable($filename)) {
          return false;
      }
      //Счётчик записей
      $count = 0;
      $header = null;
      if (($handle = fopen($filename, 'r')) !== false) {
          // 1000 это длина строки
          $this->database->open();
          fgetcsv($handle, 1000, $delimiter);
          while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
              if ($count < 1000) {
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
              } else {
                  //Запись 1000 строк в базу данных
                  $this->database->insert($query . implode(',', $values));
                  $values = [];
                  $count = 0;
              }
          }
          if (!empty($values)) {
              //если в конце меньше 1000 строк то добавляем оставшийся данные в базу данных
              $this->database->insert($query . implode(',', $values));
          }
          fclose($handle);
          $this->database->close();
      }
    }
}
$airpot = new AirportParser;
$airpot->csvToDb("airport-codes_csv.csv");
