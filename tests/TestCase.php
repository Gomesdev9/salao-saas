<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Os testes conferem os dados de cada tela, sem precisar do "npm run build"
        $this->withoutVite();
    }
}
