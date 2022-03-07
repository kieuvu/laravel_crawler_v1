<?php

namespace App\Crawler;

use Goutte\Client;
use App\Models\Link;
use App\Models\Document;

class Test
{
  /**=========================================================SETUP======================================================= */

  // Set root URL
  public function rootURL()
  {
    return "http://eprints.lse.ac.uk/";
  }

  // Set URLs which may be contain target URL
  public function validURL($url)
  {
    return
      preg_match("/^http:\/\/eprints\.lse\.ac\.uk\/view\/[a-zA-Z0-9\/]+/", $url);
  }

  // Set the target URL
  public function targetURL($url)
  {
    return preg_match("/^http:\/\/eprints\.lse\.ac\.uk\/[0-9]+\//", $url);
  }


  // Crawl handler
  public function crawlHandler($url, $filter)
  {
    $client  = new Client();
    $crawler = $client->request('GET', $url);

    $data = $crawler->filter($filter)->each(function ($node) {
      return $node->attr("href");
    });

    return $data;
  }

  /**=========================================================HANDLE====================================================== */

  // Find and get valid URL from root URL
  public function findAllURL()
  {
    $data = $this->crawlHandler($this->rootURL(), "a");

    $validURLs = array_filter($data, function ($item) {
      return $this->validURL($item);
    });

    foreach ($validURLs as $url) {
      $this->findParentURL($url);
    }
  }

  // Find the URL which may be containt target URL
  public function findParentURL($target)
  {
    $data = $this->crawlHandler($target, ".ep_tm_page_content a");

    $processedURLs  = array_map(
      function ($item) use ($target) {
        return $target . $item;
      },
      $data
    );

    foreach ($processedURLs as $url) {
      $this->findURLContainData($url);
    }
  }

  // Find the target URL from valid URL then store in database
  public function findURLContainData($target)
  {
    $data = $this->crawlHandler($target, ".ep_tm_page_content a");

    foreach ($data as $url) {
      if ($this->targetURL($url)) {
        echo "$url \n";
        if (Link::where('url_hashed', '=', md5($url))->count() > 0) {
          $this->getData($url);
          echo "Existed \n";
        } else {
          $newRecord             = new Link();
          $newRecord->url        = $url;
          $newRecord->url_hashed = md5($url);
          $newRecord->save();

          $this->getData($url);
        }
      };
    }
  }

  // Get and validate content from target URL
  public function getData($target)
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
        : "";

      if (
        empty($data["title"]) ||
        empty($data["content"]) ||
        empty($data["author"]) ||
        empty($data['download_link'])
      ) {
        $targetLink->update(['visited' => 1]);
        echo "Invalid Data \n";
      } else {
        $document                = new Document();
        $document->title         = $data['title'];
        $document->content       = $data['content'];
        $document->author        = $data['author'];
        $document->download_link = $data['download_link'];
        $document->save();

        $targetLink->update([
          'visited'  => 1,
          'has_data' => 1
        ]);
        echo "Successful \n";
      }
    } else {
      echo "Visited \n";
    }
  }

  // Initial function
  public function init()
  {
    $this->findAllURL();
  }
}
