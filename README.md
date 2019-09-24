Web crawler
===========

Simply library for crawling websites by following links with minimal dependencies.

[Czech documentation](https://php.baraja.cz/stazeni-celeho-webu-po-odkazech)

Install
-------

Install simply by Composer:

```shell
composer require baraja-core/webcrawler
```

and use in your project. :)

How to use
----------

Crawler can run without dependencies.

In default settings create instance and call `crawl()` method:

```php
$crawler = \Baraja\WebCrawler\Crawler;

$result = $crawler->crawl('https://example.com');
```

In `$result` variable will be entity of type `CrawledResult`.

Settings
--------

In constructor of service `Crawler` you can define your project specific configuration.

Simply like:

```php
$crawler = \Baraja\WebCrawler\Crawler(
    new \Baraja\WebCrawler\Config([
        // key => value
    ])
);
```

No one value is required. Please use as key-value array.

Configuration options:

| Option                  | Default value | Possible values |
|-------------------------|---------------|-----------------|
| `followExternalLinks`   | `false`       | `Bool`: Stay only in given domain? |
| `sleepBetweenRequests`  | `1`           | `Int`: Sleep in seconds. |
| `maxHttpRequests`       | `1000000`     | `Int`: Crawler budget limit. |
| `maxCrawlTimeInSeconds` | `30`          | `Int`: Stop crawling when limit is exceeded. |
| `allowedUrls`           | `['.+']`      | `String[]`: List of valid regex about allowed URL format. |
| `forbiddenUrls`         | `['']`        | `String[]`: List of valid regex about banned URL format. |
