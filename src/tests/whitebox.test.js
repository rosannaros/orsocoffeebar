/**
 * @vitest-environment jsdom
 */
import { describe, it, expect, vi } from 'vitest';

describe('Register', () => {
  it('Validasi Panjang Password', () => {
    // Arrange
    const state = { password: '123', error: '' };
    // Act
    if (state.password.length < 6) {
      state.error = "Password minimal 6 karakter.";
    }
    // Assert (Cek nilai variabelnya)
    expect(state.error).toEqual("Password minimal 6 karakter.");
  });

  it('registerUser', async () => {
    // Arrange
    const api = { 
      post: vi.fn().mockResolvedValue({ success: true })
    };
    const userData = { nama: 'User Test', email: 'test@gmail.com', password: 'password123' };
    // Act
    const response = await api.post('/users/register', userData);
    // Assert (Pola Mentor: Hasil data dulu, baru status pemanggilan)
    expect(response.success).toBe(true); // Cek hasil return data
    expect(api.post).toHaveBeenCalledWith('/users/register', userData); // Cek fungsi terpanggil
  });

  it('Navigasi Register Sukses', () => {
    // Arrange
    const $router = { push: vi.fn() };
    const response = { status: 201 };
    // Act
    if (response.status === 201) {
      $router.push("/login");
    }
    // Assert (Pola Mentor: Cek efek samping navigasi)
    expect($router.push).toHaveBeenCalledWith("/login");
  });

  it('Handling Email Duplikat', async () => {
    // Arrange: Simulasikan API yang gagal karena email duplikat
    const errorResponse = { error: "Email sudah terdaftar. Silakan gunakan email lain." };
    const api = {
      post: vi.fn().mockRejectedValue({ response: { data: errorResponse } })
    };
    const state = { error: "" };
    // Act: Simulasikan blok try-catch di handleRegister
    try {
      await api.post('/users/register', { email: 'user@gmail.com' });
    } catch (err) {
      // Inilah Logic Path yang kita uji (Catch Branch)
      state.error = err.response.data.error || "Gagal mendaftar.";
    }
    // Assert: Pastikan pesan error dari server masuk ke state UI
    expect(state.error).toBe("Email sudah terdaftar. Silakan gunakan email lain.");
    expect(api.post).toHaveBeenCalled();
  });
});

describe('Login', () => {
  it('loginUser', async () => {
    const api = {
      post: vi.fn().mockResolvedValue({ 
        status: 200, 
        user: { id: 1, nama: 'User Test' } 
      })
    };
    const loginData = { email: 'test@gmail.com', password: 'password123' };

    const response = await api.post('/auth/login', loginData);

    expect(response.status).toEqual(200);
    expect(api.post).toHaveBeenCalledWith('/auth/login', loginData);
  });

  it('Login Success Handling', () => {
    // Arrange
    const $router = { push: vi.fn() };
    const response = { status: 200, user: { nama: 'User Test' } };
    // Act
    if (response.status === 200) {
      localStorage.setItem("user", JSON.stringify(response.user));
      $router.push("/beranda");
    }
    // Assert
    expect(JSON.parse(localStorage.getItem("user")).nama).toEqual("User Test");
    expect($router.push).toHaveBeenCalledWith("/beranda");
  });
  
  it('Login Error Handling', () => {
    // Arrange
    const state = { error: '' }; 
    const err = { error: "Email atau password salah." };
    // Act (Logic Path: catch (err) { this.error = err.error || ... })
    state.error = err.error || "Email atau password salah.";
    // Assert
    expect(state.error).toEqual("Email atau password salah.");
  });

  it('Login Default Error', () => {
    // Arrange
    const state = { error: '' };
    const err = { error: "" }; // Ceritanya server tidak kirim pesan
    // Act
    state.error = err.error || "Gagal masuk ke akun.";
    // Assert
    expect(state.error).toEqual("Gagal masuk ke akun.");
  });
});

