<div align='center'>
  <picture>
    <source media='(prefers-color-scheme: dark)' srcset='https://cdn.brj.app/images/brj-logo/logo-regular.png'>
    <img src='https://cdn.brj.app/images/brj-logo/logo-dark.png' alt='BRJ logo'>
  </picture>
  <br>
  <a href="https://brj.app">BRJ organisation</a>
</div>
<hr>

Web crawler
===========

![Integrity check](https://github.com/baraja-core/webcrawler/workflows/Integrity%20check/badge.svg)

Simply library for crawling websites by following links with minimal dependencies.

[Czech documentation](https://php.baraja.cz/stazeni-celeho-webu-po-odkazech)

📦 Installation
---------------

It's best to use [Composer](https://getcomposer.org) for installation, and you can also find the package on
[Packagist](https://packagist.org/packages/baraja-core/webcrawler) and
[GitHub](https://github.com/baraja-core/webcrawler).

To install, simply use the command:

```
$ composer require baraja-core/webcrawler
```

You can use the package manually by creating an instance of the internal classes, or register a DIC extension to link the services directly to the Nette Framework.

How to use
----------

Crawler can run without dependencies.

In default settings create instance and call `crawl()` method:

```php
$crawler = new \Baraja\WebCrawler\Crawler;

$result = $crawler->crawl('https://example.com');
```

In `$result` variable will be entity of type `CrawledResult`.

Advanced checking of multiple URLs
----------------------------------

In real case you need download multiple URLs in single domain and check if some specific URLs works.

Simple example:

```php
$crawler = new \Baraja\WebCrawler\Crawler;

$result = $crawler->crawlList(
    'https://example.com', // Starting (main) URL
    [ // Additional URLs
        'https://example.com/error-404',
        '/robots.txt', // Relative links are also allowed
        '/web.config',
    ]
);
```

Notice: File **robots.txt** and sitemap will be downloaded automatically if exist.

Settings
--------

In constructor of service `Crawler` you can define your project specific configuration.

Simply like:

```php
$crawler = new \Baraja\WebCrawler\Crawler(
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
| `sleepBetweenRequests`  | `1000`        | `Int`: Sleep in milliseconds. |
| `maxHttpRequests`       | `1000000`     | `Int`: Crawler budget limit. |
| `maxCrawlTimeInSeconds` | `30`          | `Int`: Stop crawling when limit is exceeded. |
| `allowedUrls`           | `['.+']`      | `String[]`: List of valid regex about allowed URL format. |
| `forbiddenUrls`         | `['']`        | `String[]`: List of valid regex about banned URL format. |


📄 License
-----------

`baraja-core/webcrawler` is licensed under the MIT license. See the [LICENSE](https://github.com/baraja-core/variable-generator/blob/master/LICENSE) file for more details.
