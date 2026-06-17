<h3 class="fw-bold mb-4">Daftar Transaksi</h3>
<div class="table-container">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>ID Pesanan</th>
                <th>Pelanggan</th>
                <th>Total</th>
                <th>Status</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody id="table-orders-body"></tbody>
    </table>
</div>

<div class="modal fade" id="modalDetailPesanan">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header fw-bold">Detail Pesanan Pelanggan</div>
            <div class="modal-body">
                <div id="detail-info" class="alert alert-secondary py-2 mb-3"></div>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr><th>Menu</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr>
                    </thead>
                    <tbody id="detail-items"></tbody>
                </table>
                <h4 class="text-end fw-bold mt-3 text-success">Total: <span id="detail-total">Rp 0</span></h4>
            </div>
        </div>
    </div>
</div>

<script>
    async function loadOrders() {
    try {
        const orders = await fetch(`${BASE_URL}/admin/orders`, { headers }).then(r=>r.json());
        document.getElementById("table-orders-body").innerHTML = orders.map(o => `
            <tr>
                <td><strong>ORSO-${o.id_pesanan}</strong></td>
                <td>${o.nama}</td>
                <td>Rp ${parseInt(o.total_harga).toLocaleString()}</td>
                <td><span class="badge ${o.status_pesanan==='selesai'?'bg-success':'bg-warning text-dark'}">${o.status_pesanan}</span></td>
                <td>
                    <button onclick="showDetail(${o.id_pesanan})" class="btn btn-sm btn-info text-white">Detail</button>
                    ${o.status_pesanan!=='selesai' && o.status_pesanan!=='dibatalkan'
                    ? `<button onclick="updateStatus(${o.id_pesanan})" class="btn btn-sm btn-outline-success">Selesai</button>`
                    : '-'}
                </td>
            </tr>`).join('');
    } catch (e) { console.error("Error Orders:", e); }
    }

    async function updateStatus(id){
        await fetch(`${BASE_URL}/admin/orders/complete/${id}`, {method:"PUT", headers});
        loadOrders();
    }

    async function showDetail(id){
        const data = await fetch(`${BASE_URL}/orders/detail/${id}`).then(r=>r.json());
        document.getElementById("detail-info").innerHTML = `ID Transaksi: <strong>ORSO-${id}</strong>`;
        document.getElementById("detail-items").innerHTML = data.map(i => `
            <tr>
                <td>${i.nama_menu}</td>
                <td>Rp ${parseInt(i.harga).toLocaleString()}</td>
                <td>${i.jumlah_pesanan}</td>
                <td>Rp ${(i.harga*i.jumlah_pesanan).toLocaleString()}</td>
            </tr>`).join('');
        const total = data.reduce((t,i)=>t+(i.harga*i.jumlah_pesanan),0);
        document.getElementById("detail-total").innerText = `Rp ${total.toLocaleString()}`;
        new bootstrap.Modal(document.getElementById("modalDetailPesanan")).show();
    }

</script>