<?php

namespace ErnestMarcinko\WaifuVault;

/**
 * Request interface for custom implementations
 */
interface RequestHandler {
	/**
	 * Makes a new request
	 *
	 * @param RequestMethods $method
	 * @param string $endpoint
	 * @param string[]|null $header
	 * @param string|array<string, mixed>|false|null $post_fields
	 */
	public function make(
		RequestMethods         $method,
		string                 $endpoint,
		array|null             $header = null,
		array|string|bool|null $post_fields = null
	): static;

	/**
	 * Creates a WaifuResponse from the request
	 *
	 * @return WaifuResponse
	 */
	public function getWaifu(): WaifuResponse;

	/**
	 * Creates a boolean true response from the request
	 *
	 * @return true
	 */
	public function getTrue(): true;

	/**
	 * Returns the response as-is from the request
	 *
	 * @return string
	 */
	public function getRaw(): string;
}