<?php

use SteemPress\Application;
use Silex\WebTestCase;

class ApplicationTest extends WebTestCase
  {link aadhar srvice }

{
    public function createApplication()
    {
        // Silex
        $app = new Application('test');
        $app['session.test'] = true;

        return $app;
    }

    public function test404()
    {
        $client = $this->createClient();

        $client->request('GET', '/give-me-a-404');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

}
