<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->ofType(Category::TYPE_PRODUCT)->get()->keyBy('name');
        $imageMap = [
            'COF-ESP-01' => '/images/menu/espresso.png',
            'COF-AME-01' => '/images/menu/iced-americano.png',
            'COF-LAT-01' => '/images/menu/latte.png',
            'COF-CAP-01' => '/images/menu/cappuccino.png',
            'COF-SCL-01' => '/images/menu/iced-americano.png',
            'COF-PLL-01' => '/images/menu/latte.png',
            'NCF-CHO-01' => '/images/menu/chocolate.png',
            'NCF-MAT-01' => '/images/menu/chocolate.png',
            'TEA-LYC-01' => '/images/menu/tea.png',
            'TEA-LEM-01' => '/images/menu/tea.png',
            'PST-CRS-01' => '/images/menu/croissant.png',
            'PST-CRN-01' => '/images/menu/croissant.png',
            'PST-CKC-01' => '/images/menu/croissant.png',
            'FOD-RCB-01' => '/images/menu/rice-bowl.png',
            'FOD-CRB-01' => '/images/menu/rice-bowl.png',
            'SNK-FRF-01' => '/images/menu/rice-bowl.png',
            'SNK-CKN-01' => '/images/menu/rice-bowl.png',
        ];

        $catalog = [
            ['barcode' => 'COF-ESP-01', 'name' => 'Espresso Double Shot', 'category' => 'Coffee', 'cost' => 8000, 'price' => 18000, 'min' => 10],
            ['barcode' => 'COF-AME-01', 'name' => 'Iced Americano', 'category' => 'Coffee', 'cost' => 7000, 'price' => 22000, 'min' => 10],
            ['barcode' => 'COF-LAT-01', 'name' => 'Cafe Latte', 'category' => 'Coffee', 'cost' => 12000, 'price' => 29000, 'min' => 12],
            ['barcode' => 'COF-CAP-01', 'name' => 'Classic Cappuccino', 'category' => 'Coffee', 'cost' => 12500, 'price' => 30000, 'min' => 12],
            ['barcode' => 'COF-SCL-01', 'name' => 'Salted Caramel Latte Ice', 'category' => 'Coffee', 'cost' => 14000, 'price' => 32000, 'min' => 12],
            ['barcode' => 'COF-PLL-01', 'name' => 'Pandan Latte', 'category' => 'Coffee', 'cost' => 14500, 'price' => 33000, 'min' => 10],
            ['barcode' => 'NCF-CHO-01', 'name' => 'Belgian Chocolate', 'category' => 'Non-Coffee', 'cost' => 13000, 'price' => 30000, 'min' => 10],
            ['barcode' => 'NCF-MAT-01', 'name' => 'Matcha Cloud', 'category' => 'Non-Coffee', 'cost' => 15000, 'price' => 34000, 'min' => 10],
            ['barcode' => 'TEA-LYC-01', 'name' => 'Lychee Tea', 'category' => 'Tea', 'cost' => 9000, 'price' => 25000, 'min' => 8],
            ['barcode' => 'TEA-LEM-01', 'name' => 'Lemon Earl Grey', 'category' => 'Tea', 'cost' => 9500, 'price' => 26000, 'min' => 8],
            ['barcode' => 'PST-CRS-01', 'name' => 'Croissant Butter Almond', 'category' => 'Pastry', 'cost' => 12000, 'price' => 25000, 'min' => 7],
            ['barcode' => 'PST-CRN-01', 'name' => 'Cinnamon Roll', 'category' => 'Pastry', 'cost' => 11000, 'price' => 24000, 'min' => 7],
            ['barcode' => 'PST-CKC-01', 'name' => 'Burnt Cheesecake Slice', 'category' => 'Pastry', 'cost' => 18000, 'price' => 38000, 'min' => 6],
            ['barcode' => 'FOD-RCB-01', 'name' => 'Rice Bowl Chicken Sambal Matah', 'category' => 'Main Course', 'cost' => 20000, 'price' => 42000, 'min' => 8],
            ['barcode' => 'FOD-CRB-01', 'name' => 'Creamy Carbonara', 'category' => 'Main Course', 'cost' => 23000, 'price' => 48000, 'min' => 8],
            ['barcode' => 'SNK-FRF-01', 'name' => 'Truffle French Fries', 'category' => 'Snacks', 'cost' => 13000, 'price' => 29000, 'min' => 10],
            ['barcode' => 'SNK-CKN-01', 'name' => 'Crispy Chicken Bites', 'category' => 'Snacks', 'cost' => 16000, 'price' => 34000, 'min' => 10],
        ];

        $images = [
            'COF-ESP-01' => '/images/menu/espresso.webp',
            'COF-AME-01' => '/images/menu/iced-americano.webp',
            'COF-LAT-01' => '/images/menu/latte.webp',
            'COF-CAP-01' => '/images/menu/cappuccino.webp',
            'COF-SCL-01' => '/images/menu/iced-americano.webp',
            'COF-PLL-01' => '/images/menu/latte.webp',
            'NCF-CHO-01' => '/images/menu/chocolate.webp',
            'NCF-MAT-01' => '/images/menu/chocolate.webp',
            'TEA-LYC-01' => '/images/menu/tea.webp',
            'TEA-LEM-01' => '/images/menu/tea.webp',
            'PST-CRS-01' => '/images/menu/croissant.webp',
            'PST-CRN-01' => '/images/menu/croissant.webp',
            'PST-CKC-01' => '/images/menu/croissant.webp',
            'FOD-RCB-01' => '/images/menu/rice-bowl.webp',
            'FOD-CRB-01' => '/images/menu/rice-bowl.webp',
            'SNK-FRF-01' => '/images/menu/rice-bowl.webp',
            'SNK-CKN-01' => '/images/menu/rice-bowl.webp',
        ];

        foreach (Outlet::query()->get() as $outletIndex => $outlet) {
            foreach ($catalog as $index => $item) {
                $multiplier = 1 + ($outletIndex * 0.025);
                $stock = 450 + (($index * 31 + $outletIndex * 47) % 250);
                if (in_array($item['barcode'], ['PST-CRS-01', 'PST-CKC-01'], true) && $outlet->code === 'UTAMA') {
                    $stock = 5;
                }

                $product = Product::query()->updateOrCreate([
                    'outlet_id' => $outlet->id,
                    'barcode' => $item['barcode'],
                ], [
                    'category_id' => $categories[$item['category']]->id,
                    'sku' => sprintf('%s-%03d', $outlet->code, $index + 1),
                    'name' => $item['name'],
                    'description' => 'Menu unggulan Where Coffee yang dibuat menggunakan bahan berkualitas dan resep terstandar.',
                    'cost_price' => round($item['cost'] * $multiplier, -2),
                    'selling_price' => round($item['price'] * $multiplier, -2),
                    'stock' => $stock,
                    'min_stock' => $item['min'],
                    'unit' => 'porsi',
                    'image_url' => $images[$item['barcode']] ?? null,
                    'is_active' => true,
                ]);

                StockMovement::query()->updateOrCreate([
                    'product_id' => $product->id,
                    'type' => 'initial',
                    'reference' => 'DEMO-SEED',
                ], [
                    'outlet_id' => $outlet->id,
                    'quantity_change' => $stock,
                    'stock_before' => 0,
                    'stock_after' => $stock,
                    'notes' => 'Stok awal dari seeder demo',
                ]);
            }
        }
    }
}
