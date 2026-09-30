<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blog Import System (2026-09-02) — categories for the imported/new blog.
 *
 * IMPORTANT (legacy coexistence): the `blog_categories` table ALREADY
 * exists in this database (created 2026-08-17 for the legacy blog module
 * — model App\Models\BlogCategory with columns id/name/slug/timestamps).
 * Schema::create would therefore fail. To reach the spec'd end state
 * without breaking the legacy module:
 *   - table missing        → create it with the full spec columns;
 *   - table already exists → ALTER it, adding only the missing spec
 *     columns (description, post_count). Both are additive/nullable and
 *     are ignored by the legacy model, which only writes name + slug.
 *
 * CLASS-NAME NOTE: the legacy migration 2026_08_17_100001 already declares
 * class CreateBlogCategoriesTable — the exact Studly name Laravel 8.83's
 * Migrator derives for THIS filename too, so a duplicate is a PHP fatal.
 * A unique class name is used instead, guarded against redeclaration, and
 * the file RETURNS an instance so Migrator::resolvePath()'s plain-require
 * fallback (taken when class_exists(CreateBlogCategoriesTable) is false)
 * still receives a runnable migration object.
 */
if (! class_exists('CreateBlogCategoriesTableForImport')) {
class CreateBlogCategoriesTableForImport extends Migration
{
    public function up()
    {
        if (Schema::hasTable('blog_categories')) {
            // Legacy table present — bring it up to the spec columns.
            Schema::table('blog_categories', function (Blueprint $table) {
                if (!Schema::hasColumn('blog_categories', 'description')) {
                    $table->text('description')->nullable();
                }
                if (!Schema::hasColumn('blog_categories', 'post_count')) {
                    $table->unsignedInteger('post_count')->default(0);
                }
            });
            return;
        }

        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('post_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        // Only reverse what THIS migration changed; never drop the legacy
        // table if it pre-existed (it belongs to the legacy blog module).
        if (Schema::hasTable('blog_categories')) {
            Schema::table('blog_categories', function (Blueprint $table) {
                if (Schema::hasColumn('blog_categories', 'post_count')) {
                    $table->dropColumn('post_count');
                }
            });
            if (Schema::hasColumn('blog_categories', 'description')) {
                Schema::table('blog_categories', function (Blueprint $table) {
                    $table->dropColumn('description');
                });
            }
        }
    }
}
} // end class_exists guard

// Return an instance so Migrator::resolvePath()'s getRequire() fallback
// (taken because the Studly class name belongs to the legacy migration)
// receives a migration object instead of calling `new CreateBlogCategoriesTable`.
return new CreateBlogCategoriesTableForImport();
