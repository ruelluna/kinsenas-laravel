<?php

use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
    Storage::fake('public');
});

it('allows platform admin to upload a content image and returns a public url', function () {
    $admin = User::factory()->platformAdmin()->create();

    $response = $this->actingAs($admin)
        ->post(route('admin.content.uploads.store'), [
            'image' => UploadedFile::fake()->image('diagram.png', 640, 480),
        ]);

    $response->assertSuccessful()
        ->assertJsonStructure(['url']);

    $url = $response->json('url');
    expect($url)->toBeString()->toContain('/storage/content/images/');

    Storage::disk('public')->assertExists(
        str_replace('/storage/', '', parse_url($url, PHP_URL_PATH)),
    );
});

it('rejects invalid upload types', function () {
    $admin = User::factory()->platformAdmin()->create();

    $this->actingAs($admin)
        ->post(route('admin.content.uploads.store'), [
            'image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('image');
});

it('forbids non admin from uploading content images', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.content.uploads.store'), [
            'image' => UploadedFile::fake()->image('diagram.png'),
        ])
        ->assertForbidden();
});
