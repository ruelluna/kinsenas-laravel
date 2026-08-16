<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recover from a prior failed run that left partial tables behind.
        Schema::dropIfExists('content_engagement_events');
        Schema::dropIfExists('content_reactions');
        Schema::dropIfExists('content_posts');
        Schema::dropIfExists('content_series');

        Schema::create('content_series', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('content_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('content_series_id')->nullable()->constrained('content_series')->nullOnDelete();
            $table->unsignedInteger('episode_number')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('content_type');
            $table->string('publish_scope')->default('internal');
            $table->string('status')->default('draft');
            $table->string('video_embed_url')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedSmallInteger('reading_time_minutes')->default(1);
            $table->timestamps();

            $table->index(['status', 'publish_scope', 'published_at']);
            $table->unique(['content_series_id', 'episode_number']);
        });

        Schema::create('content_reactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('content_post_id')->constrained('content_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reaction_type')->default('helpful');
            $table->timestamps();

            $table->unique(['content_post_id', 'user_id']);
        });

        Schema::create('content_engagement_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('content_post_id')->constrained('content_posts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type');
            $table->string('source');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['content_post_id', 'event_type', 'created_at'], 'content_events_post_type_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_engagement_events');
        Schema::dropIfExists('content_reactions');
        Schema::dropIfExists('content_posts');
        Schema::dropIfExists('content_series');
    }
};
