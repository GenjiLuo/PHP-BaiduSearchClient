<?php
namespace MichaelLuthor\Library\Baidu\ServiceObject;
use MichaelLuthor\Library\Baidu\Util\HTTPTrait;
class ZhidaoQuestion {
    use HTTPTrait;
    
    private $url = null;
    public function __construct( $url ) {
        $this->url = $url;
    }
    
    private $pageContent = null;
    public function pull() {
        $this->pageContent = $this->getHTMLContent($this->url);
        $this->pageContent = @iconv('GBK', 'UTF-8', $this->pageContent);
    }
    
    public function getTitle() {
        preg_match('#<span class="ask-title ">(.*?)</span>#s', $this->pageContent, $matches);
        return trim($matches[1]);
    }
    
    public function getTime() {
        $uselessContent = array(
            '<ins class="accuse-area"></ins>',
            '<ins class="share-area">',
            '<span class="share-logo">',
            '</span>',
            '<em class="accuse-enter">分享<i class="i-arrow-gray-down share-arrow-gray"></i></em><span class="f-pipe">|</span>',
            '</ins>',
        );
        $uselessContent = implode("\n", $uselessContent);
        $pageContent = $this->pageContent;
        $pageContent = str_replace($uselessContent, '', $pageContent);
        preg_match('#<span class="grid-r ask-time">(.*?)</span>#s', $pageContent, $matches);
        return trim($matches[1]);
    }
    
    public function getDescription() {
        preg_match('#<pre class="line mt-5 q-content" accuse="qContent">(.*?)</pre>#', $this->pageContent, $matches);
        return isset($matches[1]) ? self::replacePicturesInText($matches[1]) : null;
    }
    
    public function getAcceptedAnswer() {
        if ( !preg_match('#<pre id="best-content-.*?" accuse="aContent" class="best-text mb-10">(.*?)</pre>#s', $this->pageContent, $matches) ) {
            return null;
        }
        return self::replacePicturesInText($matches[1]);
    }
    
    public function getProfessionalAnswer() {
        if ( preg_match('#<div class="quality-content-detail content">(.*?)</div>#s', $this->pageContent, $matches) ) {
            return self::replacePicturesInText($matches[1]);
        }
        return null;
    }
    
    public function getNormalAnswers() {
        $answers = array();
        preg_match_all('#<div id="answer-content-.*?" accuse="aContent" class="answer-text line">(.*?)</div>#s', $this->pageContent, $matches);
        foreach ( $matches[1] as $match ) {
            if ( preg_match('#<span class="con-all".*?>(.*?)</span>#s', $match, $answer) ) {
                $answers[] = self::replacePicturesInText($answer[1]);
            } else {
                preg_match('#<span class="con".*?>(.*?)</span>#s', $match, $answer);
                $answers[] = self::replacePicturesInText($answer[1]);
            }
        }
        return $answers;
    }
    
    /**
     * @var array
     */
    private static $pictureWordMap = null;
    
    /**
     * @param string $text
     * @return string
     */
    public static function replacePicturesInText( $text ) {
        if ( null === self::$pictureWordMap ) {
            $mapPath = dirname(__FILE__).'/../Util/ZhidaoPictureWordMap.php';
            self::$pictureWordMap = require $mapPath;
        }
        
        if ( !preg_match_all('#<img class="word-replace" src="(http://zhidao\.baidu\.com/api/getdecpic\?picenc=(.*?))">#', $text, $pictures) ) {
            return $text;
        }
        
        foreach ( $pictures[1] as $index => $pictureURL ) {
            $picid = $pictures[2][$index];
            if ( !isset(self::$pictureWordMap[$picid]) ) {
                self::saveWordPicture($picid, $pictureURL);
                continue;
            }
        
            $word = self::$pictureWordMap[$picid];
            $text = str_replace($pictures[0][$index], $word, $text);
        }
        return $text;
    }
    
    /**
     * @param unknown $id
     * @param unknown $url
     */
    private static function saveWordPicture( $id, $url ) {
        $path = dirname(__FILE__).'/../Data/ZhidaoWordPictures/'.$id.'.png';
        if ( file_exists($path) ) {
            return;
        }
        
        $picContent = $this->getHTMLContent($url);
        file_put_contents($path, $picContent);
    }
}