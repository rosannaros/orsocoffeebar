<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Db;
use Exception;

class MidtransHandler {

    public function handleNotification(Request $request, Response $response) {
        try {
            $body = $request->getBody()->getContents();
            $data = json_decode($body, true);

            if (!$data) {
                $response->getBody()->write(json_encode(["error" => "Invalid JSON"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $transaction = $data['transaction_status'] ?? '';
            $order_id_full = $data['order_id'] ?? '';
            
            $parts = explode('-', $order_id_full);
            $id_pesanan = isset($parts[1]) ? $parts[1] : $order_id_full;

            $db = new Db();
            $conn = $db->connect();

            if ($transaction == 'settlement' || $transaction == 'capture') {
                $sql = "UPDATE pesanan SET status_pesanan = 'diproses' WHERE id_pesanan = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':id' => $id_pesanan]);
                
                $msg = "Pesanan ID $id_pesanan berhasil diupdate menjadi diproses.";
            } else {
                $msg = "Status transaksi adalah $transaction, tidak ada perubahan di database.";
            }

            $response->getBody()->write(json_encode([
                "status" => "success",
                "message" => $msg,
                "data_received" => [
                    "order_id" => $order_id_full,
                    "status" => $transaction
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (Exception $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}