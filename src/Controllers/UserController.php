<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Db;
use PDO;

class UserController {
    public function register(Request $request, Response $response) {
        $data = $request->getParsedBody();

        $password = $data['password'] ?? '';
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $db = new Db();
            $conn = $db->connect();

            $sqlCheck = "SELECT COUNT(*) FROM users WHERE email = :email";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->execute([':email' => $data['email']]);
            $emailExists = $stmtCheck->fetchColumn();

            if ($emailExists > 0) {
                $response->getBody()->write(json_encode([
                    "error" => "Email sudah terdaftar. Silakan gunakan email lain."
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $sql = "INSERT INTO users (nama, email, password, role) 
                    VALUES (:nama, :email, :password, 'pelanggan')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nama'     => $data['nama'],
                ':email'    => $data['email'],
                ':password' => $passwordHash
            ]);

            $response->getBody()->write(json_encode(["message" => "Registrasi berhasil!"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (\PDOException $e) {
            $response->getBody()->write(json_encode(["error" => "Gagal: " . $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function login(Request $request, Response $response) {
        $data = $request->getParsedBody();

        try {
            $db = new Db();
            $conn = $db->connect();
            
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':email' => $data['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($data['password'], $user['password'])) {
                unset($user['password']); 
                
                $response->getBody()->write(json_encode([
                    "message" => "Login berhasil!",
                    "user" => $user
                ]));
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write(json_encode(["error" => "Email atau password salah."]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }
        } catch (\PDOException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function getAllUsers(Request $request, Response $response) {
        try {
            $db = new Db();
            $conn = $db->connect();
            $sql = "SELECT id_user, nama, email, role FROM users ORDER BY id_user DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode($users));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withStatus(500);
        }
    }   

    public function updateUser(Request $request, Response $response, array $args) {
        $id = $args['id'];
        $data = $request->getParsedBody();
        try {
            $db = new Db();
            $conn = $db->connect();
        
            $sql = "UPDATE users SET nama = :nama, email = :email, role = :role WHERE id_user = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nama'  => $data['nama'],
                ':email' => $data['email'],
                ':role'  => $data['role'],
                ':id'    => $id
            ]);

            $response->getBody()->write(json_encode(["message" => "User diperbarui!"]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withStatus(500);
        }
    }

    public function deleteUser(Request $request, Response $response, array $args) {
        $id = $args['id'];
        try {
            $db = new Db();
            $conn = $db->connect();
            $sql = "DELETE FROM users WHERE id_user = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);

            $response->getBody()->write(json_encode(["message" => "User dihapus!"]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withStatus(500);
        }
    }
    
    public function updateProfile(Request $request, Response $response, array $args) {
        $id = $args['id'];
        $data = $request->getParsedBody();

        try {
            $db = new Db();
            $conn = $db->connect();

            if (!empty($data['password'])) {
                $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET nama = :nama, email = :email, password = :pass WHERE id_user = :id";
                $params = [
                    ':nama'  => $data['nama'],
                    ':email' => $data['email'],
                    ':pass'  => $passwordHash,
                    ':id'    => $id
                ];
            } else {
                $sql = "UPDATE users SET nama = :nama, email = :email WHERE id_user = :id";
                $params = [
                    ':nama'  => $data['nama'],
                    ':email' => $data['email'],
                    ':id'    => $id
                ];
            }

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            $response->getBody()->write(json_encode(["message" => "Profil berhasil diperbarui!"]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\PDOException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }    
}