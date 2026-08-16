<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('uploads a profile photo and exposes avatar on auth user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ])
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->profile_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->profile_photo_path);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('auth.user.avatar', asset('storage/'.$user->profile_photo_path)));
});

it('removes an existing profile photo when requested', function () {
    $user = User::factory()->create([
        'profile_photo_path' => 'avatars/existing.jpg',
    ]);

    Storage::disk('public')->put('avatars/existing.jpg', 'photo');

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'remove_profile_photo' => '1',
        ])
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->profile_photo_path)->toBeNull();
    Storage::disk('public')->assertMissing('avatars/existing.jpg');
});

it('rejects invalid profile photo uploads', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'profile_photo' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('profile_photo');
});
