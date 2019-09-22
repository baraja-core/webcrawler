<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


use Nette\SmartObject;

/**
 * @property-read string[] $allUrls
 * @property-read string[] $followedUrls
 * @property-read string[] $openedUrls
 * @property-read string[] $urlReferences
 * @property-read Url[] $urls
 * @property-read string[] $errors
 */
class CrawledResult
{

	use SmartObject;

	/**
	 * @var string[]
	 */
	private $allUrls;

	/**
	 * @var string[]
	 */
	private $followedUrls;

	/**
	 * @var string[]
	 */
	private $openedUrls;

	/**
	 * @var string[]
	 */
	private $urlReferences;

	/**
	 * @var Url[]
	 */
	private $urls;

	/**
	 * @var string[][]
	 */
	private $errors;

	public function __construct(array $allUrls, array $followedUrls, array $openedUrls, array $urlReferences, array $urls, array $errors)
	{
		$this->allUrls = $allUrls;
		$this->followedUrls = $followedUrls;
		$this->openedUrls = $openedUrls;
		$this->urlReferences = $urlReferences;
		$this->urls = $urls;
		$this->errors = $errors;
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

}
