<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_posts', function (Blueprint $table) {
            $table->string('post_as')->nullable()->after('author_id');
        });

        Schema::table('side_hustles', function (Blueprint $table) {
            $table->string('post_as')->nullable()->after('body');
        });

        Schema::table('podcast_episodes', function (Blueprint $table) {
            $table->string('post_as')->nullable()->after('show_notes');
        });
    }

    public function down(): void
    {
        Schema::table('content_posts', function (Blueprint $table) {
            $table->dropColumn('post_as');
        });

        Schema::table('side_hustles', function (Blueprint $table) {
            $table->dropColumn('post_as');
        });

        Schema::table('podcast_episodes', function (Blueprint $table) {
            $table->dropColumn('post_as');
        });
    }
};
