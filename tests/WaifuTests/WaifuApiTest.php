<?php

//phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
//phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps

namespace ErnestMarcinko\WaifuTests;

use ErnestMarcinko\WaifuVault\WaifuRequestHandler;
use Exception;
use ErnestMarcinko\WaifuVault\WaifuApi;
use ErnestMarcinko\WaifuVault\WaifuResponse;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(WaifuApi::class)]
#[UsesClass(GlobalMock::class)]
class WaifuApiTest extends TestCase {
	private WaifuApi $waifu;
	private WaifuResponse $waifuResponse;

	public function testUploadFileValid(): void {
		$this->setUpMockWaifuRequestHandler([
			'make' => [null, 4],
			'getWaifu' => [$this->waifuResponse, 4],
		]);
		$test_args = [
			['url' => 'https://domain.com/image.png'],
			['file' =>  __DIR__ . '/image.jpg'],
			['file' =>  __DIR__ . '/image.jpg', 'filename' => 'customfilename.jpg'],
			['file_contents' =>  "file contents", 'filename' => 'customfilename.jpg'],
		];
		try {
			foreach ($test_args as $args) {
				$response = $this->waifu->uploadFile($args);
				$this->assertEqualsCanonicalizing($this->waifuResponse, $response);
			}
		} catch (Exception $e) {
			$this->fail(__FUNCTION__ . " failed: {$e->getMessage()}");
		}
	}

	public function testUploadFileExceptions(): void {
		$bad_args = [
			['file' =>  __DIR__ . '/nonexistent1.jpg'],
			['file' =>  __DIR__ . '/nonexistent2.jpg'],
			['file' =>  __DIR__ . '/nonexistent2.jpg', 'filename' => 'random'],
			['file' =>  __DIR__ . '/nonexistent2.jpg', 'filename' => ''],
			['file_contents' =>  __DIR__ . '/nonexistent2.jpg'],
			['file_contents' =>  __DIR__ . '/nonexistent2.jpg', 'filename' => ''],
			['_file' =>  __DIR__ . '/nonexistent2.jpg', '_filename' => ''],
			[]
		];
		foreach ($bad_args as $args) {
			try {
				// Upload via URL
				$this->waifu->uploadFile($args); // @phpstan-ignore-line
			} catch (Exception $e) {
				$this->assertSame(Exception::class, get_class($e));
				$this->addToAssertionCount(1);
			}
		}
		$this->assertSame(count($bad_args), $this->numberOfAssertionsPerformed());
	}

	public function testGetFileInfo(): void {
		$this->setUpMockWaifuRequestHandler([
			'make' => [null, 2],
			'getWaifu' => [$this->waifuResponse, 2],
		]);

		$response = $this->waifu->getFileInfo($this->waifuResponse->token);
		$this->assertEqualsCanonicalizing($this->waifuResponse, $response);

		$response = $this->waifu->getFileInfo($this->waifuResponse->token, false);
		$this->assertEqualsCanonicalizing($this->waifuResponse, $response);

		$this->expectException(Exception::class);
		$this->waifu->getFileInfo('', false);
	}

	public function testModifyEntry(): void {
		$this->setUpMockWaifuRequestHandler([
			'make' => [null, 1],
			'getWaifu' => [$this->waifuResponse, 1],
		]);

		$response = $this->waifu->modifyEntry(['token' => $this->waifuResponse->token]);
		$this->assertEqualsCanonicalizing($this->waifuResponse, $response);

		$this->expectException(Exception::class);
		$this->waifu->modifyEntry(['token' => '']);
	}

	public function testDeleteEntry(): void {
		$this->setUpMockWaifuRequestHandler([
			'make' => [null, 1],
			'getTrue' => [true, 1],
		]);

		$response = $this->waifu->deleteEntry($this->waifuResponse->token);
		$this->assertSame(true, $response);

		$this->expectException(Exception::class);
		$this->waifu->deleteEntry('');
	}

	public function testGetFile(): void {
		$this->setUpMockWaifuRequestHandler([
			'make' => [null, 3],
			'getRaw' => ['hi', 2],
			'getWaifu' => [$this->waifuResponse, 1],
		]);

		// make called 1 time, getRaw called 1 time
		$response = $this->waifu->getFile(['token' => $this->waifuResponse->token]);
		$this->assertSame('hi', $response);

		// make called 2 times (call to getFileinfo), getRaw called 1 time, getWaifu called 1 time
		$response = $this->waifu->getFile(['filename' => $this->waifuResponse->token]);
		$this->assertSame('hi', $response);

		$this->expectException(Exception::class);
		$this->waifu->getFile(['_filename' => $this->waifuResponse->token]);
	}

	public function setUp(): void {
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
		$this->waifu = new WaifuApi();
	}

	/**
	 * @param array<string, array<mixed>> $methods
	 * @return void
	 */
	private function setUpMockWaifuRequestHandler(array $methods): void {
		$waifuHandlerMock = $this->getMockBuilder(WaifuRequestHandler::class)
			->disableOriginalConstructor()
			->getMock();
		foreach ($methods as $method => $data) {
			$returns = $method === 'make' ? $waifuHandlerMock : $data[0];
			$waifuHandlerMock
				->expects($this->exactly($data[1]))
				->method($method)
				->willReturn($returns);
		}
		$this->waifu = new WaifuApi($waifuHandlerMock);
	}
}
