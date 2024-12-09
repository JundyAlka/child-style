<?php
// Konfigurasi koneksi database
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'online_shop';

// Tambahkan fungsi debugging
function debugLog($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'debug.log');
}

// Membuat koneksi
$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi dan tambahkan log
debugLog("Koneksi database: " . ($koneksi ? "Berhasil" : "Gagal"));

// Tambahkan kode berikut di awal file, setelah koneksi database:
debugLog("Request Method: " . $_SERVER['REQUEST_METHOD']);
debugLog("Request URI: " . $_SERVER['REQUEST_URI']);

// Memulai session
session_start();

// Tambahkan kode berikut setelah baris yang memulai sesi
// Tambahkan logika untuk mengambil ID produk dan melakukan redirect jika perlu
$id_produk = null;
if (isset($_GET['id'])) {
    $id_produk = intval($_GET['id']);
} elseif (!empty($_SESSION['checkout_details'])) {
    $id_produk = $_SESSION['checkout_details'][0]['id_produk'];
} elseif (!empty($_SESSION['keranjang'])) {
    $id_produk = array_key_first($_SESSION['keranjang']);
}

if ($id_produk === null) {
    debugLog("Tidak ada ID produk yang ditemukan. Redirect ke halaman keranjang.");
    header("Location: keranjang.php");
    exit();
}

if (!isset($_GET['id'])) {
    debugLog("ID produk tidak ada di URL. Melakukan redirect dengan ID: " . $id_produk);
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $id_produk);
    exit();
}


// Debug informasi sesi
debugLog("SESSION: " . print_r($_SESSION, true));
debugLog("POST: " . print_r($_POST, true));


// Cek apakah ada checkout details dari session
$checkout_details = isset($_SESSION['checkout_details']) ? $_SESSION['checkout_details'] : [];
$total_checkout = isset($_SESSION['total_checkout']) ? $_SESSION['total_checkout'] : 0;
$tampilkan_input_jumlah = true;

// Jika checkout details ada, gunakan produk pertama sebagai default
$default_produk = !empty($checkout_details) ? $checkout_details[0] : null;



// Tambahkan log untuk memeriksa nilai $_GET['id'] dan $default_produk
debugLog("GET['id']: " . (isset($_GET['id']) ? $_GET['id'] : "tidak ada"));
debugLog("default_produk: " . print_r($default_produk, true));

// Query untuk mengambil detail produk
$query_detail = "SELECT * FROM produk WHERE id_produk = ?";
$stmt = mysqli_prepare($koneksi, $query_detail);
mysqli_stmt_bind_param($stmt, "i", $id_produk);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);

if (!$produk) {
    debugLog("Produk tidak ditemukan untuk ID: " . $id_produk);
    die("Produk tidak ditemukan");
}

debugLog("Produk ditemukan: " . print_r($produk, true));

// Daftar jasa kirim
$jasa_kirim = [
    'jne' => 'JNE',
    'pos' => 'POS Indonesia',
    'sicepat' => 'SiCepat',
    'grab' => 'Grab Express',
    'anteraja' => 'AnterAja'
];

// Daftar metode pembayaran
$metode_pembayaran = [
    'transfer_bca' => 'Transfer Bank BCA',
    'transfer_mandiri' => 'Transfer Bank Mandiri',
    'transfer_bni' => 'Transfer Bank BNI',
    'gopay' => 'GoPay',
    'dana' => 'DANA',
    'ovo' => 'OVO',
    'cod' => 'Cash on Delivery (COD)'
];

// Inisialisasi variabel
$pesan_berhasil = false;
$error_message = '';
$payment_info = '';
$id_pesanan = null;

// Tentukan jumlah pesanan
$jumlah_pesanan_default = 1;
if (!empty($checkout_details)) {
    // Jika berasal dari keranjang, gunakan total produk di keranjang
    $jumlah_pesanan_default = array_sum(array_column($checkout_details, 'jumlah'));
}
if (empty($_SESSION['keranjang'])) {
    unset($_SESSION['checkout_details']);
    unset($_SESSION['total_checkout']);
    $checkout_details = [];
    $total_checkout = 0;
}


