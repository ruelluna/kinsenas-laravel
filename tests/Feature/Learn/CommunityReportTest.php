<?php

use App\Enums\CommunityReportStatus;
use App\Models\CommunityPost;
use App\Models\CommunityPostReport;
use App\Models\User;
use Database\Seeders\BillingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(BillingSeeder::class);
});

it('allows subscribed members to report a published community post', function () {
    $author = User::factory()->create();
    $reporter = User::factory()->create();
    grantTeamSubscriptionAccess($reporter->currentTeam);

    $post = CommunityPost::factory()->published()->create([
        'user_id' => $author->id,
        'slug' => 'report-me',
    ]);

    $this->actingAs($reporter)
        ->post(route('learn.community.report', $post), [
            'reason' => 'spam',
            'details' => 'Looks like an ad.',
        ])
        ->assertRedirect();

    $report = CommunityPostReport::query()->where('community_post_id', $post->id)->firstOrFail();

    expect($report->reporter_id)->toBe($reporter->id)
        ->and($report->status)->toBe(CommunityReportStatus::Open);
});

it('forbids members from reporting their own post', function () {
    $member = User::factory()->create();
    grantTeamSubscriptionAccess($member->currentTeam);

    $post = CommunityPost::factory()->published()->create([
        'user_id' => $member->id,
    ]);

    $this->actingAs($member)
        ->post(route('learn.community.report', $post), [
            'reason' => 'other',
        ])
        ->assertSessionHasErrors('reason');
});

it('allows platform admin to dismiss an open report', function () {
    $admin = User::factory()->platformAdmin()->create();
    $report = CommunityPostReport::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.content.community-reports.dismiss', $report))
        ->assertRedirect();

    expect($report->fresh()->status)->toBe(CommunityReportStatus::Dismissed);
});

it('lists open reports for platform admin', function () {
    $admin = User::factory()->platformAdmin()->create();
    CommunityPostReport::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.content.community-reports.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/content/community-reports/index')
            ->has('reports.data', 1));
});
