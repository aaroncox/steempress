<?php

namespace SteemPress;

use Silex\Application;

class SteemService
{
  public function __invoke(Application $app)
  {
    return new SteemClient($app["steemd.host"]);
  }
}
