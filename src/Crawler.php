<?php

declare(strict_types=1);

namespace Baraja\WebCrawler;


use Baraja\WebCrawler\Entity\Config;
use Baraja\WebCrawler\Entity\CrawledResult;
use Baraja\WebCrawler\Entity\HttpResponse;
use Baraja\WebCrawler\Entity\Url;
use Baraja\WebCrawler\Entity\UrlHelper;
use Tracy\Debugger;

class Crawler
{

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var ITextSeparator
	 */
	private $textSeparator;

	/**
	 * @var UrlHelper
	 */
	private $startingUrl;

	/**
	 * @var string[]
	 */
	private $urlList = [];

	/**
	 * @var string[]
	 */
	private $allUrls = [];

	/**
	 * @var string[][]
	 */
	private $urlReferences = [];

	/**
	 * @var string[][]
	 */
	private $errors = [];

	/**
	 * @param Config|null $config
	 */
	public function __construct(Config $config = null)
	{
		$this->config = $config ?? new Config;
		$this->textSeparator = new TextSeparator;
	}

	/**
	 * Starts/stops stopwatch from Tracy.
	 *
	 * @param string $name
	 * @return float elapsed seconds
	 */
	private static function timer(string $name): float
	{
		static $time = [];
		$now = microtime(true);
		$delta = isset($time[$name]) ? $now - $time[$name] : 0;
		$time[$name] = $now;

		return $delta;
	}

	/**
	 * @param string $url
	 * @return CrawledResult
	 */
	public function crawl(string $url): CrawledResult
	{
		$urls = [];

		$this->processBasicConfig($url);
		$robots = $this->processRobots(
			$this->startingUrl->getScheme() . '://' . $this->startingUrl->getAuthority() . '/robots.txt'
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

				if ($httpCode >= 300 && $httpCode <= 399 && isset($headers['Location']) === true) {
					$this->addUrl(RelativeUrlToAbsoluteUrl::process($crawledUrl, $headers['Location']));
				}

				$urlEntity = new Url(
					$crawledUrl,
					$httpResponse->getHtml(),
					$httpResponse->getSize(),
					trim($httpResponse->getTitle()),
					$texts->getRegularTexts(),
					$texts->getUniqueTexts(),
					$headers,
					$links,
					$httpResponse->getLoadingTime(),
					$httpCode
				);
				$urls[$urlEntity->getUrl()->getAbsoluteUrl()] = $urlEntity;
			} catch (\Throwable $e) {
				if (class_exists(Debugger::class) === true) {
					Debugger::log($e);
				}

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
			$robots
		);
	}

	/**
	 * Load more urls on start.
	 *
	 * @param string $startingUrl
	 * @param string[] $urls accept array of absolute or relative urls.
	 * @return CrawledResult
	 */
	public function crawlList(string $startingUrl, array $urls = []): CrawledResult
	{
		$basePath = function () use ($startingUrl): string {
			static $cache;

			if ($cache === null) {
				$url = new UrlHelper($startingUrl);
				$cache = trim($url->getScheme() . '://' . $url->getAuthority(), '/');
			}

			return $cache;
		};

		$this->processBasicConfig($startingUrl);

		foreach ($urls as $url) {
			$this->addUrl(Helpers::isUrl($url = ltrim($url, '/')) === true // Is absolute URL?
				? $url
				: $basePath() . '/' . $url
			);
		}

		return $this->crawl($startingUrl);
	}

	/**
	 * @param ITextSeparator $textSeparator
	 */
	public function setTextSeparator(ITextSeparator $textSeparator): void
	{
		$this->textSeparator = $textSeparator;
	}

	/**
	 * @param string $url
	 */
	private function processBasicConfig(string $url): void
	{
		$this->startingUrl = new UrlHelper($url);
		$this->addUrl($url);
	}

