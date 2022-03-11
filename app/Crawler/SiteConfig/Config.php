<?php

namespace App\Crawler\SiteConfig;

abstract class SiteConfig
{
  abstract public function rootUrl();
  abstract public function startUrl();
  abstract public function shouldCrawl($url);
  abstract public function shouldGetData($url);
  abstract public function crawlArea();
  abstract public function getData();
  abstract public function exceptionUrl($url);
}
