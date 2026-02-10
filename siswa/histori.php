<?php
include '../config.php';
$nis = isset($_GET['nis']) ? input($_GET['nis']) : '';
?>
<!DOCTYPE html>
<html>
<head><title>Histori Aspirasi</title></head>
<body>
    <h2>Histori Aspirasi Anda (NIS: <?= $nis ?>)</h2>
    <table border="1">
        <tr>
            <th>Kategori</th>
            <th>Lokasi</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th>Umpan Balik Admin</th>
        </tr>
        <?php
        if ($nis != '') {
            $query = "SELECT i.*, a.status, a.feedback, k.ket_kategori 
                      FROM Input_Aspirasi i
                      JOIN Aspirasi a ON i.id_pelaporan = a.id_pelaporan
                      JOIN Kategori k ON i.id_kategori = k.id_kategori
                      WHERE i.nis = '$nis' ORDER BY i.id_pelaporan DESC";
            $res = mysqli_query($conn, $query);
            while ($row = mysqli_fetch_assoc($res)) {
                echo "<tr>
                    <td>{$row['ket_kategori']}</td>
                    <td>{$row['lokasi']}</td>
                    <td>{$row['ket']}</td>
                    <td><b>{$row['status']}</b></td>
                    <td>" . ($row['feedback'] ? $row['feedback'] : '-') . "</td>
                </tr>";
            }
        }
        ?>
    </table>
    <br><a href="lapor.php">Kembali ke Form</a>
</body>
</html>