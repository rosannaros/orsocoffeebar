<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Db;
use PDO;
use PDOException;

class MenuController {
    public function getAll(Request $request, Response $response) {
        try {
            $db = new Db();
            $conn = $db->connect();
            $sql = "SELECT * FROM menu ORDER BY kategori ASC";
            $stmt = $conn->query($sql);
            $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode($menus));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function create(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        $foto = $uploadedFiles['image_url'] ?? null;

        $status = in_array($data['status_menu'] ?? '', ['tersedia', 'habis'])
            ? $data['status_menu']
            : 'tersedia';

        $nama_file = null;
        if ($foto && $foto->getError() === UPLOAD_ERR_OK) {
            $extension = pathinfo($foto->getClientFilename(), PATHINFO_EXTENSION);
            $nama_file = "menu-" . time() . "." . $extension;

            $directory = __DIR__ . '/../../public/uploads/menu/';
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            $foto->moveTo($directory . $nama_file);
        }

        try {
            $db = new Db();
            $conn = $db->connect();

            $sql = "INSERT INTO menu (nama_menu, harga, kategori, deskripsi, image_url, status_menu) 
                    VALUES (:nama, :harga, :kategori, :deskripsi, :img, :status)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nama'      => $data['nama_menu'],
                ':harga'     => $data['harga'],
                ':kategori'  => $data['kategori'],
                ':deskripsi' => $data['deskripsi'],
                ':img'       => $nama_file,
                ':status'    => $status
            ]);

            $response->getBody()->write(json_encode(["message" => "Menu berhasil ditambahkan!"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function update(Request $request, Response $response, array $args) {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        $foto = $uploadedFiles['image_url'] ?? null;

        $status = in_array($data['status_menu'] ?? '', ['tersedia', 'habis'])
            ? $data['status_menu']
            : 'tersedia';

        try {
            $db = new Db();
            $conn = $db->connect();

            if ($foto && $foto->getError() === UPLOAD_ERR_OK) {

                $extension = pathinfo($foto->getClientFilename(), PATHINFO_EXTENSION);
                $nama_file = "menu-" . time() . "." . $extension;

                $directory = __DIR__ . '/../../public/uploads/menu/';
                if (!is_dir($directory)) {
                    mkdir($directory, 0777, true);
                }

                $foto->moveTo($directory . $nama_file);

                $sql = "UPDATE menu SET nama_menu=:nama, harga=:harga, kategori=:kategori, 
                        deskripsi=:deskripsi, image_url=:img, status_menu=:status 
                        WHERE id_menu=:id";

                $params = [
                    ':nama'      => $data['nama_menu'] ?? '',
                    ':harga'     => $data['harga'] ?? 0,
                    ':kategori'  => $data['kategori'] ?? '',
                    ':deskripsi' => $data['deskripsi'] ?? '',
                    ':img'       => $nama_file,
                    ':status'    => $status,
                    ':id'        => $id
                ];

            } else {

                $sql = "UPDATE menu SET nama_menu=:nama, harga=:harga, kategori=:kategori, 
                        deskripsi=:deskripsi, status_menu=:status 
                        WHERE id_menu=:id";

                $params = [
                    ':nama'      => $data['nama_menu'] ?? '',
                    ':harga'     => $data['harga'] ?? 0,
                    ':kategori'  => $data['kategori'] ?? '',
                    ':deskripsi' => $data['deskripsi'] ?? '',
                    ':status'    => $status,
                    ':id'        => $id
                ];
            }

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $response->getBody()->write(json_encode(["message" => "Menu berhasil diperbarui"]));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function delete(Request $request, Response $response, array $args) {
        $id = $args['id'];

        try {
            $db = new Db();
            $conn = $db->connect();

            $stmtSelect = $conn->prepare("SELECT image_url FROM menu WHERE id_menu = :id");
            $stmtSelect->execute([':id' => $id]);
            $menu = $stmtSelect->fetch(PDO::FETCH_ASSOC);

            if ($menu && $menu['image_url']) {
                $path = __DIR__ . '/../../public/uploads/menu/' . $menu['image_url'];
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $sql = "DELETE FROM menu WHERE id_menu = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);

            $response->getBody()->write(json_encode(["message" => "Menu berhasil dihapus"]));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}