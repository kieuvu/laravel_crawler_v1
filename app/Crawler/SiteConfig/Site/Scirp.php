<?php

namespace App\Crawler\SiteConfig\Site;

use App\Crawler\SiteConfig\SiteConfig;


class Scirp extends SiteConfig
{
  public function rootUrl()
  {
    return 'https://www.scirp.org/journal/articles.aspx';
  }

  public function startUrl()
  {
    return [
      'https://www.scirp.org/journal/articles.aspx'
    ];
  }

  public function shouldCrawl($url)
  {
    return preg_match("/^https:\/\/www\.scirp\.org\/+/", $url);
  }

  public function shouldGetData($url)
  {
    return preg_match("/^https:\/\/www\.scirp\.org\/journal\/paperinformation\.aspx\?paperid\=[0-9A-Za-z]+/", $url);
  }

  public  function crawlArea()
  {
    return '.container a';
  }

  public function exceptionUrl($url)
  {
    return $url;
  }

  public function getData($request)
  {
    $data = [];

    $data["title"] = $request->filter('.art_title')->text();

    return $data;
  }
}
