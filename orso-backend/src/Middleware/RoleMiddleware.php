<?php
namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response;

class RoleMiddleware {
    private $allowedRoles;

    public function __construct(array $allowedRoles) {
        $this->allowedRoles = $allowedRoles;
    }

    public function __invoke(Request $request, Handler $handler) {
        $userRole = $request->getHeaderLine('X-Role'); 

        if (!in_array($userRole, $this->allowedRoles)) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                "error" => "Akses Ditolak: Role '$userRole' tidak memiliki izin."
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        return $handler->handle($request);
    }
}