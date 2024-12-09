<?php
// Konfigurasi koneksi database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'online_shop';

// Memulai session
session_start();

// Add this near the top of the file, after starting the session
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = array();
}

// Membuat koneksi
$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Periksa apakah user sudah login
$is_logged_in = isset($_SESSION['user_id']);

// Ganti baris query produk menjadi:
$query_flash_sale = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY jumlah_terjual DESC LIMIT 3");
$query_katalog = mysqli_query($koneksi, "SELECT * FROM produk LIMIT 6");

// Fungsi untuk format waktu
function formatTime($time) {
    $hours = floor($time / 3600);
    $minutes = floor(($time % 3600) / 60);
    $seconds = $time % 60;
    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
}

// Set waktu flash sale (contoh: 1 jam)
$flash_sale_time = 3600;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiddie Korner - Cute Child Style Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Comic+Neue:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Comic Neue', cursive;
            background: linear-gradient(to bottom, #ffe4e6, #e6e6fa);
            line-height: 1.6;
            color: #333;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #ff69b4;
        }
        .logo-container {
    display: flex;
    align-items: center;
}

.logo-image {
    height: 130px; /* Adjust size as needed */
    margin-right: 45px;
}
.logo-flash {
    align-items: center;
    display: flex;
    max-width: 250px; /* Adjust the maximum width as needed */
    height: auto; /* Maintain aspect ratio */
    transform: translateX(180%);
}

.logo-title {
    margin: 0;
}
        .nav-menu {
            display: flex;
            list-style: none;
        }
        .nav-menu li {
            margin-left: 20px;
        }
        .profile-img {
            max-width: 30px;
        }
            
        .nav-menu a {
            text-decoration: none;
            color: #9370db;
            transition: color 0.3s ease;
            font-weight: bold;
        }
        .nav-menu a:hover {
            color: #ff69b4;
        }
        .section {
            background-color: white;
            margin-bottom: 20px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .section-title {
            font-size: 2rem;
            margin-bottom: 20px;
            font-weight: bold;
            color: #9370db;
            text-align: center;
        }
        .flash-sale-timer {
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #ff69b4;
            font-weight: bold;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .product-card {
            background-color: #f9f9f9;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .product-card:hover {
            transform: scale(1.05);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product-info {
            padding: 15px;
        }
        .product-name {
            font-weight: bold;
            margin-bottom: 10px;
            color: #9370db;
        }
        .product-price {
            color: #ff69b4;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .product-sold {
            color: #32cd32;
            font-weight: bold;
        }
        .product-spec {
            margin-bottom: 5px;
        }
        .footer {
            background-color: #9370db;
            color: white;
            text-align: center;
            padding: 15px 0;
            border-radius: 10px 10px 0 0;
        }
        .profile-img {
            border-radius: 50%;
            margin-right: 20px;
            margin-left: 20px;
            vertical-align: middle;
            max-width: 35px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ff69b4;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }
        .btn:hover {
            background-color: #ff1493;
        
        
        }
        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
        }
        .product-rating {
            color: #ffa500;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container header-content">
            <div class="logo-container">
                <img src="logo_webiste.png" alt="Kiddie Korner Logo" class="logo-image">
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Products</a></li>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="keranjang.php">Keranjang</a></li>
                    <?php if ($is_logged_in): ?>
                    <li>
                        <a href="">
                            <img src="profil.jpg" alt="Profile" class="profile-img">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                    </li>
                    <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                    <li><a href="index-auth.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="section container">
        <img src="Flash_Sale.png" class="logo-flash">
            <div class="flash-sale-timer" id="flashSaleTimer">Time Left: <?php echo formatTime($flash_sale_time); ?></div>
            <div class="product-grid">
                <?php while($produk = mysqli_fetch_assoc($query_flash_sale)) { ?>
                <div class="product-card">
                    <a href="detail_produk.php?id=<?php echo $produk['id_produk']; ?>">
                    <?php if (!empty($produk['gambar_produk'])): ?>
    <img src="images/<?php echo htmlspecialchars($produk['gambar_produk']); ?>" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>" class="product-image">
<?php endif; ?>
                    </a>
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($produk['nama_produk']); ?></div>
                        <div class="product-price">Rp. <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
                        <div class="product-sold">Terjual <?php echo $produk['jumlah_terjual']; ?></div>
                        <form method="POST" action="keranjang.php">
                            <input type="hidden" name="id_produk" value="<?php echo $produk['id_produk']; ?>">
                            <input type="hidden" name="jumlah" value="1">
                            <button type="submit" name="tambah_ke_keranjang" class="btn">Add to Cart</button>
                        </form>
                    </div>
                </div>
                <?php } ?>
            </div>
        </section>

        <section class="section container">
            <h2 class="section-title">Product Catalog</h2>
            <div class="product-grid">
                <?php while($produk = mysqli_fetch_assoc($query_katalog)) { ?>
                <div class="product-card">
                    <a href="detail_produk.php?id=<?php echo $produk['id_produk']; ?>">
                    <?php if (!empty($produk['gambar_produk'])): ?>
    <img src="images/<?php echo htmlspecialchars($produk['gambar_produk']); ?>" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>" class="product-image">
<?php endif; ?>
                    </a>
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($produk['nama_produk']); ?></div>
                        <div class="product-price">Rp. <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
                        <div class="product-spec"><?php echo htmlspecialchars($produk['spesifikasi']); ?></div>
                        <div class="product-sold">Terjual <?php echo $produk['jumlah_terjual']; ?></div>
                        <a href="detail_produk.php?id=<?php echo $produk['id_produk']; ?>" class="btn">View Details</a>
                    </div>
                </div>
                <?php } ?>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            &copy; 2024 Kiddie Korner. All rights reserved.
        </div>
    </footer>

    <script>
        // Simple countdown timer for flash sale
        let timeLeft = <?php echo $flash_sale_time; ?>;
        const timerElement = document.getElementById('flashSaleTimer');

        function updateTimer() {
            const hours = Math.floor(timeLeft / 3600);
            const minutes = Math.floor((timeLeft % 3600) / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `Time Left: ${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft > 0) {
                timeLeft--;
                setTimeout(updateTimer, 1000);
            } else {
                timerElement.textContent = "Flash Sale Ended!";
            }
        }

        updateTimer();
    </script>

    <?php
    // Tutup koneksi database
    mysqli_close($koneksi);
    ?>
</body>
</html>

