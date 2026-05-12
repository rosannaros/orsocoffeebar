<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pemilik') {
    header("Location: login.php");
    exit();
}

$nama_admin = $_SESSION['nama'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Owner Dashboard - Orso Coffee</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
body {
    background: #f8f5f0;
    font-family: 'Poppins', sans-serif;
}

.sidebar {
    background: linear-gradient(180deg, #3e2c1c, #2b1d12);
    min-height: 100vh;
    color: white;
    position: fixed;
    width: 260px;
    padding: 25px 20px;
    box-shadow: 5px 0 20px rgba(0,0,0,0.1);
}

.sidebar .nav-link {
    color: #ddd;
    border-radius: 12px;
    padding: 10px 15px;
    margin-bottom: 8px;
    transition: 0.3s;
    font-weight: 500;
    border: none;
    background: none;
    text-align: left;
}

.sidebar .nav-link:hover {
    background: rgba(255,255,255,0.08);
    color: white;
    transform: translateX(5px);
}

.sidebar .nav-link.active {
    background: #6f4e37 !important;
    color: white !important;
}

.main-content {
    margin-left: 260px;
    padding: 35px;
}

.content-box {
    background: white;
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.img-preview {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}
</style>
</head>

<body>

<div class="sidebar text-center">
    <h4 class="fw-bold mb-1 mt-2">ORSO OWNER</h4>
    <p class="small text-warning mb-4">
        Halo, <?php echo htmlspecialchars($nama_admin); ?> ☕
    </p>

    <hr>

    <div class="nav flex-column nav-pills" id="menuSidebar">

        <button class="nav-link active" onclick="setActive(this); loadPage('/dashboard/summary')">
            📊 Dashboard
        </button>

        <button class="nav-link" onclick="setActive(this); loadPage('/manage/pesanan')">
            🛒 Pesanan
        </button>

        <button class="nav-link" onclick="setActive(this); loadPage('/manage/menu')">
            ☕ Menu
        </button>

        <button class="nav-link" onclick="setActive(this); loadPage('/manage/user')">
            👥 User
        </button>

        <button class="nav-link" onclick="setActive(this); loadPage('/manage/testimoni')">
            💬 Ulasan
        </button>

        <button class="nav-link" onclick="setActive(this); loadPage('/report/penjualan')">
            📜 Laporan
        </button>

        <a href="/logout" class="nav-link text-danger mt-5">
            🚪 Logout
        </a>

    </div>
</div>

<div class="main-content">
    <div id="content-area" class="content-box">
        <div class="text-center text-muted">Loading...</div>
    </div>
</div>

<script>
const BASE_URL = "http://127.0.0.1:8000";

const headers = {
    "Content-Type": "application/json",
    "X-Role": "pemilik"
};

async function loadPage(url) {
    try {
        const res = await fetch(BASE_URL + url);
        const html = await res.text();

        document.getElementById("content-area").innerHTML = html;

        const scripts = document.getElementById("content-area").querySelectorAll("script");
        scripts.forEach(oldScript => {
            const newScript = document.createElement("script");
            newScript.text = oldScript.text;
            document.body.appendChild(newScript);
        });

        setTimeout(() => {
            if (url.includes("summary") && typeof loadLaporan === "function") loadLaporan();
            if (url.includes("pesanan") && typeof loadOrders === "function") loadOrders();
            if (url.includes("menu") && typeof loadMenu === "function") loadMenu();
            if (url.includes("user") && typeof loadUsers === "function") loadUsers();
            if (url.includes("testimoni") && typeof loadTestimoni === "function") loadTestimoni();
            if (url.includes("report") && typeof loadLaporanBulanan === "function") loadLaporanBulanan();
        }, 100);

    } catch (err) {
        document.getElementById("content-area").innerHTML =
            "<div class='text-danger'>Gagal load halaman</div>";
        console.error(err);
    }
}

function setActive(btn) {
    document.querySelectorAll('#menuSidebar .nav-link')
        .forEach(el => el.classList.remove('active'));

    btn.classList.add('active');
}

window.onload = function() {
    loadPage('/dashboard/summary');
};
</script>

</body>
</html>