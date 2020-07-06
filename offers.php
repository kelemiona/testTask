<?php
 header("Content-Type: application/json");
 class Offers 
 {
     public function __construct($url) {
        $this->offers = json_decode(@file_get_contents($url),true);
     }

     public function filterOffers($offers) {
        $sortOffers = array_filter($offers, function($elem) {
            if  ($elem['for_megacard_holders'] && strlen($elem['content'])<1000) {
                return $elem;
            }
        });
        return $sortOffers;
     }
     
     public function changePath($offers) {
        $offers = array_map(function($elem){
            $elem['images'][0]['src'] = preg_replace("/^/", 'https://mega.ru', $elem['images'][0]['src']);
            $elem['content'] = preg_replace("/=\"\/upload/", '="https://mega.ru/upload', $elem['content']);
            return $elem;
        }, $offers);
        return $offers;
     }

     public function sortByPublishFrom($offers) {
        usort($offers, function($elem, $nextElem){
            if (strtotime($elem['publish_from']) == strtotime($nextElem['publish_from'])) {
                return 0;
            }
            return (strtotime($elem['publish_from']) > strtotime($nextElem['publish_from'])) ? -1 : 1;
        });
        return  $offers;
     }

     public function getOffers(){
       return $this->sortByPublishFrom($this->changePath($this->filterOffers($this->offers)));
     }
 }
 
 $result = new Offers("http://mega.stage07.scaph.ru/api/1.1/offers/");
 $result = json_encode($result->getOffers(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
 echo ($result);
