## CachedRequest

Database-cache driven version of PHP's file_get_contents().
We will fetch data from live URL, or if we have fresh copy of it in cache, use that copy instead.

## Code Example

$pdo = new PDO('mysql:host=localhost;dbname=some_database;charset=utf8mb4', 'root', '');
$cachedRequest = new CachedRequest($pdo);
$contents = $cachedRequest->getData('http://feeds.bbci.co.uk/news/rss.xml');
echo $contents;

## Motivation

Useful for example, when working with API's, external, or internal,
to lower resources usage on service side and improve speed on client side.

## Installation

Run (import) db_structure.sql script on your MySql server to create necessary db table for caching.
