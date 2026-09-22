<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $selectedCategorySlug = $request->query('category');
        $searchQuery = $request->query('search');

        $categories = Category::active()
            ->withCount(['courses' => fn ($q) => $q->where('is_published', true)])
            ->get();

        $coursesQuery = Course::query()
            ->where('is_published', true)
            ->with(['instructor:id,name,email', 'category:id,name,slug'])
            ->withCount('modules');

        if ($selectedCategorySlug) {
            $coursesQuery->whereHas('category', function ($q) use ($selectedCategorySlug) {
                $q->where('slug', $selectedCategorySlug);
            });
        }

        if ($searchQuery) {
            $coursesQuery->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', "%{$searchQuery}%")
                    ->orWhere('headline', 'like', "%{$searchQuery}%")
                    ->orWhere('description', 'like', "%{$searchQuery}%");
            });
        }

        $courses = $coursesQuery->latest()->paginate(12)->withQueryString();

        $featuredSpecializations = Specialization::where('is_published', true)
            ->with(['instructor:id,name', 'category:id,name', 'courses:id,specialization_id,title,thumbnail'])
            ->take(3)
            ->get();

        return Inertia::render('Welcome', [
            'categories' => $categories,
            'courses' => $courses,
            'featuredSpecializations' => $featuredSpecializations,
            'filters' => [
                'category' => $selectedCategorySlug,
                'search' => $searchQuery,
            ],
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }
}
