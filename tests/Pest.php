<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Shopper\CustomerGroups\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function createCustomer(string $email = 'jane@example.com'): int
{
    return DB::table('users')->insertGetId([
        'name' => 'Jane Cooper',
        'email' => $email,
        'password' => 'secret',
    ]);
}
