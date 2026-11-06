<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_root_mengarahkan_ke_login_peserta(): void
    {
        $this->get('/')->assertRedirect(route('peserta.login'));
    }
}
