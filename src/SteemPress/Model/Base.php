<?php

namespace SteemPress\Model;

use Symfony\Component\DomCrawler\Crawler;

class Base
{

  public function __construct($data) {
    foreach($data as $key => $value) {
      $this->$key = $value;
    }
  }

}
