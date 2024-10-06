<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;

abstract class FeatureTestCase extends TestCase
{
    use DatabaseTransactions;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }

    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getAdminUser(): User
    {
        return User::find(1);;
    }

    protected function getHeaders(): array
    {
        return ['Accept' => 'Application/json'];
    }
}
