<?php

declare(strict_types=1);

namespace Baraja\WebCrawler;


final class RelativeUrlToAbsoluteUrl
{

	/**
	 * @param string $baseUrl
	 * @param string $relativeUrl
	 * @return string|null
	 */
	public static function process(string $baseUrl, string $relativeUrl): ?string
	{
		$r = self::splitUrl($relativeUrl);

		if ($r === null) {
			return null;
		}

		if (!empty($r['scheme'])) {
			if (!empty($r['path']) && strpos($r['path'], '/') === 0) {
				$r['path'] = self::urlRemoveDotSegments($r['path']);
			}

			return self::joinUrl($r);
		}

		$b = self::splitUrl($baseUrl);

		if ($b === null || empty($b['scheme']) || empty($b['host'])) {
			return null;
		}

		$r['scheme'] = $b['scheme'];

		if (isset($r['host'])) {
			if (!empty($r['path'])) {
				$r['path'] = self::urlRemoveDotSegments($r['path']);
			}

			return self::joinUrl($r);
		}

		unset($r['port'], $r['user'], $r['pass']);

		$r['host'] = $b['host'];
		if (isset($b['port'])) {
			$r['port'] = $b['port'];
		}
		if (isset($b['user'])) {
			$r['user'] = $b['user'];
		}
		if (isset($b['pass'])) {
			$r['pass'] = $b['pass'];
		}

		if (empty($r['path'])) {
			if (!empty($b['path'])) {
				$r['path'] = $b['path'];
			}

			if (!isset($r['query']) && isset($b['query'])) {
				$r['query'] = $b['query'];
			}

			return self::joinUrl($r);
		}

		if ($r['path'][0] !== '/') {
			$base = mb_strrchr($b['path'] ?? '', '/', true, 'UTF-8');

			if ($base === false) {
				$base = '';
			}

			$r['path'] = $base . '/' . $r['path'];
		}

		$r['path'] = self::urlRemoveDotSegments($r['path']);

		return self::joinUrl($r);
	}


	/**
	 * @param string $url
	 * @param bool $decode
	 * @return string[]|null
	 */
	private static function splitUrl(string $url, bool $decode = true): ?array
	{
		$xUnResSub = 'a-zA-Z\d\-._~\!$&\'()*+,;=';
		$xpChar = $xUnResSub . ':@%';
		$xScheme = '([a-zA-Z][a-zA-Z\d+-.]*)';
		$xUserInfo = '(([' . $xUnResSub . '%]*)(:([' . $xUnResSub . ':%]*))?)';
		$xIpv4 = '(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})';
		$xIpv6 = '(\[([a-fA-F\d.:]+)\])';
		$xHostName = '([a-zA-Z\d-.%]+)';
		$xHost = '(' . $xHostName . '|' . $xIpv4 . '|' . $xIpv6 . ')';
		$xPort = '(\d*)';
		$xAuthority = '((' . $xUserInfo . '@)?' . $xHost . '?(:' . $xPort . ')?)';
		$xSlashSeg = '(/[' . $xpChar . ']*)';
		$xPathAuthAbs = '((//' . $xAuthority . ')((/[' . $xpChar . ']*)*))';
		$xPathRel = '([' . $xpChar . ']+' . $xSlashSeg . '*)';
		$xPathAbs = '(/(' . $xPathRel . ')?)';
		$xaPath = '(' . $xPathAuthAbs . '|' . $xPathAbs . '|' . $xPathRel . ')';
		$xQueryFrag = '([' . $xpChar . '/?' . ']*)';
		$xUrl = '^(' . $xScheme . ':)?' . $xaPath . '?(\?' . $xQueryFrag . ')?(#' . $xQueryFrag . ')?$';

		if (!preg_match('!' . $xUrl . '!', $url, $m)) {
			return null;
		}

		$parts = [];

		if (!empty($m[2])) {
			$parts['scheme'] = mb_strtolower($m[2], 'UTF-8');
		}

		if (!empty($m[7])) {
			$parts['user'] = $m[9] ?? '';
		}

		if (!empty($m[10])) {
			$parts['pass'] = $m[11];
		}

		if (!empty($m[13])) {
			$h = $parts['host'] = $m[13];
		} elseif (!empty($m[14])) {
			$parts['host'] = $m[14];
		} elseif (!empty($m[16])) {
			$parts['host'] = $m[16];
		} elseif (!empty($m[5])) {
			$parts['host'] = '';
		}

		if (!empty($m[17])) {
			$parts['port'] = $m[18];
		}

		if (!empty($m[19])) {
			$parts['path'] = $m[19];
		} elseif (!empty($m[21])) {
			$parts['path'] = $m[21];
		} elseif (!empty($m[25])) {
			$parts['path'] = $m[25];
		}

		if (!empty($m[27])) {
			$parts['query'] = $m[28];
		}

		if (!empty($m[29])) {
			$parts['fragment'] = $m[30];
		}

		if (!$decode) {
			return $parts;
		}

		if (!empty($parts['user'])) {
			$parts['user'] = rawurldecode($parts['user']);
		}

		if (!empty($parts['pass'])) {
			$parts['pass'] = rawurldecode($parts['pass']);
		}

		if (!empty($parts['path'])) {
			$parts['path'] = rawurldecode($parts['path']);
		}

		if (isset($h)) {
			$parts['host'] = rawurldecode($parts['host']);
		}

		if (!empty($parts['query'])) {
			$parts['query'] = rawurldecode($parts['query']);
		}

		if (!empty($parts['fragment'])) {
			$parts['fragment'] = rawurldecode($parts['fragment']);
		}

		return $parts;
	}


