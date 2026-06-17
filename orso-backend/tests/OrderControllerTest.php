<?php
use PHPUnit\Framework\TestCase;
use App\Controllers\OrderController;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class OrderControllerTest extends TestCase
{
    private $controller;
    private $requestFactory;
    private $responseFactory;

    protected function setUp(): void
    {
        $this->controller = new OrderController();
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    public function testCreateOrderDataTidakLengkap()
    {
        $request = $this->requestFactory->createServerRequest("POST", "/order")
            ->withParsedBody([
                'id_user' => 1,
                'items' => []
            ]);

        $response = $this->responseFactory->createResponse();
        $result = $this->controller->createOrder($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
    }

    public function testCreateOrderMenuNotFound()
    {
        $request = $this->requestFactory->createServerRequest("POST", "/order")
            ->withParsedBody([
                'id_user' => 8,
                'items' => [
                    ['id_menu' => 9999, 'jumlah_pesanan' => 1]
                ]
            ]);

        $response = $this->responseFactory->createResponse();
        $result = $this->controller->createOrder($request, $response);

        $this->assertEquals(500, $result->getStatusCode());
    }
    public function testCreateOrderBerhasil()
    {
        $request = $this->requestFactory->createServerRequest("POST", "/order")
            ->withParsedBody([
                'id_user' => 8,
                'nama_pelanggan' => 'Tester',
                'items' => [
                    ['id_menu' => 1, 'jumlah_pesanan' => 2]
                ]
            ]);

        $response = $this->responseFactory->createResponse();
        $result = $this->controller->createOrder($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertStringContainsString('snap_token', (string)$result->getBody());
    }
}