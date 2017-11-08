<?php

Hook::set('route.enter', function() use($site) {
    if ($site->is === 'page') {
        Asset::set(__DIR__ . DS . 'lot' . DS . 'asset' . DS . 'css' . DS . 'comment.min.css');
    }
});

function fn_comment_captcha($content, $G) {
    if (!isset($G['source']) || $G['source'] !== 'comments') {
        return $content;
    }
    global $language;
    if (($s = strpos($content, '<p class="form-comment-button">')) === false) {
        if (($s = strpos($content, '<p class="form-comment-button ')) === false) {
            return $content;
        }
    }
    $state = Plugin::state(__DIR__);
    $type = $state['type'];
    $html = "";
    if ($captcha = call_user_func_array('Captcha::' . $type, array_merge(['comment'], (array) $state['types'][$type]))) {
        $html .= '<div class="form-comment-input form-comment-input:captcha form-comment-input:captcha-' . $type . ' p">';
        $html .= '<label for="form-comment-input:captcha">';
        $html .= $language->captcha;
        $html .= '</label>';
        $html .= '<div>' . $captcha;
        Request::delete('post', 'captcha'); // always clear the cache value
        $html .= $type !== 'toggle' ? ' ' . Form::text('captcha', null, null, ['class[]' => ['input'], 'id' => 'form-comment-input:captcha', 'required' => true, 'autocomplete' => 'off']) : "";
        $html .= '</div>';
        $html .= '</div>';
        return substr($content, 0, $s) . $html . substr($content, $s);
    }
    return $content;
}

Hook::set('shield.get.output', 'fn_comment_captcha');

$state = Extend::state('comment');
Route::lot('%*%/' . $state['path'], function() use($state, $url) {
    if (Request::is('post') && Captcha::check(Request::post('captcha'), 'comment') === false) {
        $s = Plugin::state(__DIR__, 'type');
        Message::error('captcha' . ($s ? '_' . $s : ""));
        Request::save('post');
        Guardian::kick(Path::D($url->current) . '#' . $state['anchor'][1]);
    }
});