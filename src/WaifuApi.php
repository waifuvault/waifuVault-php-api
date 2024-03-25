<?php

/** @noinspection PhpComposerExtensionStubsInspection */

namespace ErnestMarcinko\WaifuVault;

use ErnestMarcinko\WaifuVault\Exceptions\WaifuException;
use Exception;
use CURLStringFile;

/**
 * WaifuVault PHP SDK
 *
 * @link https://waifuvault.moe/
 *
 * @phpstan-type FileUpload array{
 *     file: string,
 *     filename?: string
 * }
 * @phpstan-type UrlUpload array{
 *     url: string
 * }
 *
 * @phpstan-type uploadFileArg FileUpload|UrlUpload
 * @phpstan-type modifyFileArg array{
 *     token: string,
 *     password?: string,
 *     previousPassword?: string,
 *     customExpiry?: string,
 *     hideFilename?: bool
 * }
 *
 * @phpstan-type getFileArg array{password?:string, token:string}|array{password?:string, filename:string}
 */
class WaifuApi {
	private const string BASE_URL = 'https://waifuvault.moe';
	private const string REST_URL = self::BASE_URL . '/rest';

	public function __construct(private readonly RequestHandler $requestHandler = new WaifuRequestHandler()) {}

	/**
	 * Uploads a file from URL or path
	 *
	 * @param uploadFileArg $args
	 * @return WaifuResponse
	 * @throws WaifuException
	 * @throws Exception
	 */
	public function uploadFile(array $args): WaifuResponse {
		$post_fields = [];
		$url = self::REST_URL;
		$params = http_build_query(array_filter(
			$args,
			function ($v, $k) {
				return in_array($k, array(
						'expires',
						'hide_filename',
						'password')) && !is_null($v); // @phpstan-ignore-line
			},
			ARRAY_FILTER_USE_BOTH
		));
		if ($params !== '') {
			$url .=  '?' . $params;
		}
		if (isset($args['url'])) {
			$post_fields['url'] = $args['url'];
		} elseif (isset($args['file'])) {
			$file_name = $args['filename'] ?? basename($args['file']);
			if ($file_name === '' || !file_exists($args['file'])) {
				throw new Exception('File does not exist.');
			}
			$data = file_get_contents($args['file']);
			if ($data === false) {
				throw new Exception('File does is not readable.');
			}
			$post_fields['file'] = new CURLStringFile($data, $file_name);
		} else {
			throw new Exception('Please provide a url or file.');
		}
		return $this->requestHandler->make(
			RequestMethods::PUT,
			$url,
			null,
			$post_fields
		)->getWaifu();
	}

	/**
	 * Gets the file information by token
	 *
	 * @param string $token
	 * @param bool $formatted
	 * @return WaifuResponse
	 * @throws Exception|WaifuException
	 */
	public function getFileInfo(string $token, bool $formatted = true): WaifuResponse {
		if ($token === '') {
			throw new Exception('Token is empty.');
		}
		return $this->requestHandler->make(
			RequestMethods::GET,
			self::REST_URL . '/' . $token . ($formatted ? "?formatted=true" : ''),
		)->getWaifu();
	}

	/**
	 * Modifies an entry by token
	 *
	 * @param modifyFileArg $args
	 * @return WaifuResponse
	 * @throws WaifuException
	 * @throws Exception
	 */
	public function modifyEntry(array $args): WaifuResponse {
		if ($args['token'] === '') {
			throw new Exception('Token is empty.');
		}
		return $this->requestHandler->make(
			RequestMethods::PATCH,
			self::REST_URL . '/' . $args['token'],
			array('Content-Type: application/json; charset=utf-8'),
			json_encode($args)
		)->getWaifu();
	}

	/**
	 * Deletes and entry by token
	 *
	 * @param string $token
	 * @return true
	 * @throws WaifuException
	 * @throws Exception
	 */
	public function deleteEntry(string $token): true {
		if ($token === '') {
			throw new Exception('Token is empty.');
		}
		return $this->requestHandler->make(
			RequestMethods::DELETE,
			self::REST_URL . '/' . $token
		)->getTrue();
	}

	/**
	 * Gets file contents by filename or token
	 *
	 * @param getFileArg $args
	 * @return string
	 * @throws WaifuException
	 * @throws Exception
	 */
	public function getFile(array $args): string {
		if (isset($args['filename'])) {
			$url = self::BASE_URL . '/f/' . $args['filename'];
		} elseif (isset($args['token'])) {
			$url = $this->getFileInfo($args['token'])->url;
		} else {
			throw new Exception('A file name or token is required.');
		}
		return $this->requestHandler->make(
			RequestMethods::GET,
			$url,
			isset($args['password']) ? ["x-password:{$args['password']}"] : null,
		)->getRaw();
	}
}
