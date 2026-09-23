<?php

namespace Tests\Feature;

use App\Filament\Resources\Certificates\CertificateResource;
use App\Filament\Resources\CourseReviews\CourseReviewResource;
use App\Filament\Resources\Courses\CourseResource;
use App\Filament\Resources\Enrollments\EnrollmentResource;
use App\Filament\Resources\Specializations\SpecializationResource;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_access_filament_dashboard(): void
    {
        $admin = User::where('email', 'admin@nahj.com')->firstOrFail();

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_specializations_resource(): void
    {
        $admin = User::where('email', 'admin@nahj.com')->firstOrFail();

        $response = $this->actingAs($admin)->get('/admin/specializations');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_certificates_resource(): void
    {
        $admin = User::where('email', 'admin@nahj.com')->firstOrFail();

        $response = $this->actingAs($admin)->get('/admin/certificates');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_course_reviews_resource(): void
    {
        $admin = User::where('email', 'admin@nahj.com')->firstOrFail();

        $response = $this->actingAs($admin)->get('/admin/course-reviews');
        $response->assertStatus(200);
    }

    public function test_admin_can_access_enrollments_resource(): void
    {
        $admin = User::where('email', 'admin@nahj.com')->firstOrFail();

        $response = $this->actingAs($admin)->get('/admin/enrollments');
        $response->assertStatus(200);
    }

    public function test_instructor_query_scoping_on_specializations(): void
    {
        $instructor1 = User::where('email', 'instructor@nahj.com')->firstOrFail();
        $instructor2 = User::where('email', 'instructor@coursura.com')->firstOrFail();
        $category = Category::first();

        // Create a specialization for instructor 1
        $spec1 = Specialization::create([
            'title' => 'Instructor 1 Specialization',
            'slug' => 'instructor-1-spec',
            'instructor_id' => $instructor1->id,
            'category_id' => $category?->id,
            'level' => 'intermediate',
            'is_published' => true,
        ]);

        // Create a specialization for instructor 2
        $spec2 = Specialization::create([
            'title' => 'Instructor 2 Specialization',
            'slug' => 'instructor-2-spec',
            'instructor_id' => $instructor2->id,
            'category_id' => $category?->id,
            'level' => 'intermediate',
            'is_published' => true,
        ]);

        // When acting as Instructor 1, query should only return spec 1
        $this->actingAs($instructor1);
        $instructorScoped = SpecializationResource::getEloquentQuery()->get();
        $this->assertTrue($instructorScoped->contains('id', $spec1->id));
        $this->assertFalse($instructorScoped->contains('id', $spec2->id));

        // When acting as Admin, query should return both
        $admin = User::where('email', 'admin@nahj.com')->firstOrFail();
        $this->actingAs($admin);
        $adminScoped = SpecializationResource::getEloquentQuery()->get();
        $this->assertTrue($adminScoped->contains('id', $spec1->id));
        $this->assertTrue($adminScoped->contains('id', $spec2->id));
    }

    public function test_instructor_query_scoping_on_certificates_and_reviews(): void
    {
        $instructor1 = User::where('email', 'instructor@nahj.com')->firstOrFail();
        $instructor2 = User::where('email', 'instructor@coursura.com')->firstOrFail();
        $student = User::where('email', 'student@nahj.com')->firstOrFail();
        $course1 = Course::firstOrFail();
        $course2 = Course::skip(1)->firstOrFail();

        $cert1 = Certificate::create([
            'certificate_code' => 'NAHJ-INST1-CERT',
            'verification_hash' => hash('sha256', 'NAHJ-INST1-CERT'),
            'user_id' => $student->id,
            'course_id' => $course1->id,
            'instructor_id' => $instructor1->id,
            'grade_percentage' => 95,
            'issue_date' => now(),
        ]);

        $cert2 = Certificate::create([
            'certificate_code' => 'NAHJ-INST2-CERT',
            'verification_hash' => hash('sha256', 'NAHJ-INST2-CERT'),
            'user_id' => $student->id,
            'course_id' => $course2->id,
            'instructor_id' => $instructor2->id,
            'grade_percentage' => 88,
            'issue_date' => now(),
        ]);

        // Scoping on CertificateResource
        $this->actingAs($instructor1);
        $certScoped = CertificateResource::getEloquentQuery()->get();
        $this->assertTrue($certScoped->contains('id', $cert1->id));
        $this->assertFalse($certScoped->contains('id', $cert2->id));
    }
}
