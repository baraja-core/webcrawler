<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


final class CrawledResult
{

	/** @var string[] */
	private array $allUrls;

	/** @var string[] */
	private array $followedUrls;

	/** @var string[] */
	private array $openedUrls;

	/** @var string[][] */
	private array $urlReferences;

	/** @var Url[] */
	private array $urls;

	/** @var mixed[][] */
	private array $errors;

	/** Content of robots.txt file if exist. */
	private ?string $robots;


	/**
	 * @param string[] $allUrls
	 * @param string[] $followedUrls
	 * @param string[] $openedUrls
	 * @param string[][] $urlReferences
	 * @param Url[] $urls
	 * @param mixed[][] $errors
	 */
	public function __construct(
		array $allUrls,
		array $followedUrls,
		array $openedUrls,
		array $urlReferences,
		array $urls,
		array $errors,
		?string $robots
	) {
		$this->allUrls = $allUrls;
		$this->followedUrls = $followedUrls;
		$this->openedUrls = $openedUrls;
		$this->urlReferences = $urlReferences;
		$this->urls = $urls;
		$this->errors = $errors;
		$this->robots = $robots;
	}


	/**
	 * @return string[]
	 */
	public function getAllUrls(): array
	{
		return $this->allUrls;
	}


	/**
	 * @return string[]
	 */
	public function getFollowedUrls(): array
	{
		return $this->followedUrls;
	}


	/**
	 * @return string[]
	 */
	public function getOpenedUrls(): array
	{
		return $this->openedUrls;
	}


	/**
	 * @return string[][]
	 */
	public function getUrlReferences(): array
	{
		return $this->urlReferences;
	}


	/**
	 * @return Url[]
	 */
	public function getUrls(): array
	{
		return $this->urls;
	}


	/**
	 * @return mixed[][]
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}


	public function getRobots(): ?string
	{
		return $this->robots;
	}
}