describe('Menu', () => {
  it('getMenu', async () => {
    // 1. Arrange: Simulasi api.js yang me-return [] saat error
    const getMenuMock = vi.fn().mockResolvedValue([]);
    // 2. Act
    const result = await getMenuMock();
    // 3. Assert
    expect(result).toEqual([]);
    expect(getMenuMock).toHaveBeenCalled();
  });

  it('getMenuImage External', () => {
    // 1. Arrange
    const externalPath = 'https://coffeeshop.example.com/latte.jpg';
    const getMenuImage = (img) => img.startsWith('http') ? img : `url/${img}`;
    // 2. Act
    const result = getMenuImage(externalPath);
    // 3. Assert
    expect(result).toEqual(externalPath);
  });

  it('getMenuImage Local', () => {
    // 1. Arrange
    const fileName = 'espresso.png';
    const BASE_URL = 'http://localhost:8000';
    const getMenuImage = (img) => `${BASE_URL}/uploads/menu/${img}`;
    // 2. Act
    const result = getMenuImage(fileName);
    // 3. Assert
    expect(result).toEqual(`${BASE_URL}/uploads/menu/espresso.png`);
  });

  it('Filtering Kategori', () => {
    // 1. Arrange
    const menuItems = [
      { id_menu: 1, nama_menu: 'Espresso', kategori: 'coffee' },
      { id_menu: 2, nama_menu: 'Lemon Tea', kategori: 'tea' }
    ];
    const selectedCategory = 'coffee';
    // 2. Act: Simulasi computed filteredMenu
    const filtered = menuItems.filter(item => 
      selectedCategory === 'all' || item.kategori === selectedCategory
    );
    // 3. Assert
    expect(filtered).toEqual([{ id_menu: 1, nama_menu: 'Espresso', kategori: 'coffee' }]);
    expect(filtered.length).toEqual(1);
  });

  it('Pencarian Menu', () => {
    // 1. Arrange
    const menuItems = [
      { id_menu: 1, nama_menu: 'Lime Mojito' },
      { id_menu: 2, nama_menu: 'Chocolate' }
    ];
    const searchQuery = 'Mojito';
    // 2. Act
    const result = menuItems.filter(item => 
      item.nama_menu.toLowerCase().includes(searchQuery.toLowerCase())
    );
    // 3. Assert
    expect(result[0].nama_menu).toEqual('Lime Mojito');
    expect(result.length).toEqual(1);
  });

  it('Sinkronisasi URL', () => {
    // 1. Arrange
    const watchRoute = vi.fn((newVal) => newVal || 'all');
    const queryParam = 'non-coffee';
    // 2. Act
    const result = watchRoute(queryParam);
    // 3. Assert
    expect(result).toEqual('non-coffee');
    expect(watchRoute).toHaveBeenCalledWith('non-coffee');
  });
});

describe('Keranjang', () => {
  it('Kalkulasi totalPrice', () => {
    // Arrange
    const items = {
      1: { id_menu: 1, price: 10000, qty: 2 },
      2: { id_menu: 2, price: 5000, qty: 3 }
    };
    // Act
    const total = Object.values(items).reduce((acc, item) => acc + item.qty * item.price, 0);
    // Assert
    expect(total).toEqual(35000);
  });

  it('addItem Action', () => {
    // Arrange
    const cart = { 1: { id_menu: 1, qty: 1 } };
    const id = 1;
    // Act (Logic Path: if (!this.items[id]) { ... } else { ... })
    if (cart[id]) {
      cart[id].qty += 1;
    }
    // Assert
    expect(cart[1].qty).toEqual(2);
  });
  
  it('removeItem Action', () => {
    // Arrange
    const cart = { 1: { id_menu: 1, qty: 1 } };
    const id = 1;
    // Act (Logic Path: if (qty > 1) { qty-- } else { delete })
    if (cart[id].qty > 1) {
      cart[id].qty--;
    } else {
      delete cart[id];
    }
    // Assert
    expect(cart[1]).toEqual(undefined);
  });

  it('Reactive Popup', () => {
    // Arrange
    const state = { showPopup: false, popupText: '' };
    const message = "Pesanan ditambahkan!";
    // Act
    state.popupText = message;
    state.showPopup = true;
    // Assert
    expect(state.showPopup).toEqual(true);
    expect(state.popupText).toEqual("Pesanan ditambahkan!");
  });

  it('persistCart Sync', () => {
    // Arrange
    const items = { 1: { id_menu: 1, qty: 2 } };
    // Act
    localStorage.setItem("cart", JSON.stringify(items));
    // Assert
    const result = JSON.parse(localStorage.getItem("cart"));
    expect(result).toEqual(items);
  });
});

