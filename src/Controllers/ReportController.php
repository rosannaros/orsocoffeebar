<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Db;
use PDO;
use PDOException;

class ReportController {
    public function dailyReport(Request $request, Response $response) {
        try {
            $db = new Db();
            $conn = $db->connect();
            
            $sql = "SELECT SUM(total_harga) as total_harian, COUNT(id_pesanan) as jumlah_transaksi 
                    FROM pesanan 
                    WHERE DATE(tgl_pesanan) = CURDATE() AND status_pesanan = 'selesai'";
            $stmt = $conn->query($sql);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode([
                "tanggal" => date('Y-m-d'),
                "data" => $report
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withStatus(500);
        }
    }

    public function monthlyReport(Request $request, Response $response) {
        try {
            $db = new Db();
            $conn = $db->connect();
            
            $sql = "SELECT SUM(total_harga) as total_bulanan, COUNT(id_pesanan) as jumlah_transaksi 
                    FROM pesanan 
                    WHERE MONTH(tgl_pesanan) = MONTH(CURDATE()) 
                    AND YEAR(tgl_pesanan) = YEAR(CURDATE()) 
                    AND status_pesanan = 'selesai'";
            $stmt = $conn->query($sql);
            $report = $stmt->fetch(PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode([
                "bulan" => date('F Y'),
                "data" => $report
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => $e->getMessage()]));
            return $response->withStatus(500);
        }
    }
}