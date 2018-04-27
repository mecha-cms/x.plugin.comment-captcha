<?php

return [
    'type' => 'math', // the captcha type
    'types' => [
        // function argument(s) for `Captcha::{$any}()` after the `$id` parameter
        'math' => [1, 10],
        'text' => [false, '000'],
        'token' => [null, false]
    ]
];