<?php

namespace SteemPress;

use Silex\Application;
use Silex\ServiceProviderInterface;

class SteemProvider implements ServiceProviderInterface
{

  protected $host;

  public function boot(Application $app)
  {

  }

  public function register(Application $app)
  {
    $app["steemd.host"] = $this->host;
    $app["steemd"] = $app->share(new SteemService());
  }

}