	/**
	 * @param string $url
	 */
	private function addUrl(string $url): void
	{
		$canAdd = true;

		if (!\in_array($url, $this->allUrls, true)) {
			$this->allUrls[] = $url;
		}

		$url = (string) preg_replace('/^(.*?)(?:[\#].*)?$/', '$1', $url);

		if ($this->config->isFollowExternalLinks() === false && $this->isExternalLink($url)) { // Is external?
			$canAdd = false;
		}

		if ($canAdd === true) { // Is allowed?
			$isAllowed = false;
			foreach ($this->config->getAllowedUrls() as $allow) {
				if (preg_match('/^' . $allow . '$/', $url)) {
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
				if (preg_match('/^' . $forbidden . '$/', $url)) {
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

	/**
	 * @param string $url
	 * @return bool
	 */
	private function isExternalLink(string $url): bool
	{
		return (new UrlHelper($url))->getDomain(5) !== $this->startingUrl->getDomain(5);
	}

	/**
	 * @param string $url
	 * @return HttpResponse
	 */
	private function loadUrl(string $url): HttpResponse
	{
		if ($this->config->getSleepBetweenRequests()) {
			sleep($this->config->getSleepBetweenRequests());
		}

		self::timer($url);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);

		$response = curl_exec($ch);

		$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $headerSize);
		$contentType = '';

		if (preg_match('/Content-Type:\s+(\S+)/', $response, $contentTypeParser)) {
			$contentType = $contentTypeParser[1];
		}

		if ($contentType === 'application/xml' || strncmp($contentType, 'text/', 5) === 0) {
			$html = Helpers::normalize((string) substr($response, $headerSize));
			$size = strlen($html);

			if (strpos($html, '<?xml') !== false && preg_match_all('/<loc>(https?\:\/\/[^\s\<]+)\<\/loc>/', $html, $sitemapUrls)) {
				foreach ($sitemapUrls[1] ?? [] as $sitemapUrl) {
					if (Helpers::isUrl($sitemapUrl)) {
						$this->addUrl($sitemapUrl);
					}
				}
			}
		} else {
			$html = '<!-- FILE ' . $url . ' -->';
			if (preg_match('/Content-Length:\s+(\d+)/', $response, $contentLength)) {
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
			self::timer($url) * 1000,
			(int) ($httpCodeParser['httpCode'] ?? 500),
			$size < 0 ? 0 : $size
		);
	}

	/**
	 * @param string $html
	 * @return string
	 */
	private function formatHtml(string $html): string
	{
		$html = Helpers::normalize($html);
		$html = str_replace(
			['&nbsp;', '&ndash;'],
			[' ', '-'],
			(string) preg_replace('/\n+/', "\n", $html)
		);

		return $html;
	}

	/**
	 * @param string $header
	 * @return string[]
	 */
	private function formatHeaders(string $header): array
	{
		$headers = [];

		foreach (explode("\n", Helpers::normalize($header)) as $_header) {
			if (preg_match('/^(?<name>[^:]+):\s*(?<value>.*)$/', $_header, $headerParser)) {
				$headers[$headerParser['name']] = $headerParser['value'];
			}
		}

		return $headers;
	}

	/**
	 * @param string $url
	 * @param string $html
	 * @return string[]
	 */
	private function getLinksFromHTML(string $url, string $html): array
	{
		$links = [];

		if (preg_match_all('/<a[^>]+>/', $html, $aLinks)) {
			foreach ($aLinks[0] as $aLink) {
				if (preg_match('/href=[\'"](?<url>[^\'"]+)[\'"]/', $aLink, $link)
					&& !preg_match('/^(?:mailto|tel|phone)\:/', $link['url'])
				) {
					$formattedLink = RelativeUrlToAbsoluteUrl::process($url, $link['url']);
					if ($formattedLink !== null && !in_array($formattedLink, $links, true)) {
						$links[] = $formattedLink;
					}
				}
			}
		}

		return $links;
	}

	/**
	 * @param string $target
	 * @param string $source
	 */
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

	/**
	 * @param string $url
	 * @return string|null
	 */
	private function processRobots(string $url): ?string
	{
		$return = null;
		$response = $this->loadUrl($url);

		if ($response->getHttpCode() === 200) {
			$this->addUrl($url);
			foreach (explode("\n", $return = Helpers::normalize($response->getHtml())) as $line) {
				$line = trim($line);

				if (preg_match('/^[Ss]itemap:\s+(https?\:\/\/\S+)/', $line, $robots)) {
					$this->addUrl($robots[1]);
				}
			}
		}

		return $return;
	}

}
