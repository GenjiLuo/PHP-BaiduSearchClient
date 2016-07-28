<?php
namespace MichaelLuthor\Library\Baidu\Client;
use MichaelLuthor\Library\Baidu\Util\AbstractSubClient;
/**
 * @author Michael Luthor <michaelluthor@163.com>
 * @version 2016-07-06
 */
class BaikeSearchClient extends AbstractSubClient {
    /**
     * @param number $offset
     * @param number $pageSize
     * @return string
     */
    public function getSearchURL($offset=0, $pageSize=10) {
        $urlFormate = 'http://baike.baidu.com/search/none?word=%s&pn=%d&rn=%d&enc=utf8';
        return sprintf($urlFormate, urlencode($this->query), $offset, $pageSize);
    }
    
    /**
     * {@inheritDoc}
     * @see AbstractSubClient::getResult()
     */
    public function getResult() {
        $pageSize = 10;
        
        $uselessHeadCount = 0;
        $offset = $this->offset;
        if ( 0 !== $offset%$pageSize ) {
            $uselessHeadCount = $offset%$pageSize;
            $offset -= $offset%$pageSize;
        }
        
        $totalCount = null;
        $results = array();
        while ( count($results) < ($this->length+$uselessHeadCount) ) {
            $searchURL = $this->getSearchURL($offset, $pageSize);
            $searchReulstContent = $this->getHTMLContent($searchURL);
            if ( false === $searchReulstContent ) {
                break;
            }
            
            # 如果匹配不到， 则说明没有任何搜索结果。 则停止查询.
            if ( !preg_match('#<dl class="search-list">.*?<div class="search-page">#s', $searchReulstContent, $matches) ) {
                break;
            }
            $resultContainer = $matches[0];
            
            if ( null === $totalCount ) {
                preg_match('#<div class="result-count">百度百科为您找到相关词条约(.*?)个<\/div>#s', $searchReulstContent, $totalCountMatch);
                $totalCount = (int)$totalCountMatch[1];
            }
            
            $matchCount = preg_match_all('#<dd>(.*?)</dd>#s', $resultContainer, $matches);
            foreach ( $matches[1] as $baikeItem ) {
                if ( !preg_match('#<a class="result-title" href="(http:\/\/baike\.baidu\.com.*?)" target="_blank">(.*?)</a>#s', $baikeItem, $baikeLinkMatch) ) {
                    continue;
                }
                
                $pageLink = $baikeLinkMatch[1];
                $pageTitle = str_replace('_百度百科', '', strip_tags($baikeLinkMatch[0]));
                
                preg_match('#<span class="result-date">(.*?)</span>#s', $baikeItem, $baikeDateMatch);
                $pageLink = trim($pageLink);
                
                $results[$pageLink] = array(
                    'title' => trim($pageTitle),
                    'link' => $pageLink,
                    'date' => $baikeDateMatch[1],
                );
            }
            $offset += count($matches[1]);
            if ( $offset >= $totalCount ) {
                break;
            }
        }
        $results = array_slice($results, $uselessHeadCount, $this->length);
        return $results;
    }
}