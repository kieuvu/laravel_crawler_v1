<?php
class Sql
{
  public function getTheFirstPendingStack($model, $site)
  {
    return $model::orderBy('visited')
      ->where('site', '=', $site)
      ->where('visited', '0')
      ->first();
  }

  public function checkExist($model, $url)
  {
    return $model::where('url_hash', '=', md5($url))->count() > 0;
  }

  public function setState($model, $state, $url)
  {
    $model::where('url_hash', '=', md5($url))->update([
      'visited'  => $state,
    ]);
  }

  public function saveUrl($model, $url, $site, $parent, $data = [])
  {
    $crawl = new $model();
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
