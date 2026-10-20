<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    // Halaman awal mengarahkan ke dashboard (yang lalu meminta login)
    public function test_root_redirects(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }
}
