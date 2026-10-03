<?php
// config.php — koneksi database SQLite + auto-buat tabel + seed data awal
// Amels Beauty - Aplikasi Kasir & Manajemen Stok

session_start();

$dbPath = __DIR__ . '/data/amels.sqlite';
$isNew = !file_exists($dbPath);

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage() . '<br>Pastikan ekstensi pdo_sqlite aktif di PHP kamu.');
}

if ($isNew) {
    $pdo->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL CHECK(role IN ('owner','kasir')),
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE layanan (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_layanan TEXT NOT NULL,
            kategori TEXT NOT NULL,
            harga INTEGER NOT NULL DEFAULT 0
        );

        CREATE TABLE bahan (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_bahan TEXT NOT NULL,
            kategori_layanan TEXT,
            satuan TEXT NOT NULL DEFAULT 'pcs',
            stok REAL NOT NULL DEFAULT 0,
            stok_minimum REAL NOT NULL DEFAULT 0
        );

        CREATE TABLE alat (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama_alat TEXT NOT NULL,
            jenis TEXT NOT NULL CHECK(jenis IN ('kecil','elektronik')),
            kondisi TEXT NOT NULL DEFAULT 'baik' CHECK(kondisi IN ('baik','perlu servis','rusak')),
            tanggal_servis_terakhir TEXT,
            tanggal_servis_berikutnya TEXT
        );

        CREATE TABLE layanan_bahan (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            layanan_id INTEGER NOT NULL,
            bahan_id INTEGER NOT NULL,
            jumlah_terpakai REAL NOT NULL DEFAULT 0,
            FOREIGN KEY (layanan_id) REFERENCES layanan(id) ON DELETE CASCADE,
            FOREIGN KEY (bahan_id) REFERENCES bahan(id) ON DELETE CASCADE
        );

        CREATE TABLE transaksi (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tanggal TEXT NOT NULL,
            kasir_id INTEGER NOT NULL,
            total INTEGER NOT NULL DEFAULT 0,
            metode_bayar TEXT NOT NULL CHECK(metode_bayar IN ('tunai','qris','edc')),
            status_pembayaran TEXT NOT NULL DEFAULT 'lunas',
            uang_diterima INTEGER,
            kembalian INTEGER,
            FOREIGN KEY (kasir_id) REFERENCES users(id)
        );

        CREATE TABLE transaksi_detail (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            transaksi_id INTEGER NOT NULL,
            layanan_id INTEGER NOT NULL,
            nama_layanan TEXT NOT NULL,
            harga_satuan INTEGER NOT NULL,
            jumlah INTEGER NOT NULL DEFAULT 1,
            subtotal INTEGER NOT NULL,
            FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
            FOREIGN KEY (layanan_id) REFERENCES layanan(id)
        );

        CREATE TABLE pengeluaran (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tanggal TEXT NOT NULL,
            keterangan TEXT NOT NULL,
            kategori TEXT NOT NULL,
            jumlah INTEGER NOT NULL DEFAULT 0
        );
    ");

    // ---- Seed users ----
    $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (?,?,?,?)")
        ->execute(['Yeni', 'owner', password_hash('owner123', PASSWORD_DEFAULT), 'owner']);
    $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (?,?,?,?)")
        ->execute(['Cantika', 'kasir', password_hash('kasir123', PASSWORD_DEFAULT), 'kasir']);

    // ---- Seed layanan (kategori, nama, harga) — sesuai price list Amels Beauty ----
    $layanan = [
        // Eyelash
        ['Eyelash', 'Classic', 100000],
        ['Eyelash', 'Natural', 100000],
        ['Eyelash', 'Volume', 130000],
        ['Eyelash', 'Wispy', 130000],
        ['Eyelash', 'Cat Eye', 120000],
        ['Eyelash', 'Doll Eye', 120000],
        ['Eyelash', 'Lash Lift', 80000],
        // Nail
        ['Nail', 'Manicure', 30000],
        ['Nail', 'Pedicure', 40000],
        ['Nail', 'Gel Polish', 50000],
        ['Nail', 'Nail Art', 50000],
        ['Nail', 'Nail Extension', 100000],
        ['Nail', 'Soft Gel', 100000],
        ['Nail', 'Tip Extension', 80000],
        ['Nail', 'French Tip', 70000],
        ['Nail', 'Chrome', 70000],
        ['Nail', 'Cat Eye', 70000],
        ['Nail', 'Matte', 60000],
        ['Nail', 'Glossy', 50000],
        ['Nail', 'Nail Removal', 20000],
        // Smoothing - Matrix
        ['Smoothing', 'Matrix - Pendek', 200000],
        ['Smoothing', 'Matrix - Sedang', 250000],
        ['Smoothing', 'Matrix - Panjang', 300000],
        ['Smoothing', 'Matrix - Extra Panjang', 350000],
        // Smoothing - Keratin
        ['Smoothing', 'Keratin - Pendek', 250000],
        ['Smoothing', 'Keratin - Sedang', 300000],
        ['Smoothing', 'Keratin - Panjang', 400000],
        ['Smoothing', 'Keratin - Extra Panjang', 500000],
        // Coloring - Full Coloring
        ['Coloring Rambut', 'Full Coloring - Pendek', 150000],
        ['Coloring Rambut', 'Full Coloring - Sedang', 200000],
        ['Coloring Rambut', 'Full Coloring - Panjang', 250000],
        ['Coloring Rambut', 'Full Coloring - Extra Panjang', 300000],
        // Coloring - Highlight
        ['Coloring Rambut', 'Highlight - Pendek', 200000],
        ['Coloring Rambut', 'Highlight - Sedang', 250000],
        ['Coloring Rambut', 'Highlight - Panjang', 300000],
        ['Coloring Rambut', 'Highlight - Extra Panjang', 350000],
        // Coloring - Balayage
        ['Coloring Rambut', 'Balayage - Pendek', 250000],
        ['Coloring Rambut', 'Balayage - Sedang', 300000],
        ['Coloring Rambut', 'Balayage - Panjang', 400000],
        ['Coloring Rambut', 'Balayage - Extra Panjang', 500000],
        // Coloring - Ombre
        ['Coloring Rambut', 'Ombre - Pendek', 200000],
        ['Coloring Rambut', 'Ombre - Sedang', 250000],
        ['Coloring Rambut', 'Ombre - Panjang', 350000],
        ['Coloring Rambut', 'Ombre - Extra Panjang', 450000],
        // Coloring - Bleaching
        ['Coloring Rambut', 'Bleaching - Pendek', 150000],
        ['Coloring Rambut', 'Bleaching - Sedang', 200000],
        ['Coloring Rambut', 'Bleaching - Panjang', 300000],
        ['Coloring Rambut', 'Bleaching - Extra Panjang', 400000],
        // Coloring - Toner
        ['Coloring Rambut', 'Toner - Pendek', 100000],
        ['Coloring Rambut', 'Toner - Sedang', 125000],
        ['Coloring Rambut', 'Toner - Panjang', 150000],
        ['Coloring Rambut', 'Toner - Extra Panjang', 200000],
        // Masker Rambut
        ['Masker Rambut', 'Hair Mask', 50000],
        ['Masker Rambut', 'Creambath', 50000],
        ['Masker Rambut', 'Keratin Hair Treatment', 150000],
        // Sulam Alis
        ['Sulam Alis', 'Sulam Alis', 500000],
    ];
    $stmtL = $pdo->prepare("INSERT INTO layanan (kategori, nama_layanan, harga) VALUES (?,?,?)");
    foreach ($layanan as $l) $stmtL->execute($l);

    // ---- Seed bahan (nama, kategori_layanan, satuan, stok, stok_minimum) ----
    $bahan = [
        ['Eyelash Extensions', 'Eyelash', 'tray', 20, 5],
        ['Lem Eyelash', 'Eyelash', 'pcs', 8, 3],
        ['Remover Eyelash', 'Eyelash', 'pcs', 6, 2],
        ['Eyelash Tape', 'Eyelash', 'roll', 10, 3],
        ['Under Eye Patch', 'Eyelash', 'pack', 15, 5],
        ['Nail Polish', 'Nail', 'botol', 25, 5],
        ['Gel Polish', 'Nail', 'botol', 20, 5],
        ['Nail Glue', 'Nail', 'pcs', 10, 3],
        ['Top Coat', 'Nail', 'botol', 12, 3],
        ['Base Coat', 'Nail', 'botol', 12, 3],
        ['Smoothing Cream', 'Smoothing', 'ml', 4000, 800],
        ['Neutralizer', 'Smoothing', 'ml', 4000, 800],
        ['Keratin Cream', 'Smoothing', 'ml', 3000, 600],
        ['Cat Rambut', 'Coloring Rambut', 'ml', 3000, 500],
        ['Developer/Oxidant', 'Coloring Rambut', 'ml', 3000, 500],
        ['Bleaching Powder', 'Coloring Rambut', 'gr', 2000, 400],
        ['Toner Rambut', 'Coloring Rambut', 'ml', 1500, 300],
        ['Shampoo', 'Masker Rambut', 'ml', 3000, 500],
        ['Conditioner', 'Masker Rambut', 'ml', 2500, 500],
        ['Masker Rambut', 'Masker Rambut', 'ml', 2000, 400],
        ['Hair Vitamin', 'Masker Rambut', 'ml', 1500, 300],
        ['Pigment Alis', 'Sulam Alis', 'botol', 6, 2],
        ['Numbing Cream', 'Sulam Alis', 'pcs', 5, 2],
    ];
    $stmtB = $pdo->prepare("INSERT INTO bahan (nama_bahan, kategori_layanan, satuan, stok, stok_minimum) VALUES (?,?,?,?,?)");
    foreach ($bahan as $b) $stmtB->execute($b);

    // ---- Seed alat ----
    $alat = [
        ['Pinset', 'kecil', 'baik', null, null],
        ['Hair Dryer', 'elektronik', 'baik', '2026-06-01', '2026-12-01'],
        ['Catokan', 'elektronik', 'baik', '2026-05-15', '2026-11-15'],
        ['Hair Steamer', 'elektronik', 'perlu servis', '2026-01-10', '2026-07-10'],
        ['UV/LED Nail Lamp', 'elektronik', 'baik', '2026-04-01', '2026-10-01'],
        ['Nail Drill', 'elektronik', 'baik', '2026-03-01', '2026-09-01'],
        ['Alat Sulam Alis', 'elektronik', 'baik', '2026-02-01', '2026-08-01'],
    ];
    $stmtA = $pdo->prepare("INSERT INTO alat (nama_alat, jenis, kondisi, tanggal_servis_terakhir, tanggal_servis_berikutnya) VALUES (?,?,?,?,?)");
    foreach ($alat as $a) $stmtA->execute($a);

    // ---- Seed layanan_bahan (pemakaian bahan per layanan, untuk auto-kurangi stok) ----
    // Dipetakan per kategori (dan sedikit per nama sub-layanan untuk Coloring), supaya
    // realistis tanpa harus detail satu-satu untuk 50+ sub layanan.
    $bahanIds = $pdo->query("SELECT id, nama_bahan FROM bahan")->fetchAll(PDO::FETCH_KEY_PAIR);
    $namaToBahanId = array_flip($bahanIds);
    $stmtLB = $pdo->prepare("INSERT INTO layanan_bahan (layanan_id, bahan_id, jumlah_terpakai) VALUES (?,?,?)");

    $allLayanan = $pdo->query("SELECT id, nama_layanan, kategori FROM layanan")->fetchAll(PDO::FETCH_ASSOC);

    function tambahPemakaian($stmt, $layananId, $namaToBahanId, $pasangan) {
        foreach ($pasangan as $namaBahan => $qty) {
            if (isset($namaToBahanId[$namaBahan])) {
                $stmt->execute([$layananId, $namaToBahanId[$namaBahan], $qty]);
            }
        }
    }

    foreach ($allLayanan as $l) {
        switch ($l['kategori']) {
            case 'Eyelash':
                tambahPemakaian($stmtLB, $l['id'], $namaToBahanId, [
                    'Eyelash Extensions' => 1, 'Lem Eyelash' => 0.1, 'Under Eye Patch' => 1,
                ]);
                break;
            case 'Nail':
                tambahPemakaian($stmtLB, $l['id'], $namaToBahanId, [
                    'Nail Polish' => 1, 'Top Coat' => 0.2, 'Base Coat' => 0.2,
                ]);
                break;
            case 'Smoothing':
                if (str_contains($l['nama_layanan'], 'Keratin')) {
                    tambahPemakaian($stmtLB, $l['id'], $namaToBahanId, ['Keratin Cream' => 100]);
                } else {
                    tambahPemakaian($stmtLB, $l['id'], $namaToBahanId, [
                        'Smoothing Cream' => 100, 'Neutralizer' => 100,
                    ]);
                }
                break;
            case 'Coloring Rambut':
                if (str_contains($l['nama_layanan'], 'Bleaching')) {
                    tambahPemakaian($stmtLB, $l['id'], $namaToBahanId, ['Bleaching Powder' => 50]);
                } elseif (str_contains($l['nama_layanan'], 'Toner')) {
                    tambahPemakaian($stmtLB, $l['id'], $namaToBahanId, ['Toner Rambut' => 80]);
                } else {
                    tambahPemakaian($stmtLB, $l['id'], $namaToBahanId, [
                        'Cat Rambut' => 80, 'Developer/Oxidant' => 80,
                    ]);
                }
                break;
            case 'Masker Rambut':
                tambahPemakaian($stmtLB, $l['id'], $namaToBahanId, [
                    'Shampoo' => 30, 'Masker Rambut' => 50,
                ]);
                break;
            case 'Sulam Alis':
                tambahPemakaian($stmtLB, $l['id'], $namaToBahanId, [
                    'Pigment Alis' => 1, 'Numbing Cream' => 1,
                ]);
                break;
        }
    }

    // ---- Seed pengeluaran contoh ----
    $pdo->prepare("INSERT INTO pengeluaran (tanggal, keterangan, kategori, jumlah) VALUES (?,?,?,?)")
        ->execute([date('Y-m-d'), 'Belanja lem eyelash & bulu mata', 'belanja bahan', 250000]);
}

