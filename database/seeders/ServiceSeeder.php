<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $express    = ServiceCategory::firstOrCreate(['slug' => 'express-delivery'], ['name' => 'Express Delivery', 'icon_class' => 'fas fa-shipping-fast']);
        $freight    = ServiceCategory::firstOrCreate(['slug' => 'freight-cargo'],    ['name' => 'Freight & Cargo',   'icon_class' => 'fas fa-truck-loading']);
        $warehousing = ServiceCategory::firstOrCreate(['slug' => 'warehousing'],      ['name' => 'Warehousing',        'icon_class' => 'fas fa-warehouse']);

        $services = [
            [
                'slug' => 'express-parcel-delivery', 'title' => 'Express Parcel Delivery',
                'service_category_id' => $express->id, 'type' => 'express', 'price' => 24.99, 'sort' => 1,
                'short_desc' => 'Lorem ipsum dolor sit amet — next-day parcel delivery to major cities.',
                'description' => '<p>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae. Donec velit neque, auctor sit amet aliquam vel, ullamcorper sit amet ligula.</p><p>Praesent sapien massa, convallis a pellentesque nec, egestas non nisi.</p>',
            ],
            [
                'slug' => 'international-standard-shipping', 'title' => 'International Standard Shipping',
                'service_category_id' => $express->id, 'type' => 'international', 'price' => 39.50, 'sort' => 2,
                'short_desc' => 'Curabitur arcu erat — affordable worldwide delivery in 5-10 business days.',
                'description' => '<p>Curabitur non nulla sit amet nisl tempus convallis quis ac lectus. Vivamus magna justo, lacinia eget consectetur sed, convallis at tellus.</p>',
            ],
            [
                'slug' => 'air-and-sea-freight', 'title' => 'Air & Sea Freight',
                'service_category_id' => $freight->id, 'type' => 'freight', 'price' => 120.00, 'sort' => 3,
                'short_desc' => 'Quisque velit nisi — palletised freight by air or sea.',
                'description' => '<p>Pellentesque in ipsum id orci porta dapibus. Nulla porttitor accumsan tincidunt. Quisque velit nisi, pretium ut lacinia in, elementum id enim.</p>',
            ],
            [
                'slug' => 'secure-warehousing-storage', 'title' => 'Secure Warehousing & Storage',
                'service_category_id' => $warehousing->id, 'type' => 'warehousing', 'price' => null, 'sort' => 4,
                'short_desc' => 'Mauris blandit aliquet elit — flexible storage with pick and pack (quote on request).',
                'description' => '<p>Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a. Vivamus suscipit tortor eget felis porttitor volutpat. Contact our team for a tailored quote.</p>',
            ],
        ];

        foreach ($services as $data) {
            Service::firstOrCreate(
                ['slug' => $data['slug']],
                $data + ['is_available' => true]
            );
        }
    }
}
