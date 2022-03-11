<?php

namespace App\Crawler\DBMethod;

use App\Models\Crawl;

class Sql
{
  public function getTheFirstPendingStack($site)
  {
    return Crawl::orderBy('visited')
      ->where('site', '=', $site)
      ->where('visited', '0')
      ->first();
  }

  public function checkExist($url)
  {
    return Crawl::where('url_hash', '=', md5($url))->count() > 0;
  }

  public function setState($state, $url)
  {
    Crawl::where('url_hash', '=', md5($url))->update([
      'visited'  => $state,
    ]);
  }

  public function saveUrl($url, $site, $parent, $data = [])
  {
    $crawl = new Crawl();
    $crawl->url = $url;
    $crawl->url_hash = md5($url);
    $crawl->site = $site;
    $crawl->parent = $parent;
    if (count($data) > 0) {
      $crawl->data = json_encode($data);
    }
    $crawl->save();
  }
}
