<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Marketplace\Infrastructure\Models\ProductModel;

class SeedMarketplaceProducts extends Command
{
    protected $signature = 'marketplace:seed';
    protected $description = 'Seed the marketplace with demo products';

    public function handle()
    {
        $items = [
            ['name' => 'Hoodie 1337', 'description' => 'Sweat à capuche premium brodé du logo 1337. Coton 100%, coupe streetwear.', 'price' => 500, 'quantity' => 20, 'image' => 'https://images.unsplash.com/photo-1556821840-3a63f15732ce?w=400&q=80'],
            ['name' => 'Mug Développeur', 'description' => 'Mug céramique 350ml avec le motto "Code. Sleep. Repeat." en édition limitée.', 'price' => 150, 'quantity' => 50, 'image' => 'https://images.unsplash.com/photo-1514228742587-6b1558fcca3d?w=400&q=80'],
            ['name' => 'T-Shirt Nadi', 'description' => 'T-shirt coupe regular avec logo Nadi sur le cœur. Matière bio-coton premium.', 'price' => 250, 'quantity' => 35, 'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&q=80'],
            ['name' => 'Casque Pro Dev', 'description' => 'Casque audio sans-fil avec réduction de bruit active. Parfait pour les sessions de code.', 'price' => 800, 'quantity' => 10, 'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&q=80'],
            ['name' => 'Sticker Pack 1337', 'description' => 'Pack de 15 autocollants tech: terminaux, langages, frameworks. Pour votre laptop.', 'price' => 50, 'quantity' => 100, 'image' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=400&q=80'],
            ['name' => 'Clé USB 64GB', 'description' => 'Clé USB 3.1 Gen 2 ultra-rapide 64Go. Design aluminium avec logo 1337.', 'price' => 200, 'quantity' => 30, 'image' => 'https://images.unsplash.com/photo-1618517351616-38fb9c5210c6?w=400&q=80'],
        ];

        foreach ($items as $item) {
            ProductModel::firstOrCreate(['name' => $item['name']], $item);
        }

        $this->info('✅ Done: ' . ProductModel::count() . ' products in the marketplace.');
    }
}
