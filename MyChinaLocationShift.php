<?php

require_once('src/ChinaLocationShift.php');

if (file_exists('config.php'))
    include_once('config.php');

if (!defined('CHINA_LOCATION_SHIFT_DBHOST'))
    define('CHINA_LOCATION_SHIFT_DBHOST', 'localhost');
if (!defined('CHINA_LOCATION_SHIFT_DBNAME'))
    define('CHINA_LOCATION_SHIFT_DBNAME', 'china_location_shift');
if (!defined('CHINA_LOCATION_SHIFT_DBUSER'))
    define('CHINA_LOCATION_SHIFT_DBUSER', 'root');
if (!defined('CHINA_LOCATION_SHIFT_DBPASS'))
    define('CHINA_LOCATION_SHIFT_DBPASS', 'mysql');

class MyChinaLocationShift extends ChinaLocationShift
{
    private static $dblink = null;
    private $shift_data = array();
    private $unshift_data = array();

    private function connectDb()
    {
        if (self::$dblink == null) {
            self::$dblink = mysql_connect(CHINA_LOCATION_SHIFT_DBHOST, CHINA_LOCATION_SHIFT_DBUSER, CHINA_LOCATION_SHIFT_DBPASS);
            mysql_select_db(CHINA_LOCATION_SHIFT_DBNAME);
        }
    }

    protected function getData($type, $j ,$i)
    {
        if ($type == self::SHIFT) {
            $data = &$this->shift_data;
            $table = 'china_location_shift';
        } else {
            $data = &$this->unshift_data;
            $table = 'china_location_unshift';
        }

        $id1 = $i + self::LONGITUDE_COUNT * $j;
        if (array_key_exists($id1, $data)) {
            return $data[$id1];
        }
        
        $id2 = $i + 1 + self::LONGITUDE_COUNT * $j;
        $id3 = $i + self::LONGITUDE_COUNT * ($j + 1);
        $id4 = $i + 1 + self::LONGITUDE_COUNT * ($j + 1);

        $this->connectDb();
        $query = "SELECT `latitude`, `longitude` FROM `{$table}` WHERE `id` = '{$id1}' OR `id` = '{$id2}' OR `id` = '{$id3}' OR `id` = '{$id4}'";
        $result = mysql_query($query);
        if ($result) {
            $data[$id1] = array();
            while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
                $data[$id1][] = $row;
            }
            mysql_free_result($result);
            return $data[$id1];
        }
        return false;
    }

    public function clean()
    {
        foreach ($this->shift_data as $k => $v) {
            unset($this->shift_data[$k]);
        }
        foreach ($this->unshift_data as $k => $v) {
            unset($this->unshift_data[$k]);
        }
    }

}