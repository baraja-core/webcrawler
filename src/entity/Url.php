<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


final class Url
{
	private \Nette\Http\Url $url;


	/**
	 * @param string[] $texts
	 * @param string[] $uniqueTexts
	 * @param string[] $headers
	 * @param string[] $links
	 */
	public function __construct(
		string $url,
		private string $html,
		private int $size,
		private string $title,
		private array $texts,
		private array $uniqueTexts,
		private array $headers,
		private array $links,
		private float $loadingTime,
		private int $httpCode,
	) {
		$this->url = new \Nette\Http\Url($url);
	}


	public function getUrl(): \Nette\Http\Url
	{
		return $this->url;
	}


	public function getHtml(): string
	{
		return $this->html;
	}


	public function getSize(): int
	{
		return $this->size;
	}


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


	public function getLoadingTime(): float
	{
		return $this->loadingTime;
	}


	public function getHttpCode(): int
	{
		return $this->httpCode;
	}


	public function getDomain(): string
	{
		return $this->url->getHost();
	}


	/** @return array<string, mixed> */
	public function toArray(): array
	{
		return [
			'url' => $this->url->getAbsoluteUrl(),
			'html' => $this->html,
			'size' => $this->size,
			'title' => $this->title,
			'texts' => $this->texts,
			'uniqueTexts' => $this->uniqueTexts,
			'headers' => $this->headers,
			'links' => $this->links,
			'loadingTime' => $this->loadingTime,
			'httpCode' => $this->httpCode,
		];
	}
}
