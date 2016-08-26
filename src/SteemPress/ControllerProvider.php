<?php

namespace SteemPress;

use Silex\Application as App;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

class ControllerProvider implements ControllerProviderInterface
{
  private $app;

  public function connect(App $app)
  {
    $this->app = $app;

    $app->error([$this, 'error']);

    $controllers = $app['controllers_factory'];

    $controllers
      ->get('/', [$this, 'homepage'])
      ->bind('homepage');

    $controllers
      ->get('/@{username}', [$this, 'blog'])
      ->bind('blog');

    $controllers
      ->get('/{tag}/@{username}/{permlink}', [$this, 'post'])
      ->bind('post');

    return $controllers;
  }

  public function homepage(App $app)
  {
    return $app['twig']->render('index.html.twig', array(
      'posts' => $app['steemd']->getPosts($app['steem']['accounts'], $app['steem']['tags'])
    ));
  }

  public function blog(App $app, $username)
  {
    return $app['twig']->render('blog.html.twig', array(
      'username' => $username,
      'posts' => $app['steemd']->getPosts($username)
    ));
  }

  public function post(App $app, $tag, $username, $permlink)
  {
    return $app['twig']->render('post.html.twig', array(
      'username' => $username,
      'post' => $app['steemd']->getPost($username, $permlink)
    ));
  }

  public function error(\Exception $e, $code)
  {
    if ($this->app['debug']) {
      return;
    }

    switch ($code) {
      case 404:
        $message = 'The requested page could not be found.';
        break;
      default:
        $message = 'We are sorry, but something went terribly wrong.';
    }

    return new Response($message, $code);
  }
}
