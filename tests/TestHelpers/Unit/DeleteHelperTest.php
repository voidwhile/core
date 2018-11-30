<?php

use TestHelpers\DeleteHelper;
use TestHelpers\WebDavHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Subscriber\History;

class DeleteHelperTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$mock = new Mock([
			new Response(204, []),
		]);

		$this->client = new Client();

		$this->history = new History();

		$this->client->getEmitter()->attach($mock);
		$this->client->getEmitter()->attach($this->history);
	}

	public function testDeleteHelperWithOlderDavVersion() {
		$response = DeleteHelper::delete(
			'http://localhost',
			'user',
			'password',
			'secret/file.txt',
			[],
			1,
			null,
			$this->client
		);

		$lastRequest = $this->history->getLastRequest();
		$this->assertEquals(
			'http://localhost/remote.php/webdav/secret/file.txt',
			$lastRequest->getUrl()
		);
		$this->assertEquals('DELETE', $lastRequest->getMethod());
		$this->assertNull($lastRequest->getBody());

		$this->assertEquals(
			['Basic ' . \base64_encode('user:password')],
			$lastRequest->getHeaders()["Authorization"]
		);
	}

	public function testDeleteHelperWithNewerDavVersion() {
		$response = DeleteHelper::delete(
			'http://localhost',
			'user',
			'password',
			'secret/file.txt',
			[],
			2,
			null,
			$this->client
		);

		$lastRequest = $this->history->getLastRequest();
		$this->assertEquals(
			'http://localhost/remote.php/dav/files/user/secret/file.txt',
			$lastRequest->getUrl()
		);
		$this->assertEquals('DELETE', $lastRequest->getMethod());
		$this->assertNull($lastRequest->getBody());

		$this->assertEquals(
			['Basic ' . \base64_encode('user:password')],
			$lastRequest->getHeaders()["Authorization"]
		);
	}

	public function testDeleteHelperSendsWithGivenHeaders() {
		$headers = ["Cache-Control" => "no-cache"];
		$response = DeleteHelper::delete(
			'http://localhost',
			'user',
			'password',
			'secret/file.txt',
			$headers,
			1,
			null,
			$this->client
		);

		$lastRequest = $this->history->getLastRequest();

		$this->assertArrayHasKey("Cache-Control", $lastRequest->getHeaders());
		// Guzzle adds it to the array
		$this->assertEquals(["no-cache"], $lastRequest->getHeaders()["Cache-Control"]);
	}
}
