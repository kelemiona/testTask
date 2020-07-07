<?php
header("Content-Type: application/json");

class Offers
{

    private $offers;

    public function __construct($url)
    {
        $this->offers = $this->getDecodeContent($url);
    }

    private function getDecodeContent($url)
    {
        try {
            $content = @file_get_contents($url);
            if (!$content) {
                throw new Exception("Cannot access '$url' to read contents.");
            } else {
                return json_decode($content, true);
            }
        } catch (Throwable $t) {
            echo "Error: ", $t->getMessage();
            die();
        }
    }

    private function filterOffers($offers)
    {
        return array_filter($offers, function ($elem) {

            if ($elem['for_megacard_holders'] && (mb_strlen(($elem['content']), "utf-8") >= 1000)) {
                return $elem;
            }
        });
    }

    private function changePath($offers)
    {
        $offers = array_map(function ($elem) {
            $elem['images'][0]['src'] = 'https://mega.ru' . $elem['images'][0]['src'];
            $elem['content'] = preg_replace("/=\"\/upload/", '="https://mega.ru/upload', $elem['content']);
            return $elem;
        }, $offers);
        return $offers;
    }

    private function sortByPublishFrom($offers)
    {
        usort($offers, function ($elem, $nextElem) {
            if (strtotime($elem['publish_from']) == strtotime($nextElem['publish_from'])) {
                return 0;
            }
            return (strtotime($elem['publish_from']) > strtotime($nextElem['publish_from'])) ? -1 : 1;
        });
        return $offers;
    }

    public function getEncodeOffers()
    {
        $filteredOffers = $this->filterOffers($this->offers);
        $absolutePath = $this->changePath($filteredOffers);
        $sortOffers = $this->sortByPublishFrom($absolutePath);
        return json_encode($sortOffers);
    }
}

$result = new Offers("http://mega.stage07.scaph.ru/api/1.1/offers/");
$result = $result->getEncodeOffers();
echo($result);
