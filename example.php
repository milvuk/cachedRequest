<?php

/*
 * Example of usage for CachedRequest class.
 */

// Prepare the PDO object to be used with CachedRequest
$pdo = new PDO('mysql:host=localhost;dbname=some_database;charset=utf8mb4', 'root', '');

// Instantiate CachedRequest class
require_once('CachedRequest.php');
$cachedRequest = new CachedRequest($pdo);

// URL to fetch data from
$url = 'http://feeds.bbci.co.uk/news/rss.xml';

// Fetch data from given URL, or from cache and store it in a variable
$contents = $cachedRequest->getData($url);

// Use data
echo $contents;
