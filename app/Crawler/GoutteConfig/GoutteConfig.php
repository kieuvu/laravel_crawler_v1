<?php

namespace App\Crawler\GoutteConfig;

use Goutte\Client;

class GoutteConfig
{
  public function __construct()
  {
    $this->client = new Client();
  }

  public function makeRequest($url)
  {
    return $this->client->request('GET', $url);
  }

  public function getAllLink($url, $filter)
  {
    $data = $this->makeRequest($url)->filter($filter)->each(function ($node) {
      return $node->attr("href");
    });

    return $data;
  }
}
