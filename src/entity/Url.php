<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


final class Url
{

	/** @var \Nette\Http\Url */
	private $url;

	/** @var string */
	private $html;

	/** @var int */
	private $size;

	/** @var string */
	private $title;

	/** @var string[] */
	private $texts;

	/** @var string[] */
	private $uniqueTexts;

	/** @var string[] */
	private $headers;

	/** @var string[] */
	private $links;

	/** @var float */
	private $loadingTime;

	/** @var int */
	private $httpCode;


	/**
	 * @param string $url
	 * @param string $html
	 * @param int $size
	 * @param string $title
	 * @param string[] $texts
	 * @param string[] $uniqueTexts
	 * @param string[] $headers
	 * @param string[] $links
	 * @param float $loadingTime
	 * @param int $httpCode
	 */
	public function __construct(string $url, string $html, int $size, string $title, array $texts, array $uniqueTexts, array $headers, array $links, float $loadingTime, int $httpCode)
	{
		$this->url = new \Nette\Http\Url($url);
		$this->html = $html;
		$this->size = $size;
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
	 * @return int
	 */
	public function getSize(): int
	{
		return $this->size;
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
