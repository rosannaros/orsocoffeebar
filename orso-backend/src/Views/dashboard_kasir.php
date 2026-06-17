<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kasir') {
    header("Location: login.php");
    exit();
}

$nama_kasir = $_SESSION['nama'] ?? 'Kasir';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cashier Dashboard - Orso Coffee</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
body {
    background: #f5f1eb;
    font-family: 'Poppins', sans-serif;
}

.navbar-kasir {
    background: linear-gradient(90deg, #4b3621, #6f4e37);
}

.navbar-brand {
    letter-spacing: 1px;
}

.card-order {
    border: none;
    border-radius: 18px;
    padding: 20px;
    transition: 0.3s;
    box-shadow: 0 8px 25px rgba(0,0,0,0.05);
}

.card-order:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.1);
}

.table-container {
    background: white;
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.table thead {
    background: #6f4e37;
    color: white;
}

.table-hover tbody tr:hover {
    background-color: #f3ede6;
}

.status-badge {
    padding: 7px 14px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
}

.btn-dark {
    background: #4b3621;
    border: none;
}
.btn-dark:hover {
    background: #6f4e37;
}

.btn-success {
    background: #2e7d32;
    border: none;
}

.modal-content {
    border-radius: 18px;
}
</style>
</head>
<body>

<nav class="navbar navbar-kasir shadow-sm mb-4">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold text-white">☕ ORSO KASIR</span>
        <div class="d-flex align-items-center">
            <span class="me-3 badge bg-light text-dark">
                Petugas: <?php echo htmlspecialchars($nama_kasir); ?>
            </span>
            <span class="me-3 text-white small" id="clock"></span>
            <a href="/logout" class="btn btn-outline-light btn-sm" onclick="return confirm('Selesaikan shift dan keluar?')">
                Logout
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card-order border-start border-warning border-4">
                <small class="text-muted fw-semibold">Pesanan Masuk</small>
                <h2 id="count-pending" class="fw-bold text-warning m-0">0</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-order border-start border-success border-4">
                <small class="text-muted fw-semibold">Pesanan Selesai</small>
                <h2 id="count-selesai" class="fw-bold text-success m-0">0</h2>
            </div>
        </div>
        <div class="col-md-4 d-flex align-items-stretch">
            <button class="btn btn-dark w-100 fw-bold" onclick="loadOrders()">
                🔄 Refresh Data
            </button>
        </div>
    </div>

    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0">Antrean Pesanan</h4>

            <div class="btn-group">
                <input type="radio" class="btn-check" name="filterStatus" id="filter1" checked onclick="filterTable('pending')">
                <label class="btn btn-outline-warning" for="filter1">Belum Dibayar</label>

                <input type="radio" class="btn-check" name="filterStatus" id="filterProses" onclick="filterTable('diproses')">
                <label class="btn btn-outline-primary" for="filterProses">Sedang Dibuat</label>

                <input type="radio" class="btn-check" name="filterStatus" id="filter2" onclick="filterTable('selesai')">
                <label class="btn btn-outline-success" for="filter2">Riwayat Selesai</label>
                
                <input type="radio" class="btn-check" name="filterStatus" id="filterCancel" onclick="filterTable('dibatalkan')">
                <label class="btn btn-outline-danger" for="filterCancel">Dibatalkan</label>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-orders-kasir"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetailKasir">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header fw-bold">
                Detail Pesanan
            </div>
            <div class="modal-body">
                <div id="detail-info-kasir" class="mb-3 small"></div>
                <ul class="list-group list-group-flush" id="detail-items-kasir"></ul>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span id="detail-total-kasir">Rp 0</span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary w-100" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = "http://127.0.0.1:8000";
const headers = { "X-Role": "kasir" };
let allOrders = [];

function updateClock() {
    const now = new Date();
    document.getElementById('clock').innerText = now.toLocaleString('id-ID');
}
setInterval(updateClock, 1000);

function formatOrderTime(datetime) {
    if (!datetime) return "-";

    const date = new Date(datetime);

    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

async function loadOrders() {
    try {
        const res = await fetch(`${BASE_URL}/admin/orders`, { headers });
        allOrders = await res.json();

        document.getElementById('count-pending').innerText =
            allOrders.filter(o => o.status_pesanan === 'pending').length;

        document.getElementById('count-selesai').innerText =
            allOrders.filter(o => o.status_pesanan === 'selesai').length;

        const isSelesaiActive = document.getElementById('filter2').checked;
        renderTable(isSelesaiActive ? 'selesai' : 'pending');
    } catch (e) {
        console.error("Gagal memuat pesanan", e);
    }
}

function renderTable(status) {
    const filtered = allOrders.filter(o => o.status_pesanan === status);
    const tbody = document.getElementById('table-orders-kasir');

    tbody.innerHTML = filtered.length ? filtered.map(o => {

        let badgeClass = '';
        let badgeText = '';
        let actionButton = '';

        if (o.status_pesanan === 'pending') {
            badgeClass = 'bg-warning text-dark';
            badgeText = 'BELUM BAYAR';

            actionButton = `
                <button class="btn btn-sm btn-outline-secondary" disabled>
                    Menunggu
                </button>`;
        }

        else if (o.status_pesanan === 'diproses') {
            badgeClass = 'bg-primary text-white';
            badgeText = 'SEDANG DIBUAT';

            actionButton = `
                <button class="btn btn-sm btn-success"
                    onclick="completeOrder(${o.id_pesanan})">
                    Selesai
                </button>`;
        }

        else if (o.status_pesanan === 'selesai') {
            badgeClass = 'bg-success text-white';
            badgeText = 'SELESAI';

            actionButton = `
                <button class="btn btn-sm btn-outline-secondary" disabled>
                    Selesai
                </button>`;
        }

        else if (o.status_pesanan === 'dibatalkan') {
            badgeClass = 'bg-danger text-white';
            badgeText = 'DIBATALKAN';

            actionButton = `
                <button class="btn btn-sm btn-outline-secondary" disabled>
                    Dibatalkan
                </button>`;
        }

        return `
        <tr>
            <td><strong>ORSO-${o.id_pesanan}</strong></td>
            <td>${formatOrderTime(o.tgl_pesanan)}</td>
            <td>${o.nama}</td>
            <td>Rp ${parseInt(o.total_harga).toLocaleString()}</td>
            <td>
                <span class="status-badge ${badgeClass}">
                    ${badgeText}
                </span>
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-dark me-1"
                    onclick="showDetail(${o.id_pesanan})">
                    Detail
                </button>
                ${actionButton}
            </td>
        </tr>`;
    }).join('')
    :
    `<tr>
        <td colspan="6" class="text-center py-4 text-muted">
            Tidak ada pesanan ${status}
        </td>
    </tr>`;
}

function filterTable(status) {
    renderTable(status);
}

async function completeOrder(id) {
    if(confirm('Tandai pesanan ini sudah selesai & dibayar?')) {
        await fetch(`${BASE_URL}/admin/orders/complete/${id}`, {
            method: "PUT",
            headers
        });
        loadOrders();
    }
}

async function showDetail(id) {
    const data = await fetch(`${BASE_URL}/orders/detail/${id}`)
        .then(r => r.json());

    document.getElementById("detail-info-kasir").innerHTML =
        `ID Pesanan: <strong>ORSO-${id}</strong>`;

    document.getElementById("detail-items-kasir").innerHTML = data.map(i => `
        <li class="list-group-item d-flex justify-content-between align-items-center">
            ${i.nama_menu}
            <span class="badge bg-primary rounded-pill">
                x${i.jumlah_pesanan}
            </span>
        </li>
    `).join('');

    const total = data.reduce((t, i) =>
        t + (i.harga * i.jumlah_pesanan), 0);

    document.getElementById("detail-total-kasir").innerText =
        `Rp ${total.toLocaleString()}`;

    new bootstrap.Modal(
        document.getElementById("modalDetailKasir")
    ).show();
}

setInterval(loadOrders, 30000);
window.onload = loadOrders;
</script>

</body>
</html>