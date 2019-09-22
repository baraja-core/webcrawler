<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


use Nette\SmartObject;

/**
 * @property-read bool $followExternalLinks
 * @property-read int $sleepBetweenRequests
 * @property-read int $maxHttpRequests
 * @property-read int $maxCrawlTimeInSeconds
 * @property-read string[] $allowedUrls
 * @property-read string[] $forbiddenUrls
 */
class Config
{

	use SmartObject;

	/**
	 * @var bool
	 */
	private $followExternalLinks;

	/**
	 * @var int
	 */
	private $sleepBetweenRequests;

	/**
	 * @var int
	 */
	private $maxHttpRequests;

	/**
	 * @var int
	 */
	private $maxCrawlTimeInSeconds;

	/**
	 * @var string[]
	 */
	private $allowedUrls;

	/**
	 * @var string[]
	 */
	private $forbiddenUrls;

	public function __construct(array $config = [])
	{
		$this->followExternalLinks = (bool) ($config['followExternalLinks'] ?? false);
		$this->sleepBetweenRequests = (int) ($config['sleepBetweenRequests'] ?? 1);
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