// Proses form pemesanan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    debugLog("POST request received");
    if (isset($_POST['confirm_payment'])) {
        debugLog("Confirm payment request received");
        $id_pesanan = isset($_POST['id_pesanan']) ? intval($_POST['id_pesanan']) : 0;
        
        // Debug log tambahan
        debugLog("ID Pesanan: $id_pesanan");
        
        if ($id_pesanan > 0) {
            // Query untuk mengambil detail pesanan dengan informasi produk
            $query_pesanan = "
                SELECT p.*, pr.nama_produk, pr.id_produk
                FROM pesanan p
                JOIN produk pr ON p.id_produk = pr.id_produk
                WHERE p.id_pesanan = $id_pesanan
            ";
            
            $result_pesanan = mysqli_query($koneksi, $query_pesanan);
            
            if ($result_pesanan && mysqli_num_rows($result_pesanan) > 0) {
                $pesanan = mysqli_fetch_assoc($result_pesanan);
                
                // Debug log
                debugLog("Pesanan ditemukan: " . print_r($pesanan, true));
                
                $id_produk = $pesanan['id_produk'];
                
                $query_update_payment_status = "UPDATE pesanan SET status_pembayaran = 'sudah_bayar' WHERE id_pesanan = $id_pesanan";
                
                if (mysqli_query($koneksi, $query_update_payment_status)) {
                    $payment_confirmed = true;
                    
                    // Gunakan data produk dari query sebelumnya
                    $produk = [
                        'id_produk' => $pesanan['id_produk'],
                        'nama_produk' => $pesanan['nama_produk']
                    ];
                    
                    // Reset checkout details jika ada
                    unset($_SESSION['checkout_details']);
                    unset($_SESSION['keranjang']);
                    unset($_SESSION['total_checkout']);
                    
                    debugLog("Pembayaran berhasil dikonfirmasi untuk pesanan ID: $id_pesanan");
                } else {
                    $payment_error = "Gagal mengonfirmasi pembayaran: " . mysqli_error($koneksi);
                    debugLog($payment_error);
                }
            } else {
                $payment_error = "Pesanan tidak ditemukan. ID: $id_pesanan";
                debugLog($payment_error);
            }
        } else {
            $payment_error = "ID Pesanan tidak valid: $id_pesanan";
            debugLog($payment_error);
        }
    } else {
        debugLog("Order submission request received");
        // Validasi input
        $jumlah_pesan = [];
        if (!empty($checkout_details)) {
            foreach ($checkout_details as $item) {
                $jumlah_pesan[$item['id_produk']] = $item['jumlah'];
            }
        } elseif (isset($_POST['jumlah'])) {
            // For direct "Beli Sekarang" purchases
            $jumlah_pesan[$id_produk] = intval($_POST['jumlah']);
        } else {
            // Default to 1 if no quantity is specified
            $jumlah_pesan[$id_produk] = 1;
        }
        debugLog("Jumlah pesanan: " . print_r($jumlah_pesan, true));

        // Update the query to insert multiple orders
        if (empty($errors)) {
            // Determine the number of items to order
            if (!empty($checkout_details)) {
                $jumlah_pesan = array_sum(array_column($checkout_details, 'jumlah'));  // Total dari keranjang
            } elseif (isset($_POST['jumlah'])) {
                $jumlah_pesan = intval($_POST['jumlah']);  // Input manual jika bukan dari keranjang
            } else {
                $jumlah_pesan = 1;  // Default to 1 if no quantity is specified
            }

            // Sanitize and validate input
            $nama_pembeli = mysqli_real_escape_string($koneksi, $_POST['nama_pembeli']);
            $email_pembeli = filter_var($_POST['email_pembeli'], FILTER_VALIDATE_EMAIL);
            $no_hp_pembeli = mysqli_real_escape_string($koneksi, $_POST['no_hp_pembeli']);
            $alamat_pembeli = mysqli_real_escape_string($koneksi, $_POST['alamat_pembeli']);
            
            $jasa_kirim_input = mysqli_real_escape_string($koneksi, $_POST['jasa_kirim']);
            $metode_bayar_input = mysqli_real_escape_string($koneksi, $_POST['metode_pembayaran']);

            // Hitung total harga
            $total_harga = $produk['harga'] * $jumlah_pesan;

            // Simpan data pembeli ke tabel pembeli
            $query_insert_pembeli = "INSERT INTO pembeli (nama_lengkap, email, no_hp, alamat) VALUES ('$nama_pembeli', '$email_pembeli', '$no_hp_pembeli', '$alamat_pembeli')";
            
            if (mysqli_query($koneksi, $query_insert_pembeli)) {
                $id_pembeli = mysqli_insert_id($koneksi);

                // Simpan pesanan ke tabel pesanan
                $query_insert_pesanan = "INSERT INTO pesanan (id_produk, id_pembeli, jumlah, total_harga, jasa_kirim, metode_pembayaran, status_pembayaran) VALUES ($id_produk, $id_pembeli, $jumlah_pesan, $total_harga, '$jasa_kirim_input', '$metode_bayar_input', 'belum_bayar')";
                
                if (mysqli_query($koneksi, $query_insert_pesanan)) {
                    // Update stok produk
                    $stok_baru = $produk['stok'] - $jumlah_pesan;
                    $query_update_stok = "UPDATE produk SET stok = $stok_baru WHERE id_produk = $id_produk";
                    mysqli_query($koneksi, $query_update_stok);
                    
                    // Jika berasal dari keranjang, hapus item di keranjang
                    if (!empty($checkout_details)) {
                        foreach ($checkout_details as $detail) {
                            unset($_SESSION['keranjang'][$detail['id_produk']]);
                        }
                        unset($_SESSION['checkout_details']);
                        unset($_SESSION['total_checkout']);
                    }
                    
                    $pesan_berhasil = true;
                    $id_pesanan = mysqli_insert_id($koneksi);

                    // Generate payment information
                    $payment_info = '';
                    switch ($metode_bayar_input) {
                        case 'transfer_bca':
                            $payment_info = "Silakan transfer ke rekening BCA: 1234567890 a.n. Kiddie Korner";
                            break;
                        case 'transfer_mandiri':
                            $payment_info = "Silakan transfer ke rekening Mandiri: 0987654321 a.n. Kiddie Korner";
                            break;
                        case 'transfer_bni':
                            $payment_info = "Silakan transfer ke rekening BNI: 1122334455 a.n. Kiddie Korner";
                            break;
                        case 'gopay':
                            $payment_info = "Silakan bayar melalui GoPay ke nomor: 081234567890";
                            break;
                        case 'dana':
                            $payment_info = "Silakan bayar melalui DANA ke nomor: 081234567890";
                            break;
                        case 'ovo':
                            $payment_info = "Silakan bayar melalui OVO ke nomor: 081234567890";
                            break;
                        case 'cod':
                            $payment_info = "Pembayaran akan dilakukan saat barang diterima";
                            break;
                        default:
                            $payment_info = "Silakan hubungi customer service untuk informasi pembayaran";
                    }

                    // Tambahkan variabel untuk metode pembayaran yang dapat dibaca
                    $metode_bayar_label = isset($metode_pembayaran[$metode_bayar_input]) 
                        ? $metode_pembayaran[$metode_bayar_input] 
                        : 'Metode Pembayaran Tidak Diketahui';
                    $tampilkan_input_jumlah = empty($checkout_details);

                    // Simpan informasi pembayaran ke dalam database
                    $query_update_payment_info = "UPDATE pesanan SET informasi_pembayaran = '$payment_info' WHERE id_pesanan = $id_pesanan";
                    mysqli_query($koneksi, $query_update_payment_info);
                } else {
                    $errors[] = "Gagal menyimpan pesanan: " . mysqli_error($koneksi);
                }
            } else {
                $errors[] = "Gagal menyimpan data pembeli: " . mysqli_error($koneksi);
            }
        }
        
        // Jika ada error
        if (!empty($errors)) {
            $error_message = implode('<br>', $errors);
        }

            // Simpan data pembeli ke tabel pembeli
            $query_insert_pembeli = "INSERT INTO pembeli (nama_lengkap, email, no_hp, alamat) VALUES ('$nama_pembeli', '$email_pembeli', '$no_hp_pembeli', '$alamat_pembeli')";
            if (mysqli_query($koneksi, $query_insert_pembeli)) {
                $id_pembeli = mysqli_insert_id($koneksi);

                // Simpan pesanan ke tabel pesanan
                $query_insert_pesanan = "INSERT INTO pesanan (id_produk, id_pembeli, jumlah, total_harga, jasa_kirim, metode_pembayaran, status_pembayaran) VALUES ($id_produk, $id_pembeli, $jumlah_pesan, $total_harga, '$jasa_kirim_input', '$metode_bayar_input', 'belum_bayar')";
                
                if (mysqli_query($koneksi, $query_insert_pesanan)) {
                    // Update stok produk
                    $stok_baru = $produk['stok'] - $jumlah_pesan;
                    $query_update_stok = "UPDATE produk SET stok = $stok_baru WHERE id_produk = $id_produk";
                    mysqli_query($koneksi, $query_update_stok);
                    
                    // Jika berasal dari keranjang, hapus item di keranjang
                    if (!empty($checkout_details)) {
                        foreach ($checkout_details as $detail) {
                            unset($_SESSION['keranjang'][$detail['id_produk']]);
                        }
                        unset($_SESSION['checkout_details']);
                        unset($_SESSION['total_checkout']);
                    }
                    
                    $pesan_berhasil = true;
                    $id_pesanan = mysqli_insert_id($koneksi);

                    // Generate payment information
                    $payment_info = '';
                    switch ($metode_bayar_input) {
                        case 'transfer_bca':
                            $payment_info = "Silakan transfer ke rekening BCA: 1234567890 a.n. Kiddie Korner";
                            break;
                        case 'transfer_mandiri':
                            $payment_info = "Silakan transfer ke rekening Mandiri: 0987654321 a.n. Kiddie Korner";
                            break;
                        case 'transfer_bni':
                            $payment_info = "Silakan transfer ke rekening BNI: 1122334455 a.n. Kiddie Korner";
                            break;
                        case 'gopay':
                            $payment_info = "Silakan bayar melalui GoPay ke nomor: 081234567890";
                            break;
                        case 'dana':
                            $payment_info = "Silakan bayar melalui DANA ke nomor: 081234567890";
                            break;
                        case 'ovo':
                            $payment_info = "Silakan bayar melalui OVO ke nomor: 081234567890";
                            break;
                        case 'cod':
                            $payment_info = "Pembayaran akan dilakukan saat barang diterima";
                            break;
                        default:
                            $payment_info = "Silakan hubungi customer service untuk informasi pembayaran";
                    }

                    // Tambahkan variabel untuk metode pembayaran yang dapat dibaca
                    $metode_bayar_label = isset($metode_pembayaran[$metode_bayar_input]) 
    ? $metode_pembayaran[$metode_bayar_input] 
    : 'Metode Pembayaran Tidak Diketahui';
$tampilkan_input_jumlah = empty($checkout_details);
                       

                    // Simpan informasi pembayaran ke dalam database
                    $query_update_payment_info = "UPDATE pesanan SET informasi_pembayaran = '$payment_info' WHERE id_pesanan = $id_pesanan";
                    mysqli_query($koneksi, $query_update_payment_info);
                } else {
                    $errors[] = "Gagal menyimpan pesanan: " . mysqli_error($koneksi);
                }
            } else {
                $errors[] = "Gagal menyimpan data pembeli: " . mysqli_error($koneksi);
            }
        }
        
        // Jika ada error
        if (!empty($errors)) {
            $error_message = implode('<br>', $errors);
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiddie Korner - Order <?php echo $produk['nama_produk']; ?></title>
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
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .pesanan-container {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .product-image {
            width: 50%;
            height: 300px;
            object-fit: cover;
        }
        .produk-info {
            margin-bottom: 20px;
        }
        .produk-nama {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #ff69b4;
        }
        .produk-harga {
            font-size: 1.5rem;
            color: #9370db;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .produk-stok {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        .form-pesanan {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }
        .form-control {
            padding: 12px;
            border: 2px solid #ff69b4;
            border-radius: 25px;
            font-size: 1rem;
            font-family: 'Comic Neue', cursive;
        }
        .btn-pesan {
            grid-column: 1 / -1;
            padding: 12px;
            background-color: #9370db;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: opacity 0.3s ease;
            font-size: 1rem;
            font-weight: bold;
            font-family: 'Comic Neue', cursive;
        }
        .btn-pesan:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }
        .btn-pesan:hover:not(:disabled) {
            opacity: 0.9;
        }
        .error-message {
            color: #ff4500;
            margin-bottom: 15px;
            grid-column: 1 / -1;
            font-weight: bold;
        }
        .success-message {
            color: #32cd32;
            margin-bottom: 15px;
            grid-column: 1 / -1;
            text-align: center;
            font-weight: bold;
        }
        .detail-pesanan {
            grid-column: 1 / -1;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 20px;
            margin-top: 20px;
        }
        .payment-info {
            background-color: #e6f7ff;
            border: 2px solid #91d5ff;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            font-size: 1.1rem;
        }
        .confirm-payment-btn {
            background-color: #52c41a;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .confirm-payment-btn:hover {
            background-color: #389e0d;
        }
        @media (max-width: 768px) {
            .form-pesanan {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="pesanan-container">
        <?php if (!empty($produk['gambar_produk'])): ?>
    <img src="images/<?php echo htmlspecialchars($produk['gambar_produk']); ?>" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>" class="product-image">
<?php endif; ?>
            
            <?php if (!empty($checkout_details)): ?>
                <div class="checkout-details">
                    <h2>Detail Produk yang Dipesan:</h2>
                    <?php foreach ($checkout_details as $detail): ?>
                        <div class="checkout-item">
                            <p><strong>Produk:</strong> <?php echo $detail['nama_produk']; ?></p>
                            <p><strong>Jumlah:</strong> <?php echo $detail['jumlah']; ?></p>
                            <p><strong>Subtotal:</strong> Rp. <?php echo number_format($detail['subtotal'], 0, ',', '.'); ?></p>
                        </div>
                    <?php endforeach; ?>
                    <p><strong>Total Keseluruhan:</strong> Rp. <?php echo number_format($total_checkout, 0, ',', '.'); ?></p>
                </div>
            <?php endif; ?>

            
            <?php if ($pesan_berhasil): ?>
                <div class="success-message">
                    <h2>Pesanan Berhasil!</h2>
                    <div class="detail-pesanan">
                        <h3>Detail Pesanan:</h3>
                        <p><strong>Produk:</strong> <?php echo $produk['nama_produk']; ?></p>
                        <p><strong>Jumlah:</strong> <?php echo $jumlah_pesan; ?></p>
                        <p><strong>Total Harga:</strong> Rp. <?php echo number_format($total_harga, 0, ',', '.'); ?></p>
                        <p><strong>Metode Pembayaran:</strong> <?php echo $metode_bayar_label; ?></p>
                        <p><strong>Jasa Kirim:</strong> <?php echo $jasa_kirim[$jasa_kirim_input]; ?></p>
                        <p><strong>Nama Pembeli:</strong> <?php echo htmlspecialchars($nama_pembeli); ?></p>
                        <p><strong>Alamat:</strong> <?php echo htmlspecialchars($alamat_pembeli); ?></p>
                    </div>
                    <div class="payment-info">
                        <h3>Informasi Pembayaran:</h3>
                        <p><?php echo $payment_info; ?></p>
                    </div>
                    <form method="POST" style="margin-top: 20px;">
    <input type="hidden" name="id_pesanan" value="<?php echo isset($id_pesanan) ? $id_pesanan : 0; ?>">
    <input type="hidden" name="confirm_payment" value="1">
    <button type="submit" class="confirm-payment-btn">Konfirmasi Pembayaran</button>
</form>
                </div>
            <?php elseif (isset($payment_confirmed)): ?>
                <div class="success-message">
                    <h2>Pembayaran Berhasil Dikonfirmasi!</h2>
                    <p>Terima kasih atas pembayaran Anda. Pesanan Anda akan segera diproses.</p>
                </div>
            <?php elseif (isset($payment_error)): ?>
                <div class="error-message">
                    <?php echo $payment_error; ?>
                </div>
            <?php else: ?>
                <?php if (!empty($error_message)): ?>
                    <div class="error-message">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form-pesanan">
                    <div class="form-group">
                        <label for="nama_pembeli">Nama Lengkap</label>
                        <input 
                            type="text" 
                            name="nama_pembeli" 
                            id="nama_pembeli"
                            class="form-control" 
                            placeholder="Masukkan nama lengkap" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="email_pembeli">Email</label>
                        <input 
                            type="email" 
                            name="email_pembeli" 
                            id="email_pembeli"
                            class="form-control" 
                            placeholder="Masukkan email" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="no_hp_pembeli">Nomor Handphone</label>
                        <input 
                            type="tel" 
                            name="no_hp_pembeli" 
                            id="no_hp_pembeli"
                            class="form-control" 
                            placeholder="Masukkan nomor HP" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="alamat_pembeli">Alamat Lengkap</label>
                        <textarea 
                            name="alamat_pembeli" 
                            id="alamat_pembeli"
                            class="form-control" 
                            placeholder="Masukkan alamat lengkap" 
                            required
                        ></textarea>
                    </div>

                    <?php if ($tampilkan_input_jumlah): ?>
            <div class="form-group">
                <label for="jumlah">Jumlah Pesanan</label>
                <input 
                    type="number" 
                    name="jumlah" 
                    id="jumlah"
                    min="1" 
                    max="<?php echo $produk['stok']; ?>" 
                    placeholder="Masukkan jumlah pesanan" 
                    class="form-control" 
                    value="<?php echo !empty($checkout_details) ? array_sum(array_column($checkout_details, 'jumlah')) : 1; ?>"
                    required
                >
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="jasa_kirim">Pilih Jasa Kirim</label>
                        <select 
                            name="jasa_kirim" 
                            id="jasa_kirim"
                            class="form-control" 
                            required
                        >
                            <option value="">Pilih Jasa Kirim</option>
                            <?php foreach ($jasa_kirim as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="metode_pembayaran">Metode Pembayaran</label>
                        <select 
                            name="metode_pembayaran" 
                            id="metode_pembayaran"
                            class="form-control" 
                            required
                        >
                            <option value="">Pilih Metode Pembayaran</option>
                            <?php foreach ($metode_pembayaran as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button 
                        type="submit" 
                        class="btn-pesan"
                        <?php echo $produk['stok'] == 0 ? 'disabled' : ''; ?>
                    >
                        <?php echo $produk['stok'] == 0 ? 'Stok Habis' : 'Konfirmasi Pesanan'; ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php
    // Tutup koneksi database
    debugLog("Selesai memproses halaman");
    mysqli_close($koneksi);
    ?>
</body>
</html>

