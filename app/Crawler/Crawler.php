<?php

namespace App\Crawler;

use App\Crawler\SiteConfig\Site\Eprints as SiteClass;
use App\Crawler\GoutteConfig\GoutteConfig as GoutteClass;
use App\Crawler\DBMethod\Sql;

class Crawler
{
  public function __construct()
  {
    $this->QUEUE  = new Sql();
    $this->SITE   = new SiteClass();
    $this->GOUTTE = new GoutteClass();
  }

  public function run()
  {
    $firstStack = $this->QUEUE->getTheFirstPendingStack($this->SITE->rootUrl());
    if (is_null($firstStack)) {
      echo "Done\n";
      return;
    }

    $currentUrl = $firstStack->url;
    echo "Goto: [$currentUrl]\n";

    $urls = array_map(function ($item) {
      return $this->SITE->exceptionUrl($item);
    }, array_filter($this->GOUTTE->getAllLink($currentUrl, $this->SITE->crawlArea()), function ($item) {
      return !is_null($item);
    }));
    foreach ($urls as $url) {
      if ($this->QUEUE->checkExist($url)) {
        continue;
      } else {
        if ($this->SITE->shouldGetData($url)) {
          $request = $this->GOUTTE->makeRequest($url);
          $data =  $this->SITE->getData($request);
          $this->QUEUE->saveUrl($url, $this->SITE->rootUrl(), $currentUrl, $data);
          print_r($data);
        } else if ($this->SITE->shouldCrawl($url)) {
          $this->QUEUE->saveUrl($url, $this->SITE->rootUrl(), $currentUrl);
        } else {
          continue;
        }
      }
    }
    $this->QUEUE->setState(1, $currentUrl);
    $this->run();
  }

  public function init()
  {
    foreach ($this->SITE->startUrl() as $url) {
      if (!$this->QUEUE->checkExist($url)) {
        $this->QUEUE->saveUrl($url, $this->SITE->rootUrl(), $this->SITE->rootUrl());
        echo "Added Start URL: [$url]\n";
      }
    }
    $this->run();
  }
}
