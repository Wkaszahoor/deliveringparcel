<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run()
    {
        $news    = BlogCategory::firstOrCreate(['slug' => 'company-news'],    ['name' => 'Company News']);
        $guides  = BlogCategory::firstOrCreate(['slug' => 'shipping-guides'], ['name' => 'Shipping Guides']);

        Blog::firstOrCreate(['slug' => 'welcome-to-the-deliveringparcel-blog'], [
            'title'            => 'Welcome to the DeliveringParcel Blog',
            'blog_category_id' => $news->id,
            'excerpt'          => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. See what we ship, where we ship it, and how we keep parcels safe.',
            'body'             => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae.</p>'
                                . '<p>Praesent sapien massa, convallis a pellentesque nec, egestas non nisi. Curabitur arcu erat, accumsan id imperdiet et, porttitor at sem. Vivamus magna justo, lacinia eget consectetur sed, convallis at tellus.</p>'
                                . '<h3>What to expect</h3><p>Quisque velit nisi, pretium ut lacinia in, elementum id enim. Donec rutrum congue leo eget malesuada. Pellentesque in ipsum id orci porta dapibus.</p>',
            'meta_title'       => 'DeliveringParcel Blog',
            'meta_description' => 'News, guides and shipping tips from the DeliveringParcel team.',
            'meta_keywords'    => 'shipping, parcel, logistics, blog',
            'status'           => 'published',
            'published_at'     => now(),
        ]);

        Blog::firstOrCreate(['slug' => 'how-to-package-fragile-items'], [
            'title'            => 'How to Package Fragile Items for International Shipping',
            'blog_category_id' => $guides->id,
            'excerpt'          => 'Lorem ipsum dolor sit amet — a short draft guide about bubble wrap, double boxing and customs labels.',
            'body'             => '<p>Sed porttitor lectus nibh. Cras ultricies ligula sed magna dictum porta. Nulla quis lorem ut libero malesuada feugiat.</p>'
                                . '<p>Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a. Vivamus suscipit tortor eget felis porttitor volutpat. Draft — more sections coming soon.</p>',
            'meta_title'       => 'How to Package Fragile Items',
            'meta_description' => 'A practical guide to packing fragile parcels for international delivery.',
            'meta_keywords'    => 'fragile, packaging, shipping guide',
            'status'           => 'draft',
            'published_at'     => null,
        ]);
    }
}
