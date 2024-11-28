<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input URL</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
        include 'menu.php';
    ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-center">Input URL</h4>
                    </div>
                    <div class="card-body">
                        <?php
                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                require_once 'env.php';
                            
                                try {
                                    $pdo = getPdoInstance();
                                    $urls = explode("\n", trim($_POST['url'])); // Memisahkan berdasarkan baris                                    
                                                                
                                    $pesan = "";
                                    foreach ($urls as $url) {
                                        if (preg_match('/(https:\/\/tokopedia\.link\/[^\s]+)/', $url, $matches)) {
                                            // Menambahkan URL ke array hasil
                                            $url = trim($matches[0]);

                                            // if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                                            //     // Mempersiapkan query untuk memeriksa keberadaan URL
                                            //     $stmt = $pdo->prepare('SELECT COUNT(*) FROM tb_url WHERE url = :url');
                                            //     $stmt->execute(['url' => $url]);
                                            //     $exists = $stmt->fetchColumn();

                                            //     if ($exists > 0) {
                                            //         // Jika URL sudah ada, hapus data berdasarkan URL tersebut
                                            //         $stmt = $pdo->prepare('DELETE FROM tb_url WHERE url = :url');
                                            //         $stmt->execute(['url' => $url]);

                                            //         $pesan .= '<div class="alert alert-success">URL "' . htmlspecialchars($url) . '" sudah ada dan telah dihapus dari database.</div>';
                                            //     } else {
                                            //         // Jika URL tidak ada, simpan ke database
                                            //         // $stmt = $pdo->prepare('INSERT INTO tb_url (url, tgl, is_executed) VALUES (:url, :tgl, 0)');
                                            //         // $stmt->execute(['url' => $url, 'tgl' => date("Y/m/d")]);
                                                    
                                            //         $pesan .= '<div class="alert alert-success">URL "' . htmlspecialchars($url) . '" Tidak Ada sehingga tidak perlu dihapus</div>';
                                            //     }
                                            // }


                                            if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                                                $stmt = $pdo->prepare('SELECT COUNT(*) FROM tb_url WHERE url = :url');
                                                $stmt->execute(['url' => $url]);
                                                $exists = $stmt->fetchColumn();

                                                if ($exists > 0) {
                                                    $pesan .= '<div class="alert alert-warning">URL "' . htmlspecialchars($url) . '" sudah ada.</div>';
                                                } else {
                                                    // Jika URL tidak ada, simpan ke database
                                                    $stmt = $pdo->prepare('INSERT INTO tb_url (url, tgl, is_executed) VALUES (:url, :tgl, 0)');
                                                    $stmt->execute(['url' => $url, 'tgl' => date("Y/m/d")]);
                                                }
                                            }
                                        }
                                        
                                    }

                                    if ($pesan != ""){
                                        echo $pesan;
                                    } else {
                                        echo '<div class="alert alert-success">Semua URL berhasil disimpan.</div>';
                                    }
                                    
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger">Terjadi kesalahan: ' . $e->getMessage() . '</div>';
                                }
                            }
                        
                        ?>

                        <form method="POST" action="">
                        <div class="form-group">
                            <label for="url">URL:</label>
                            <textarea class="form-control" id="url" name="url" rows="5" placeholder="Masukkan URL, satu URL per baris" required></textarea>
                        </div>

                            
                            <button type="submit" class="btn btn-primary btn-block">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
