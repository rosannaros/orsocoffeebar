<template>
  <div class="beranda-page">
    <div class="fullwidth-section hero">
      <img src="../assets/images/sections/heroberanda.jpg" alt="Hero Beranda"/>
    </div>
  
    <div class="fullwidth-section">
      <img src="../assets/images/sections/motto-section.jpg" alt="Motto Section"/>
    </div>
 
    <div class="weekly-special">
      <img src="../assets/images/weekly/strawberry-cheesecake.png" alt="Weekly Special Section"/>
      <div class="special-text">
        <h3>Favorite Minggu Ini: Coffee Strawberry Cheesecake</h3>
        <p>Kreasi minuman yang memadukan rasa manis dan creamy dari cheesecake dengan sentuhan segar stroberi.</p>
        <router-link to="/menu" class="order-btn"> Order Now </router-link>
      </div>
    </div>

    <div class="fullwidth-section">
      <img src="../assets/images/sections/crafted-section.jpg" alt="Made For You Section"/>
    </div>

    <div class="categories">
      <div class="category-list">
        <router-link to="/menu?kategori=coffee" class="cat-item">
          <img src="../assets/images/category-icons/coffee.png" alt="Coffee" />
          <p>Coffee</p>
        </router-link>

        <router-link to="/menu?kategori=non-coffee" class="cat-item">
          <img src="../assets/images/category-icons/noncoffee.png" alt="Non Coffee" />
          <p>Non-Coffee</p>
        </router-link>

        <router-link to="/menu?kategori=tea" class="cat-item">
          <img src="../assets/images/category-icons/tea.png" alt="Tea" />
          <p>Tea</p>
        </router-link>

        <router-link to="/menu?kategori=mojito" class="cat-item">
          <img src="../assets/images/category-icons/mojito.jpg" alt="Mojito" />
          <p>Mojito</p>
        </router-link>
      </div>
    </div>

    <div class="fullwidth-section">
      <img src="../assets/images/sections/gallery-section.jpg" alt="Our Gallery Section"/>
    </div>

    <div class="gallery">
      <div class="gallery-grid">
        <img src="../assets/images/gallery/orso1.jpg" alt="Gallery 1" />
        <img src="../assets/images/gallery/orso2.jpg" alt="Gallery 2" />
        <img src="../assets/images/gallery/orso3.jpg" alt="Gallery 3" />
        <img src="../assets/images/gallery/orso4.jpg" alt="Gallery 4" />
        <img src="../assets/images/gallery/orso5.jpg" alt="Gallery 5" />
        <img src="../assets/images/gallery/orso6.jpg" alt="Gallery 6" />
        <img src="../assets/images/gallery/orso7.jpg" alt="Gallery 7" />
        <img src="../assets/images/gallery/orso8.jpg" alt="Gallery 8" />
        <img src="../assets/images/gallery/orso9.jpg" alt="Gallery 9" />
      </div>
    </div>
  </div>
</template>

<style>
.weekly-special {
  background: #f5ebe1;
  padding: 60px 10%;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 40px;
}

.weekly-special img {
  max-width: 280px;
  border-radius: 50px;
}

.special-text h3 {
  font-size: 26px;
  color: #5C4033;
  margin-bottom: 15px;
}

.order-btn {
  padding: 12px 28px;
  border-radius: 50px;
  text-decoration: none;
  font-weight: 600;
  display: inline-block;
  transition: box-shadow 0.25s ease, transform 0.25s ease;
  margin-top: 20px;
}

.categories {
  padding: 60px 5%;
  text-align: center;
}

.category-list {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.cat-item {
  background: #EDE1D7;
  border-radius: 16px;
  text-decoration: none;
  font-weight: 700;
  max-width: 220px;
  aspect-ratio: 3 / 4;
  padding: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-self: center;
  transition: transform 0.25s ease;
}

.cat-item img {
  width: 100%;
  aspect-ratio: 1 / 1;
  object-fit: cover;
  border-radius: 14px;
}

.cat-item p {
  color: #5C4033;
  margin-top: 14px;
  text-align: center;
  flex-grow: 1;
  display: flex;
  align-items: center;
}

.cat-item:hover {
  transform: translateY(-8px);
}

.gallery {
  max-width: 1000px;     
  margin: 0 auto 40px;  
  padding: 20px;
  background: #fff;
  overflow-x: hidden;   
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

.gallery-grid img {
  width: 100%;
  max-width: 100%;
  aspect-ratio: 1 / 1;   
  object-fit: cover;
  border-radius: 8px;
  display: block;
}

@media (max-width: 768px) {
  .weekly-special {
    padding: 20px 4%;
    gap: 12px;
  }

  .weekly-special img {
    max-width: 130px;
    border-radius: 24px;
  }

  .special-text h3 {
    font-size: 16px;
    line-height: 1.3;
  }

  .special-text p {
    font-size: 12px;
    line-height: 1.4;
  }

  .order-btn {
    padding: 8px 16px;
    font-size: 13px;
  }

  .categories {
    padding: 20px 4%;
    overflow-x: hidden;
  }

  .category-list {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 6px;
    scroll-snap-type: x mandatory;
  }

  .cat-item {
    flex: 0 0 120px;   
    padding: 8px;
    scroll-snap-align: start;
    border-radius: 12px;
  }

  .cat-item img {
    height: 100px;
    border-radius: 8px;
  }

  .cat-item p {
    font-size: 12px;
    margin-top: 6px;
  }

  .category-list::-webkit-scrollbar {
    display: none;
  }

  .gallery {
    max-width: 600px;
    padding: 15px;
  }

  .gallery-grid {
    gap: 10px;
  }

  .gallery-grid img {
    border-radius: 6px;
  }
}
</style>
