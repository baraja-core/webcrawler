<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


final class HttpResponse
{
	private string $html;

	private string $title;

	private float $loadingTime;

	private int $httpCode;

	private int $size;

	/** @var string[] */
	private array $headers;


	/**
	 * @param string[] $headers
	 */
	public function __construct(
		string $html,
		string $title,
		array $headers,
		float $loadingTime,
		int $httpCode,
		int $size
	) {
		$this->html = $html;
		$this->title = $title;
		$this->headers = $headers;
		$this->loadingTime = $loadingTime;
		$this->httpCode = $httpCode;
		$this->size = $size;
	}


	public function getHtml(): string
	{
		return $this->html;
	}


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


	public function getLoadingTime(): float
	{
		return $this->loadingTime;
	}


	public function getHttpCode(): int
	{
		return $this->httpCode;
	}


	public function getSize(): int
	{
		return $this->size;
	}
}
