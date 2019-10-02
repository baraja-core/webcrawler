<?php

declare(strict_types=1);

namespace Baraja\WebCrawler\Entity;


/**
 * @internal moved from nette/utils
 *
 * Mutable representation of a URL.
 *
 * <pre>
 * scheme  user  password  host  port  basePath   relativeUrl
 *   |      |      |        |      |    |             |
 * /--\   /--\ /------\ /-------\ /--\/--\/----------------------------\
 * http://john:x0y17575@nette.org:8042/en/manual.php?name=param#fragment  <-- absoluteUrl
 *        \__________________________/\____________/^\________/^\______/
 *                     |                     |           |         |
 *                 authority               path        query    fragment
 * </pre>
 */
class UrlHelper implements \JsonSerializable
{

	/** @var int[] */
	public static $defaultPorts = [
		'http' => 80,
		'https' => 443,
		'ftp' => 21,
	];

	/** @var string */
	private $scheme;

	/** @var string */
	private $user;

	/** @var string */
	private $password;

	/** @var string */
	private $host;

	/** @var int|null */
	private $port;

	/** @var string */
	private $path = '';

	/** @var array */
	private $query = [];

	/** @var string */
	private $fragment;

	/**
	 * @internal moved from nette/utils
	 * @param string $url
	 */
	public function __construct(string $url)
	{
		$p = @parse_url($url); // @ - is escalated to exception
		if ($p === false) {
			trigger_error('Malformed or unsupported URI "' . $url . '".');
		}

		$this->scheme = $p['scheme'] ?? '';
		$this->port = $p['port'] ?? null;
		$this->host = rawurldecode($p['host'] ?? '');
		$this->user = rawurldecode($p['user'] ?? '');
		$this->password = rawurldecode($p['pass'] ?? '');
		$this->setPath($p['path'] ?? '');
		$this->setQuery($p['query'] ?? []);
		$this->fragment = rawurldecode($p['fragment'] ?? '');
	}

	/**
	 * Similar to rawurldecode, but preserves reserved chars encoded.
	 */
	public static function unescape(string $s, string $reserved = '%;/?:@&=+$,'): string
	{
		// reserved (@see RFC 2396) = ";" | "/" | "?" | ":" | "@" | "&" | "=" | "+" | "$" | ","
		// within a path segment, the characters "/", ";", "=", "?" are reserved
		// within a query component, the characters ";", "/", "?", ":", "@", "&", "=", "+", ",", "$" are reserved.
		if ($reserved !== '') {
			$s = preg_replace_callback(
				'#%(' . substr(chunk_split(bin2hex($reserved), 2, '|'), 0, -1) . ')#i',
				function (array $m): string {
					return '%25' . strtoupper($m[1]);
				},
				$s
			);
		}

		return rawurldecode($s);
	}

	/**
	 * Parses query string.
	 */
	public static function parseQuery(string $s): array
	{
		$s = str_replace(['%5B', '%5b'], '[', $s);
		$s = preg_replace('#&([^[&=]+)([^&]*)#', '&0[$1]$2', '&' . $s);
		parse_str($s, $res);

		return $res[0] ?? [];
	}

	public function getScheme(): string
	{
		return $this->scheme;
	}

	/**
	 * @return static
	 */
	public function setScheme(string $scheme)
	{
		$this->scheme = $scheme;

		return $this;
	}

	public function getUser(): string
	{
		return $this->user;
	}

	/**
	 * @return static
	 */
	public function setUser(string $user)
	{
		$this->user = $user;

		return $this;
	}

	public function getPassword(): string
	{
		return $this->password;
	}

	/**
	 * @return static
	 */
	public function setPassword(string $password)
	{
		$this->password = $password;

		return $this;
	}

	public function getHost(): string
	{
		return $this->host;
	}

	/**
	 * @return static
	 */
	public function setHost(string $host)
	{
		$this->host = $host;
		$this->setPath($this->path);

		return $this;
	}

	/**
	 * Returns the part of domain.
	 */
	public function getDomain(int $level = 2): string
	{
		$parts = ip2long($this->host) ? [$this->host] : explode('.', $this->host);
		$parts = $level >= 0 ? array_slice($parts, -$level) : array_slice($parts, 0, $level);

		return implode('.', $parts);
	}

	public function getPort(): ?int
	{
		return $this->port ? : (self::$defaultPorts[$this->scheme] ?? null);
	}

