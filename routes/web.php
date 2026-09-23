<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Internationalization & Locale Switch
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

// Public Catalog & Homepage
Route::get('/', [HomeController::class, 'index'])->name('home');

// Course Details & Learning Player
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/specializations/{specialization:slug}', [\App\Http\Controllers\SpecializationController::class, 'show'])->name('specializations.show');
Route::get('/learn/{course:slug}', [CourseController::class, 'learn'])->name('courses.learn');
Route::get('/learn/{course:slug}/lecture/{lesson:slug}', [LessonController::class, 'show'])->name('courses.lecture');
Route::post('/learn/{course:slug}/lecture/{lesson:slug}/progress', [\App\Http\Controllers\LessonProgressController::class, 'update'])->middleware('auth')->name('courses.lecture.progress');
Route::post('/learn/{course:slug}/lecture/{lesson:slug}/notes', [\App\Http\Controllers\LessonNoteController::class, 'store'])->middleware('auth')->name('courses.lecture.notes.store');
Route::delete('/learn/{course:slug}/lecture/{lesson:slug}/notes/{note}', [\App\Http\Controllers\LessonNoteController::class, 'destroy'])->middleware('auth')->name('courses.lecture.notes.destroy');

// Quizzes & Assessments
Route::get('/learn/{course:slug}/quiz/{quiz}', [\App\Http\Controllers\QuizController::class, 'show'])->name('courses.quiz.show');
Route::post('/learn/{course:slug}/quiz/{quiz}/submit', [\App\Http\Controllers\QuizController::class, 'submit'])->middleware('auth')->name('courses.quiz.submit');

// Checkout & Enrollments
Route::get('/courses/{course:slug}/checkout', [\App\Http\Controllers\CheckoutController::class, 'show'])->name('courses.checkout.show');
Route::post('/courses/{course:slug}/enroll', [\App\Http\Controllers\CheckoutController::class, 'enroll'])->middleware('auth')->name('courses.enroll');

// Discussion Community & Q&A
Route::get('/courses/{course:slug}/discussions', [\App\Http\Controllers\DiscussionController::class, 'index'])->name('courses.discussions.index');
Route::get('/courses/{course:slug}/discussions/{thread}', [\App\Http\Controllers\DiscussionController::class, 'show'])->name('courses.discussions.show');
Route::post('/courses/{course:slug}/discussions', [\App\Http\Controllers\DiscussionController::class, 'store'])->middleware('auth')->name('courses.discussions.store');
Route::post('/courses/{course:slug}/discussions/{thread}/reply', [\App\Http\Controllers\DiscussionController::class, 'reply'])->middleware('auth')->name('courses.discussions.reply');
Route::post('/courses/{course:slug}/discussions/upvote', [\App\Http\Controllers\DiscussionController::class, 'upvote'])->middleware('auth')->name('courses.discussions.upvote');
Route::post('/courses/{course:slug}/discussions/replies/{reply}/endorse', [\App\Http\Controllers\DiscussionController::class, 'endorse'])->middleware('auth')->name('courses.discussions.endorse');

// Public Certificate Verification
Route::get('/certificates/{code}', [\App\Http\Controllers\CertificateController::class, 'verify'])->name('certificates.verify');

// Course Reviews & Ratings
Route::post('/courses/{course:slug}/reviews', [\App\Http\Controllers\CourseReviewController::class, 'store'])->middleware('auth')->name('courses.reviews.store');
Route::delete('/courses/{course:slug}/reviews/{review}', [\App\Http\Controllers\CourseReviewController::class, 'destroy'])->middleware('auth')->name('courses.reviews.destroy');

// Public Instructor Profile
Route::get('/instructors/{id}', [\App\Http\Controllers\InstructorController::class, 'show'])->name('instructors.show');

// Student Dashboard ("My Learning")
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
