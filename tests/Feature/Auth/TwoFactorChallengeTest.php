<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class TwoFactorChallengeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());
    }

    public function test_two_factor_challenge_redirects_to_login_when_not_authenticated(): void
    {
        $this->markTestSkipped('Two factor redirect not yet implemented with custom auth action.');
    }

    public function test_two_factor_challenge_can_be_rendered(): void
    {
        $this->markTestSkipped('Two factor redirect not yet implemented with custom auth action.');
    }
}
