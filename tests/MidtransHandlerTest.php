<?php
use PHPUnit\Framework\TestCase;
use App\Controllers\MidtransHandler;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Stream;

// class MidtransHandlerTest extends TestCase
// {
//     private $handler;
//     private $requestFactory;
//     private $responseFactory;

//     protected function setUp(): void
//     {
//         $this->handler = new MidtransHandler();
//         $this->requestFactory = new ServerRequestFactory();
//         $this->responseFactory = new ResponseFactory();
//     }

//     public function testHandleNotificationInvalidJson()
//     {
//         $request = $this->requestFactory->createServerRequest("POST", "/notification");
//         $stream = fopen('php://temp', 'r+');
//         fwrite($stream, 'bukan-json-format');
//         rewind($stream);
//         $request = $request->withBody(new \Slim\Psr7\Stream($stream));

//         $response = $this->responseFactory->createResponse();
//         $result = $this->handler->handleNotification($request, $response);

//         $this->assertEquals(400, $result->getStatusCode());
//     }

//     public function testHandleNotificationSettlement()
//     {
//         $request = $this->requestFactory->createServerRequest("POST", "/notification");
//         $data = json_encode([
//             "transaction_status" => "settlement",
//             "order_id" => "ORSO-227-177303" 
//         ]);
        
//         $stream = fopen('php://temp', 'r+');
//         fwrite($stream, $data);
//         rewind($stream);
//         $request = $request->withBody(new \Slim\Psr7\Stream($stream));

//         $response = $this->responseFactory->createResponse();
//         $result = $this->handler->handleNotification($request, $response);

//         $this->assertEquals(200, $result->getStatusCode());
//         $this->assertStringContainsString('berhasil diupdate', (string)$result->getBody());
//     }

//     public function testHandleNotificationPending()
//     {
//         $request = $this->requestFactory->createServerRequest("POST", "/notification");
//         $data = json_encode([
//             "transaction_status" => "pending",
//             "order_id" => "ORSO-228-177303"
//         ]);
        
//         $stream = fopen('php://temp', 'r+');
//         fwrite($stream, $data);
//         rewind($stream);
//         $request = $request->withBody(new \Slim\Psr7\Stream($stream));

//         $response = $this->responseFactory->createResponse();
//         $result = $this->handler->handleNotification($request, $response);

//         $this->assertEquals(200, $result->getStatusCode());
//         $this->assertStringContainsString('tidak ada perubahan', (string)$result->getBody());
//     }
// }