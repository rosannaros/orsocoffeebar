<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Db;
use Midtrans\Config;
use Midtrans\Snap;
use PDO;
use Exception;

class OrderController {
public function createOrder(Request $request, Response $response) {
    date_default_timezone_set('Asia/Makassar');

    $data = $request->getParsedBody();
    $db = new Db();
    $conn = $db->connect();

    if (!isset($data['id_user']) || !isset($data['items']) || empty($data['items'])) {
        $response->getBody()->write(json_encode([
            "error" => "Data tidak lengkap (id_user atau items kosong)."
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    Config::$serverKey = 'Mid-server-Fv0j-OGf-rcrajTkL7HbF4EK';
    Config::$isProduction = false;
    Config::$isSanitized = true;
    Config::$is3ds = true;

    try {
        $conn->beginTransaction();

        $expired = date('Y-m-d H:i:s', strtotime('-10 minutes'));
        $sqlAutoCancel = "UPDATE pesanan 
                          SET status_pesanan = 'dibatalkan' 
                          WHERE status_pesanan = 'pending' 
                          AND tgl_pesanan < :expired";
        $stmtAuto = $conn->prepare($sqlAutoCancel);
        $stmtAuto->execute([':expired' => $expired]);

        $total_harga_final = 0;
        $items_dengan_harga = [];

        foreach ($data['items'] as $item) {

            $stmtMenu = $conn->prepare("
                SELECT nama_menu, harga, status_menu 
                FROM menu 
                WHERE id_menu = :id
            ");
            $stmtMenu->execute([':id' => $item['id_menu']]);
            $menu = $stmtMenu->fetch(PDO::FETCH_ASSOC);

            if (!$menu) {
                throw new Exception("Menu dengan ID " . $item['id_menu'] . " tidak ditemukan.");
            }

            if ($menu['status_menu'] !== 'tersedia') {
                throw new Exception("Menu '" . $menu['nama_menu'] . "' sedang habis dan tidak bisa dipesan.");
            }

            $subtotal = $menu['harga'] * $item['jumlah_pesanan'];
            $total_harga_final += $subtotal;

            $items_dengan_harga[] = [
                'id_menu' => $item['id_menu'],
                'nama_menu' => $menu['nama_menu'],
                'qty' => $item['jumlah_pesanan'],
                'harga_satuan' => $menu['harga'],
                'subtotal' => $subtotal
            ];
        }

        $waktuSekarang = date('Y-m-d H:i:s');

        $sqlOrder = "INSERT INTO pesanan (id_user, total_harga, status_pesanan, tgl_pesanan) 
                     VALUES (:id_user, :total, 'pending', :tgl)";
        $stmtOrder = $conn->prepare($sqlOrder);
        $stmtOrder->execute([
            ':id_user' => $data['id_user'],
            ':total'   => $total_harga_final,
            ':tgl'     => $waktuSekarang
        ]);

        $idPesanan = $conn->lastInsertId();

        foreach ($items_dengan_harga as $item) {
            $sqlDetail = "INSERT INTO detail_pesanan (id_pesanan, id_menu, jumlah_pesanan, total_harga) 
                          VALUES (:id_p, :id_m, :qty, :subtotal)";
            $stmtDetail = $conn->prepare($sqlDetail);
            $stmtDetail->execute([
                ':id_p'     => $idPesanan,
                ':id_m'     => $item['id_menu'],
                ':qty'      => $item['qty'],
                ':subtotal' => $item['subtotal']
            ]);
        }

        $params = [
            'transaction_details' => [
                'order_id' => 'ORSO-' . $idPesanan . '-' . time(),
                'gross_amount' => (int)$total_harga_final,
            ],
            'customer_details' => [
                'first_name' => $data['nama_pelanggan'] ?? 'Pelanggan Orso',
            ],
            'expiry' => [
                'unit' => 'minutes',
                'duration' => 10
            ]
        ];

        $snapToken = Snap::getSnapToken($params);

        $stmtToken = $conn->prepare("
            UPDATE pesanan 
            SET snap_token = :token 
            WHERE id_pesanan = :id
        ");
        $stmtToken->execute([
            ':token' => $snapToken,
            ':id' => $idPesanan
        ]);

        $conn->commit();

        $response->getBody()->write(json_encode([
            "message" => "Order berhasil dibuat",
            "snap_token" => $snapToken,
            "total_yang_harus_dibayar" => $total_harga_final,
            "id_pesanan" => $idPesanan
        ]));

        return $response->withHeader('Content-Type', 'application/json');

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        $response->getBody()->write(json_encode([
            "error" => $e->getMessage()
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
}
    public function getCustomerOrders(Request $request, Response $response, array $args) {
        $id_user = $args['id_user']; 
        try {
            $db = new Db();
            $conn = $db->connect();

            $sqlCleanup = "UPDATE pesanan SET status_pesanan = 'dibatalkan' 
                           WHERE id_user = :id_u 
                           AND status_pesanan = 'pending' 
                           AND tgl_pesanan < NOW() - INTERVAL 10 MINUTE";
            $stmtCleanup = $conn->prepare($sqlCleanup);
            $stmtCleanup->execute([':id_u' => $id_user]);

            $sql = "SELECT id_pesanan, total_harga, status_pesanan, tgl_pesanan, snap_token 
                    FROM pesanan WHERE id_user = :id ORDER BY tgl_pesanan DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id_user]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode($orders));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    public function getOrderDetail(Request $request, Response $response, array $args) {
        $id_pesanan = $args['id'];
        try {
            $db = new Db();
            $conn = $db->connect();
            $sql = "SELECT d.*, m.nama_menu, m.harga, m.image_url 
                    FROM detail_pesanan d 
                    JOIN menu m ON d.id_menu = m.id_menu 
                    WHERE d.id_pesanan = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id_pesanan]);
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($details as &$item) {
                $item['full_image_path'] = $item['image_url'] ? "http://127.0.0.1:8000/uploads/menu/" . $item['image_url'] : null;
            }

            $response->getBody()->write(json_encode($details));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withStatus(500);
        }
    }

    public function getAllOrders(Request $request, Response $response) {
        try {
            $db = new Db();
            $conn = $db->connect();
            $sql = "SELECT p.*, u.nama FROM pesanan p JOIN users u ON p.id_user = u.id_user ORDER BY p.tgl_pesanan DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->getBody()->write(json_encode($orders));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withStatus(500);
        }
    }

    public function getOrdersByDate(Request $request, Response $response, array $args) {
        $tanggal = $args['date']; 
        try {
            $db = new Db();
            $conn = $db->connect();
            $sql = "SELECT p.*, u.nama FROM pesanan p JOIN users u ON p.id_user = u.id_user 
                    WHERE DATE(p.tgl_pesanan) = :tgl ORDER BY p.tgl_pesanan DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':tgl' => $tanggal]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->getBody()->write(json_encode($orders));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withStatus(500);
        }
    }

    public function updateStatusSelesai(Request $request, Response $response, array $args) {
        $id_pesanan = $args['id'];
        try {
            $db = new Db();
            $conn = $db->connect();
            $sql = "UPDATE pesanan SET status_pesanan = 'selesai' WHERE id_pesanan = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id_pesanan]);
            $response->getBody()->write(json_encode(["message" => "Pesanan berhasil diselesaikan!"]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withStatus(500);
        }
    }

    public function getReports(Request $request, Response $response) {
        try {
            $db = new Db();
            $conn = $db->connect();
            
            $sqlHari = "SELECT SUM(total_harga) as total FROM pesanan 
                        WHERE DATE(tgl_pesanan) = CURDATE() 
                        AND status_pesanan NOT IN ('pending', 'dibatalkan')";
            $stmtHari = $conn->query($sqlHari);
            $hariIni = $stmtHari->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $sqlBulan = "SELECT SUM(total_harga) as total FROM pesanan 
                         WHERE MONTH(tgl_pesanan) = MONTH(CURDATE()) 
                         AND YEAR(tgl_pesanan) = YEAR(CURDATE()) 
                         AND status_pesanan NOT IN ('pending', 'dibatalkan')";
            $stmtBulan = $conn->query($sqlBulan);
            $bulanIni = $stmtBulan->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            $response->getBody()->write(json_encode(["harian" => (float)$hariIni, "bulanan" => (float)$bulanIni]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            return $response->withStatus(500);
        }
    }
}