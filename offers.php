<?php
 class Offers 
 {
     public function __construct($url = null)
     {
         if ($url) {
            $AllOffers  = json_decode(@file_get_contents($url),true);
            $this->preparedOffers = $this->filterOffers($AllOffers);
            $this->preparedOffers = $this->changePath($this->preparedOffers);
            $this->preparedOffers = $this->sortByPublishFrom($this->preparedOffers);
            $this->preparedOffers = json_encode($this->preparedOffers);
         }
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
        },$offers);
        return $offers;
     }

     public function sortByPublishFrom($offers) {
        uasort($offers, function($elem, $nextElem){
            if ($elem['publish_from'] == $nextElem['publish_from']) {
                return 0;
            }
            return ($elem['publish_from'] > $nextElem['publish_from']) ? -1 : 1;
        });
        return $offers;
     }
 }
 
 $result = new Offers("http://mega.stage07.scaph.ru/api/1.1/offers/");
 print_r($result);