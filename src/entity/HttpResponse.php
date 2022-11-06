<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


final class HttpResponse
{
	/**
	 * @param string[] $headers
	 */
	public function __construct(
		private string $html,
		private string $title,
		private array $headers,
		private float $loadingTime,
		private int $httpCode,
		private int $size,
	) {
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
