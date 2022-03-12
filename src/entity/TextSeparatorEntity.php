<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


final class TextSeparatorEntity
{
	/** @var array<int, string> */
	private array $regularTexts;

	/** @var array<int, string> */
	private array $uniqueTexts;


	/**
	 * @param array<int, string> $regularTexts
	 * @param array<int, string> $uniqueTexts
	 */
	public function __construct(array $regularTexts, array $uniqueTexts)
	{
		$this->regularTexts = $regularTexts;
		$this->uniqueTexts = $uniqueTexts;
	}


	/**
	 * @return array<int, string>
	 */
	public function getRegularTexts(): array
	{
		return $this->regularTexts;
	}


	/**
	 * @return array<int, string>
	 */
	public function getUniqueTexts(): array
	{
		return $this->uniqueTexts;
	}
}
