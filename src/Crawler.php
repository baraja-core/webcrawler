<?php

declare(strict_types=1);

namespace Baraja\WebCrawler;


use Baraja\WebCrawler\Entity\Config;
use Baraja\WebCrawler\Entity\CrawledResult;
use Baraja\WebCrawler\Entity\HttpResponse;
use Baraja\WebCrawler\Entity\Url;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

final class Crawler
{
	private Config $config;

	private ITextSeparator $textSeparator;

	private \Nette\Http\Url $startingUrl;

	/** @var array<int, string> */
	private array $urlList = [];

	/** @var array<int, string> */
	private array $allUrls = [];

	/** @var array<string, array<int, string>> */
	private array $urlReferences = [];

	/** @var array<int, array{url: string, message: string, trace: array<int, mixed>}> */
	private array $errors = [];


	public function __construct(?Config $config = null)
	{
		$this->config = $config ?? new Config;
		$this->textSeparator = new TextSeparator;
	}


	/** Starts/stops stopwatch from Tracy. Return elapsed seconds. */
	private static function timer(string $name): float
	{
		static $time = [];
		$now = microtime(true);
		$delta = isset($time[$name]) ? $now - $time[$name] : 0;
		$time[$name] = $now;

		return $delta;
	}


	public function crawl(string $url): CrawledResult
	{
		$urls = [];

		$this->processBasicConfig($url);
		$robots = $this->processRobots(
			$this->startingUrl->getScheme() . '://' . $this->startingUrl->getAuthority() . '/robots.txt',
		);
		$crawlerStartTime = \time();

		for ($iterator = 0; isset($this->urlList[$iterator]); $iterator++) {
			if ($iterator >= $this->config->getMaxHttpRequests()
				|| \time() - $crawlerStartTime >= $this->config->getMaxCrawlTimeInSeconds()
			) {
				break;
			}

			$crawledUrl = $this->urlList[$iterator];
			try {
				$httpResponse = $this->loadUrl($crawledUrl);
				$links = $this->getLinksFromHTML($crawledUrl, $httpResponse->getHtml());

				foreach ($links as $link) {
					$this->addUrl($link);
					$this->addUrlReference($crawledUrl, $link);
				}

				$texts = $this->textSeparator->getTexts($httpResponse->getHtml());
				$httpCode = $httpResponse->getHttpCode();
				$headers = $httpResponse->getHeaders();
				$title = trim($httpResponse->getTitle());

				if ($httpCode >= 300 && $httpCode <= 399 && isset($headers['Location']) === true) {
					$this->addUrl($title = (string) RelativeUrlToAbsoluteUrl::process($crawledUrl, $headers['Location']));
				}

				$urlEntity = new Url(
					$crawledUrl,
					$httpResponse->getHtml(),
					$httpResponse->getSize(),
					$title,
					$texts->getRegularTexts(),
					$texts->getUniqueTexts(),
					$headers,
					$links,
					$httpResponse->getLoadingTime(),
					$httpCode,
				);
				$urls[$urlEntity->getUrl()->getAbsoluteUrl()] = $urlEntity;
			} catch (\Throwable $e) {
				$this->errors[] = [
					'url' => $crawledUrl,
					'message' => $e->getMessage(),
					'trace' => $e->getTrace(),
				];
			}
		}

		return new CrawledResult(
			$this->allUrls,
			$this->urlList,
			array_keys($urls),
			$this->urlReferences,
			$urls,
			$this->errors,
			$robots,
		);
	}


	/**
	 * Load more urls on start.
	 *
	 * @param string[] $urls accept array of absolute or relative urls.
	 * @return CrawledResult
	 */
	public function crawlList(string $startingUrl, array $urls = []): CrawledResult
	{
		$basePath = function () use ($startingUrl): string {
			static $cache;
			if ($cache === null) {
				$url = new \Nette\Http\Url($startingUrl);
				$cache = trim($url->getScheme() . '://' . $url->getAuthority(), '/');
			}

			return $cache;
		};

		$this->processBasicConfig($startingUrl);
		foreach ($urls as $url) {
			$this->addUrl(
				Validators::isUrl($url = ltrim($url, '/')) === true // Is absolute URL?
				? $url
				: $basePath() . '/' . $url,
			);
		}

		return $this->crawl($startingUrl);
	}


	public function setTextSeparator(ITextSeparator $textSeparator): void
	{
		$this->textSeparator = $textSeparator;
	}


	private function processBasicConfig(string $url): void
	{
		$startingUrl = new \Nette\Http\Url($url);
		$this->startingUrl = $startingUrl;
		$this->addUrl($url);
		$this->addUrl(sprintf(
			'%s://%s',
			$startingUrl->getScheme() === 'https' ? 'http' : 'https',
			$startingUrl->getAuthority(),
		));
	}


