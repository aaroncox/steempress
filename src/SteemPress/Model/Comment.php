<?php

namespace SteemPress\Model;

use Symfony\Component\DomCrawler\Crawler;

class Comment extends Base
{

  public function __construct($data) {
    parent::__construct($data);
    $meta = json_decode($data['json_metadata'], true);
    $this->json_metadata = $meta;
    $this->html = $this->parseContent($data['body']);
    $this->html_preview = $this->previewFromPost($this->html);
    $this->image = (isset($meta['image']))
        ? array_shift($meta['image'])
        : $this->getFirstImage($this->html);
    $this->ts = strtotime($this->created);
  }

  public function getCategory() {
    return (is_array($this->json_metadata) && array_key_exists('tags', $this->json_metadata)) ? $this->json_metadata['tags'][0] : 'unknown';
  }

  public function getTags() {
    return (is_array($this->json_metadata) && array_key_exists('tags', $this->json_metadata)) ? $this->json_metadata['tags'] : [];
  }

  protected function parseContent($string) {
    // Let's turn image URLs into <img> tags
    $regex = "~<img[^>]*>(*SKIP)(*FAIL)|\\[[^\\]]*\\](*SKIP)(*FAIL)|\\([^\\)]*\\)(*SKIP)(*FAIL)|https?://[^/\\s]+/\\S+\\.(?:jpg|png|gif)~i";
    $string = preg_replace($regex, '<img src="${0}">', $string);
    // Then let's parse the markdown
    $string = \Michelf\Markdown::defaultTransform($string);
    // Now clean it
    $purifier = new \HTMLPurifier();
    $string = $purifier->purify($string);
    return $string;
  }

  public function previewFromPost($html, $limit = 2) {
    $elements = array();
    $dom = new \DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$html);
    foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
      if($node->nodeType === XML_ELEMENT_NODE && sizeof($elements) <= $limit - 1) {
        $elements[] = $node->ownerDocument->saveXML($node);
      }
    }
    return strip_tags(implode($elements),'<p><br>');
  }

  protected function getFirstImage($string) {
    preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $string, $matches);
    if($matches[1] && $matches[1][0]) {
      return $matches[1][0];
    }
    return null;
  }


}
