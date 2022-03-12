<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


final class Config
{
	private bool $followExternalLinks;

	private int $sleepBetweenRequests;

	private int $maxHttpRequests;

	private int $maxCrawlTimeInSeconds;

	/** @var array<int, string> */
	private array $allowedUrls;

	/** @var array<int, string> */
	private array $forbiddenUrls;


	/**
	 * @param array{
	 *    followExternalLinks?: bool,
	 *    sleepBetweenRequests?: int,
	 *    maxHttpRequests?: int,
	 *    maxCrawlTimeInSeconds?: int,
	 *    allowedUrls?: array<int, string>,
	 *    forbiddenUrls?: array<int, string>
	 * } $config
	 */
	public function __construct(array $config = [])
	{
		$this->followExternalLinks = $config['followExternalLinks'] ?? false;
		$this->sleepBetweenRequests = $config['sleepBetweenRequests'] ?? 1_000;
		$this->maxHttpRequests = $config['maxHttpRequests'] ?? 1_000_000;
		$this->maxCrawlTimeInSeconds = $config['maxCrawlTimeInSeconds'] ?? 30;
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
	 * @return array<int, string>
	 */
	public function getAllowedUrls(): array
	{
		return $this->allowedUrls;
	}


	/**
	 * @return array<int, string>
	 */
	public function getForbiddenUrls(): array
	{
		return $this->forbiddenUrls;
	}
}
