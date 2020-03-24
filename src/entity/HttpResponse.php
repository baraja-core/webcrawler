<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


final class HttpResponse
{

	/** @var string */
	private $html;

	/** @var string */
	private $title;

	/** @var string[] */
	private $headers;

	/** @var float */
	private $loadingTime;

	/** @var int */
	private $httpCode;

	/** @var int */
	private $size;


	/**
	 * @param string $html
	 * @param string $title
	 * @param string[] $headers
	 * @param float $loadingTime
	 * @param int $httpCode
	 * @param int $size
	 */
	public function __construct(string $html, string $title, array $headers, float $loadingTime, int $httpCode, int $size)
	{
		$this->html = $html;
		$this->title = $title;
		$this->headers = $headers;
		$this->loadingTime = $loadingTime;
		$this->httpCode = $httpCode;
		$this->size = $size;
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


	/**
	 * @return int
	 */
	public function getSize(): int
	{
		return $this->size;
	}
}
