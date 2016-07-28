<?php
namespace MichaelLuthor\Library\Baidu\Client;
use MichaelLuthor\Library\Baidu\Util\AbstractSubClient;
/**
 * @author Michael Luthor <michaelluthor@163.com>
 * @version 2016-06-30
 */
class ImageSearchClient extends AbstractSubClient {
    /**
     * @var integer
     */
    const SIZE_ALL = 0;
    
    /**
     * @var integer
     */
    const SIZE_SMALL = 1;
    
    /**
     * @var integer
     */
    const SIZE_MEDIUM = 2;
    
    /**
     * @var integer
     */
    const SIZE_BIG = 3;
    
    /**
     * @var integer
     */
    const SIZE_VERY_BIG = 9;
    
    /**
     * @var integer
     */
    public $size = self::SIZE_ALL;
    
    /**
     * {@inheritDoc}
     * @see AbstractSubClient::getResult()
     */
    public function getResult() {
        $url = 'http://image.baidu.com/search/index?tn=resultjson&ie=utf-8&word=%s&pn=%d&rn=%d&z=%d';
        $pageSize = 10;
        
        $uselessHeadCount = 0;
        $offset = $this->offset;
        if ( 0 !== $offset%$pageSize ) {
            $uselessHeadCount = $offset%$pageSize;
            $offset -= $offset%$pageSize;
        }
        
        $results = array();
        while ( count($results) < ($this->length+$uselessHeadCount) ) {
            $searchURL = sprintf($url, urlencode($this->query), $offset, $pageSize, $this->size);
            $searchReulstContent = $this->getHTMLContent($searchURL);
            $searchReulst = json_decode($searchReulstContent, true);
            foreach ( $searchReulst['data'] as $image ) {
                if ( !isset($image['objURL']) ) {
                    continue;
                }
                $results[$image['objURL']] = array(
                    'imageURL' => $image['objURL'],
                    'pageURL' => $image['fromURL'],
                    'pageTitle' => html_entity_decode(strip_tags($image['fromPageTitle'])),
                    'width' => $image['width'],
                    'height' => $image['height'],
                    'type' => $image['type'],
                );
            }
            $offset += count($searchReulst['data']);
        }
        $results = array_slice($results, $uselessHeadCount, $this->length);
        return $results;
    }
}