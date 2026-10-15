<?php

namespace Tests\Feature;

use Tests\TestCase;

class AccessLandingTest extends TestCase
{
    public function test_admin_access_card_points_to_laravel_login(): void
    {
        $this->get('/site/acceso')
            ->assertOk()
            ->assertSee('/site/login?perfil=admin', false)
            ->assertDontSee('tutorias-web-main.test', false);
    }
}
