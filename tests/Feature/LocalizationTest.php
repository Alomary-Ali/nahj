<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_locale_is_arabic_with_rtl_direction(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('locale', 'ar')
            ->where('direction', 'rtl')
            ->has('translations')
            ->where('translations.platform_name', 'نهج')
        );
    }

    public function test_user_can_switch_locale_to_english(): void
    {
        $response = $this->post(route('locale.update'), [
            'locale' => 'en',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
        $response->assertCookie('locale', 'en');

        // Subsequent request reflects English and LTR direction
        $pageResponse = $this->withSession(['locale' => 'en'])->get('/');
        $pageResponse->assertStatus(200);
        $pageResponse->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('locale', 'en')
            ->where('direction', 'ltr')
            ->where('translations.platform_name', 'Nahj')
            ->where('translations.explore_courses', 'Explore Courses')
        );
    }

    public function test_unsupported_locale_fails_validation(): void
    {
        $response = $this->post(route('locale.update'), [
            'locale' => 'invalid_lang',
        ]);

        $response->assertSessionHasErrors('locale');
    }
}
