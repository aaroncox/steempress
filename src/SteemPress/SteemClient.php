<?php

namespace SteemPress;

use JsonRPC\Client;
use JsonRPC\HttpClient;

use SteemPress\Model\Comment;

class SteemClient
{

  protected $host;
  protected $client;

  public function __construct($host)
  {
    $this->host = $host;
    $httpClient = new HttpClient($host);
    $httpClient->withoutSslVerification();
    $this->client = new Client($host, false, $httpClient);
  }

  public function getPost($username, $permlink) {
    $client = $this->client;
    $response = $client->get_content($username, $permlink);
    $post = new Comment($response);
    return $post;
  }

  protected function amendPosts($posts, $tags = []) {
    $return = [];
    foreach($posts as $data) {
      $post = new Comment($data);
      if(empty($tags) || in_array($post->category, $tags) || ($post->json_metadata && count(array_intersect($tags, $post->json_metadata['tags'])) > 0)) {
        $return[] = $post;
      }
    }
    return $return;
  }

  public function getPostsFromAccount($account, $tags, $limit, $skip)
  {
    $client = $this->client;
    $response = $client->get_state('@' . $account);
    return $this->amendPosts($response['content'], $tags);
  }

  public function sortPosts($posts) {
    // Sort the posts by the new timestamp
    uasort($posts, function($a, $b) {
      if ($a->ts == $b->ts) {
        return 0;
      }
      return ($a->ts < $b->ts) ? 1 : -1;
    });
    return $posts;
  }

  public function getPosts($accounts, $tags = array(), $limit = 5, $skip = 0)
  {
    if(is_array($accounts)) {
      $posts = array();
      foreach($accounts as $account) {
        $posts = array_merge($posts, $this->getPosts($account, $tags));
      }
    } else {
      $posts = $this->getPostsFromAccount($accounts, $tags, $limit, $skip);
    }
    // Sort these posts by timestamp
    $posts = $this->sortPosts($posts);
    // Slice to get our desired amount
    $posts = array_slice($posts, $skip, $limit);
    // Return our posts
    return $posts;
  }

  public function getApi($name)
  {
    return $this->client->call(1, 'get_api_by_name', [$name]);
  }

  public function getFollowing($username, $limit = 100, $skip = -1)
  {
    // Load the appropriate API
    $api = $this->getApi('follow_api');
    // Get our followers
    return $this->client->call($api, 'get_following', [$username, $skip, $limit]);;
  }
}
