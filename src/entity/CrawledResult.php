<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


class CrawledResult
{

	/** @var string[] */
	private $allUrls;

	/** @var string[] */
	private $followedUrls;

	/** @var string[] */
	private $openedUrls;

	/** @var string[] */
	private $urlReferences;

	/** @var Url[] */
	private $urls;

	/** @var string[][] */
	private $errors;

	/**
	 * Content of robots.txt file if exist.
	 *
	 * @var string|null
	 */
	private $robots;


	/**
	 * @param string[] $allUrls
	 * @param string[] $followedUrls
	 * @param string[] $openedUrls
	 * @param string[] $urlReferences
	 * @param Url[] $urls
	 * @param string[] $errors
	 * @param string|null $robots
	 */
	public function __construct(array $allUrls, array $followedUrls, array $openedUrls, array $urlReferences, array $urls, array $errors, ?string $robots)
	{
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
	 * @return string[]
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
	 * @return string[][]
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}


	/**
	 * @return string|null
	 */
	public function getRobots(): ?string
	{
		return $this->robots;
	}
}
