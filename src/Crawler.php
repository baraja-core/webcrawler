<?php

declare(strict_types=1);

namespace Baraja\WebCrawler;


use Baraja\WebCrawler\Entity\Config;
use Baraja\WebCrawler\Entity\CrawledResult;
use Baraja\WebCrawler\Entity\HttpResponse;
use Baraja\WebCrawler\Entity\Url;
use Nette\Utils\Strings;

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
	 * @var \Nette\Http\Url
	 */
	private $inputUrl;

	/**
	 * @var string[]
	 */
	private $urlList = [];

	/**
	 * @var string[]
	 */
	private $followedUrls = [];

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

		$this->inputUrl = new \Nette\Http\Url($url);
		$this->addUrl($url);
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
				$links = $this->getLinksFromHTML($url, $httpResponse->html);

				foreach ($links as $link) {
					$this->addUrl($link);
					$this->addUrlReference($crawledUrl, $link);
				}

				$texts = $this->textSeparator->getTexts($httpResponse->html);

				$urls[] = new Url(
					$crawledUrl,
					$httpResponse->html,
					Strings::trim($httpResponse->title),
					$texts->regularTexts,
					$texts->uniqueTexts,
					$httpResponse->headers,
					$links,
					$httpResponse->loadingTime,
					(int) $httpResponse->httpCode
				);
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
			$this->followedUrls,
			$this->urlReferences,
			$urls,
			$this->errors
		);
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
	private function addUrl(string $url): void
	{
		$canAdd = true;

		if (!\in_array($url, $this->allUrls, true)) {
			$this->allUrls[] = $url;
		}

		$url = (string) preg_replace('/^(.*?)[\#].*$/', '$1', $url);

		if ($this->config->isFollowExternalLinks() === false && $this->isExternalLink($url)) { // Is external?
			$canAdd = false;
		}

		if ($canAdd) { // Is allowed?
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

		if ($canAdd) { // Is forbidden?
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

		if ($canAdd && \in_array($url, $this->urlList, true)) { // Is in list?
			$canAdd = false;
		}

		if ($canAdd) {
			$this->urlList[] = $url;
		}
	}

	/**
	 * @param string $url
	 * @return bool
	 */
	private function isExternalLink(string $url): bool
	{
		return (new \Nette\Http\Url($url))->host !== $this->inputUrl->host;
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

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$html = Strings::normalize(substr($response, $header_size));
		$this->followedUrls[] = $url;

		preg_match('/^.+?\s+(?<httpCode>\d+)/', $response, $httpCodeParser);
		preg_match('/<title[^>]*>(?<title>[^<]+)<\/title>/', $html, $titleParser);

		return new HttpResponse(
			$this->formatHtml($html),
			$titleParser['title'] ?? $url,
			$this->formatHeaders($header),
			self::timer($url) * 1000,
			(int) ($httpCodeParser['httpCode'] ?? 500)
		);
	}

	/**
	 * @param string $html
	 * @return string
	 */
	private function formatHtml(string $html): string
	{
		$html = Strings::normalize($html);
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

		foreach (explode("\n", Strings::normalize($header)) as $_header) {
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

}
