<?php

use App\Crawler\SiteConfig\SiteConfig;


class Eprints extends SiteConfig
{
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

  public function shouldCrawl($url)
  {
    return preg_match("/^http:\/\/eprints\.lse\.ac\.uk\/view\/[a-zA-Z0-9\/]+/", $url);
  }

  public function shouldGetData($url)
  {
    return preg_match("/^http:\/\/eprints\.lse\.ac\.uk\/[0-9]+\//", $url);
  }

  public  function crawlArea()
  {
    return '.ep_tm_page_content a';
  }

  public function exceptionUrl($url)
  {
    if (preg_match("/^[a-zA-Z]+\.html/", $url)) {
      return 'http://eprints.lse.ac.uk/view/subjects/' . $url;
    }
    if (preg_match("/^[0-9]+\.html/", $url)) {
      return 'http://eprints.lse.ac.uk/view/year/' . $url;
    }

    return $url;
  }

  public function getData()
  {
  }
}
