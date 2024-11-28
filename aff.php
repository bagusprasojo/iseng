<?php
session_start();

require_once 'env.php'; // Sertakan file env.php

// Tentukan jumlah data per halaman
$limit = 10;

// Ambil nomor halaman saat ini dari URL, jika tidak ada default ke 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Process URLs</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
        include 'menu.php';
    ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <!-- <div class="card-header"> -->
                        <h4 class="card-title text-center">URL Processing</h4>
                    <!-- </div> -->
                    <div class="card-body">

                        <!-- Form input untuk filter tanggal -->
                        <form method="POST" action="aff.php">
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="filter_date">Pilih Tanggal</label>
                                </div>
                                <div class="col-md-6">                                    
                                        <input type="date" name="filter_date" id="filter_date" class="form-control" required>                                        
                                    
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary">Cari</button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary" id="copyAllUrls">Copy All URLs</button>                            
                                </div>

                            </div>
                        </form>

                        <?php
                        try {
                            // Ambil instance PDO dari env.php
                            $pdo = getPdoInstance();
                            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                            // Cek apakah user telah memasukkan tanggal melalui form
                            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                                if (isset($_POST['filter_date'])){
                                    $_SESSION['filter_date'] = $_POST['filter_date'];                                    
                                } else {
                                    $tgl_hari_ini = date('Y-m-d');
                                    $_SESSION['filter_date'] = $tgl_hari_ini;
                                }
                                
                                $filter_date = $_SESSION['filter_date'];                                
                            } else {
                                if (!isset($_SESSION['filter_date'])){
                                    $tgl_hari_ini = date('Y-m-d');
                                    $_SESSION['filter_date'] = $tgl_hari_ini;
                                }
                                $filter_date = $_SESSION['filter_date'];                                
                            }

                            // Query untuk menghitung total baris
                            $count_stmt = $pdo->prepare('SELECT COUNT(*) FROM tb_url WHERE DATE(tgl) = :filter_date');
                            $count_stmt->execute(['filter_date' => $filter_date]);
                            $total_rows = $count_stmt->fetchColumn();

                            // Query untuk mendapatkan URL berdasarkan tanggal input user dengan limit dan offset
                            $stmt = $pdo->prepare('SELECT id,url,tgl FROM tb_url WHERE DATE(tgl) = :filter_date LIMIT :limit OFFSET :offset');
                            $stmt->bindParam(':filter_date', $filter_date);
                            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                            $stmt->execute();
                            $urls = $stmt->fetchAll();

                            // Hitung jumlah halaman yang tersedia
                            $total_pages = ceil($total_rows / $limit);
                            ?>

                            <div class="container">
                                <table class="table table-bordered mt-3">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>URL</th>
                                            <th>Tanggal</th>
                                            <th>Aksi</th> <!-- Kolom aksi -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($urls): ?>
                                            <?php foreach ($urls as $index => $row): ?>
                                                <tr>
                                                    <td><?php echo $index + 1 + $offset; ?></td>
                                                    <td><a href="<?php echo htmlspecialchars($row['url']); ?>" target="_blank" class="url-link">
                                                        <?php echo htmlspecialchars($row['url']); ?>
                                                    </a></td>
                                                    <td><?php echo $row['tgl']; ?></td>
                                                    <td>
                                                        <!-- Tombol Hapus -->
                                                        <a href="delete.php?page=<?php echo $page?>&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this URL?');">Hapus</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No URLs found for the selected date.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>

                                </table>
                            </div>

                            <!-- Pagination links -->
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>

                            <?php

                        } catch (PDOException $e) {
                            echo '<div class="alert alert-danger">Connection failed: ' . $e->getMessage() . '</div>';
                        }
                        ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('copyAllUrls').addEventListener('click', function() {
            // Ambil semua elemen dengan class 'url-link'
            var urlElements = document.querySelectorAll('.url-link');
            var urls = [];

            // Loop melalui semua elemen dan ambil URL-nya
            urlElements.forEach(function(element) {
                urls.push(element.href);
            });

            // Gabungkan URL menjadi satu string dengan new line
            var urlText = urls.join('\n');

            // Buat elemen textarea untuk sementara
            var tempTextArea = document.createElement('textarea');
            tempTextArea.value = urlText;
            document.body.appendChild(tempTextArea);

            // Select dan copy text
            tempTextArea.select();
            document.execCommand('copy');

            // Hapus elemen textarea sementara
            document.body.removeChild(tempTextArea);

            // Beri notifikasi bahwa URL telah disalin
            alert('All URLs have been copied to clipboard!');
        });
    </script>


    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>


</html>
