<?php

declare(strict_types=1);

namespace Baraja\WebCrawler;


use Baraja\WebCrawler\Entity\TextSeparatorEntity;

class TextSeparator implements ITextSeparator
{

	/**
	 * @var string[]
	 */
	private $crawledTexts = [];

	/**
	 * @param string $html
	 * @return TextSeparatorEntity
	 */
	public function getTexts(string $html): TextSeparatorEntity
	{
		$html = (string) preg_replace('/\s+/', ' ', $html);
		$html = (string) preg_replace('/(["<>])/', ' $1 ', $html);
		preg_match_all('/(([\w\.\,\:\•\?\(\)\-]|[ěščřžýáíéťďůúňüó]|[ĚŠČŘŽÝÁÍÉÚŮŤĎŇÓ])+\s+){5,}/u', $html, $texts);

		$regularTexts = [];
		$uniqueTexts = [];

		foreach ($texts[0] as $text) {
			$text = trim($text);
			$canAdd = true;

			if (preg_match('/^\-\-\s*.+(\s*\-\-)?$/', $text)) {
				$canAdd = false;
			}

			preg_match_all('/\d+/', $text, $numbers);
			preg_match_all('/[^\d\s]+/', $text, $chars);

			if (count($numbers[0]) > count($chars[0])) {
				$canAdd = false;
			}

			if ($canAdd === true) {
				$regularTexts[] = $text;
				$normalizeText = Helpers::webalize($text);
				if (!isset($this->crawledTexts[$normalizeText])) {
					$uniqueTexts[] = $text;
					$this->crawledTexts[$normalizeText] = true;
				}
			}
		}

		return new TextSeparatorEntity(
			$regularTexts,
			$uniqueTexts
		);
	}

}
