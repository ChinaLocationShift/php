<?php

/**
 * ChinaLocationShift
 * 
 */
abstract class ChinaLocationShift
{
    const SHIFT = true;
    const UNSHIFT = false;

    const N = 10;
    const LATITUDE_MIN = 10.0;
    const LATITUDE_MAX = 54.9;
    const LONGITUDE_MIN = 72.0;
    const LONGITUDE_MAX = 137.9;
    const LONGITUDE_COUNT = 660;

    /**
     * Get location shift/unshift base data;
     *
     * @param boolean $type Shift or unshift
     * @param integer $j Latitude ID
     * @param integer $j Longitude ID
     * @return array(array($latitude, $longitude), array($latitude, $longitude), array($latitude, $longitude), array($latitude, $longitude))
     */
    abstract protected function getData($type, $j, $i);

    /**
     * Singleton instance of ChinaLocationShift
     *
     * @var ChinaLocationShift
     */
    protected static $instance = null;

    /**
     * Get singleton instance of ChinaLocationShift
     *
     * @param boolean $autocreate
     * @return ChinaLocationShift
     */
    final public static function getInstance($autocreate = true)
    {
        if ($autocreate === true && !static::$instance) {
            static::init();
        }
        return static::$instance;
    }
    
    /**
     * Create ChinaLocationShift object and stores it for singleton access
     *
     * @return ChinaLocationShift
     */
    final public static function init()
    {
        return static::setInstance(new static());
    }

    /**
     * Set the instance of the ChinaLocationShift singleton
     * 
     * @param ChinaLocationShift $instance The ChinaLocationShift object instance
     * @return ChinaLocationShift
     */
    final public static function setInstance($instance)
    {
        return static::$instance = $instance;
    }

    /**
     * Shift and unshift translate.
     *
     * @param boolean $type Shift or unshift
     * @param float $latitude
     * @param float $longitude
     * @return array($latitude, $longitude)
     */
    public function translate($type, $latitude, $longitude)
    {
        $nx = $longitude;
        $ny = $latitude;
        
        for ($k = 0; $k < self::N; $k++) {
            // FIXME: Only in China
            if ($nx < self::LONGITUDE_MIN || $nx > self::LONGITUDE_MAX || $ny < self::LATITUDE_MIN || $ny > self::LATITUDE_MAX) {
                return array($latitude, $longitude);
            }

            $i = intval(($nx - self::LONGITUDE_MIN) * 10.0);
            $j = intval(($ny - self::LATITUDE_MIN) * 10.0);
            
            $data = $this->getData($type, $j, $i);
            if ($data === false) {
                return array($latitude, $longitude);
            }
            list(list($y1, $x1), list($y2, $x2), list($y3, $x3), list($y4, $x4)) = $data;

            $t = ($nx - self::LONGITUDE_MIN - 0.1 * $i) * 10.0;
            $u = ($ny - self::LATITUDE_MIN - 0.1 * $j) * 10.0;
            
            $dx = (1.0 - $t) * (1.0 - $u) * $x1 + $t * (1.0 - $u) * $x2 + (1.0 - $t) * $u * $x3 + $t * $u * $x4 - $nx;
            $dy = (1.0 - $t) * (1.0 - $u) * $y1 + $t * (1.0 - $u) * $y2 + (1.0 - $t) * $u * $y3 + $t * $u * $y4 - $ny;

            $nx = ($nx + $longitude - $dx) / 2.0;
            $ny = ($ny + $latitude - $dy) / 2.0;
        }
        return array(round($ny, 6), round($nx, 6));
    }

    /**
     * Shift location data
     *
     * @param float $latitude
     * @param float $longitude
     * @return array($latitude, $longitude)
     */
    public function shift($latitude, $longitude)
    {
        return $this->translate(self::SHIFT, $latitude, $longitude);
    }

    /**
     * Unshift location data
     *
     * @param float $latitude
     * @param float $longitude
     * @return array($latitude, $longitude)
     */
    public function unshift($latitude, $longitude)
    {
        return $this->translate(self::UNSHIFT, $latitude, $longitude);
    }

}