// ---- Migrasi ringan: tambah kolom nomor_wa & nama_pelanggan kalau belum ada ----
$cols = $pdo->query("PRAGMA table_info(transaksi)")->fetchAll(PDO::FETCH_ASSOC);
$existingCols = array_column($cols, 'name');
if (!in_array('nomor_wa', $existingCols)) {
    $pdo->exec("ALTER TABLE transaksi ADD COLUMN nomor_wa TEXT");
}
if (!in_array('nama_pelanggan', $existingCols)) {
    $pdo->exec("ALTER TABLE transaksi ADD COLUMN nama_pelanggan TEXT");
}

function formatNomorWA($nomor) {
    $nomor = preg_replace('/\D/', '', $nomor ?? '');
    if ($nomor === '') return '';
    if (substr($nomor, 0, 1) === '0') {
        $nomor = '62' . substr($nomor, 1);
    } elseif (substr($nomor, 0, 2) !== '62') {
        $nomor = '62' . $nomor;
    }
    return $nomor;
}

function rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function kategoriIcon($kategori) {
    $map = [
        'Eyelash' => 'eyelash',
        'Nail' => 'nail',
        'Smoothing' => 'hair',
        'Coloring Rambut' => 'coloring',
        'Masker Rambut' => 'mask',
        'Sulam Alis' => 'brow',
    ];
    return $map[$kategori] ?? 'hair';
}
