<?php

namespace App\Crawler;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\BrowserKit\HttpBrowser;
use App\Http\Controllers\Crawler\SaveData;
use Symfony\Component\HttpClient\HttpClient;


class Eprints
{

  private $domain = 'http://eprints.lse.ac.uk';

  public function urlCrawler()
  {
    for ($i = 1970; $i <= 2023; $i++) {
      $url     = 'http://eprints.lse.ac.uk/view/year/' . $i . '.html';
      $client  = new Client();
      $crawler = $client->request('GET', $url);

      $data = $crawler->filter('p > a')->each(function ($node) {
        return $node->attr("href");
      });

      foreach ($data as $url) {
        $saver = new SaveData();
        $saver->saveUrl($url);
        $this->dataCrawler($url);
      }
    }
  }

  public function dataCrawler($target)
  {
    $url     = $target;
    $client  = new Client();
    $crawler = $client->request('GET', $url);

    $data = [];

    $data["title"]         = $crawler->filter('.ep_tm_pagetitle')->text();
    $data["content"]       = $crawler->filter('.ep_summary_content_main > p')->last()->text();
    $data["author"]        = $crawler->filter('.ep_summary_content_main .person_name')->text();
    $data['download_link'] = ($crawler->filter('a.ep_document_link')->count() > 0)
      ? $crawler->filter('a.ep_document_link')->attr('href')
      : "";

    $saver = new saveData();
    $saver->saveDocument($data);
    $saver->updateStage($url, 1);
  }
}
