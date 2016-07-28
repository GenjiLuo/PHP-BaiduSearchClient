<?php
namespace MichaelLuthor\Library\Baidu\Util;
abstract class AbstractSubClient {
    /**
     * 
     */
    use HTTPTrait;
    
    /**
     * @var string
     */
    protected $query = null;
    
    /**
     * @param string $query
     */
    public function __construct( $query ) {
        $this->query = $query;
    }
    
    /**
     * @var integer
     */
    public $offset = 0;
    
    /**
     * @var integer
     */
    public $length = 50;
    
    /**
     * @return array
     */
    abstract public function getResult ();
}