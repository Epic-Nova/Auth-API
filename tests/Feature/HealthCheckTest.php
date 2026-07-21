<?php

test('the health endpoint reports the auth api as available', function () {
    $this->getJson('/api/health')
        ->assertOk()
        ->assertExactJson([
            'status' => 'ok',
            'service' => 'auth-api',
        ]);
});
