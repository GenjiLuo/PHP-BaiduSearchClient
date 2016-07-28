<?php
namespace MichaelLuthor\Library\Baidu\Util;
trait HTTPTrait {
    /**
     * @param unknown $url
     * @return string|false
     */
    protected function getHTMLContent( $url ) {
        $triedTimtCount = 3;
        while ( 0 < $triedTimtCount ) {
            $triedTimtCount --;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT,2);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $pageContent = curl_exec($ch);
            curl_close($ch);
            if ( false !== $pageContent ) {
                return $pageContent;
            }
            
            sleep(1);
        }
        return false;
    }
}