<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(PurchaseService $purchases, SaleService $sales): void
    {
        // Akun demo (bukan kredensial asli). Password di-hash otomatis oleh model User.
        $admin = User::create([
            'name' => 'Admin Owner', 'email' => 'admin@example.com',
            'password' => 'admin12345', 'role' => 'admin',
        ]);
        $staff = User::create([
            'name' => 'Staff Toko', 'email' => 'staff@example.com',
            'password' => 'staff12345', 'role' => 'staff',
        ]);

        $cats = [];
        foreach (['Biji Kopi', 'Peralatan Seduh', 'Minuman Siap Saji', 'Susu & Bahan Tambahan', 'Camilan', 'Kemasan'] as $name) {
            $cats[$name] = Category::create(['name' => $name]);
        }

        $supplierA = Supplier::create(['name' => 'PT Nusantara Kopi', 'phone' => '081311110001', 'email' => 'sales@nusantarakopi.test']);
        $supplierB = Supplier::create(['name' => 'CV Cangkir Emas', 'phone' => '082322220002', 'email' => 'info@cangkiremas.test']);
        $supplierC = Supplier::create(['name' => 'UD Tirta Segar', 'phone' => '083833330003', 'email' => 'order@tirtasegar.test']);

        // Produk dibuat dengan stok 0. Stok awal masuk lewat transaksi pembelian,
        // jadi stok selalu sesuai dengan riwayat transaksi. SKU unik untuk setiap produk.
        // [kategori, sku, nama, harga beli, harga jual, stok minimum, stok awal]
        $rows = [
            ['Biji Kopi', 'KOP-001', 'Kopi Arabika Gayo 250g', 55000, 78000, 8, 30],
            ['Biji Kopi', 'KOP-002', 'Kopi Robusta Lampung 250g', 38000, 55000, 8, 30],
            ['Biji Kopi', 'KOP-003', 'Kopi Toraja Sapan 250g', 60000, 85000, 6, 20],
            ['Biji Kopi', 'KOP-004', 'Kopi Bubuk Bali 200g', 32000, 47000, 8, 25],
            ['Peralatan Seduh', 'SDH-001', 'Dripper V60 Keramik', 85000, 125000, 4, 10],
            ['Peralatan Seduh', 'SDH-002', 'Kertas Filter V60 (isi 100)', 22000, 35000, 8, 30],
            ['Peralatan Seduh', 'SDH-003', 'Teko Leher Angsa 450ml', 150000, 210000, 3, 6],
            ['Peralatan Seduh', 'SDH-004', 'Timbangan Kopi Digital', 120000, 175000, 3, 8],
            ['Peralatan Seduh', 'SDH-005', 'French Press 600ml', 95000, 140000, 4, 10],
            ['Minuman Siap Saji', 'MSS-001', 'Cold Brew Botol 250ml', 14000, 22000, 10, 40],
            ['Minuman Siap Saji', 'MSS-002', 'Es Kopi Susu Gula Aren', 11000, 18000, 10, 36],
            ['Minuman Siap Saji', 'MSS-003', 'Teh Tarik Instan Sachet', 3500, 6000, 15, 60],
            ['Minuman Siap Saji', 'MSS-004', 'Cokelat Panas Sachet', 4000, 7000, 15, 60],
            ['Susu & Bahan Tambahan', 'BHN-001', 'Susu UHT Full Cream 1L', 17000, 24000, 10, 36],
            ['Susu & Bahan Tambahan', 'BHN-002', 'Sirup Karamel 750ml', 48000, 68000, 5, 15],
            ['Susu & Bahan Tambahan', 'BHN-003', 'Sirup Vanila 750ml', 48000, 68000, 5, 15],
            ['Susu & Bahan Tambahan', 'BHN-004', 'Gula Aren Cair 500ml', 21000, 32000, 6, 20],
            ['Camilan', 'CML-001', 'Croissant Mentega', 9000, 15000, 8, 24],
            ['Camilan', 'CML-002', 'Brownies Cokelat Potong', 7000, 12000, 8, 30],
            ['Camilan', 'CML-003', 'Cookies Oat Kismis', 6500, 11000, 8, 30],
            ['Camilan', 'CML-004', 'Roti Bakar Cokelat Keju', 8000, 14000, 8, 20],
            ['Kemasan', 'KMS-001', 'Gelas Kertas 8oz (isi 50)', 18000, 27000, 6, 20],
            ['Kemasan', 'KMS-002', 'Tutup Gelas 8oz (isi 50)', 9000, 15000, 6, 20],
            ['Kemasan', 'KMS-003', 'Sedotan Kertas (isi 100)', 12000, 19000, 6, 20],
        ];

        $products = [];
        $stockIn = [];
        foreach ($rows as [$catName, $sku, $name, $buy, $sell, $min, $qty]) {
            $products[$sku] = Product::create([
                'category_id' => $cats[$catName]->id, 'sku' => $sku, 'name' => $name,
                'purchase_price' => $buy, 'selling_price' => $sell,
                'stock' => 0, 'minimum_stock' => $min,
            ]);
            $stockIn[$sku] = ['product_id' => $products[$sku]->id, 'quantity' => $qty, 'unit_cost' => $buy];
        }

        $pick = fn (array $skus) => array_values(array_intersect_key($stockIn, array_flip($skus)));

        $purchases->create($admin, $supplierA->id, $pick(['KOP-001', 'KOP-002', 'KOP-003', 'KOP-004', 'SDH-001', 'SDH-002', 'SDH-003', 'SDH-004', 'SDH-005']));
        $purchases->create($admin, $supplierB->id, $pick(['MSS-001', 'MSS-002', 'MSS-003', 'MSS-004', 'BHN-001', 'BHN-002', 'BHN-003', 'BHN-004']));
        $purchases->create($admin, $supplierC->id, $pick(['CML-001', 'CML-002', 'CML-003', 'CML-004', 'KMS-001', 'KMS-002', 'KMS-003']));

        // Beberapa penjualan contoh. Teko Leher Angsa sengaja dibuat hampir habis (stok rendah).
        $sales->create($staff, [
            ['product_id' => $products['SDH-003']->id, 'quantity' => 4],
            ['product_id' => $products['MSS-001']->id, 'quantity' => 10],
        ]);
        $sales->create($admin, [
            ['product_id' => $products['KOP-001']->id, 'quantity' => 3],
            ['product_id' => $products['CML-001']->id, 'quantity' => 6],
        ]);
    }
}
