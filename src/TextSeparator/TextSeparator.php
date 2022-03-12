<?php

declare(strict_types=1);

namespace Baraja\WebCrawler;


use Baraja\WebCrawler\Entity\TextSeparatorEntity;
use Nette\Utils\Strings;

final class TextSeparator implements ITextSeparator
{

	/** @var true[] */
	private array $crawledTexts = [];


	public function getTexts(string $html): TextSeparatorEntity
	{
		$html = (string) preg_replace('/\s+/', ' ', $html);
		$html = (string) preg_replace('/(["<>])/', ' $1 ', $html);
		preg_match_all('/(([\w\.\,\:\•\?\(\)\-]|[ěščřžýáíéťďůúňüó]|[ĚŠČŘŽÝÁÍÉÚŮŤĎŇÓ])+\s+){5,}/u', $html, $texts);

		$regularTexts = [];
		$uniqueTexts = [];
		foreach ($texts[0] ?? [] as $text) {
			$canAdd = true;
			if (preg_match('/^\-\-\s*.+(\s*\-\-)?$/', $text = trim($text)) === 1) {
				$canAdd = false;
			}

			preg_match_all('/\d+/', $text, $numbers);
			preg_match_all('/[^\d\s]+/', $text, $chars);

			if (count($numbers[0]) > count($chars[0])) {
				$canAdd = false;
			}
			if ($canAdd === true) {
				$regularTexts[] = $text;
				$normalizeText = Strings::webalize($text);
				if (isset($this->crawledTexts[$normalizeText]) === false) {
					$uniqueTexts[] = $text;
					$this->crawledTexts[$normalizeText] = true;
				}
			}
		}

		return new TextSeparatorEntity(
			$regularTexts,
			$uniqueTexts,
		);
	}
}
