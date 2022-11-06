<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


final class TextSeparatorEntity
{
	/**
	 * @param array<int, string> $regularTexts
	 * @param array<int, string> $uniqueTexts
	 */
	public function __construct(
		private array $regularTexts,
		private array $uniqueTexts,
	) {
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