	private function addUrl(string $url): void
	{
		if (!\in_array($url, $this->allUrls, true)) {
			$this->allUrls[] = $url;
		}

		$url = (string) preg_replace('/^(.*?)(?:[\#].*)?$/', '$1', $url);
		$canAdd = true;
		if ($this->config->isFollowExternalLinks() === false && $this->isExternalLink($url)) { // Is external?
			$canAdd = false;
		}
		if ($canAdd === true) { // Is allowed?
			$isAllowed = false;
			foreach ($this->config->getAllowedUrls() as $allow) {
				if (preg_match('/^' . $allow . '$/', $url) === 1) {
					$isAllowed = true;
					break;
				}
			}
			if ($isAllowed === false) {
				$canAdd = false;
			}
		}
		if ($canAdd === true) { // Is forbidden?
			$isForbidden = false;
			foreach ($this->config->getForbiddenUrls() as $forbidden) {
				if (preg_match('/^' . $forbidden . '$/', $url) === 1) {
					$isForbidden = true;
					break;
				}
			}
			if ($isForbidden) {
				$canAdd = false;
			}
		}
		if ($canAdd === true && \in_array($url, $this->urlList, true)) { // Is in list?
			$canAdd = false;
		}
		if ($canAdd === true) {
			$this->urlList[] = $url;
		}
	}


	private function isExternalLink(string $url): bool
	{
		return (new \Nette\Http\Url($url))->getDomain(5) !== $this->startingUrl->getDomain(5);
	}


	private function loadUrl(string $url): HttpResponse
	{
		if ($this->config->getSleepBetweenRequests() > 0) {
			usleep($this->config->getSleepBetweenRequests() * 1_000);
		}

		self::timer($url);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);

		$response = (string) curl_exec($ch);
		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $headerSize);
		$contentType = '';

		if (preg_match('/Content-Type:\s+(\S+)/', $response, $contentTypeParser) === 1) {
			$contentType = $contentTypeParser[1];
		}
		if ($contentType === 'application/xml' || strncmp($contentType, 'text/', 5) === 0) {
			$html = Strings::normalize(substr($response, $headerSize));
			$size = strlen($html);

			if (
				str_contains($html, '<?xml')
				&& preg_match_all('/<loc>(https?\:\/\/[^\s\<]+)\<\/loc>/', $html, $sitemapUrls) === 1
			) {
				foreach ($sitemapUrls[1] ?? [] as $sitemapUrl) {
					if (Validators::isUrl($sitemapUrl)) {
						$this->addUrl($sitemapUrl);
					}
				}
			}
		} else {
			$html = '<!-- FILE ' . $url . ' -->';
			if (preg_match('/Content-Length:\s+(\d+)/', $response, $contentLength) === 1) {
				$size = (int) $contentLength[1];
			} else {
				$size = strlen($response) - $headerSize;
			}
		}

		preg_match('/^.+?\s+(?<httpCode>\d+)/', $response, $httpCodeParser);
		preg_match('/<title[^>]*>(?<title>[^<]+)<\/title>/', $html, $titleParser);

		return new HttpResponse(
			$this->formatHtml($html),
			$titleParser['title'] ?? $url,
			$this->formatHeaders($header),
			self::timer($url) * 1_000,
			(int) ($httpCodeParser['httpCode'] ?? 500),
			max($size, 0),
		);
	}


	private function formatHtml(string $html): string
	{
		$html = Strings::normalize($html);
		return str_replace(
			['&nbsp;', '&ndash;'],
			[' ', '-'],
			(string) preg_replace('/\n+/', "\n", $html),
		);
	}


	/**
	 * @return string[]
	 */
	private function formatHeaders(string $header): array
	{
		$return = [];
		foreach (explode("\n", Strings::normalize($header)) as $_header) {
			if (preg_match('/^(?<name>[^:]+):\s*(?<value>.*)$/', $_header, $headerParser) === 1) {
				$return[$headerParser['name']] = $headerParser['value'];
			}
		}

		return $return;
	}


	/**
	 * @return string[]
	 */
	private function getLinksFromHTML(string $url, string $html): array
	{
		$return = [];
		if (preg_match_all('/<a[^>]+>/', $html, $aLinks) > 0) {
			foreach ($aLinks[0] as $aLink) {
				if (preg_match('/href=[\'"](?<url>[^\'"]+)[\'"]/', $aLink, $link) === 1
					&& preg_match('/^(?:mailto|tel|phone)\:/', $link['url']) !== 1
				) {
					$formattedLink = RelativeUrlToAbsoluteUrl::process($url, $link['url']);
					if ($formattedLink !== null && !in_array($formattedLink, $return, true)) {
						$return[] = $formattedLink;
					}
				}
			}
		}

		return $return;
	}


	private function addUrlReference(string $target, string $source): void
	{
		if (isset($this->urlReferences[$source])) {
			$referenceExists = false;
			foreach ($this->urlReferences[$source] as $reference) {
				if ($reference === $target) {
					$referenceExists = true;
					break;
				}
			}
			if ($referenceExists === false) {
				$this->urlReferences[$source][] = $target;
			}
		} else {
			$this->urlReferences[$source][] = $target;
		}
	}


	private function processRobots(string $url): ?string
	{
		$return = null;
		$response = $this->loadUrl($url);
		if ($response->getHttpCode() === 200) {
			$this->addUrl($url);
			$return = Strings::normalize($response->getHtml());
			foreach (explode("\n", $return) as $line) {
				$line = trim($line);
				if (preg_match('/^[Ss]itemap:\s+(https?\:\/\/\S+)/', $line, $robots) === 1) {
					$this->addUrl($robots[1]);
				}
			}
		}

		return $return;
	}
}
