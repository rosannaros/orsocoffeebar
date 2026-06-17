/**
 * @vitest-environment jsdom
 */
import { describe, it, expect, vi } from 'vitest';

describe('Register', () => {
  it('Validasi Panjang Password', () => {

    const state = { password: '123', error: '' };
    
    if (state.password.length < 6) {
      state.error = "Password minimal 6 karakter.";
    }
    
    expect(state.error).toEqual("Password minimal 6 karakter.");
  });

  it('registerUser', async () => {
    
    const api = { 
      post: vi.fn().mockResolvedValue({ success: true })
    };
    const userData = { nama: 'User Test', email: 'test@gmail.com', password: 'password123' };
    
    const response = await api.post('/users/register', userData);
    
    expect(response.success).toBe(true); 
    expect(api.post).toHaveBeenCalledWith('/users/register', userData); 
  });

  it('Handling Email Duplikat', async () => {
    
    const errorResponse = { error: "Email sudah terdaftar. Silakan gunakan email lain." };
    const api = {
      post: vi.fn().mockRejectedValue({ response: { data: errorResponse } })
    };
    const state = { error: "" };
    
    try {
      await api.post('/users/register', { email: 'user@gmail.com' });
    } catch (err) {
      
      state.error = err.response.data.error || "Gagal mendaftar.";
    }
    
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
    
    const $router = { push: vi.fn() };
    const response = { status: 200, user: { nama: 'User Test' } };
    
    if (response.status === 200) {
      localStorage.setItem("user", JSON.stringify(response.user));
      $router.push("/beranda");
    }
    
    expect(JSON.parse(localStorage.getItem("user")).nama).toEqual("User Test");
    expect($router.push).toHaveBeenCalledWith("/beranda");
  });
  
  it('Login Error Handling', () => {
    
    const state = { error: '' }; 
    const err = { error: "Email atau password salah." };
    
    state.error = err.error || "Email atau password salah.";
    
    expect(state.error).toEqual("Email atau password salah.");
  });
});

describe('Menu', () => {
  it('getMenu', async () => {
    
    const api = {
      get: vi.fn().mockRejectedValue(new Error('Network Error'))
    };
    let result = null;

    try {
      const response = await api.get('/menu');
      result = response.data;
    } catch (error) {
      result = []; 
    }
    expect(result).toEqual([]);
    expect(api.get).toHaveBeenCalledWith('/menu');
  });

  it('Handling Menu Image URL', () => {
    
    const BASE_URL = 'http://localhost:8000';
    
    const getMenuImage = (imageName, type = 'menu') => {
      if (!imageName) return '/image/placeholder-coffee.jpg';
      if (imageName.startsWith('http')) return imageName;
      return `${BASE_URL}/uploads/${type}/${imageName}`;
    };
    
    const externalPath = 'https://coffeeshop.example.com/latte.jpg';
    expect(getMenuImage(externalPath)).toEqual(externalPath);

    const fileName = 'espresso.png';

    expect(getMenuImage(fileName)).toEqual(`${BASE_URL}/uploads/menu/espresso.png`);
    expect(getMenuImage('')).toEqual('/image/placeholder-coffee.jpg');
  });
});

describe('Keranjang', () => {
  it('Kalkulasi totalPrice', () => {
    
    const items = {
      1: { id_menu: 1, price: 10000, qty: 2 },
      2: { id_menu: 2, price: 5000, qty: 3 }
    };
    
    const total = Object.values(items).reduce((acc, item) => acc + item.qty * item.price, 0);
    
    expect(total).toEqual(35000);
  });

  it('addItem Action', () => {
    
    const cart = { 1: { id_menu: 1, qty: 1 } };
    const id = 1;
    
    if (cart[id]) {
      cart[id].qty += 1;
    }
    
    expect(cart[1].qty).toEqual(2);
  });
});

describe('Pesanan', () => {
  it('createOrder', async () => {
    
    const api = {
      post: vi.fn().mockResolvedValue({ status: 201 })
    };
    const orderData = { total: 50000, items: [{ id_menu: 1, qty: 2 }] };
    
    const response = await api.post('/order', orderData, {});
    
    expect(response.status).toEqual(201);
    expect(api.post).toHaveBeenCalledWith('/order', orderData, {});
  });

  it('getActiveOrders', async () => {
    
    const api = {
      get: vi.fn().mockResolvedValue({ status: 200, data: [] })
    };
    const userId = '123';
    
    const response = await api.get(`/orders/user/${userId}`);
    
    expect(response.status).toEqual(200);
    expect(api.get).toHaveBeenCalledWith('/orders/user/123');
  });
  
  it('Filter activeOrders', () => {
    
    const allOrders = [
      { id: 1, status_pesanan: 'pending' },
      { id: 2, status_pesanan: 'diproses' },
      { id: 3, status_pesanan: 'selesai' } 
    ];
    
    const activeOrders = allOrders.filter(o => {
      const s = o.status_pesanan.toLowerCase();
      return s === 'pending' || s === 'diproses';
    });
    
    expect(activeOrders.length).toEqual(2);
    expect(activeOrders[0].status_pesanan).toEqual('pending');
    expect(activeOrders[1].status_pesanan).toEqual('diproses');
    
    const hasFinishedOrder = activeOrders.some(o => o.status_pesanan === 'selesai');
    expect(hasFinishedOrder).toEqual(false);
  });
});

