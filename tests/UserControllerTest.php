<?php
use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class UserControllerTest extends TestCase
{
    public function testLoginBerhasil()
    {
        $controller = new UserController();

        $requestFactory = new ServerRequestFactory();
        $responseFactory = new ResponseFactory();

        $request = $requestFactory->createServerRequest("POST", "/login");
        $request = $request->withParsedBody([
            'email' => 'maritza@gmail.com',
            'password' => 'password123'
        ]);

        $response = $responseFactory->createResponse();

        $result = $controller->login($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
    }

    // public function testLoginGagal()
    // {
    //     $controller = new UserController();

    //     $requestFactory = new ServerRequestFactory();
    //     $responseFactory = new ResponseFactory();

    //     $request = $requestFactory->createServerRequest("POST", "/login");
    //     $request = $request->withParsedBody([
    //         'email' => 'afrah@gmail.com',
    //         'password' => 'salah'
    //     ]);

    //     $response = $responseFactory->createResponse();

    //     $result = $controller->login($request, $response);

    //     $this->assertEquals(401, $result->getStatusCode());
    // }

    // public function testRegisterBerhasil()
    // {
    //     $controller = new UserController();

    //     $requestFactory = new \Slim\Psr7\Factory\ServerRequestFactory();
    //     $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();

    //     $request = $requestFactory->createServerRequest("POST", "/register");
    //     $request = $request->withParsedBody([
    //         'nama' => 'maritzaa',
    //         'email' => 'baru@mail.com',
    //         'password' => '123456'
    //     ]);

    //     $response = $responseFactory->createResponse();

    //     $result = $controller->register($request, $response);

    //     $this->assertEquals(201, $result->getStatusCode());
    // }
    // public function testRegisterEmailDuplikat()
    // {
    // $controller = new UserController();

    // $requestFactory = new \Slim\Psr7\Factory\ServerRequestFactory();
    // $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();

    // $request = $requestFactory->createServerRequest("POST", "/register");
    // $request = $request->withParsedBody([
    //     'nama' => 'Afrah',
    //     'email' => 'afrah@gmail.com', // sudah ada
    //     'password' => 'password123'
    // ]);

    // $response = $responseFactory->createResponse();

    // $result = $controller->register($request, $response);

    // $this->assertEquals(400, $result->getStatusCode());
    // }
    
}
use PHPUnit\TextUI\Configuration\Php;