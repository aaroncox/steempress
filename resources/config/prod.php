<?php

// set the language
$app['locale'] = 'en';

// set the steem json-rpc that we're going to request data from
$app['steemd'] = 'https://steem.steemstats.com';

// theme configuration
$app['theme'] = 'foundation6-default';

// what type of posts should we load?
$app['steem'] = [
  // an array of users allowed to appear
  'accounts' => ['jesta'],
  // an array of tags to ensure exist
  'tags' => [
    'steemstats',
  ],
];

$app['blog'] = [
  'title' => 'SteemPress powered Blog'
];
