<?php

namespace App\Crawler;

use Goutte\Client;
use App\Models\Crawl;

class EprintsUpdate
{
  /**=========================================================SETUP======================================================= */

  public function rootUrl()
  {
    return 'http://eprints.lse.ac.uk';
  }

  public function startUrl()
  {
    return [
      'http://eprints.lse.ac.uk/view/year/',
      'http://eprints.lse.ac.uk/view/subjects/'
    ];
  }

  public function shouldCrawl(string $url)
  {
    return preg_match("/^http:\/\/eprints\.lse\.ac\.uk\/view\/[a-zA-Z0-9\/]+/", $url);
  }

  public function shouldGetData(string $url)
  {
    return preg_match("/^http:\/\/eprints\.lse\.ac\.uk\/[0-9]+\//", $url);
  }

  public  function crawlArea()
  {
    return '.ep_tm_page_content a';
  }

  public function exceptionUrl(string $url)
  {
    if (preg_match("/^[a-zA-Z]+\.html/", $url)) {
      return 'http://eprints.lse.ac.uk/view/subjects/' . $url;
    }
    if (preg_match("/^[0-9]+\.html/", $url)) {
      return 'http://eprints.lse.ac.uk/view/year/' . $url;
    }

    return $url;
  }

  /**=========================================================HANDLE====================================================== */

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

  public function saveUrl($url, $parent, $data = [])
  {
    $crawl = new Crawl();
    $crawl->url = $url;
    $crawl->url_hash = md5($url);
    $crawl->site = $this->rootUrl();
    $crawl->parent = $parent;
    if (count($data) > 0) {
      $crawl->data = json_encode($data);
    }
    $crawl->save();
  }

  public function getTheFirstPendingStack($site)
  {
    return Crawl::orderBy('visited')
      ->where('site', '=', $site)
      ->where('visited', '0')
      ->first();
  }

  function checkExist($url)
  {
    return Crawl::where('url_hash', '=', md5($url))->count() > 0;
  }

  public function setState($state, $url)
  {
    Crawl::where('url_hash', '=', md5($url))->update([
      'visited'  => $state,
    ]);
  }

  /**=========================================================STEP====================================================== */

  public function run()
  {
    $firstStack = $this->getTheFirstPendingStack($this->rootUrl());

    $currentUrl = $firstStack->url;

    echo "Goto: [$currentUrl]\n";

    $urls = array_map(function ($item) {
      return $this->exceptionUrl($item);
    }, array_filter($this->crawlHandler($currentUrl, $this->crawlArea()), function ($item) {
      return !is_null($item);
    }));

    foreach ($urls as $url) {

      if ($this->checkExist($url)) {
        continue;
      } else {
        if ($this->shouldCrawl($url)) {
          $this->saveUrl($url, $currentUrl);
        }
        if ($this->shouldGetData($url)) {
          $client  = new Client();
          $crawler = $client->request('GET', $url);

          $data = [];

          $data["title"]         = $crawler->filter('.ep_tm_pagetitle')->text();
          $data["content"]       = $crawler->filter('.ep_summary_content_main > p')->last()->text();
          $data["author"]        = $crawler->filter('.ep_summary_content_main .person_name')->text();
          $data['download_link'] = ($crawler->filter('a.ep_document_link')->count() > 0)
            ? $crawler->filter('a.ep_document_link')->attr('href')
            : "";

          $this->saveUrl($url, $currentUrl, $data);
          $this->setState(1, $url);
          print_r($data);
        }
      }
    }
    $this->setState(1, $url);
    $this->run();
  }

  public function init()
  {
    foreach ($this->startUrl() as $url) {
      if (!$this->checkExist($url)) {
        $this->saveUrl($url, $url);
        echo "Added: [$url]\n";
        $this->run();
      } else {
        $this->run();
      }
    }
  }
}
