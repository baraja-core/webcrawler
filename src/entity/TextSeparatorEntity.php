<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


final class TextSeparatorEntity
{

	/** @var string[] */
	private $regularTexts;

	/** @var string[] */
	private $uniqueTexts;


	/**
	 * @param string[] $regularTexts
	 * @param string[] $uniqueTexts
	 */
	public function __construct(array $regularTexts, array $uniqueTexts)
	{
		$this->regularTexts = $regularTexts;
		$this->uniqueTexts = $uniqueTexts;
	}


	/**
	 * @return string[]
	 */
	public function getRegularTexts(): array
	{
		return $this->regularTexts;
	}


	/**
	 * @return string[]
	 */
	public function getUniqueTexts(): array
	{
		return $this->uniqueTexts;
	}
}
