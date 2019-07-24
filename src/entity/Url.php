<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


use Nette\SmartObject;

/**
 * @property-read \Nette\Http\Url $url
 * @property-read string $html
 * @property-read string $title
 * @property-read array $texts
 * @property-read array $uniqueTexts
 * @property-read array $links
 * @property-read array $headers
 * @property-read float $loadingTime
 * @property-read int $httpCode
 */
class Url
{

	use SmartObject;

	/**
	 * @var \Nette\Http\Url
	 */
	private $url;

	/**
	 * @var string
	 */
	private $html;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string[]
	 */
	private $texts;

	/**
	 * @var string[]
	 */
	private $uniqueTexts;

	/**
	 * @var string[]
	 */
	private $links;

	/**
	 * @var string[]
	 */
	private $headers;

	/**
	 * @var float
	 */
	private $loadingTime;

	/**
	 * @var int
	 */
	private $httpCode;

	public function __construct(string $url, string $html, string $title, array $texts, array $uniqueTexts, array $headers, array $links, float $loadingTime, int $httpCode)
	{
		$this->url = new \Nette\Http\Url($url);
		$this->html = $html;
		$this->title = $title;
		$this->texts = $texts;
		$this->uniqueTexts = $uniqueTexts;
		$this->headers = $headers;
		$this->links = $links;
		$this->loadingTime = $loadingTime;
		$this->httpCode = $httpCode;
	}

	/**
	 * @return \Nette\Http\Url
	 */
	public function getUrl(): \Nette\Http\Url
	{
		return $this->url;
	}

	/**
	 * @return string
	 */
	public function getHtml(): string
	{
		return $this->html;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}

	/**
	 * @return string[]
	 */
	public function getTexts(): array
	{
		return $this->texts;
	}

	/**
	 * @return string[]
	 */
	public function getUniqueTexts(): array
	{
		return $this->uniqueTexts;
	}

	/**
	 * @return string[]
	 */
	public function getLinks(): array
	{
		return $this->links;
	}

	/**
	 * @return string[]
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * @return float
	 */
	public function getLoadingTime(): float
	{
		return $this->loadingTime;
	}

	/**
	 * @return int
	 */
	public function getHttpCode(): int
	{
		return $this->httpCode;
	}

}
