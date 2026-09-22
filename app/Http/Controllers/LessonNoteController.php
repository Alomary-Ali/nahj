<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LessonNoteController extends Controller
{
    /**
     * Store a timestamped lecture note for the authenticated user.
     */
    public function store(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'timestamp_seconds' => ['required', 'integer', 'min:0'],
            'note_text' => ['required', 'string', 'max:3000'],
        ]);

        LessonNote::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'timestamp_seconds' => $validated['timestamp_seconds'],
            'note_text' => $validated['note_text'],
        ]);

        return back()->with('success', 'تم حفظ الملاحظة بنجاح.');
    }

    /**
     * Delete a lecture note.
     */
    public function destroy(Request $request, Course $course, Lesson $lesson, LessonNote $note): RedirectResponse
    {
        $user = $request->user();

        if ($note->user_id !== $user->id && ! $user->hasRole('admin')) {
            abort(403, 'غير مصرح لك بحذف هذه الملاحظة.');
        }

        $note->delete();

        return back()->with('success', 'تم حذف الملاحظة بنجاح.');
    }
}