	/**
	 * @param string $path
	 * @return string
	 */
	private static function urlRemoveDotSegments(string $path): string
	{
		$inputSegments = preg_split('!/!u', $path);
		$outputSegments = [];

		foreach ($inputSegments as $seg) {
			if ($seg === '' || $seg === '.') {
				continue;
			}

			if ($seg === '..') {
				array_pop($outputSegments);
			} else {
				$outputSegments[] = $seg;
			}
		}

		$outPath = implode('/', $outputSegments);
		if (strpos($path, '/') === 0) {
			$outPath = '/' . $outPath;
		}

		if ($outPath !== '/' && (mb_strlen($path) - 1) === mb_strrpos($path, '/', 'UTF-8')) {
			$outPath .= '/';
		}

		return $outPath;
	}


	/**
	 * @param string[] $parts
	 * @param bool $encode
	 * @return string
	 */
	private static function joinUrl(array $parts, bool $encode = true): string
	{
		if ($encode) {
			if (isset($parts['user'])) {
				$parts['user'] = rawurlencode($parts['user']);
			}

			if (isset($parts['pass'])) {
				$parts['pass'] = rawurlencode($parts['pass']);
			}

			if (isset($parts['host']) && !preg_match('!^(\[[\da-f.:]+\]])|([\da-f.:]+)$!ui', $parts['host'])) {
				$parts['host'] = rawurlencode($parts['host']);
			}

			if (!empty($parts['path'])) {
				$parts['path'] = preg_replace('!%2F!ui', '/', rawurlencode($parts['path']));
			}

			if (isset($parts['query'])) {
				$parts['query'] = rawurlencode($parts['query']);
			}

			if (isset($parts['fragment'])) {
				$parts['fragment'] = rawurlencode($parts['fragment']);
			}
		}

		$url = '';
		if (!empty($parts['scheme'])) {
			$url .= $parts['scheme'] . ':';
		}

		if (isset($parts['host'])) {
			$url .= '//';

			if (isset($parts['user'])) {
				$url .= $parts['user'];

				if (isset($parts['pass'])) {
					$url .= ':' . $parts['pass'];
				}

				$url .= '@';
			}

			if (preg_match('!^[\da-f]*:[\da-f.:]+$!ui', $parts['host'])) {
				$url .= '[' . $parts['host'] . ']';
			} else {
				$url .= $parts['host'];
			}

			if (isset($parts['port'])) {
				$url .= ':' . $parts['port'];
			}

			if (!empty($parts['path']) && $parts['path'][0] !== '/') {
				$url .= '/';
			}
		}

		if (!empty($parts['path'])) {
			$url .= $parts['path'];
		}

		if (isset($parts['query'])) {
			$url .= '?' . $parts['query'];
		}

		if (isset($parts['fragment'])) {
			$url .= '#' . $parts['fragment'];
		}

		return $url;
	}
}