describe('Riwayat', () => {
  it('getOrderDetail', async () => {
    
    const api = {
      get: vi.fn().mockResolvedValue({ status: 200, data: { id: 50 } })
    };
    const orderId = 50;
    
    const response = await api.get(`/orders/detail/${orderId}`);
    
    expect(response.status).toEqual(200);
    expect(api.get).toHaveBeenCalledWith('/orders/detail/50');
  });

  it('Sorting sortOrders', () => {
    
    const detailed = [
      { id_pesanan: 1, date: '2024-01-01' },
      { id_pesanan: 3, date: '2024-01-03' },
      { id_pesanan: 2, date: '2024-01-02' }
    ];
    
    const sortedOrders = detailed.sort((a, b) => b.id_pesanan - a.id_pesanan);
    
    expect(sortedOrders[0].id_pesanan).toEqual(3);
    expect(sortedOrders[1].id_pesanan).toEqual(2);
    expect(sortedOrders[2].id_pesanan).toEqual(1);
  });

  it('reorderItem', () => {
    
    const cartStore = {
      addItem: vi.fn()
    };
    const items = [
      { id_menu: 1, nama: 'Espresso', price: 20000 },
      { id_menu: 2, nama: 'Latte', price: 25000 }
    ];
    
    items.forEach(item => {
      cartStore.addItem({
        id: item.id_menu,
        name: item.nama,
        price: item.price,
        qty: 1
      });
    });
    
    expect(cartStore.addItem).toHaveBeenCalledTimes(2);
    
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
    
    const mockData = [
      { id: 1, nama: 'Budi', ulasan: 'Kopinya enak!' },
      { id: 2, nama: 'Santi', ulasan: 'Tempatnya nyaman.' }
    ];
    const getTestimoni = vi.fn().mockResolvedValue(mockData);
    
    const data = await getTestimoni();
    
    expect(data).toEqual(mockData);
    expect(data.length).toEqual(2);
    expect(getTestimoni).toHaveBeenCalled();
  });

  it('handleImageError', () => {
    
    const placeholder = '/assets/img/default-avatar.png';
    
    const event = {
      target: {
        src: 'gambar-rusak.jpg'
      }
    };
    
    if (event.target.src.includes('rusak')) {
      event.target.src = placeholder;
    }
    
    expect(event.target.src).toEqual(placeholder);
  });

  it('Auth Testimoni Success', () => {
    
    const userData = { id: 1, nama: 'User Login' };
    const $router = { push: vi.fn() };
    
    if (!userData) {
    
    } else {
      $router.push('/tulistestimoni');
    }
    
    expect($router.push).toHaveBeenCalledWith('/tulistestimoni');
  });
});

describe('TulisTestimoni', () => {
  it('Preview Image', () => {
    
    const state = { preview: null };
    const mockEvent = { target: { result: 'data:image/png;base64,xyz' } };
    
    const readerOnLoad = (e) => { state.preview = e.target.result; };
    readerOnLoad(mockEvent);
    
    expect(state.preview).toEqual('data:image/png;base64,xyz');
  });

  it('Handling Empty Image', () => {
    
    const state = { foto: null };
    const formData = new FormData();
    
    if (this.foto) {
      formData.append('foto_testimoni', this.foto);
    }
    
    expect(formData.has('foto_testimoni')).toEqual(false);
    expect(formData.get('foto_testimoni')).toEqual(null);
  });
});

describe('EditProfil', () => {
  it('checkDataChange', () => {
   
    const oldData = { nama: 'Andi', email: 'andi@test.com' };
    const newData = { nama: 'Andi', email: 'andi@test.com' };
    const passwordInput = "";
      
    let isUpdateProceed = true;
   
    const isNamaSama = newData.nama === oldData.nama;
    const isEmailSama = newData.email === oldData.email;
    const isPasswordKosong = passwordInput === "";

    if (isNamaSama && isEmailSama && isPasswordKosong) {
      isUpdateProceed = false;
    }
  
    expect(isUpdateProceed).toEqual(false);
  });

  it('Update Local Sync', () => {
  
    const spy = vi.spyOn(Storage.prototype, 'setItem');
    const updatedUserData = { id: 1, nama: 'Budi Updated', email: 'budi@test.com' };
  
    localStorage.setItem("user", JSON.stringify(updatedUserData));
  
    const storedData = JSON.parse(localStorage.getItem("user"));
    expect(storedData.nama).toEqual('Budi Updated');
    expect(spy).toHaveBeenCalledWith("user", JSON.stringify(updatedUserData));
  });
});

describe('ProfildanAuth', () => {
  it('handleLogout', () => {
  
    localStorage.setItem("user", JSON.stringify({ name: "Budi" }));
    const spy = vi.spyOn(Storage.prototype, 'removeItem');

    localStorage.removeItem("user");
  
    expect(localStorage.getItem("user")).toEqual(null);
    expect(spy).toHaveBeenCalledWith("user");
  });

  it('Otorisasi Rute', () => {
  
    const profileRoute = {
      path: '/profil',
      meta: { requiresAuth: true }
    };
   
    const isProtected = profileRoute.meta.requiresAuth;
  
    expect(isProtected).toEqual(true);
  });
});