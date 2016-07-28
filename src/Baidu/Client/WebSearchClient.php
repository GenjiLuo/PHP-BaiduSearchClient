<?php
namespace MichaelLuthor\Library\Baidu\Client;
use MichaelLuthor\Library\Baidu\Util\AbstractSubClient;
/**
 * @author Michael Luthor <michaelluthor@163.com>
 * @version 2016-07-06
 */
class WebSearchClient extends AbstractSubClient {
    /**
     * @param number $offset
     * @param number $pageSize
     * @return string
     */
    public function getSearchURL($offset=0, $pageSize=10) {
        $urlFormate = 'https://www.baidu.com/s?wd=%s&pn=%d&rn=%d&tn=baidulocal&ie=utf-8';
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
        
        $results = array();
        while ( count($results) < ($this->length+$uselessHeadCount) ) {
            $searchURL = $this->getSearchURL($offset, $pageSize);
            $searchReulstContent = $this->getHTMLContent($searchURL);
            if ( false === $searchReulstContent ) {
                break;
            }
            
            if ( !preg_match('/<br clear=all>(.*?)<div style="text-align:center;background-color:#e6e6e6;height:20px;padding-top:2px;font-size:12px;">/s', $searchReulstContent, $matches) ) {
                continue;
            }
            $resultContainer = $matches[1];
            
            $matchCount = preg_match_all('/<table border="0" cellpadding="0" cellspacing="0"><tr><td class=f>.*?<a href="(.*?)" target="_blank">(.*?)<\/a><br>.*?<font color=#008000>.*?&nbsp;.*?&nbsp;(.*?)&nbsp;<\/font>.*?<br><\/font><\/td><\/tr><\/table>/s', $resultContainer, $webPageMatches);
            foreach ( $webPageMatches[1] as $index => $resultItem ) {
                $pageLink = trim($webPageMatches[1][$index]);
                $pageTitle = strip_tags($webPageMatches[2][$index]);
                $pageDate = trim($webPageMatches[3][$index]);
                
                $results[$pageLink] = array(
                    'title' => trim($pageTitle),
                    'link' => $pageLink,
                    'date' => $pageDate,
                );
            }
            $offset += count($webPageMatches[1]);
            
            if ( !preg_match('#<a href=".*?"><font size=3>下一页</font></a>#s', $searchReulstContent) ) {
                break;
            }
        
        }
        $results = array_slice($results, $uselessHeadCount, $this->length);
        return $results;
    }
}