<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


use Nette\SmartObject;

/**
 * @property-read string $html
 * @property-read string $title
 * @property-read array $headers
 * @property-read float $loadingTime
 * @property-read int $httpCode
 */
class HttpResponse
{

	use SmartObject;

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
	private $headers;

	/**
	 * @var float
	 */
	private $loadingTime;

	/**
	 * @var int
	 */
	private $httpCode;

	public function __construct(string $html, string $title, array $headers, float $loadingTime, int $httpCode)
	{
		$this->html = $html;
		$this->title = $title;
		$this->headers = $headers;
		$this->loadingTime = $loadingTime;
		$this->httpCode = $httpCode;
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
