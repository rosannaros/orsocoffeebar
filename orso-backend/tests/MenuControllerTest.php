<?php
use PHPUnit\Framework\TestCase;
use App\Controllers\MenuController;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\UploadedFileFactory;

class MenuControllerTest extends TestCase
{
    private $controller;
    private $requestFactory;
    private $responseFactory;

    protected function setUp(): void
    {
        $this->controller = new MenuController();
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    public function testGetAllMenuBerhasil()
    {
        $request = $this->requestFactory->createServerRequest("GET", "/menu");
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->getAll($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertStringContainsString('application/json', $result->getHeaderLine('Content-Type'));
    }

    public function testCreateMenuTanpaGambar()
    {
        $request = $this->requestFactory->createServerRequest("POST", "/owner/menu");
        $request = $request->withParsedBody([
            'nama_menu' => 'Kopi Susu Test',
            'harga' => 15000,
            'kategori' => 'Coffee',
            'deskripsi' => 'Testing menu baru tanpa gambar'
        ]);

        $response = $this->responseFactory->createResponse();
        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
    }

    public function testUpdateMenuTanpaGantiGambar()
    {
        $args = ['id' => '25']; 
        
        $request = $this->requestFactory->createServerRequest("POST", "/owner/menu/25");
        $request = $request->withParsedBody([
            'nama_menu' => 'Kopi Update',
            'harga' => 25000,
            'kategori' => 'Coffee',
            'deskripsi' => 'Update deskripsi saja'
        ]);

        $response = $this->responseFactory->createResponse();
        $result = $this->controller->update($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
    }

 public function testUpdateMenuDenganGambarValid()
{
    $args = ['id' => '1'];

    $tempFilePath = tempnam(sys_get_temp_dir(), 'PHPUPLOAD');
    file_put_contents($tempFilePath, 'isi_file_gambar_dummy');
    $size = filesize($tempFilePath);

    $streamFactory = new \Slim\Psr7\Factory\StreamFactory();
    $stream = $streamFactory->createStreamFromFile($tempFilePath);

    $fileFactory = new \Slim\Psr7\Factory\UploadedFileFactory();
    $image = $fileFactory->createUploadedFile(
        $stream,
        $size, 
        UPLOAD_ERR_OK,
        'kopi_test.jpg',
        'image/jpeg'
    );

    $request = $this->requestFactory->createServerRequest("POST", "/owner/menu/1")
        ->withParsedBody([
            'nama_menu' => 'Kopi Update Gambar',
            'harga' => 25000,
            'kategori' => 'Coffee'
        ])
        ->withUploadedFiles(['image_url' => $image]);

    $response = $this->responseFactory->createResponse();
    
    $result = $this->controller->update($request, $response, $args);

    $this->assertEquals(200, $result->getStatusCode());

    if (file_exists($tempFilePath)) {
        unlink($tempFilePath);
    }
}
    public function testDeleteMenuBerhasil()
    {
        $args = ['id' => '27'];

        $request = $this->requestFactory->createServerRequest("DELETE", "/owner/menu/27");
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->delete($request, $response, $args);

        $this->assertEquals(200, $result->getStatusCode());
    }
}