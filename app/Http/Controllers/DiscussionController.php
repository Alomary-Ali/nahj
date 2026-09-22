<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\DiscussionReply;
use App\Models\DiscussionThread;
use App\Models\DiscussionUpvote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscussionController extends Controller
{
    public function index(Request $request, string $courseSlug): Response
    {
        $course = Course::where('slug', $courseSlug)->where('is_published', true)->firstOrFail();

        $query = DiscussionThread::where('course_id', $course->id)
            ->with([
                'user:id,name',
                'lesson:id,title,slug',
            ]);

        // Filter by lesson
        if ($request->filled('lesson_id')) {
            $query->where('lesson_id', $request->input('lesson_id'));
        }

        // Search query
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        if ($sort === 'top') {
            $query->orderBy('is_pinned', 'desc')->orderBy('upvotes_count', 'desc');
        } elseif ($sort === 'unanswered') {
            $query->where('replies_count', 0)->orderBy('is_pinned', 'desc')->latest();
        } else {
            $query->orderBy('is_pinned', 'desc')->latest();
        }

        $threads = $query->paginate(15)->withQueryString();

        // Course lessons list for filter dropdown
        $lessons = $course->lessons()->select('lessons.id', 'lessons.title', 'lessons.slug')->get();

        return Inertia::render('Courses/Discussions/Index', [
            'course' => $course,
            'threads' => $threads,
            'lessons' => $lessons,
            'filters' => [
                'lesson_id' => $request->input('lesson_id'),
                'search' => $request->input('search'),
                'sort' => $sort,
            ],
        ]);
    }

    public function show(Request $request, string $courseSlug, int $threadId): Response
    {
        $course = Course::where('slug', $courseSlug)->where('is_published', true)->firstOrFail();

        $thread = DiscussionThread::where('id', $threadId)
            ->where('course_id', $course->id)
            ->with([
                'user:id,name',
                'lesson:id,title,slug',
                'replies' => function ($q) {
                    $q->with('user:id,name')
                        ->orderBy('is_instructor_endorsed', 'desc')
                        ->orderBy('created_at', 'asc');
                },
            ])
            ->firstOrFail();

        $user = $request->user();
        $isThreadUpvoted = $user ? $thread->isUpvotedBy($user) : false;

        $upvotedReplyIds = [];
        if ($user) {
            $upvotedReplyIds = DiscussionUpvote::where('user_id', $user->id)
                ->where('upvotable_type', 'reply')
                ->whereIn('upvotable_id', $thread->replies->pluck('id'))
                ->pluck('upvotable_id')
                ->toArray();
        }

        $isInstructorOrAdmin = $user ? ($user->hasRole('admin') || $user->id === $course->instructor_id) : false;

        return Inertia::render('Courses/Discussions/Show', [
            'course' => $course,
            'thread' => $thread,
            'isThreadUpvoted' => $isThreadUpvoted,
            'upvotedReplyIds' => $upvotedReplyIds,
            'isInstructorOrAdmin' => $isInstructorOrAdmin,
        ]);
    }

    public function store(Request $request, string $courseSlug): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
        ]);

        $course = Course::where('slug', $courseSlug)->where('is_published', true)->firstOrFail();

        $thread = DiscussionThread::create([
            'course_id' => $course->id,
            'lesson_id' => $validated['lesson_id'] ?? null,
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'body' => $validated['body'],
        ]);

        return redirect()->route('courses.discussions.show', ['course' => $course->slug, 'thread' => $thread->id])
            ->with('success', 'تم نشر سؤالك بنجاح في مجتمع المساق.');
    }

    public function reply(Request $request, string $courseSlug, int $threadId): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string'],
        ]);

        $course = Course::where('slug', $courseSlug)->where('is_published', true)->firstOrFail();
        $thread = DiscussionThread::where('id', $threadId)->where('course_id', $course->id)->firstOrFail();

        $user = auth()->user();
        $isInstructor = ($user->id === $course->instructor_id || $user->hasRole('admin'));

        DiscussionReply::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'body' => $validated['body'],
            'is_instructor_endorsed' => $isInstructor,
        ]);

        $thread->increment('replies_count');

        return back()->with('success', 'تم إضافة ردك ومشاركته مع الطلاب بنجاح.');
    }

    public function upvote(Request $request, string $courseSlug): RedirectResponse
    {
        $validated = $request->validate([
            'upvotable_type' => ['required', 'in:thread,reply'],
            'upvotable_id' => ['required', 'integer'],
        ]);

        $user = auth()->user();
        $type = $validated['upvotable_type'];
        $id = $validated['upvotable_id'];

        $existing = DiscussionUpvote::where('user_id', $user->id)
            ->where('upvotable_type', $type)
            ->where('upvotable_id', $id)
            ->first();

        if ($existing) {
            $existing->delete();
            if ($type === 'thread') {
                DiscussionThread::where('id', $id)->decrement('upvotes_count');
            } else {
                DiscussionReply::where('id', $id)->decrement('upvotes_count');
            }
        } else {
            DiscussionUpvote::create([
                'user_id' => $user->id,
                'upvotable_type' => $type,
                'upvotable_id' => $id,
            ]);

            if ($type === 'thread') {
                DiscussionThread::where('id', $id)->increment('upvotes_count');
            } else {
                DiscussionReply::where('id', $id)->increment('upvotes_count');
            }
        }

        return back();
    }

    public function endorse(Request $request, string $courseSlug, int $replyId): RedirectResponse
    {
        $course = Course::where('slug', $courseSlug)->where('is_published', true)->firstOrFail();
        $user = auth()->user();

        if ($user->id !== $course->instructor_id && ! $user->hasRole('admin')) {
            abort(403, 'غير مصرح لك باعتماد الإجابات.');
        }

        $reply = DiscussionReply::where('id', $replyId)->firstOrFail();
        $reply->update([
            'is_instructor_endorsed' => ! $reply->is_instructor_endorsed,
        ]);

        return back()->with('success', 'تم تحديث شارة اعتماد الإجابة بنجاح.');
    }
}
