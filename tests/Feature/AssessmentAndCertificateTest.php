<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AssessmentAndCertificateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_quiz_page_renders_with_sanitized_options_hiding_is_correct(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $quiz = Quiz::where('course_id', $course->id)->firstOrFail();

        $response = $this->get(route('courses.quiz.show', ['course' => $course->slug, 'quiz' => $quiz->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Courses/Quiz')
            ->has('quiz.title')
            ->has('quiz.questions', 5)
            ->missing('quiz.questions.0.options.0.is_correct') // Confirms is_correct is stripped from frontend
            ->where('quiz.passing_percentage', '80.00')
        );
    }

    public function test_submitting_quiz_requires_authentication(): void
    {
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $quiz = Quiz::where('course_id', $course->id)->firstOrFail();

        $response = $this->post(route('courses.quiz.submit', ['course' => $course->slug, 'quiz' => $quiz->id]), [
            'answers' => [1 => 1],
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_submitting_quiz_with_passing_score_generates_certificate(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $quiz = Quiz::where('course_id', $course->id)->with('questions.options')->firstOrFail();

        // Prepare 100% correct answers
        $correctAnswers = [];
        foreach ($quiz->questions as $question) {
            $correctOption = $question->options->firstWhere('is_correct', true);
            $correctAnswers[$question->id] = $correctOption->id;
        }

        $response = $this->actingAs($student)
            ->post(route('courses.quiz.submit', ['course' => $course->slug, 'quiz' => $quiz->id]), [
                'answers' => $correctAnswers,
            ]);

        $response->assertRedirect(route('courses.quiz.show', ['course' => $course->slug, 'quiz' => $quiz->id]));
        $response->assertSessionHas('success');

        // Verify submission in DB
        $submission = QuizSubmission::where('user_id', $student->id)->where('quiz_id', $quiz->id)->first();
        $this->assertNotNull($submission);
        $this->assertTrue((bool) $submission->passed);
        $this->assertEquals(100.0, (float) $submission->percentage);

        // Verify certificate generated in DB
        $certificate = Certificate::where('user_id', $student->id)->where('course_id', $course->id)->first();
        $this->assertNotNull($certificate);
        $this->assertStringStartsWith('COUR-', $certificate->certificate_code);
        $this->assertNotEmpty($certificate->verification_hash);
        $this->assertEquals(64, strlen($certificate->verification_hash)); // SHA-256 length
    }

    public function test_submitting_quiz_with_failing_score_does_not_generate_certificate(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();
        $quiz = Quiz::where('course_id', $course->id)->with('questions.options')->firstOrFail();

        // Prepare 0% correct answers (pick wrong option for all)
        $wrongAnswers = [];
        foreach ($quiz->questions as $question) {
            $wrongOption = $question->options->firstWhere('is_correct', false);
            $wrongAnswers[$question->id] = $wrongOption->id;
        }

        $response = $this->actingAs($student)
            ->post(route('courses.quiz.submit', ['course' => $course->slug, 'quiz' => $quiz->id]), [
                'answers' => $wrongAnswers,
            ]);

        $response->assertRedirect(route('courses.quiz.show', ['course' => $course->slug, 'quiz' => $quiz->id]));
        $response->assertSessionHas('error');

        // Verify submission in DB
        $submission = QuizSubmission::where('user_id', $student->id)->where('quiz_id', $quiz->id)->first();
        $this->assertNotNull($submission);
        $this->assertFalse((bool) $submission->passed);
        $this->assertEquals(0.0, (float) $submission->percentage);

        // Verify NO certificate generated
        $certificate = Certificate::where('user_id', $student->id)->where('course_id', $course->id)->first();
        $this->assertNull($certificate);
    }

    public function test_public_certificate_verification_page_renders_authenticity_data(): void
    {
        $student = User::where('email', 'student@coursura.com')->firstOrFail();
        $instructor = User::where('email', 'instructor@coursura.com')->firstOrFail();
        $course = Course::where('slug', 'neural-networks-and-deep-learning')->firstOrFail();

        $certificate = Certificate::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'instructor_id' => $instructor->id,
            'grade_percentage' => 95.50,
            'issue_date' => now()->toDateString(),
        ]);

        $response = $this->get(route('certificates.verify', $certificate->certificate_code));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Certificates/Verify')
            ->where('certificate.certificate_code', $certificate->certificate_code)
            ->where('certificate.user.name', $student->name)
            ->where('certificate.course.title', $course->title)
            ->where('certificate.verification_hash', $certificate->verification_hash)
            ->where('certificate.grade_percentage', '95.50')
        );
    }

    public function test_invalid_certificate_code_returns_404(): void
    {
        $response = $this->get(route('certificates.verify', 'COUR-INVALID-CODE'));

        $response->assertStatus(404);
    }
}
