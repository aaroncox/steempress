<?php

namespace SteemPress;

use Silex\Application as SilexApplication;
use Silex\ServiceProviderInterface;

class SteemProvider implements ServiceProviderInterface
{

  protected $host;

  public function boot(SilexApplication $app)
  {

  }

  public function register(SilexApplication $app)
  {
    $app["steemd.host"] = $this->host;
    $app["steemd"] = $app->share(new SteemService());
  }

}
