<?php

declare(strict_types=1);

namespace Baraja\WebCrawler;


final class Helpers
{

	/**
	 * Removes special controls characters and normalizes line endings, spaces and normal form to NFC in UTF-8 string.
	 *
	 * @internal moved from nette/utils
	 * @param string $s
	 * @return string
	 */
	public static function normalize(string $s): string
	{
		// convert to compressed normal form (NFC)
		if (class_exists('Normalizer', false)) {
			$s = \Normalizer::normalize($s, \Normalizer::FORM_C);
		}

		$s = str_replace(["\r\n", "\r"], "\n", $s);

		// remove control characters; leave \t + \n
		$s = (string) preg_replace('#[\x00-\x08\x0B-\x1F\x7F-\x9F]+#u', '', $s);

		// right trim
		$s = (string) preg_replace('#[\t ]+$#m', '', $s);

		// leading and trailing blank lines
		$s = trim($s, "\n");

		return $s;
	}

	/**
	 * Converts UTF-8 string to web safe characters [a-z0-9-] text.
	 *
	 * @internal moved from nette/utils
	 * @param string $s
	 * @param string|null $charList
	 * @param bool $lower
	 * @return string
	 */
	public static function webalize(string $s, string $charList = null, bool $lower = true): string
	{
		$s = self::toAscii($s);
		if ($lower) {
			$s = strtolower($s);
		}
		$s = (string) preg_replace('#[^a-z0-9' . ($charList !== null ? preg_quote($charList, '#') : '') . ']+#i', '-', $s);
		$s = trim($s, '-');

		return $s;
	}

	/**
	 * Finds whether a string is a valid http(s) URL.
	 *
	 * @internal moved from nette/utils
	 * @param string $value
	 * @return bool
	 */
	public static function isUrl(string $value): bool
	{
		$alpha = "a-z\x80-\xFF";

		return (bool) preg_match("(^
			https?://(
				(([-_0-9$alpha]+\\.)*                       # subdomain
					[0-9$alpha]([-0-9$alpha]{0,61}[0-9$alpha])?\\.)?  # domain
					[$alpha]([-0-9$alpha]{0,17}[$alpha])?   # top domain
				|\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}  # IPv4
				|\[[0-9a-f:]{3,39}\]                        # IPv6
			)(:\\d{1,5})?                                   # port
			(/\\S*)?                                        # path
		\\z)ix", $value);
	}

	/**
	 * Converts UTF-8 string to ASCII.
	 *
	 * @internal moved from nette/utils
	 * @param string $s
	 * @return string
	 */
	private static function toAscii(string $s): string
	{
		static $transliterator = null;
		if ($transliterator === null && class_exists('Transliterator', false)) {
			$transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
		}

		$s = preg_replace('#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{2FF}\x{370}-\x{10FFFF}]#u', '', $s);
		$s = strtr($s, '`\'"^~?', "\x01\x02\x03\x04\x05\x06");
		$s = (string) str_replace(
			["\u{201E}", "\u{201C}", "\u{201D}", "\u{201A}", "\u{2018}", "\u{2019}", "\u{B0}"],
			["\x03", "\x03", "\x03", "\x02", "\x02", "\x02", "\x04"], $s
		);
		if ($transliterator !== null) {
			$s = $transliterator->transliterate($s);
		}
		if (ICONV_IMPL === 'glibc') {
			$s = (string) str_replace(
				["\u{BB}", "\u{AB}", "\u{2026}", "\u{2122}", "\u{A9}", "\u{AE}"],
				['>>', '<<', '...', 'TM', '(c)', '(R)'], $s
			);
			$s = iconv('UTF-8', 'WINDOWS-1250//TRANSLIT//IGNORE', $s);
			$s = strtr($s, "\xa5\xa3\xbc\x8c\xa7\x8a\xaa\x8d\x8f\x8e\xaf\xb9\xb3\xbe\x9c\x9a\xba\x9d\x9f\x9e"
				. "\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3"
				. "\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8"
				. "\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf8\xf9\xfa\xfb\xfc\xfd\xfe"
				. "\x96\xa0\x8b\x97\x9b\xa6\xad\xb7",
				'ALLSSSSTZZZallssstzzzRAAAALCCCEEEEIIDDNNOOOOxRUUUUYTsraaaalccceeeeiiddnnooooruuuuyt- <->|-.');
			$s = (string) preg_replace('#[^\x00-\x7F]++#', '', $s);
		} else {
			$s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
		}

		return strtr(str_replace(['`', "'", '"', '^', '~', '?'], '', $s), "\x01\x02\x03\x04\x05\x06", '`\'"^~?');
	}

}