<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('content_post_categories')) {
            Schema::create('content_post_categories', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('status')->default('draft');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_post_category')) {
            Schema::create('content_post_category', function (Blueprint $table) {
                $table->foreignUuid('content_post_id')->constrained('content_posts')->cascadeOnDelete();
                $table->foreignUuid('content_post_category_id')->constrained('content_post_categories')->cascadeOnDelete();
                $table->primary(['content_post_id', 'content_post_category_id']);
            });
        }

        if (! Schema::hasTable('community_post_category')) {
            Schema::create('community_post_category', function (Blueprint $table) {
                $table->foreignUuid('community_post_id')->constrained('community_posts')->cascadeOnDelete();
                $table->foreignUuid('community_category_id')->constrained('community_categories')->cascadeOnDelete();
                $table->primary(['community_post_id', 'community_category_id']);
            });
        }

        if (Schema::hasColumn('community_posts', 'community_category_id')) {
            DB::table('community_posts')
                ->whereNotNull('community_category_id')
                ->orderBy('created_at')
                ->each(function (object $post): void {
                    $exists = DB::table('community_post_category')
                        ->where('community_post_id', $post->id)
                        ->where('community_category_id', $post->community_category_id)
                        ->exists();

                    if (! $exists) {
                        DB::table('community_post_category')->insert([
                            'community_post_id' => $post->id,
                            'community_category_id' => $post->community_category_id,
                        ]);
                    }
                });

            Schema::table('community_posts', function (Blueprint $table) {
                $table->dropForeign(['community_category_id']);
                $table->dropColumn('community_category_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('community_post_category');
        Schema::dropIfExists('content_post_category');
        Schema::dropIfExists('content_post_categories');
    }
};
