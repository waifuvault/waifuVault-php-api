<?php

/**
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace ErnestMarcinko\WaifuTests;

use ErnestMarcinko\WaifuVault\Exceptions\WaifuException;
use ErnestMarcinko\WaifuVault\RequestMethods;
use ErnestMarcinko\WaifuVault\WaifuRequestHandler;
use ErnestMarcinko\WaifuVault\WaifuResponse;
use ErrorException;
use Exception;
use PHPUnit\Framework\TestCase;

class WaifuRequestHandlerTest extends TestCase {
	private WaifuResponse $waifuResponse;
	public function testMake(): void {
		$handler = new WaifuRequestHandler();

		$this->setGlobalMocks([
			'curl_setopt_array' => function ($curl, $curl_options) {
				$this->assertSame(60, $curl_options[CURLOPT_CONNECTTIMEOUT]);
				$this->assertSame(1, $curl_options[CURLOPT_RETURNTRANSFER]);
				$this->assertSame('fake1', $curl_options[CURLOPT_URL]);
				$this->addToAssertionCount(3);
			},
			'curl_exec' => 'result',
			'curl_getinfo' => 200,
		]);
		$handler->make(RequestMethods::GET, 'fake1');

		$this->setGlobalMocks([
			'curl_setopt_array' => function ($curl, $curl_options) {
				$this->assertSame(60, $curl_options[CURLOPT_CONNECTTIMEOUT]);
				$this->assertSame(1, $curl_options[CURLOPT_RETURNTRANSFER]);
				$this->assertSame('POST', $curl_options[CURLOPT_CUSTOMREQUEST]);
				$this->assertSame('fake2', $curl_options[CURLOPT_URL]);
				$this->assertEquals([1, '1', 'hey'], $curl_options[CURLOPT_HTTPHEADER]);
				$this->addToAssertionCount(5);
			}
		]);
		$handler->make(RequestMethods::POST, 'fake2', [1, '1', 'hey']); // @phpstan-ignore-line

		$this->setGlobalMocks([
			'curl_setopt_array' => function ($curl, $curl_options) {
				$this->assertSame(60, $curl_options[CURLOPT_CONNECTTIMEOUT]);
				$this->assertSame(1, $curl_options[CURLOPT_RETURNTRANSFER]);
				$this->assertSame('PUT', $curl_options[CURLOPT_CUSTOMREQUEST]);
				$this->assertSame('fake3', $curl_options[CURLOPT_URL]);
				$this->assertEquals([1, '1', 'hey'], $curl_options[CURLOPT_POSTFIELDS]);
				$this->addToAssertionCount(5);
			}
		]);
		$handler->make(RequestMethods::PUT, 'fake3', null, [1, '1', 'hey']); // @phpstan-ignore-line

		$this->setGlobalMocks([
			'curl_setopt_array' => function ($curl, $curl_options) {
				$this->assertSame(60, $curl_options[CURLOPT_CONNECTTIMEOUT]);
				$this->assertSame(1, $curl_options[CURLOPT_RETURNTRANSFER]);
				$this->assertSame('PATCH', $curl_options[CURLOPT_CUSTOMREQUEST]);
				$this->assertSame('fake4', $curl_options[CURLOPT_URL]);
				$this->assertEquals([1, '1', 'hey'], $curl_options[CURLOPT_POSTFIELDS]);
				$this->addToAssertionCount(6);
			},
			'curl_exec' => null
		]);
		$this->expectException(Exception::class);
		$handler->make(RequestMethods::PATCH, 'fake4', null, [1, '1', 'hey']); // @phpstan-ignore-line

		$this->assertSame(16, $this->numberOfAssertionsPerformed());
	}

	public function testGetWaifu(): void {
		$this->setGlobalMocks([
			'curl_exec' => json_encode((array)$this->waifuResponse),
			'curl_getinfo' => 200,
			'json_validate' => true,
		]);
		$handler = new WaifuRequestHandler();
		$response = $handler->make(RequestMethods::GET, 'fake')
			->getWaifu();
		$this->assertEqualsCanonicalizing($this->waifuResponse, $response);

		try {
			$this->setGlobalMocks([
				'curl_exec' => 'result',
				'curl_getinfo' => 200,
				'json_validate' => false,
			]);
			$handler = new WaifuRequestHandler();
			$handler->make(RequestMethods::GET, 'fake')
				->getWaifu();
		} catch (Exception) {
			$this->addToAssertionCount(1);
		}

		try {
			$this->setGlobalMocks([
				'curl_exec' => 'result',
				'curl_getinfo' => 200,
				'json_validate' => true,
				'json_decode' => null,
			]);
			$handler = new WaifuRequestHandler();
			$handler->make(RequestMethods::GET, 'fake')
				->getWaifu();
		} catch (Exception) {
			$this->addToAssertionCount(1);
		}

		// trigger responseErrorCheck #1
		try {
			$this->setGlobalMocks([
				'curl_exec' => 'result',
				'curl_getinfo' => 300,
			]);
			$handler = new WaifuRequestHandler();
			$handler->make(RequestMethods::GET, 'fake')
				->getWaifu();
		} catch (Exception $e) {
			$this->assertSame(ErrorException::class, get_class($e));
			$this->addToAssertionCount(1);
		}

		// trigger responseErrorCheck #1
		try {
			$this->setGlobalMocks([
				'curl_exec' => 'result',
				'curl_getinfo' => 300,
				'json_validate' => true,
				'json_decode' => array()
			]);
			$handler = new WaifuRequestHandler();
			$handler->make(RequestMethods::GET, 'fake')
				->getWaifu();
		} catch (Exception $e) {
			$this->assertSame(WaifuException::class, get_class($e));
			$this->addToAssertionCount(1);
		}

		// trigger responseErrorCheck #3 - call without ->make()
		try {
			$handler = new WaifuRequestHandler();
			$handler->getWaifu();
		} catch (Exception $e) {
			$this->assertSame(ErrorException::class, get_class($e));
			$this->addToAssertionCount(1);
		}

		$this->assertSame(5, $this->numberOfAssertionsPerformed());
	}

	public function testGetTrue(): void {
		$this->setGlobalMocks([
			'curl_exec' => 'true',
			'curl_getinfo' => 200,
			'json_validate' => true,
		]);
		$handler = new WaifuRequestHandler();
		$response = $handler->make(RequestMethods::GET, 'fake')
			->getTrue();
		$this->assertSame(true, $response);

		// trigger responseErrorCheck call without ->make()
		try {
			$handler = new WaifuRequestHandler();
			$handler->getTrue();
		} catch (Exception $e) {
			$this->assertSame(ErrorException::class, get_class($e));
			$this->addToAssertionCount(1);
		}
		$this->assertSame(1, $this->numberOfAssertionsPerformed());
	}

	public function testGetRaw(): void {
		$this->setGlobalMocks([
			'curl_exec' => 'content',
			'curl_getinfo' => 200,
			'json_validate' => true,
		]);
		$handler = new WaifuRequestHandler();
		$response = $handler->make(RequestMethods::GET, 'fake')
			->getRaw();
		$this->assertSame('content', $response);

		// trigger responseErrorCheck call without ->make()
		try {
			$handler = new WaifuRequestHandler();
			$handler->getRaw();
		} catch (Exception $e) {
			$this->assertSame(ErrorException::class, get_class($e));
			$this->addToAssertionCount(1);
		}
		$this->assertSame(1, $this->numberOfAssertionsPerformed());

		$this->setGlobalMocks([
			'curl_exec' => 'content',
			'curl_getinfo' => 403,
			'json_validate' => true,
		]);
		$handler = new WaifuRequestHandler();
		$this->expectException(Exception::class);
		$handler->make(RequestMethods::GET, 'fake')
			->getRaw();
	}

	public function setUp(): void {
		GlobalMock::disable();
		$args = [
			"token" => "13b2485a-1010-4e3e-8f75-20f2a0c50b56",
			"url" => "https://waifuvault.moe/f/1711098733870/image.jpg",
			"protected" => false,
			"retentionPeriod" => "300 days 10 hours 5 minutes 1 second"
		];
		$this->waifuResponse = new WaifuResponse(...$args);
	}

	/**
	 * Defines the global mocks via an array of function_name=>reponse
	 *
	 * @param array<string, mixed> $global_mocks key as function name, value as response
	 * @return void
	 */
	private function setGlobalMocks(array $global_mocks): void {
		try {
			foreach ($global_mocks as $function_name => $return) {
				GlobalMock::mock($function_name, $return);
			}
		} catch (Exception $e) {
			$this->fail('Mocking failed: ' . $e->getMessage());
		}
	}
}
