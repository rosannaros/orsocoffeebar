<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Db;
use Midtrans\Config;
use Midtrans\Notification;
use Exception;

class MidtransController {

    public function handleNotification(Request $request, Response $response) {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            $response->getBody()->write(json_encode(["error" => "No data received"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        Config::$serverKey = 'Mid-server-Fv0j-OGf-rcrajTkL7HbF4EK';
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        try {
            $notif = new Notification();

            $transaction = $notif->transaction_status;
            $order_id_full = $notif->order_id;

            $parts = explode('-', $order_id_full);
            $id_pesanan = $parts[1] ?? null;

            if (!$id_pesanan) {
                $response->getBody()->write(json_encode(["error" => "Order ID tidak valid"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            $db = new Db();
            $conn = $db->connect();

            switch ($transaction) {
                case 'settlement':
                case 'capture':
                    $status = 'diproses';
                    break;
                case 'pending':
                    $status = 'pending';
                    break;
                case 'expire':
                case 'cancel':
                    $status = 'dibatalkan';
                    break;
                default:
                    $status = $transaction;
            }

            $sql = "UPDATE pesanan SET status_pesanan = :status WHERE id_pesanan = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':status' => $status,
                ':id' => $id_pesanan
            ]);

            $response->getBody()->write(json_encode([
                "status" => "success",
                "message" => "Pesanan ID $id_pesanan berhasil diupdate menjadi $status.",
                "data_received" => [
                    "order_id" => $order_id_full,
                    "transaction_status" => $transaction
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (Exception $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