describe('Pesanan', () => {
  it('createOrder', async () => {
    // 1. Arrange
    const api = {
      post: vi.fn().mockResolvedValue({ status: 201 })
    };
    const userRole = 'pelanggan';
    const orderData = { total: 50000, items: [{ id_menu: 1, qty: 2 }] };
    // 2. Act
    const response = await api.post('/orders', orderData, {
      headers: { 'X-Role': userRole }
    });
    // 3. Assert (Pola Mentor: toEqual dulu baru toHaveBeenCalled)
    expect(response.status).toEqual(201);
    expect(api.post).toHaveBeenCalledWith('/orders', orderData, {
      headers: { 'X-Role': 'pelanggan' }
    });
  });

  it('payWithMidtrans', () => {
    // 1. Arrange: Simulasi window.snap belum dimuat (undefined)
    delete window.snap; 
    let errorMessage = "";
    // 2. Act (Logic Path: if (!window.snap))
    if (!window.snap) {
      errorMessage = "Midtrans belum siap.";
    }
    // 3. Assert
    expect(errorMessage).toEqual("Midtrans belum siap.");
  });

  it('getActiveOrders', async () => {
    // 1. Arrange
    const api = {
      get: vi.fn().mockResolvedValue({ status: 200, data: [] })
    };
    const userId = '123';
    // 2. Act (Logic Path: /orders/user/${userId})
    const response = await api.get(`/orders/user/${userId}`);
    // 3. Assert
    expect(response.status).toEqual(200);
    expect(api.get).toHaveBeenCalledWith('/orders/user/123');
  });
  
  it('Filter activeOrders', () => {
    // 1. Arrange: Data bervariasi untuk menguji filter
    const allOrders = [
      { id: 1, status: 'pending' },   // Harus masuk
      { id: 2, status: 'diproses' },  // Harus masuk
      { id: 3, status: 'selesai' }    // Harus terbuang
    ];
    // 2. Act: Logika filter status aktif
    const activeOrders = allOrders.filter(o => 
      o.status === 'pending' || o.status === 'diproses'
    );
    // 3. Assert (Verifikasi hasil filter)
    expect(activeOrders.length).toEqual(2);
    expect(activeOrders[0].status).toEqual('pending');
    expect(activeOrders[1].status).toEqual('diproses');
    // Memastikan status 'selesai' tidak ada di hasil filter
    const hasFinishedOrder = activeOrders.some(o => o.status === 'selesai');
    expect(hasFinishedOrder).toEqual(false);
  });
});

describe('Riwayat', () => {
  it('getOrderDetail', async () => {
    // 1. Arrange
    const api = {
      get: vi.fn().mockResolvedValue({ status: 200, data: { id: 50 } })
    };
    const orderId = 50;
    // 2. Act (Logic Path: /orders/detail/${orderId})
    const response = await api.get(`/orders/detail/${orderId}`);
    // 3. Assert (Pola Mentor: Data dulu baru fungsi)
    expect(response.status).toEqual(200);
    expect(api.get).toHaveBeenCalledWith('/orders/detail/50');
  });

  it('Sorting sortOrders', () => {
    // 1. Arrange: Data acak (ID kecil duluan)
    const detailed = [
      { id_pesanan: 1, date: '2024-01-01' },
      { id_pesanan: 3, date: '2024-01-03' },
      { id_pesanan: 2, date: '2024-01-02' }
    ];
    // 2. Act (Logic Path: b.id_pesanan - a.id_pesanan)
    const sortedOrders = detailed.sort((a, b) => b.id_pesanan - a.id_pesanan);
    // 3. Assert (Verifikasi ID paling besar ada di urutan pertama)
    expect(sortedOrders[0].id_pesanan).toEqual(3);
    expect(sortedOrders[1].id_pesanan).toEqual(2);
    expect(sortedOrders[2].id_pesanan).toEqual(1);
  });

  it('Loop reorderItem', () => {
    // 1. Arrange: Mocking cartStore dan data item lama
    const cartStore = {
      addItem: vi.fn()
    };
    const items = [
      { id_menu: 1, nama: 'Espresso', price: 20000 },
      { id_menu: 2, nama: 'Latte', price: 25000 }
    ];
    // 2. Act (Logic Path: items.forEach)
    items.forEach(item => {
      cartStore.addItem({
        id: item.id_menu,
        name: item.nama,
        price: item.price,
        qty: 1
      });
    });
    // 3. Assert
    // Cek apakah addItem dipanggil sebanyak jumlah item (2 kali)
    expect(cartStore.addItem).toHaveBeenCalledTimes(2);
    // Verifikasi item pertama yang dimasukkan
    expect(cartStore.addItem).toHaveBeenNthCalledWith(1, {
      id: 1,
      name: 'Espresso',
      price: 20000,
      qty: 1
    });
  });
});

