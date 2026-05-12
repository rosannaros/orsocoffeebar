<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Db;
use Midtrans\Config;
use Midtrans\Notification;
use PDO;

class MidtransController {
    public function handleNotification(Request $request, Response $response) {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            $response->getBody()->write(json_encode(["error" => "No data received"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        Config::$serverKey = 'Mid-server-Fv0j-OGf-rcrajTkL7HbF4EK';
        Config::$isProduction = false;

        try {
            $notif = new Notification();
            
            $transaction = $notif->transaction_status;
            $order_id_full = $notif->order_id; 

            $parts = explode('-', $order_id_full);
            $id_pesanan = $parts[1];

            $db = new Db();
            $conn = $db->connect();
            
            if ($transaction == 'settlement' || $transaction == 'capture') {
                $sql = "UPDATE pesanan SET status_pesanan = 'diproses' WHERE id_pesanan = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute([':id' => $id_pesanan]);
            }

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $e) {
            return $response->withStatus(500);
        }
    }
}