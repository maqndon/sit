<?php

use Tests\Traits\UserTrait;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(UserTrait::class);

beforeEach(function () {
    $this->refreshDatabase();
});

it('ensures all responses are JSON', function () {
    $endpoints = [
        'api/login',
        'api/logout',
        'api/unauthenticated',
        'api/tasks',
        'api/users',
        'api/projects',
    ];

    foreach ($endpoints as $endpoint) {
        $response = get($endpoint);
        $response->assertHeader('Content-Type', 'application/json');
    }
});

it('returns JSON error responses', function () {
    $user = $this->createUser();

    $this->actingAs($user);

    $response = post('api/tasks', [
        'title' => '',
        'status' => 'invalid_status',
    ]);

    $response->assertStatus(422);
    $response->assertJson(fn ($json) => $json->has('message') && $json->has('errors'));
});
