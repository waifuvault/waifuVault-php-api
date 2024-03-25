<?php

/** @noinspection PhpComposerExtensionStubsInspection */

namespace ErnestMarcinko\WaifuVault;

use AllowDynamicProperties;
use ErnestMarcinko\WaifuVault\Exceptions\WaifuException;
use Error;
use ErrorException;
use Exception;

/**
 * Handles requests for WaifuAPI via CURL
 */
#[AllowDynamicProperties] class WaifuRequestHandler implements RequestHandler {
	private string $result;

	private int $response_code;

	/**
	 * Constructs a new WaifuRequest
	 *
	 * @param RequestMethods $method
	 * @param string $endpoint
	 * @param string[]|null $header
	 * @param string|array<string, mixed>|false|null $post_fields
	 * @return static
	 * @throws Exception
	 */
	public function make(
		RequestMethods $method,
		string $endpoint,
		array|null $header = null,
		array|string|bool|null $post_fields = null
	): static {
		$curl = curl_init();
		$curl_options = [
			CURLOPT_CONNECTTIMEOUT => 60,
			CURLOPT_RETURNTRANSFER => 1, // This prevents $results from being bool
			CURLOPT_URL => $endpoint,
		];
		switch ($method) {
			case RequestMethods::POST:
				$curl_options[CURLOPT_CUSTOMREQUEST] = 'POST';
				break;
			case RequestMethods::PUT:
				$curl_options[CURLOPT_CUSTOMREQUEST] = 'PUT';
				break;
			case RequestMethods::PATCH:
				$curl_options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
				break;
			case RequestMethods::DELETE:
				$curl_options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
				break;
		}
		if (!is_null($header)) {
			$curl_options[CURLOPT_HTTPHEADER] = $header;
		}
		if (!empty($post_fields)) {
			$curl_options[CURLOPT_POSTFIELDS] = $post_fields;
		}

		curl_setopt_array($curl, $curl_options);
		$result = curl_exec($curl);
		if (!is_string($result)) {
			throw new Exception('Curl error: ' . curl_error($curl));
		}
		curl_close($curl);
		$this->result = $result;
		$this->response_code = intval(curl_getinfo($curl, CURLINFO_RESPONSE_CODE));

		return $this;
	}

	/**
	 * Creates a WaifuResponse
	 *
	 * @return WaifuResponse
	 * @throws WaifuException
	 * @throws Exception
	 */
	public function getWaifu(): WaifuResponse {
		$this->responseErrorCheck();
		if (json_validate($this->result)) {
			$response = json_decode($this->result, true);
			if (!is_array($response)) {
				throw new Exception('The response was invalid.');
			}
			return new WaifuResponse(...$response);
		}
		throw new Exception('Something went wrong.');
	}

	/**
	 * Creates a boolean true response
	 *
	 * @return true
	 * @throws WaifuException
	 * @throws Exception
	 */
	public function getTrue(): true {
		$this->responseErrorCheck();
		return true;
	}

	/**
	 * Returns the response as-is
	 *
	 * @return string
	 * @throws WaifuException
	 * @throws Exception
	 */
	public function getRaw(): string {
		$this->responseErrorCheck();
		if ($this->response_code === 403) {
			throw new Exception('The password is incorrect.');
		}
		return $this->result;
	}

	/**
	 * Checks the response body if an error was returned
	 *
	 * @return void
	 * @throws WaifuException
	 * @throws Exception
	 */
	private function responseErrorCheck(): void {
		if ($this->response_code < 300) {
			return;
		}
		if (json_validate($this->result)) {
			$response = json_decode($this->result, true);
			if (is_array($response)) {
				throw new WaifuException(new WaifuError(...$response));
			}
		}
		throw new ErrorException('The response was invalid.');
	}
}
