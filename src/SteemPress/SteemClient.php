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

  public function getPosts($username, $limit = 5, $skip = 0)
  {
    $client = $this->client;
    $response = $client->get_state('@' . $username);
    // Add our extra data for displaying posts
    $posts = $this->amendPosts($response['content']);
    // Sort the posts by the new timestamp
    uasort($posts, function($a, $b) {
      if ($a['ts'] == $b['ts']) {
        return 0;
      }
      return ($a['ts'] < $b['ts']) ? 1 : -1;
    });
    // Slice to get our desired amount
    $posts = array_slice($posts, $skip, $limit);
    // Return our posts
    return $posts;
  }
}
