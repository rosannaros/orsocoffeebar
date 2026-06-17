<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold">Manajemen User</h3>
    <button class="btn btn-dark px-4 rounded-pill" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
        + Tambah User
    </button>
</div>

<div class="table-container">
    <table class="table table-hover align-middle">
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th class="text-center">Aksi</th>
            </tr>
        <tbody id="table-user-body">
            </tbody>
    </table>
</div>

<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="formTambahUser" onsubmit="saveUser(event)">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-4">
                <div class="mb-3">
                    <label class="small fw-bold text-muted">NAMA LENGKAP</label>
                    <input name="nama" class="form-control bg-light" placeholder="Masukkan nama" required>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold text-muted">EMAIL</label>
                    <input name="email" type="email" class="form-control bg-light" placeholder="email@contoh.com" required>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold text-muted">PASSWORD</label>
                    <input name="password" type="password" class="form-control bg-light" placeholder="Min. 6 karakter" required>
                </div>
                <div class="mb-0">
                    <label class="small fw-bold text-muted">ROLE / HAK AKSES</label>
                    <select name="role" class="form-select bg-light">
                        <option value="kasir">Kasir</option>
                        <option value="pemilik">Pemilik (Owner)</option>
                        <option value="pelanggan">Pelanggan</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success px-4 fw-bold">SIMPAN USER</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="formEditUser" onsubmit="updateUser(event)">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Edit Data Pengguna</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-4">
                <input type="hidden" id="edit-user-id" name="id_user">
                <div class="mb-3">
                    <label class="small fw-bold text-muted">NAMA LENGKAP</label>
                    <input id="edit-user-nama" name="nama" class="form-control bg-light" required>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold text-muted">EMAIL</label>
                    <input id="edit-user-email" name="email" type="email" class="form-control bg-light" required>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold text-muted">GANTI PASSWORD (OPSIONAL)</label>
                    <input name="password" type="password" class="form-control bg-light" placeholder="Kosongkan jika tidak ganti">
                </div>
                <div class="mb-0">
                    <label class="small fw-bold text-muted">ROLE / HAK AKSES</label>
                    <select id="edit-user-role" name="role" class="form-select bg-light">
                        <option value="kasir">Kasir</option>
                        <option value="pemilik">Pemilik (Owner)</option>
                        <option value="pelanggan">Pelanggan</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold">UPDATE DATA</button>
            </div>
        </form>
    </div>
</div>

<script>

async function loadUsers() {
    const tbody = document.getElementById("table-user-body");
    try {
        const res = await fetch(`${BASE_URL}/users`, { headers });
        const data = await res.json();

        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted">Belum ada data user.</td></tr>`;
            return;
        }

        tbody.innerHTML = data.map(u => `
            <tr>
                <td><strong>${u.nama}</strong></td>
                <td>${u.email}</td>
                <td><span class="badge ${u.role === 'pemilik' ? 'bg-danger' : 'bg-secondary'}">${u.role}</span></td>
                <td class="text-center">
                    <button onclick='openEditUserModal(${JSON.stringify(u)})' class="btn btn-sm btn-outline-primary border-0">
                        Edit
                    </button>
                    <button onclick="deleteUser(${u.id_user})" class="btn btn-sm btn-outline-danger border-0">
                        Hapus
                    </button>
                </td>
            </tr>`).join('');
    } catch (e) {
        console.error("Gagal memuat user:", e);
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">Error koneksi server.</td></tr>`;
    }
}

async function saveUser(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    const payload = Object.fromEntries(fd.entries());

    try {
        const res = await fetch(`${BASE_URL}/users/register`, { 
            method: "POST", 
            headers: { ...headers, "Content-Type": "application/json" },
            body: JSON.stringify(payload) 
        });

        if (res.ok) {
            alert("User baru berhasil ditambahkan!");
            bootstrap.Modal.getInstance(document.getElementById('modalTambahUser')).hide();
            e.target.reset();
            loadUsers();
        } else {
            const err = await res.json();
            alert("Gagal: " + (err.error || "Terjadi kesalahan"));
        }
    } catch (e) { console.error(e); }
}

function openEditUserModal(user) {
    document.getElementById('edit-user-id').value = user.id_user;
    document.getElementById('edit-user-nama').value = user.nama;
    document.getElementById('edit-user-email').value = user.email;
    document.getElementById('edit-user-role').value = user.role;
    new bootstrap.Modal(document.getElementById('modalEditUser')).show();
}

async function updateUser(e) {
    e.preventDefault();
    const id = document.getElementById('edit-user-id').value;
    const fd = new FormData(e.target);
    const payload = Object.fromEntries(fd.entries());
    
    if(!payload.password) delete payload.password;

    try {
        const res = await fetch(`${BASE_URL}/users/${id}`, { 
            method: "PUT", 
            headers: { ...headers, "Content-Type": "application/json" },
            body: JSON.stringify(payload) 
        });

        if (res.ok) {
            alert("Data user berhasil diperbarui!");
            bootstrap.Modal.getInstance(document.getElementById('modalEditUser')).hide();
            loadUsers();
        } else {
            alert("Gagal memperbarui data user.");
        }
    } catch (e) { console.error(e); }
}

async function deleteUser(id) {
    if(confirm("Apakah Anda yakin ingin menghapus user ini?")) {
        try {
            const res = await fetch(`${BASE_URL}/users/${id}`, { method: "DELETE", headers });
            if (res.ok) {
                alert("User telah dihapus.");
                loadUsers();
            }
        } catch (e) { console.error(e); }
    }
}
</script>