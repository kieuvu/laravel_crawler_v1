<?php

namespace App\Crawler\SiteConfig\Site;

use App\Crawler\SiteConfig\SiteConfig;


class Plosone extends SiteConfig
{
  public function rootUrl()
  {
    return 'https://journals.plos.org/plosone';
  }

  public function startUrl()
  {
    return [
      "https://journals.plos.org/plosone/"
    ];
  }

  public function shouldCrawl($url)
  {
    return preg_match("/^https:\/\/journals\.plos\.org\/+/", $url);
  }

  public function shouldGetData($url)
  {
    return preg_match("/https:\/\/journals\.plos\.org\/plosone\/article\?id\=[A-Z0-9a-z\/\.]+/", $url);
  }

  public  function crawlArea()
  {
    return '#home-content a';
  }

  public function exceptionUrl($url)
  {
    if (preg_match("/^\/plosone\//", $url)) {
      return $this->rootUrl() . $url;
    } else {
      return $url;
    }
  }

  public function getData($request)
  {
    $data = [];

    $data["title"] = $request->filter('#artTitle')->text();
    dd($data);
    return $data;
  }
}