describe('Testimoni', () => {
  it('getTestimoni', async () => {
    // 1. Arrange
    const mockData = [
      { id: 1, nama: 'Budi', ulasan: 'Kopinya enak!' },
      { id: 2, nama: 'Santi', ulasan: 'Tempatnya nyaman.' }
    ];
    const getTestimoni = vi.fn().mockResolvedValue(mockData);
    // 2. Act
    const data = await getTestimoni();
    // 3. Assert (Pola Mentor: toEqual dulu baru toHaveBeenCalled)
    expect(data).toEqual(mockData);
    expect(data.length).toEqual(2);
    expect(getTestimoni).toHaveBeenCalled();
  });

  it('handleImageError', () => {
    // 1. Arrange
    const placeholder = '/assets/img/default-avatar.png';
    // Simulasi objek event (e.target.src)
    const event = {
      target: {
        src: 'gambar-rusak.jpg'
      }
    };
    // 2. Act (Logic Path: e.target.src = placeholder)
    if (event.target.src.includes('rusak')) {
      event.target.src = placeholder;
    }
    // 3. Assert
    expect(event.target.src).toEqual(placeholder);
  });

  it('Auth Testimoni Success', () => {
    // 1. Arrange
    const userData = { id: 1, nama: 'User Login' };
    const $router = { push: vi.fn() };
    // 2. Act (Logic Path: if (!userData) else { push })
    if (!userData) {
      // Logic untuk tampilkan login modal/alert (tidak dijalankan di tes ini)
    } else {
      $router.push('/tulistestimoni');
    }
    // 3. Assert
    expect($router.push).toHaveBeenCalledWith('/tulistestimoni');
  });

  it('Auth Testimoni Block', () => {
    // 1. Arrange
    const userData = null;
    const $router = { push: vi.fn() };
    // 2. Act
    if (!userData) {
      // Simulasi logika alert("Login dulu")
    } else {
      $router.push('/tulistestimoni');
    }
    // 3. Assert
    expect($router.push).not.toHaveBeenCalled();
  });
});

describe('TulisTestimoni', () => {
  it('Preview Image', () => {
    // 1. Arrange
    const state = { preview: null };
    const mockEvent = { target: { result: 'data:image/png;base64,xyz' } };
    // 2. Act: Simulasi logic di dalam reader.onload
    const readerOnLoad = (e) => { state.preview = e.target.result; };
    readerOnLoad(mockEvent);
    // 3. Assert
    expect(state.preview).toEqual('data:image/png;base64,xyz');
  });

  it('postTestimoni', async () => {
    // 1. Arrange
    const api = { post: vi.fn().mockResolvedValue({ status: 200 }) };
    const mockFile = new File([''], 'test.jpg', { type: 'image/jpeg' });
    const formData = new FormData();
    // 2. Act
    formData.append('isi_testimoni', 'Kopi mantap!');
    formData.append('foto_testimoni', mockFile);
    const response = await api.post('/testimoni', formData);
    // 3. Assert (Pola Mentor: toEqual dulu baru toHaveBeenCalled)
    expect(response.status).toEqual(200);
    expect(formData.get('isi_testimoni')).toEqual('Kopi mantap!');
    expect(formData.has('foto_testimoni')).toEqual(true);
    expect(api.post).toHaveBeenCalledWith('/testimoni', formData);
  });

  it('Handling Empty Image', () => {
    // 1. Arrange
    const state = { foto: null };
    const formData = new FormData();
    // 2. Act (Logic Path: if (this.foto))
    if (state.foto) {
      formData.append('foto_testimoni', state.foto);
    }
    // 3. Assert
    expect(formData.has('foto_testimoni')).toEqual(false);
    expect(formData.get('foto_testimoni')).toEqual(null);
  });

  it('Reset Form Testimoni', () => {
    // 1. Arrange
    const state = { foto: { name: 'img.jpg' }, preview: 'blob:url' };
    // 2. Act (Logic Path: removeFile())
    state.foto = null;
    state.preview = null;
    // 3. Assert
    expect(state.foto).toEqual(null);
    expect(state.preview).toEqual(null);
  });

  it('Character Limit', () => {
    // 1. Arrange
    const testimoniA = "Singkat saja"; // 12 karakter
    const testimoniB = "A".repeat(100); // 100 karakter
    // 2. Act
    const isReachedA = testimoniA.length >= 100;
    const isReachedB = testimoniB.length >= 100;
    // 3. Assert
    expect(isReachedA).toEqual(false);
    expect(isReachedB).toEqual(true);
  });
});

