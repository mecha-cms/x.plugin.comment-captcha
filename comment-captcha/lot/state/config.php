<?php

return [
    'type' => 'math', // the captcha type
    'types' => [
        'math' => [1, 10], // function argument(s) for `Captcha::math()` after the `$id` parameter
        'text' => [false, '000'], // function argument(s) for `Captcha::text()` after the `$id` parameter
        'toggle' => [] // function argument(s) for `Captcha::toggle()` after the `$id` parameter
    ]
];