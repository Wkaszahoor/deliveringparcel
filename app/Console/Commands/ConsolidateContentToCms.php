<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * CMS ENDGAME (2026-09-19): one-way, idempotent migration of the duplicate
 * content stores into cms_posts — the single content system.
 *
 *   blogs        (2)   → post_type=blog_post
 *   blog_posts   (92)  → post_type=blog_post
 *   services     (4)   → post_type=service
 *   shop_products (3)  → post_type=product
 *
 * Each row is stamped meta.legacy_ref ("blogs:3") so re-runs skip anything
 * already migrated. Source tables are NOT touched — read-only harvest.
 * Existing shop cart/checkout still reads shop_products until the shop
 * public swap (intentionally out of scope: cart model relations).
 */
class ConsolidateContentToCms extends Command
{
    protected $signature = 'cms:consolidate-content {--dry : show what would migrate}';

    protected $description = 'Migrate blogs, blog_posts, services and shop_products rows into cms_posts (idempotent)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');

        $n = 0;
        $n += $this->migrate('blogs', 'blog_post', function ($r) {
            return [
                'title' => $r->title,
                'excerpt' => $r->excerpt,
                'content' => $r->body,
                'featured_image' => $r->cover_image,
                'status' => $r->status === 'published' ? 'published' : 'draft',
                'published_at' => $r->published_at,
                'meta_title' => $r->meta_title,
                'meta_description' => $r->meta_description,
                'meta_keywords' => $r->meta_keywords,
                'meta_extra' => ['legacy_category_id' => $r->blog_category_id],
            ];
        });
        $n += $this->migrate('blog_posts', 'blog_post', function ($r) {
            return [
                'title' => $r->title,
                'excerpt' => $r->excerpt,
                'content' => $r->content,
                'featured_image' => $r->featured_image_url ?: $r->featured_image,
                'status' => $r->status === 'published' ? 'published' : 'draft',
                'published_at' => $r->published_at,
                'meta_title' => $r->seo_title,
                'meta_description' => $r->seo_description,
                'meta_keywords' => $r->seo_keywords,
                'view_count' => (int) $r->view_count,
                'meta_extra' => [
                    'author_name' => $r->author_name,
                    'source_url' => $r->source_url,
                    'import_batch' => $r->import_batch_id,
                    'archived' => $r->status === 'archived',
                ],
            ];
        });
        $n += $this->migrate('services', 'service', function ($r) {
            return [
                'title' => $r->title,
                'excerpt' => $r->short_desc,
                'content' => $r->description,
                'featured_image' => $r->image,
                'status' => $r->is_available ? 'published' : 'draft',
                'menu_order' => (int) $r->sort,
                'meta_extra' => ['price' => (float) $r->price, 'type' => $r->type],
            ];
        });
        $n += $this->migrate('shop_products', 'product', function ($r) {
            $images = json_decode((string) $r->images, true) ?: [];
            return [
                'title' => $r->name,
                'excerpt' => null,
                'content' => $r->description,
                'featured_image' => $images[0] ?? null,
                'status' => $r->is_active ? 'published' : 'draft',
                'meta_extra' => [
                    'sku' => $r->sku,
                    'price' => (float) $r->price,
                    'compare_price' => (float) $r->compare_price,
                    'stock' => (int) $r->stock,
                    'featured' => (bool) $r->featured,
                    'forced_payment_method_code' => $r->forced_payment_method_code,
                ],
            ];
        });

        $this->info($dry ? "DRY: would migrate {$n} rows" : "Migrated {$n} rows into cms_posts.");
        $this->line('cms_posts now: ' . DB::table('cms_posts')->count() . ' rows');

        return 0;
    }

    protected function migrate(string $table, string $postType, callable $map): int
    {
        $count = 0;
        foreach (DB::table($table)->whereNull('deleted_at')->orderBy('id')->get() as $row) {
            $ref = $table . ':' . $row->id;

            $exists = DB::table('cms_posts')
                ->where('post_type', $postType)
                ->where(function ($q) use ($ref, $row) {
                    $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(meta, '$.legacy_ref')) = ?", [$ref])
                      ->orWhere('slug', $row->slug);
                })
                ->exists();
            if ($exists) {
                continue;
            }

            $m = $map($row);
            $metaExtra = $m['meta_extra'] ?? [];
            unset($m['meta_extra']);

            $payload = array_merge([
                'post_type' => $postType,
                'title' => $m['title'],
                'slug' => $this->uniqueSlug($postType, $row->slug),
                'status' => $m['status'],
                'content' => $m['content'],
                'excerpt' => $m['excerpt'],
                'featured_image' => $m['featured_image'],
                'published_at' => $m['published_at'] ?? null,
                'meta_title' => $m['meta_title'] ?? null,
                'meta_description' => $m['meta_description'] ?? null,
                'meta_keywords' => $m['meta_keywords'] ?? null,
                'view_count' => $m['view_count'] ?? 0,
                'menu_order' => $m['menu_order'] ?? 0,
                'meta' => json_encode(array_merge(['legacy_ref' => $ref], $metaExtra)),
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at ?? now(),
            ], []);

            if (!$this->option('dry')) {
                DB::table('cms_posts')->insert($payload);
            }
            $count++;
        }

        $this->line(str_pad($table, 16) . " → {$postType}: {$count}");

        return $count;
    }

    protected function uniqueSlug(string $postType, string $slug): string
    {
        $base = $slug !== '' ? $slug : 'post-' . uniqid();
        $slug = $base;
        $i = 2;
        while (DB::table('cms_posts')->where('post_type', $postType)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
