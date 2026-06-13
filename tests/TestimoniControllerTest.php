<?php
use PHPUnit\Framework\TestCase;
use App\Controllers\TestimoniController;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\UploadedFileFactory;
use Slim\Psr7\Factory\StreamFactory;

class TestimoniControllerTest extends TestCase
{
    private $controller;
    private $requestFactory;
    private $responseFactory;

    protected function setUp(): void
    {
        $this->controller = new TestimoniController();
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    public function testCreateTestimoniTanpaUserId()
    {
        $request = $this->requestFactory->createServerRequest("POST", "/testimoni")
            ->withParsedBody(['isi_testimoni' => 'Kopinya enak!']);

        $response = $this->responseFactory->createResponse();
        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
    }

    public function testCreateTestimoniFotoDefault()
    {
        $request = $this->requestFactory->createServerRequest("POST", "/testimoni")
            ->withParsedBody([
                'id_user' => 8,
                'isi_testimoni' => 'Pelayanan ramah sekali.'
            ]);

        $response = $this->responseFactory->createResponse();
        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
    }

    public function testCreateTestimoniFotoKustom()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'TEST');
        file_put_contents($tempFile, 'fake_image_data');

        $stream = (new StreamFactory())->createStreamFromFile($tempFile);
        $uploadedFile = (new UploadedFileFactory())->createUploadedFile(
            $stream, filesize($tempFile), UPLOAD_ERR_OK, 'test.jpg', 'image/jpeg'
        );

        $request = $this->requestFactory->createServerRequest("POST", "/testimoni")
            ->withParsedBody([
                'id_user' => 8,
                'isi_testimoni' => 'Foto kustom tes'
            ])
            ->withUploadedFiles(['foto_testimoni' => $uploadedFile]);

        $response = $this->responseFactory->createResponse();
        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        if (file_exists($tempFile)) unlink($tempFile);
    }

    public function testApproveTestimoni()
    {
        $args = ['id' => '1']; 
        $request = $this->requestFactory->createServerRequest("PUT", "/approve/1");
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->approveTestimoni($request, $response, $args);
        $this->assertEquals(200, $result->getStatusCode());
    }
}