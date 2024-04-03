<?php

/**
 * @noinspection PhpUnhandledExceptionInspection
 * @noinspection PhpComposerExtensionStubsInspection
 */

namespace ErnestMarcinko\WaifuTests;

use ErnestMarcinko\MockUtils\MockUtils;
use ErnestMarcinko\WaifuVault\Exceptions\WaifuException;
use ErnestMarcinko\WaifuVault\RequestMethods;
use ErnestMarcinko\WaifuVault\WaifuRequestHandler;
use ErnestMarcinko\WaifuVault\WaifuResponse;
use ErrorException;
use Exception;
use PHPUnit\Framework\TestCase;

class WaifuRequestHandlerTest extends TestCase {
	use MockUtils;

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
		], "ErnestMarcinko\\WaifuVault");
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
		], "ErnestMarcinko\\WaifuVault");
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
		], "ErnestMarcinko\\WaifuVault");
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
		], "ErnestMarcinko\\WaifuVault");
		$this->expectException(Exception::class);
		$handler->make(RequestMethods::PATCH, 'fake4', null, [1, '1', 'hey']); // @phpstan-ignore-line

		$this->assertSame(19, $this->numberOfAssertionsPerformed());
	}

	public function testGetWaifu(): void {
		$this->setGlobalMocks([
			'curl_exec' => json_encode((array)$this->waifuResponse),
			'curl_getinfo' => 200,
			'json_validate' => true,
		], "ErnestMarcinko\\WaifuVault");
		$handler = new WaifuRequestHandler();
		$response = $handler->make(RequestMethods::GET, 'fake')
			->getWaifu();
		$this->assertEqualsCanonicalizing($this->waifuResponse, $response);

		$this->setGlobalMocks([
			'curl_exec' => 'result',
			'curl_getinfo' => 200,
			'json_validate' => false,
		], "ErnestMarcinko\\WaifuVault");
		$handler = new WaifuRequestHandler();
		$this->expectCatchException(
			fn()=>$handler->make(RequestMethods::GET, 'fake')->getWaifu(),
			Exception::class
		);

		$this->setGlobalMocks([
			'curl_exec' => 'result',
			'curl_getinfo' => 200,
			'json_validate' => true,
			'json_decode' => null,
		], "ErnestMarcinko\\WaifuVault");
		$handler = new WaifuRequestHandler();
		$this->expectCatchException(
			fn()=>$handler->make(RequestMethods::GET, 'fake')->getWaifu(),
			Exception::class
		);

		// trigger responseErrorCheck #1
		$this->setGlobalMocks([
			'curl_exec' => 'result',
			'curl_getinfo' => 300,
		], "ErnestMarcinko\\WaifuVault");
		$handler = new WaifuRequestHandler();
		$this->expectCatchException(
			fn()=>$handler->make(RequestMethods::GET, 'fake')->getWaifu(),
			ErrorException::class
		);

		// trigger responseErrorCheck #1
		$this->setGlobalMocks([
			'curl_exec' => 'result',
			'curl_getinfo' => 300,
			'json_validate' => true,
			'json_decode' => array()
		], "ErnestMarcinko\\WaifuVault");
		$handler = new WaifuRequestHandler();
		$this->expectCatchException(
			fn()=>$handler->make(RequestMethods::GET, 'fake')->getWaifu(),
			WaifuException::class
		);

		// trigger responseErrorCheck #3
		$handler = new WaifuRequestHandler();
		$this->expectCatchException(
			fn()=>$handler->getWaifu(),
			ErrorException::class
		);
	}

	public function testGetTrue(): void {
		$this->setGlobalMocks([
			'curl_exec' => 'true',
			'curl_getinfo' => 200,
			'json_validate' => true,
		], "ErnestMarcinko\\WaifuVault");
		$handler = new WaifuRequestHandler();
		$response = $handler->make(RequestMethods::GET, 'fake')
			->getTrue();
		$this->assertSame(true, $response);

		// trigger responseErrorCheck call without ->make()
		$handler = new WaifuRequestHandler();
		$this->expectCatchException(
			fn()=>$handler->getTrue(),
			ErrorException::class
		);
	}

	public function testGetRaw(): void {
		$this->setGlobalMocks([
			'curl_exec' => 'content',
			'curl_getinfo' => 200,
			'json_validate' => true,
		], "ErnestMarcinko\\WaifuVault");
		$handler = new WaifuRequestHandler();
		$response = $handler->make(RequestMethods::GET, 'fake')
			->getRaw();
		$this->assertSame('content', $response);

		// trigger responseErrorCheck call without ->make()
		$handler = new WaifuRequestHandler();
		$this->expectCatchException(
			fn()=>$handler->getRaw(),
			ErrorException::class
		);

		$this->setGlobalMocks([
			'curl_exec' => 'content',
			'curl_getinfo' => 403,
			'json_validate' => true,
		], "ErnestMarcinko\\WaifuVault");
		$handler = new WaifuRequestHandler();
		$this->expectException(Exception::class);
		$handler->make(RequestMethods::GET, 'fake')->getRaw();
	}

	public function setUp(): void {
		$this->unsetGlobalMocks();
		$args = [
			"token" => "13b2485a-1010-4e3e-8f75-20f2a0c50b56",
			"url" => "https://waifuvault.moe/f/1711098733870/image.jpg",
			"retentionPeriod" => "300 days 10 hours 5 minutes 1 second",
			"options" => [
				"hideFilename" => true,
				"oneTimeDownload" => false,
				"protected" => false,
			]
		];
		$this->waifuResponse = new WaifuResponse(...$args);
	}
}
