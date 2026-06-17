import axios from 'axios';

const BASE_URL = import.meta.env.VITE_API_URL;

const api = axios.create({
  baseURL: BASE_URL,
  headers: { 'Content-Type': 'application/json' }
});

export const createOrder = async (orderData) => {
  const userData = JSON.parse(localStorage.getItem('user'));
  const userRole = userData ? userData.role : 'pelanggan';
  try {
    const response = await api.post('/order', orderData, {
      headers: { 'X-Role': userRole }
    });
    return response.data;
  } catch (error) {
    throw error.response ? error.response.data : new Error("Gagal membuat pesanan");
  }
};

export const getActiveOrders = async (userId) => {
  try {
    const response = await api.get(`/orders/user/${userId}`);
    return response.data;
  } catch (error) {
    throw error.response ? error.response.data : new Error("Gagal mengambil daftar pesanan");
  }
};

export const getOrderDetail = async (orderId) => {
  try {
    const response = await api.get(`/orders/detail/${orderId}`);
    return response.data;
  } catch (error) {
    throw error.response ? error.response.data : new Error("Gagal mengambil detail pesanan");
  }
};

export const registerUser = async (userData) => {
  try {
    const response = await api.post('/users/register', userData);
    return response.data;
  } catch (error) {
    throw error.response ? error.response.data : new Error("Koneksi server gagal");
  }
};

export const loginUser = async (loginData) => {
  try {
    const response = await api.post('/auth/login', loginData);
    return response.data;
  } catch (error) {
    throw error.response ? error.response.data : new Error("Koneksi server gagal");
  }
};

export const updateUser = async (userId, userData) => {
  try {
    const cleanId = userId.toString().replace(/\D/g, "");
    const response = await api.put(`/users/update/${cleanId}`, userData);
    return response.data;
  } catch (error) {
    throw error.response ? error.response.data : new Error("Gagal mengupdate profil");
  }
};

export const getMenu = async () => {
  try {
    const response = await api.get('/menu');
    return response.data;
  } catch (error) {
    return [];
  }
};
export const getMenuImage = (imageName, type = 'menu') => {
  if (!imageName) return '/image/favicon.png';
  if (imageName.startsWith('http')) return imageName;
  return `${BASE_URL}/uploads/${type}/${imageName}`;
};

export const getTestimoni = async () => {
  try {
    const response = await api.get('/testimoni');
    return Array.isArray(response.data) ? response.data : [];
  } catch (error) {
    return [];
  }
};

export const postTestimoni = async (formData) => {
  const userData = JSON.parse(localStorage.getItem('user'));
  const userRole = userData ? userData.role : ''; 
  try {
    const response = await api.post('/testimoni', formData, {
      headers: { 
        'Content-Type': 'multipart/form-data',
        'X-Role': userRole
      }
    });
    return response.data;
  } catch (error) {
    throw error.response ? error.response.data : new Error("Gagal mengirim testimoni");
  }
};

export const payWithMidtrans = (snapToken, callbacks) => {
  if (!window.snap) {
    Swal.fire("Error", "Cek koneksi internet kamu. Silakan refresh halaman.", "error");
    return;
  }

  window.snap.pay(snapToken, {
    onSuccess: (result) => callbacks.onSuccess(result),
    onPending: (result) => callbacks.onPending(result),
    onError: (result) => callbacks.onError(result),
    onClose: () => callbacks.onClose(),
  });
};

export default api;