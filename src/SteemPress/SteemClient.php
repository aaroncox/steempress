<?php

namespace SteemPress;

use cebe\markdown;
use JsonRPC\Client;
use Symfony\Component\DomCrawler\Crawler;

class SteemClient
{

  protected $host;
  protected $client;

  public function __construct($host)
  {
    $this->host = $host;
    $this->client = new Client($host);
    $this->parser = new \cebe\markdown\MarkdownExtra();
  }

  public function previewFromPost($html, $limit = 2) {
    $elements = array();
    $dom = new \DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
      if($node->nodeType === XML_ELEMENT_NODE && sizeof($elements) <= $limit - 1) {
        $elements[] = $node->ownerDocument->saveXML($node);
      }
    }
    return implode($elements);
  }

  public function getPost($username, $permlink) {
    $client = $this->client;
    $response = $client->get_content($username, $permlink);
    $content = $this->amendPost($response);
    return $content;
  }

  protected function amendPost($post) {
    $html = $this->parser->parse($post['body']);
    return array_merge($post, array(
      'html' => $html,
      'html_preview' => $this->previewFromPost($html),
      'metadata' => json_decode($post['json_metadata'], true),
      'ts' => strtotime($post['created'])
    ));
  }

  protected function amendPosts($posts) {
    $return = array();
    // Iterate over the content to add additional data
    foreach($posts as $index => $post) {
      $return[] = $this->amendPost($post);
    }
    return $return;
  }

  public function getPostsFromAccount($account, $limit, $skip)
  {
    $client = $this->client;
    $response = $client->get_state('@' . $account);
    return $this->amendPosts($response['content']);
  }

  public function sortPosts($posts) {
    // Sort the posts by the new timestamp
    uasort($posts, function($a, $b) {
      if ($a['ts'] == $b['ts']) {
        return 0;
      }
      return ($a['ts'] < $b['ts']) ? 1 : -1;
    });
    return $posts;
  }

  public function getPosts($accounts, $limit = 5, $skip = 0)
  {
    if(is_array($accounts)) {
      $posts = array();
      foreach($accounts as $account) {
        $posts = array_merge($posts, $this->getPosts($account));
      }
    } else {
      $posts = $this->getPostsFromAccount($accounts, $limit, $skip);
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
