<?php

declare(strict_types=1);

namespace Baraja\WebCrawler;


use Baraja\WebCrawler\Entity\TextSeparatorEntity;

interface ITextSeparator
{

	/**
	 * @param string $html
	 * @return TextSeparatorEntity
	 */
	public function getTexts(string $html): TextSeparatorEntity;

}
