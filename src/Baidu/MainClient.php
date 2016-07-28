<?php
namespace MichaelLuthor\Library\Baidu;
use MichaelLuthor\Library\Baidu\Client\ImageSearchClient;
use MichaelLuthor\Library\Baidu\Client\ZhidaoSearchClient;
use MichaelLuthor\Library\Baidu\Client\BaikeSearchClient;
use MichaelLuthor\Library\Baidu\Client\WebSearchClient;

/**
 * @author Michael Luthor <michaelluthor@163.com>
 * @version 0.0.0
 */
class MainClient {
    /**
     * @param string $query
     * @return ImageSearchClient
     */
    public static function searchImage( $query ) {
        return new ImageSearchClient($query);
    }
    
    /**
     * 
     * @param string $query
     * @return ZhidaoSearchClient
     */
    public static function searchZhidao( $query ) {
        return new ZhidaoSearchClient($query);
    }
    
    /**
     * @param string $query
     * @return BaikeSearchClient
     */
    public static function searchBaike($query) {
        return new BaikeSearchClient($query);
    }
    
    /**
     * @param string $query
     * @return WebSearchClient
     */
    public static function searchWeb( $query ) {
        return new WebSearchClient($query);
    }
    
    /**
     * @param unknown $class
     */
    public static function _autoload( $class ) {
        $class = explode('\\', $class);
        if ( 3 > count($class) || 'MichaelLuthor' !== $class[0] ) {
            return;
        }
        
        array_shift($class);
        array_shift($class);
        array_shift($class);
        
        $path = implode(DIRECTORY_SEPARATOR, array_merge(array(dirname(__FILE__)), $class)).'.php';
        require $path;
    }
}

/**
 * Register autoload handler.
 */
spl_autoload_register(array('MichaelLuthor\\Library\\Baidu\\MainClient', '_autoload'));