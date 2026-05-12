<?php
namespace App\Controllers;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Db;
use PDO;

class AdminController {
    protected $view;

    public function __construct(Twig $view) {
        $this->view = $view;
    }

    public function index(Request $request, Response $response) {
        $db = new Db();
        $conn = $db->connect();
        
        $stats = [
            'total_menu' => $conn->query("SELECT COUNT(*) FROM menu")->fetchColumn(),
            'total_order' => $conn->query("SELECT COUNT(*) FROM pesanan")->fetchColumn(),
            'total_pendapatan' => $conn->query("SELECT SUM(total_harga) FROM pesanan WHERE status_pesanan != 'pending'")->fetchColumn() ?? 0,
            'testimoni_pending' => $conn->query("SELECT COUNT(*) FROM testimoni WHERE status_persetujuan = 'pending'")->fetchColumn()
        ];

        $recentOrders = $conn->query("SELECT * FROM pesanan ORDER BY tgl_pesanan DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

        return $this->view->render($response, 'admin/dashboard.html', [
            'stats' => $stats,
            'orders' => $recentOrders
        ]);
    }

    public function listMenu(Request $request, Response $response) {
        $db = new Db();
        $conn = $db->connect();
        $menus = $conn->query("SELECT * FROM menu ORDER BY kategori ASC")->fetchAll(PDO::FETCH_ASSOC);
        return $this->view->render($response, 'admin/menu.html', ['menus' => $menus]);
    }

    public function listTestimoni(Request $request, Response $response) {
        $db = new Db();
        $conn = $db->connect();
        $testimonis = $conn->query("SELECT * FROM testimoni ORDER BY status_persetujuan DESC")->fetchAll(PDO::FETCH_ASSOC);
        return $this->view->render($response, 'admin/testimoni.html', ['testimonis' => $testimonis]);
    }

    public function updateStatus(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $db = new Db();
        $conn = $db->connect();
        $sql = "UPDATE pesanan SET status_pesanan = :status WHERE id_pesanan = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':status' => $data['status'], ':id' => $data['id']]);
        
        return $response->withHeader('Location', '/admin/dashboard')->withStatus(302);
    }
}