<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


final class CrawledResult
{
	/**
	 * @param string[] $allUrls
	 * @param string[] $followedUrls
	 * @param string[] $openedUrls
	 * @param array<string,array<int,string>> $urlReferences
	 * @param Url[] $urls
	 * @param array<int,array{url: string, message: string, trace: array<int,mixed>}> $errors
	 */
	public function __construct(
		private array $allUrls,
		private array $followedUrls,
		private array $openedUrls,
		private array $urlReferences,
		private array $urls,
		private array $errors,
		private ?string $robots,
	) {
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
	 * @return array<string,array<int,string>>
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
	 * @return array<int, array{url: string, message: string, trace: array<int, mixed>}>
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}


	public function getRobots(): ?string
	{
		return $this->robots;
	}


	/**
	 * get all data as array
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(): array
	{
		return [
			'allUrls' => $this->allUrls,
			'followedUrls' => $this->followedUrls,
			'openedUrls' => $this->openedUrls,
			'urlReferences' => $this->urlReferences,
			'urls' => array_map(static fn (Url $url): array => $url->toArray(), $this->urls),
			'errors' => $this->errors,
			'robots' => $this->robots,
		];
	}
}
