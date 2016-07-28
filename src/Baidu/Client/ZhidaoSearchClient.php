<?php
namespace MichaelLuthor\Library\Baidu\Client;
use MichaelLuthor\Library\Baidu\Util\AbstractSubClient;
use MichaelLuthor\Library\Baidu\ServiceObject\ZhidaoQuestion;
/**
 * @author Michael Luthor <michaelluthor@163.com>
 * @version 2016-07-06
 */
class ZhidaoSearchClient extends AbstractSubClient {
    /**
     * @param number $offset
     * @param number $pageSize
     * @return string
     */
    public function getSearchURL($offset=0, $pageSize=10) {
        $urlFormate = 'http://zhidao.baidu.com/search?word=%s&pn=%d&lm=0&rn=%d&fr=search&ie=utf8';
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
            
            $searchReulstContent = @iconv('GBK', 'UTF-8', $searchReulstContent);
            if ( false === $searchReulstContent ) {
                break;
            }
            
            if ( !preg_match('/<div class="list-inner">.*?<div class="list-footer">/s', $searchReulstContent, $matches) ) {
                sleep(1);
                continue;
            }
            $resultContainer = $matches[0];
            
            $matchCount = preg_match_all('#<dl class=".*?" data-fb="pos:dt>a,type:normal" data-rank=".*?">(.*?)</dl>#s', $resultContainer, $matches);
            
            foreach ( $matches[1] as $question ) {
                if ( !preg_match('#<dt .*?(http://zhidao.baidu.com/question/.*?\.html).*?</dt>#s', $question, $questionLinkMatch) ) {
                    continue;
                }
                
                $questionLink = $questionLinkMatch[1];
                $questionTitle = strip_tags($questionLinkMatch[0]);
                
                preg_match('#<span class="mr-8">(.*?)</span>#s', $question, $questionDateMatch);
                preg_match('#<span class="mr-8">.*?(\d*?)个回答.*?</span>#s', $question, $questionAnswerCountMatch);
                
                # 如果没有回答， 则匹配不到答案数.
                if ( !isset($questionAnswerCountMatch[1]) ) {
                    $questionAnswerCountMatch[1] = 0;
                }
                
                $questionLink = trim($questionLink);
                $results[$questionLink] = array(
                    'title' => trim($questionTitle),
                    'link' => $questionLink,
                    'date' => $questionDateMatch[1],
                    'answerCount' => $questionAnswerCountMatch[1],
                    'question' => new ZhidaoQuestion($questionLink)
                );
            }
            $offset += count($matches[1]);
            
            if ( !preg_match('#<a class="pager-next" href=".*?">下一页&gt;</a>#s', $searchReulstContent) ) {
                break;
            }
            
        }
        $results = array_slice($results, $uselessHeadCount, $this->length);
        return $results;
    }
}