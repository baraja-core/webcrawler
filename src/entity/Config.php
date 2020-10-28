<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


final class Config
{
	private bool $followExternalLinks;

	private int $sleepBetweenRequests;

	private int $maxHttpRequests;

	private int $maxCrawlTimeInSeconds;

	/** @var string[] */
	private array $allowedUrls;

	/** @var string[] */
	private array $forbiddenUrls;


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


	public function isFollowExternalLinks(): bool
	{
		return $this->followExternalLinks;
	}


	/** Time in milliseconds. */
	public function getSleepBetweenRequests(): int
	{
		return $this->sleepBetweenRequests;
	}


	public function getMaxHttpRequests(): int
	{
		return $this->maxHttpRequests;
	}


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
