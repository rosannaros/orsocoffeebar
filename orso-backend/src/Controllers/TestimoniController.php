<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Db;
use PDO;
use Exception;

class TestimoniController {
    public function getApproved(Request $request, Response $response) {
        try {
            $db = new Db();
            $conn = $db->connect();

            $sql = "SELECT t.id_testimoni, u.nama, t.isi_testimoni, t.foto_testimoni
                    FROM testimoni t
                    JOIN users u ON t.id_user = u.id_user
                    WHERE t.status_persetujuan = 'disetujui'
                    ORDER BY t.id_testimoni DESC";

            $stmt = $conn->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (\PDOException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withStatus(500);
        }
    }

    public function create(Request $request, Response $response) {

        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        $id_user = $data['id_user'] ?? null;
        $isi_testimoni = $data['isi_testimoni'] ?? '';
        $foto = $uploadedFiles['foto_testimoni'] ?? null;
        $fotoName = null;

        if (!$id_user) {
            $response->getBody()->write(json_encode(["error" => "User ID tidak ditemukan."]));
            return $response->withStatus(400);
        }

        try {
            $db = new Db();
            $conn = $db->connect();

            $testimoniDir = __DIR__ . '/../../public/uploads/testimoni';

            if (!is_dir($testimoniDir)) {
                mkdir($testimoniDir, 0777, true);
            }

            if ($foto && $foto->getError() === UPLOAD_ERR_OK) {
                $extension = pathinfo($foto->getClientFilename(), PATHINFO_EXTENSION);
                $fotoName = bin2hex(random_bytes(8)) . '.' . $extension;

                $foto->moveTo($testimoniDir . DIRECTORY_SEPARATOR . $fotoName);

            } else {
                $fotoName = 'beruang-imut.jpeg';
            }

            $sql = "INSERT INTO testimoni
                    (id_user, isi_testimoni, foto_testimoni, status_persetujuan)
                    VALUES (:id_user, :isi, :foto, 'pending')";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id_user' => $id_user,
                ':isi'     => $isi_testimoni,
                ':foto'    => $fotoName
            ]);

            $response->getBody()->write(json_encode([
                "message" => "Ulasan terkirim! Menunggu moderasi admin."
            ]));

            return $response->withStatus(201);

        } catch (Exception $e) {

            $response->getBody()->write(json_encode([
            "error" => $e->getMessage()
            ]));

            return $response->withStatus(500);
        }
    }

    public function approveTestimoni(Request $request, Response $response, array $args) {

        $id = $args['id'];

        try {
            $db = new Db();
            $conn = $db->connect();

            $sql = "UPDATE testimoni
                    SET status_persetujuan = 'disetujui'
                    WHERE id_testimoni = :id";

            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);

            $response->getBody()->write(json_encode([
                "message" => "Testimoni Approved"
            ]));

            return $response->withStatus(200);

        } catch (Exception $e) {

            $response->getBody()->write(json_encode([
                "error" => $e->getMessage()
            ]));

            return $response->withStatus(500);
        }
    }

    public function delete(Request $request, Response $response, array $args) {

        $id = $args['id'];

        try {
            $db = new Db();
            $conn = $db->connect();
            $stmtFile = $conn->prepare(
                "SELECT foto_testimoni FROM testimoni WHERE id_testimoni = :id"
            );
            $stmtFile->execute([':id' => $id]);
            $row = $stmtFile->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['foto_testimoni']) {

                $path = __DIR__ . '/../../public/uploads/testimoni/' . $row['foto_testimoni'];

                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $stmt = $conn->prepare(
                "DELETE FROM testimoni WHERE id_testimoni = :id"
            );
            $stmt->execute([':id' => $id]);

            $response->getBody()->write(json_encode([
                "message" => "Testimoni dihapus."
            ]));

            return $response->withStatus(200);

        } catch (Exception $e) {

            $response->getBody()->write(json_encode([
                "error" => $e->getMessage()
            ]));

            return $response->withStatus(500);
        }
    }

    public function getAll(Request $request, Response $response) {

        try {
            $db = new Db();
            $conn = $db->connect();

            $sql = "SELECT t.id_testimoni, u.nama, t.isi_testimoni,
                           t.foto_testimoni, t.status_persetujuan
                    FROM testimoni t
                    JOIN users u ON t.id_user = u.id_user
                    ORDER BY t.id_testimoni DESC";

            $stmt = $conn->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');

        } catch (Exception $e) {

            $response->getBody()->write(json_encode([
                "error" => $e->getMessage()
            ]));

            return $response->withStatus(500);
        }
    }
}