<?php

namespace App\Crawler;

use Goutte\Client;
use App\Models\Link;
use App\Models\Document;

class Eprints
{
  private $domain = 'http://eprints.lse.ac.uk';

  /**
   * Find Target Data (Regex): /http:\/\/eprints\.lse\.ac\.uk\/[0-9]+\//g
   */

  public function urlCrawler()
  {
    $client = new Client();

    for ($i = 2023; $i >= 1970; $i--) {
      $url     = 'http://eprints.lse.ac.uk/view/year/' . $i . '.html';
      $crawler = $client->request('GET', $url);

      $data = $crawler->filter('p > a')->each(function ($node) {
        return $node->attr("href");
      });

      foreach ($data as $url) {
        echo "$url \n";
        if (Link::where('url_hashed', '=', md5($url))->count() > 0) {
          $this->dataCrawler($url);
          echo "Existed \n";
        } else {
          $newRecord             = new Link();
          $newRecord->url        = $url;
          $newRecord->url_hashed = md5($url);
          $newRecord->save();

          $this->dataCrawler($url);
        }
      }
    }
  }

  public function dataCrawler($target)
  {
    $targetLink = Link::where('url_hashed', '=', md5($target));

    if ($targetLink->first()['visited'] == 0) {
      $url     = $target;
      $client  = new Client();
      $crawler = $client->request('GET', $url);

      $data = [];

      $data["title"]         = $crawler->filter('.ep_tm_pagetitle')->text();
      $data["content"]       = $crawler->filter('.ep_summary_content_main > p')->last()->text();
      $data["author"]        = $crawler->filter('.ep_summary_content_main .person_name')->text();
      $data['download_link'] = ($crawler->filter('a.ep_document_link')->count() > 0)
        ? $crawler->filter('a.ep_document_link')->attr('href')
        :  "";

      if (
        empty($data["title"]) ||
        empty($data["content"]) ||
        empty($data["author"]) ||
        empty($data['download_link'])
      ) {
        $targetLink->update(['visited' => 1]);
        echo "Invalid Data \n";
      } else {
        $document = new Document();
        $document->title = $data['title'];
        $document->content = $data['content'];
        $document->author = $data['author'];
        $document->download_link = $data['download_link'];
        $document->save();

        $targetLink->update([
          'visited' => 1,
          'has_data' => 1
        ]);
        echo "Successful \n";
      }
    } else {
      echo "Visited \n";
    }
  }
}
