<template>
  <footer class="footer">
    <p>Follow Us</p>
    <div class="social-icons">
      <a href="https://www.instagram.com/orsocoffeebar/" target="_blank">
        <i class="fab fa-instagram"></i>
      </a>
      <a href="https://www.tiktok.com/@orsocoffeebar/" target="_blank">
        <i class="fab fa-tiktok"></i>
      </a>
    </div>
    <p>&copy; 2025 Orso Coffee Bar</p>
  </footer>
</template>

<style>
footer {
  font-family: inherit;
  background-color: #000;
  color: #fff;
  text-align: center;
  padding: 20px 20px;
  width: 100%;
  display: block;
  overflow: hidden;
}

footer p {
  margin: 10px 0;
  font-size: 14px;
  letter-spacing: 0.3px;
}

footer .social-icons {
  margin: 20px 0;
  display: flex;
  justify-content: center;
  gap: 10px;
}

footer .social-icons a {
  color: #fff;
  font-size: 24px;
  transition: all 0.3s ease;
  text-decoration: none;
}

footer .social-icons a:hover {
  color: #c49a6c;
  transform: translateY(-5px);
}

footer p:last-child {
  opacity: 0.7;
  font-weight: 300;
  font-size: 12px;
}

@media (max-width: 768px) {
  footer {
    padding: 14px 12px;       
  }

  footer p {
    margin: 6px 0;
    font-size: 12px; 
  }

  footer .social-icons {
    margin: 12px 0;      
    gap: 8px;
  }

  footer .social-icons a {
    font-size: 18px;    
  }

  footer p:last-child {
    font-size: 11px;      
  }
}
</style>