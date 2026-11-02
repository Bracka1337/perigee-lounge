<?php

it('redirects the root url to the default locale', function () {
    $response = $this->get('/');

    $response->assertRedirect('/ru');
});
