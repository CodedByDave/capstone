<?php

test('returns a successful response', function () {
    $response = $this->get(route('landing'));

    $response->assertStatus(200);
});
