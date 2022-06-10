<?php
/*namespace  Drupal\scraping\Services;

use Drupal\Component\Utility\Html;

class ScrapingServices {

  public function getContentUrl($endpont) {
    $return = [];
    $url = "https://www.milanuncios.com/motor/";
    $agents = 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.155 Safari/537.36';
    $referer = "https://www.milanuncios.com";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_USERAGENT, $agents);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);

    $titles = stripos($data, 'class="Births"');
    return $return;
  }
}*/

namespace Drupal\scraping\Services;

use Drupal\Component\Utility\Html;
use GuzzleHttp\Client;

class ScrapingServices
{
  protected $client;

  public function __construct(Client $client)
  {
    $this->client = $client;
  }

  public function getContentUrlV2($endpont)
  {
    $params = [
      'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.155 Safari/537.36',
        'Accept' => 'text/html'
      ]
    ];
    try {
      $response = $this->client->request('GET', $endpont, $params);
    } catch (\Exception $exception) {
      $response = $exception;
    }

    return $response;

  }

  public function getContentUrl($endpont) {
    $return = [];
    $url = "https://www.milanuncios.com";
    $agents = 'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.155 Safari/537.36';
    $referer = "https://www.milanuncios.com";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_USERAGENT, $agents);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);

    $pokemon_doc = new \DOMDocument;
    libxml_use_internal_errors(true);
    $pokemon_doc->loadHTML($data);
    libxml_clear_errors();
    $pokemon_xpath = new \DOMXPath($pokemon_doc);
    $titles = $pokemon_xpath->evaluate('string(//h2[@class="ma-AdCard-title-text"]/a)');

    return $return;
  }
}
