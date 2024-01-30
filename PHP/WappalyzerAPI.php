<?php

class WappalyzerAPI {
	/**
	 * Get technologies used by a website using Wappalyzer API
	 *
	 * @author [IhsanDevs](https://ihsandevs.com)
	 * @example
	 * ```
	 * $url = 'https://example.com';
	 * $callback_url = 'https://example.com/callback';
	 * $data = WappalyzerAPI::analyze_web($url, $callback_url);
	 * print_r($data);
	 * ```
	 *
	 * @param string|array $url
	 * @param string|NULL  $callback_url
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function analyze_web(string|array $url, string $callback_url = null): array
	{
		if (is_array($url)) {
			// loop through array and validate each url
			foreach ($url as $key => $value) {
				if (
					!filter_var($value, FILTER_VALIDATE_URL)
				) {
					throw new Exception("Invalid URL $value. Example: https://example.com. You can also pass multiple URLs separated by comma. Example: https://example.com,https://example2.com");
				}
			}
		} else {
			if (
				!filter_var($url, FILTER_VALIDATE_URL)
			) {
				throw new Exception("Invalid URL $url. Example: https://example.com. You can also pass multiple URLs separated by comma. Example: https://example.com,https://example2.com");
			}
		}

		if (is_array($url)) {
			$url = implode(',', $url);
		}

		$url = "https://api.wappalyzer.com/v2/lookup/?urls=$url&sets=all&icons=true&recursive=true";

		if (!is_null($callback_url)) {
			// validate callback url
			if (
				!filter_var($callback_url, FILTER_VALIDATE_URL)
			) {
				throw new Exception("Invalid callback URL. Example: https://example.com/callback");
			}


			$url .= "&callback_url=$callback_url&live=true";
		}

		$headers = [
			'X-Api-Key: 8pNVQNyF5n4EPY2rW987E3hqZGjSVyef7qa6lnRI',
			'User-Agent: Wappalyzer/1 CFNetwork/1492.0.1 Darwin/23.3.0',
			'Accept: application/json',
			'Host: api.wappalyzer.com',
		];

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);

		curl_close($ch);

		$data = json_decode($response, true);

		if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
			throw new Exception("Failed to get technologies");
		}

		// walk recursively through the array and get the svg syntax for each icon where key is "icon"
		array_walk_recursive($data, function (&$value, $key) {
			if ($key === 'icon') {
				$value = self::get_svg_syntax($value);
			}
		});

		// if callback url is set, add key "callback_url" to the array
		if (!is_null($callback_url)) {
			$data['callback_url'] = $callback_url;
		}

		return $data;
	}

	/**
	 * @example https://www.wappalyzer.com/images/icons/wordpress.svg
	 * @param string $icon
	 *
	 * @return string
	 */
	private static function get_svg_syntax(string $icon): string
	{
		// space to %20
		$icon = str_replace(' ', '%20', $icon);
		return "https://www.wappalyzer.com/images/icons/$icon";
	}
}


/**
 * Example usage
 */
$url = 'https://example.com';
$callback_url = 'https://example.com/callback';
try {
	$data = WappalyzerAPI::analyze_web($url, $callback_url);
} catch (Exception $e) {
	$data = [
		'error' => $e->getMessage(),
	];
}

print_r($data);