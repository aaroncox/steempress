<?php

namespace SteemPress;

use Silex\Application as SilexApplication;

class SteemService
{
  public function __invoke(SilexApplication $app)
  {
    return new SteemClient($app["steemd.host"]);
  }
}
