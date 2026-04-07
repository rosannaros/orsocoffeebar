<template>
  <div class="tentang-page">
    <div class="fullwidth-section hero">
      <img src="../assets/images/sections/herotentang.jpg" alt="Hero Tentang"/>
    </div>

    <section class="fullwidth-section">
      <img src="../assets/images/sections/tentang-section.jpg" alt="Tentang Orso"/>
    </section>

    <section class="fullwidth-section">
      <img src="../assets/images/sections/visi-section.jpg" alt="Visi Orso"/>
      <p class="visi-text">
        Kami percaya bahwa setiap kunjungan ke <strong>Orso Coffee Bar</strong>
        adalah kesempatan untuk menemukan kenyamanan, inspirasi, dan kebersamaan.
        <br />
        Baik Anda ingin bersantai, bekerja, atau bertemu teman, kami menyediakan
        tempat yang sempurna untuk setiap momen Anda.
      </p>
    </section>
  
    <section class="fullwidth-section">
      <img src="../assets/images/sections/misi-section.jpg" alt="Misi Orso"/>

      <div class="misi-grid">
        <div class="misi-item">
          <div class="badge"><i class="fa-solid fa-mug-hot"></i></div>
          <p>Menyajikan kopi berkualitas tinggi dari biji pilihan terbaik dengan proses teliti dan penuh dedikasi.</p>
        </div>

        <div class="misi-item">
          <div class="badge"><i class="fa-solid fa-handshake"></i></div>
          <p>Memberikan pelayanan cepat, profesional, dan hangat dengan fokus pada kepuasan pelanggan.</p>
        </div>

        <div class="misi-item">
          <div class="badge"><i class="fa-solid fa-lightbulb"></i></div>
          <p>Mendorong inovasi dalam menu dan layanan, menciptakan pengalaman baru bagi pecinta kopi.</p>
        </div>

        <div class="misi-item">
          <div class="badge"><i class="fa-solid fa-users"></i></div>
          <p>Mendukung kolaborasi kreatif dengan berbagai acara, komunitas, dan kegiatan lokal melalui kehadiran sebagai tenant dan mitra.</p>
        </div>
      </div>
    </section>

    <section class="fullwidth-section">
      <img src="../assets/images/sections/lokasi-section.jpg"alt="Lokasi Orso"/>

      <div class="lokasi-content">
        <div class="lokasi-info">
          <p><strong>Alamat:</strong> Jl. MT Haryono Gg. Pramuka II, Balikpapan</p>
          <p><strong>Telepon:</strong> (021) 123-4567</p>
          <p><strong>Jam Operasional:</strong> 19.00 – 00.00 WITA</p>
        </div>

        <div class="lokasi-map">
          <iframe src= "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.8479784390393!2d116.85614887496554!3d-1.2636658987243095!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2df145bcd7e9d261%3A0xf146dc732db8fa34!2sOrso%20Coffeebar!5e0!3m2!1sid!2sid!4v1768057318348!5m2!1sid!2sid"  
            allowfullscreen
            loading="lazy" 
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
      </div>
    </section>
  </div>
</template>

<style>
.visi-text {
  max-width: 800px;
  margin: 0 auto;           
  padding: 0 20px;
  font-size: 16px;
  line-height: 1.8;
  text-align: center;
}

.misi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  max-width: 1000px;
  margin: 0 auto 40px;         
  padding: 0 24px;
}

.misi-item {
  background: #fff;
  padding: 24px 20px;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  text-align: center;
}

.badge {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  border: 2px solid #c49a6c;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 14px;
}

.badge i {
  font-size: 26px;
  color: #c49a6c;
}

.misi-item p {
  font-size: 14px;
  line-height: 1.6;
  margin: 0;
}

.lokasi-content {
  display: flex;
  gap: 40px;
  justify-content: center;
  flex-wrap: wrap;
  max-width: 1100px;
  margin: 0 auto 40px;         
  padding: 0 20px;
}

.lokasi-info p {
  font-size: 15px;
  line-height: 1.6;
  margin: 6px 0;
}

.lokasi-map iframe {
  width: 460px;
  height: 360px;
  border-radius: 12px;
  border: none;
}

@media (max-width: 1024px) {
  .visi-text {
    font-size: 14px;
    line-height: 1.7;
    max-width: 720px;
  }

  .misi-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
  }

  .misi-item {
    padding: 20px 16px;
  }

  .badge {
    width: 60px;
    height: 60px;
  }

  .badge i {
    font-size: 22px;
  }

  .misi-item p {
    font-size: 13px;
  }

  .lokasi-info p {
    font-size: 14px;
  }
}

@media (max-width: 768px) {
  .visi-text {
    font-size: 12px;
    line-height: 1.6;
  }

  .misi-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
  }

  .misi-item {
    padding: 18px 14px;
  }

  .badge {
    width: 56px;
    height: 56px;
  }

  .badge i {
    font-size: 20px;
  }

  .misi-item p {
    font-size: 12px;
    line-height: 1.5;
  }

  .lokasi-map iframe {
    width: 100%;
    height: 300px;
  }
}
</style>
