<div class="mb-4">
    <h3 class="fw-bold">Laporan Penjualan Perbulan</h3>
    <small class="text-muted">Total pendapatan yang berstatus 'Selesai'.</small>
</div>

<table class="table table-hover align-middle">
    <thead>
        <tr>
            <th>Bulan & Tahun</th>
            <th>Jumlah Transaksi</th>
            <th>Total Pendapatan</th>
        </tr>
    </thead>
    <tbody id="table-laporan-bulanan-body"></tbody>
</table>

<script>
    async function loadLaporan() {
    try {
        const rep = await fetch(`${BASE_URL}/admin/reports`, { headers }).then(r=>r.json());
        document.getElementById("rep-hari").innerText = `Rp ${parseInt(rep.harian||0).toLocaleString()}`;
        document.getElementById("rep-bulan").innerText = `Rp ${parseInt(rep.bulanan||0).toLocaleString()}`;

        const menus = await fetch(`${BASE_URL}/menu`).then(r=>r.json());
        document.getElementById("stat-menu").innerText = menus.length;

        const orders = await fetch(`${BASE_URL}/admin/orders`, { headers }).then(r=>r.json());
        document.getElementById("stat-pesanan").innerText = orders.length;

        const testimonials = await fetch(`${BASE_URL}/owner/testimoni`, { headers }).then(r=>r.json());
        const pending = testimonials.filter(t => t.status_persetujuan === 'pending').length;
        document.getElementById("stat-testimoni").innerText = pending;
    } catch (e) { console.error("Error Dashboard:", e); }
    }   

    async function loadLaporanBulanan() {
        try {
            const res = await fetch(`${BASE_URL}/admin/orders`, { headers });
            const orders = await res.json();
        
            const ordersSelesai = orders.filter(o => o.status_pesanan === 'selesai');

            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni",
                                "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

            const grouping = ordersSelesai.reduce((acc, order) => {
                const tglDb = order.tgl_pesanan; 
            
                if (tglDb) {
                    const date = new Date(tglDb);
                    const bulan = monthNames[date.getMonth()];
                    const tahun = date.getFullYear();
                    const key = `${bulan} ${tahun}`;
                
                    if (!acc[key]) {
                        acc[key] = { total: 0, count: 0 };
                    }
                    acc[key].total += parseFloat(order.total_harga);
                    acc[key].count += 1;
                }
                return acc;
            }, {});

            const tbody = document.getElementById("table-laporan-bulanan-body");
            const keys = Object.keys(grouping);

            if (keys.length === 0) {
                tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-muted">Tidak ada data penjualan yang berstatus 'selesai'.</td></tr>`;
                return;
            }

            tbody.innerHTML = keys.map(key => `
                <tr>
                    <td><span class="fw-bold">${key}</span></td>
                    <td>${grouping[key].count} Transaksi</td>
                    <td><span class="text-success fw-bold">Rp ${grouping[key].total.toLocaleString('id-ID')}</span></td>
                </tr>
            `).join('');

        } catch (e) {
            console.error("Gagal memuat laporan:", e);
        }
    }
</script>