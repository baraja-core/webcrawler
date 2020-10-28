<?php

declare(strict_types=1);

namespace Baraja\WebCrawler;


use Baraja\WebCrawler\Entity\TextSeparatorEntity;

interface ITextSeparator
{
	public function getTexts(string $html): TextSeparatorEntity;
}
