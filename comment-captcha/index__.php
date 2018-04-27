<?php

Hook::set('shield.enter', function() use($site) {
    if ($site->is('page')) {
        Asset::set(__DIR__ . DS . 'lot' . DS . 'asset' . DS . 'css' . DS . 'comment.min.css');
    }
});

// Set unique ID as the form name
Config::set('_comment_captcha_id', sprintf('captcha:%x', crc32(date('Ymd')))); // valid for a day

function fn_comment_captcha($content) {
    if (($s = strpos($content, '<p class="form-comment-button">')) === false) {
        if (($s = strpos($content, '<p class="form-comment-button ')) === false) {
            return $content;
        }
    }
    $state = Plugin::state('comment-captcha');
    $type = $state['type'];
    $html = "";
    $id = Config::get('_comment_captcha_id');
    global $language;
    if ($captcha = call_user_func('Captcha::' . $type, ...array_merge(['comment'], (array) $state['types'][$type]))) {
        $html .= '<div class="form-comment-input form-comment-input:captcha form-comment-input:captcha-' . $type . ' p">';
        $html .= '<label for="form-comment-input:captcha">';
        $html .= $language->captcha;
        $html .= '</label>';
        $html .= '<div>';
        HTTP::delete('post', $id); // always clear the cache value
        $html .= $type !== 'token' ? $captcha . ' ' . Form::text($id, null, null, [
            'class[]' => ['input'],
            'id' => 'form-comment-input:captcha',
            'required' => true,
            'autocomplete' => 'off'
        ]) : Form::check($id, $captcha, false, $language->captcha_token_check, [
            'class[]' => ['input', 'captcha'],
            'id' => 'form-comment-input:captcha'
        ]);
        $html .= '</div>';
        $html .= '</div>';
        return substr($content, 0, $s) . $html . substr($content, $s);
    }
    return $content;
}

// Apply captcha only for inactive user(s)
if (!Extend::exist('user') || !Is::user()) {

    // Set through `shield.yield` or `view.yield` hook instead of `shield.get`
    // and `view.get` because cookie data must be sent before HTTP header(s) set,
    // and `shield.yield` or `view.yield` can be used to make sure that the output
    // buffer started before any other output buffer(s)
    Hook::set('shield.yield', 'fn_comment_captcha');

    $state = Extend::state('comment');
    Route::lot('%*%/' . $state['path'], function() use($state, $url) {
        $id = Config::get('_comment_captcha_id');
        if (HTTP::is('post') && Captcha::check(HTTP::post($id), 'comment') === false) {
            $s = Plugin::state('comment-captcha', 'type');
            if ($s === 'token') {
                $s .= '_check';
            }
            Message::error('captcha' . ($s ? '_' . $s : ""));
            HTTP::save('post');
            Guardian::kick(Path::D($url->clean) . $url->query . '#' . $state['anchor'][1]);
        }
    });

}