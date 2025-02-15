<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Helpers\TestHelper;
use Carbon\Carbon;

class DateRetrievalTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_login_user_can_see_date_and_time()
    {
        $now = Carbon::now();
        $user = TestHelper::userLogin();

        $response = $this->get(route('create'));
        $response->assertStatus(200);
        $response->assertSee($now->translatedFormat('Y年n月j日(D)'));
        $response->assertSee($now->format('H:i'));

    }
}
