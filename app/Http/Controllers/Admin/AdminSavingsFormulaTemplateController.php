<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSavingsFormulaTemplateRequest;
use App\Models\SavingsFormulaTemplate;
use App\Models\SavingsFormulaTemplateCategory;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AdminSavingsFormulaTemplateController extends Controller
{
    public function index(): Response
    {
        $templates = SavingsFormulaTemplate::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description']);

        return Inertia::render('admin/formula-templates/index', [
            'templates' => $templates->map(fn (SavingsFormulaTemplate $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'description' => $template->description,
            ]),
        ]);
    }

    public function edit(SavingsFormulaTemplate $template): Response
    {
        $template->load('categories');

        return Inertia::render('admin/formula-templates/edit', [
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
                'description' => $template->description,
                'bestFor' => $template->best_for,
                'videoEmbedUrl' => $template->video_embed_url,
                'categories' => $template->categories->map(fn (SavingsFormulaTemplateCategory $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'percentage' => (string) $category->percentage,
                    'description' => $category->description,
                ]),
            ],
        ]);
    }

    public function update(UpdateSavingsFormulaTemplateRequest $request, SavingsFormulaTemplate $template): RedirectResponse
    {
        $template->update([
            'description' => $request->validated('description'),
            'best_for' => $request->validated('best_for'),
            'video_embed_url' => $request->validated('video_embed_url'),
        ]);

        foreach ($request->validated('categories') as $categoryData) {
            SavingsFormulaTemplateCategory::query()
                ->where('id', $categoryData['id'])
                ->where('template_id', $template->id)
                ->update(['description' => $categoryData['description'] ?? null]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Formula template updated.')]);

        return back();
    }
}
