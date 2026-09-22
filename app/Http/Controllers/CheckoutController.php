<?php

namespace App\Http\Controllers;

use App\Actions\Enrollment\EnrollUserAction;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(string $slug): Response|RedirectResponse
    {
        $course = Course::where('slug', $slug)
            ->where('is_published', true)
            ->with(['instructor:id,name', 'category:id,name'])
            ->firstOrFail();

        if (auth()->check() && auth()->user()->isEnrolledIn($course)) {
            return redirect()->route('courses.learn', $course->slug)
                ->with('info', 'أنت مسجل بالفعل في هذا المقرر.');
        }

        return Inertia::render('Checkout/Show', [
            'course' => $course,
        ]);
    }

    public function enroll(Request $request, string $slug, EnrollUserAction $enrollUserAction): RedirectResponse
    {
        $course = Course::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $user = auth()->user();

        $enrollUserAction->execute($user, $course, (float) $course->price);

        return redirect()->route('courses.learn', $course->slug)
            ->with('success', 'تهانينا! تم تسجيلك بنجاح في المقرر. يمكنك الآن متابعة جميع المحاضرات وحل الاختبارات والحصول على الشهادة الرسمية.');
    }
}
