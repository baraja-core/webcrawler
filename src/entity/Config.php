<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


final class Config
{

	/** @var bool */
	private $followExternalLinks;

	/** @var int */
	private $sleepBetweenRequests;

	/** @var int */
	private $maxHttpRequests;

	/** @var int */
	private $maxCrawlTimeInSeconds;

	/** @var string[] */
	private $allowedUrls;

	/** @var string[] */
	private $forbiddenUrls;


	/**
	 * @param mixed[] $config
	 */
	public function __construct(array $config = [])
	{
		$this->followExternalLinks = (bool) ($config['followExternalLinks'] ?? false);
		$this->sleepBetweenRequests = (int) ($config['sleepBetweenRequests'] ?? 1000);
		$this->maxHttpRequests = (int) ($config['maxHttpRequests'] ?? 1000000);
		$this->maxCrawlTimeInSeconds = (int) ($config['maxCrawlTimeInSeconds'] ?? 30);
		$this->allowedUrls = $config['allowedUrls'] ?? ['.+'];
		$this->forbiddenUrls = $config['forbiddenUrls'] ?? [''];
	}


	/**
	 * @return bool
	 */
	public function isFollowExternalLinks(): bool
	{
		return $this->followExternalLinks;
	}


	/**
	 * Time in milliseconds.
	 *
	 * @return int
	 */
	public function getSleepBetweenRequests(): int
	{
		return $this->sleepBetweenRequests;
	}


	/**
	 * @return int
	 */
	public function getMaxHttpRequests(): int
	{
		return $this->maxHttpRequests;
	}


	/**
	 * @return int
	 */
	public function getMaxCrawlTimeInSeconds(): int
	{
		return $this->maxCrawlTimeInSeconds;
	}


	/**
	 * @return string[]
	 */
	public function getAllowedUrls(): array
	{
		return $this->allowedUrls;
	}


	/**
	 * @return string[]
	 */
	public function getForbiddenUrls(): array
	{
		return $this->forbiddenUrls;
	}
}