	/**
	 * @return static
	 */
	public function setPort(int $port)
	{
		$this->port = $port;

		return $this;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * @return static
	 */
	public function setPath(string $path)
	{
		$this->path = $path;
		if ($this->host && substr($this->path, 0, 1) !== '/') {
			$this->path = '/' . $this->path;
		}

		return $this;
	}

	/**
	 * @param  string|array $value
	 * @return static
	 */
	public function appendQuery($query)
	{
		$this->query = is_array($query)
			? $query + $this->query
			: self::parseQuery($this->getQuery() . '&' . $query);

		return $this;
	}

	public function getQuery(): string
	{
		return http_build_query($this->query, '', '&', PHP_QUERY_RFC3986);
	}

	/**
	 * @param  string|array $query
	 * @return UrlHelper
	 */
	public function setQuery($query): self
	{
		$this->query = is_array($query) ? $query : self::parseQuery($query);

		return $this;
	}

	public function getQueryParameters(): array
	{
		return $this->query;
	}

	/**
	 * @param string $name
	 * @return mixed|null
	 */
	public function getQueryParameter(string $name)
	{
		return $this->query[$name] ?? null;
	}

	/**
	 * @param string $name
	 * @param mixed $value null unsets the parameter
	 * @return UrlHelper
	 */
	public function setQueryParameter(string $name, $value): self
	{
		$this->query[$name] = $value;

		return $this;
	}

	public function getFragment(): string
	{
		return $this->fragment;
	}

	/**
	 * @param string $fragment
	 * @return UrlHelper
	 */
	public function setFragment(string $fragment): self
	{
		$this->fragment = $fragment;

		return $this;
	}

	public function getAbsoluteUrl(): string
	{
		return $this->getHostUrl() . $this->path
			. (($tmp = $this->getQuery()) ? '?' . $tmp : '')
			. ($this->fragment === '' ? '' : '#' . $this->fragment);
	}

	/**
	 * Returns the [user[:pass]@]host[:port] part of URI.
	 */
	public function getAuthority(): string
	{
		return $this->host === ''
			? ''
			: ($this->user !== ''
				? rawurlencode($this->user) . ($this->password === '' ? '' : ':' . rawurlencode($this->password)) . '@'
				: '')
			. $this->host
			. ($this->port && (!isset(self::$defaultPorts[$this->scheme]) || $this->port !== self::$defaultPorts[$this->scheme])
				? ':' . $this->port
				: '');
	}

	/**
	 * Returns the scheme and authority part of URI.
	 */
	public function getHostUrl(): string
	{
		return ($this->scheme ? $this->scheme . ':' : '')
			. (($authority = $this->getAuthority()) !== '' ? '//' . $authority : '');
	}

	public function getBasePath(): string
	{
		$pos = strrpos($this->path, '/');

		return $pos === false ? '' : substr($this->path, 0, $pos + 1);
	}

	public function getBaseUrl(): string
	{
		return $this->getHostUrl() . $this->getBasePath();
	}

	public function getRelativeUrl(): string
	{
		return substr($this->getAbsoluteUrl(), strlen($this->getBaseUrl()));
	}

	/**
	 * URL comparison.
	 *
	 * @param  string|self $url
	 */
	public function isEqual($url): bool
	{
		$url = new self($url);
		$query = $url->query;
		ksort($query);
		$query2 = $this->query;
		ksort($query2);

		return $url->scheme === $this->scheme
			&& !strcasecmp($url->host, $this->host)
			&& $url->getPort() === $this->getPort()
			&& $url->user === $this->user
			&& $url->password === $this->password
			&& self::unescape($url->path, '%/') === self::unescape($this->path, '%/')
			&& $query === $query2
			&& $url->fragment === $this->fragment;
	}

	/**
	 * Transforms URL to canonical form.
	 *
	 * @return static
	 */
	public function canonicalize()
	{
		$this->path = preg_replace_callback(
			'#[^!$&\'()*+,/:;=@%]+#',
			function (array $m): string {
				return rawurlencode($m[0]);
			},
			self::unescape($this->path, '%/')
		);
		$this->host = strtolower($this->host);

		return $this;
	}

	public function __toString(): string
	{
		return $this->getAbsoluteUrl();
	}

	public function jsonSerialize(): string
	{
		return $this->getAbsoluteUrl();
	}

}