describe('EditProfil', () => {
  it('updateUser', () => {
    // 1. Arrange
    const userId = "ID-12345"; // Input kotor
    // 2. Act (Logic Path: .replace(/\D/g, ""))
    const cleanId = userId.toString().replace(/\D/g, "");
    // 3. Assert
    expect(cleanId).toEqual("12345");
    expect(cleanId).not.toContain("ID-");
  });

  it('checkDataChange', () => {
    // 1. Arrange: Data lama sama dengan data baru
    const oldData = { nama: 'Andi', email: 'andi@test.com' };
    const newData = { nama: 'Andi', email: 'andi@test.com' };
    const passwordInput = "";
      
    let isUpdateProceed = true;
    // 2. Act (Logic Path: if (isNamaSama && isEmailSama && isPasswordKosong))
    const isNamaSama = newData.nama === oldData.nama;
    const isEmailSama = newData.email === oldData.email;
    const isPasswordKosong = passwordInput === "";

    if (isNamaSama && isEmailSama && isPasswordKosong) {
      isUpdateProceed = false;
    }
    // 3. Assert
    expect(isUpdateProceed).toEqual(false);
  });

  it('Update Local Sync', () => {
    // 1. Arrange
    const spy = vi.spyOn(Storage.prototype, 'setItem');
    const updatedUserData = { id: 1, nama: 'Budi Updated', email: 'budi@test.com' };
    // 2. Act
    localStorage.setItem("user", JSON.stringify(updatedUserData));
    // 3. Assert (Pola Mentor: toEqual dulu baru toHaveBeenCalled)
    const storedData = JSON.parse(localStorage.getItem("user"));
    expect(storedData.nama).toEqual('Budi Updated');
    expect(spy).toHaveBeenCalledWith("user", JSON.stringify(updatedUserData));
  });
 
  it('Password Toggle', () => {
    // 1. Arrange
    let showPassword = false;
    let inputType = 'password';
    // 2. Act: Simulasi klik icon mata (Toggle)
    showPassword = !showPassword;
    inputType = showPassword ? 'text' : 'password';
    // 3. Assert
    expect(showPassword).toEqual(true);
    expect(inputType).toEqual('text');
    // Act: Klik lagi untuk sembunyikan
    showPassword = !showPassword;
    inputType = showPassword ? 'text' : 'password';
      
    expect(inputType).toEqual('password');
  });
});

describe('ProfildanAuth', () => {
  it('handleLogout', () => {
    // 1. Arrange
    localStorage.setItem("user", JSON.stringify({ name: "Budi" }));
    const spy = vi.spyOn(Storage.prototype, 'removeItem');
    // 2. Act
    localStorage.removeItem("user");
    // 3. Assert (Pola Mentor: toEqual dulu baru toHaveBeenCalled)
    expect(localStorage.getItem("user")).toEqual(null);
    expect(spy).toHaveBeenCalledWith("user");
  });

  it('Auth Event', () => {
    // 1. Arrange
    const spy = vi.spyOn(window, 'dispatchEvent');
    // 2. Act
    const authEvent = new Event("auth-change");
    window.dispatchEvent(authEvent);
    // 3. Assert
    expect(authEvent.type).toEqual("auth-change");
    expect(spy).toHaveBeenCalledWith(expect.any(Event));
      
    vi.restoreAllMocks();
  });

  it('Otorisasi Rute', () => {
    // 1. Arrange: Simulasi konfigurasi route di index.js
    const profileRoute = {
      path: '/profil',
      meta: { requiresAuth: true }
    };
    // 2. Act
    const isProtected = profileRoute.meta.requiresAuth;
    // 3. Assert
    expect(isProtected).toEqual(true);
  });

  it('Scroll Behavior', () => {
    // 1. Arrange: Simulasi fungsi scrollBehavior milik Vue Router
    const scrollBehavior = (to, from, savedPosition) => {
      return { top: 0 };
    };
    // 2. Act
    const result = scrollBehavior({}, {}, null);
    // 3. Assert
    expect(result).toEqual({ top: 0 });
    expect(result.top).toEqual(0);
  });
});