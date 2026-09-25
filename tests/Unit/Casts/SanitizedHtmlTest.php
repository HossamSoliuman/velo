<?php

use App\Casts\SanitizedHtml;

test('keeps the formatting the rich text editor produces', function () {
    $html = '<h1>Title</h1><div><strong>Bold</strong> <em>italic</em> <del>struck</del><br>line</div><ul><li>One</li></ul><ol><li>Two</li></ol><blockquote>Quote</blockquote><pre>code</pre><div><a href="https://velo.example/about">About</a></div>';

    expect(SanitizedHtml::clean($html))->toBe($html);
});

test('removes dangerous elements together with their contents', function (string $html) {
    expect(SanitizedHtml::clean($html))->toBe('<div>Safe</div>');
})->with([
    'script' => '<div>Safe<script>alert(1)</script></div>',
    'style' => '<div>Safe<style>body{display:none}</style></div>',
    'iframe' => '<div>Safe<iframe src="https://evil.test"></iframe></div>',
    'image with onerror' => '<div>Safe<img src="x" onerror="alert(1)"></div>',
    'svg' => '<div>Safe<svg onload="alert(1)"><circle/></svg></div>',
    'form' => '<div>Safe<form action="https://evil.test"><input name="password"></form></div>',
]);

test('removes attributes that are not allowed', function () {
    expect(SanitizedHtml::clean('<div class="x" style="color:red" onclick="steal()">Text</div>'))->toBe('<div>Text</div>');
});

test('drops links with unsafe schemes but keeps the link text', function (string $href) {
    expect(SanitizedHtml::clean('<a href="'.$href.'">Click</a>'))->toBe('<a>Click</a>');
})->with([
    'javascript' => 'javascript:alert(1)',
    'javascript with spaces and case' => '  JaVaScRiPt:alert(1)',
    'data' => 'data:text/html;base64,PHNjcmlwdD4=',
    'vbscript' => 'vbscript:msgbox(1)',
    'protocol-relative' => '//evil.test',
]);

test('keeps safe link schemes', function (string $href) {
    expect(SanitizedHtml::clean('<a href="'.$href.'">Contact</a>'))->toBe('<a href="'.$href.'">Contact</a>');
})->with([
    'https' => 'https://velo.example',
    'mailto' => 'mailto:sales@velo.example',
    'tel' => 'tel:+919876543210',
    'relative path' => '/contact',
]);

test('unwraps unknown tags and keeps their text', function () {
    expect(SanitizedHtml::clean('<div><span style="color:red">Red</span> <font>text</font></div>'))->toBe('<div>Red text</div>');
});

test('removes html comments', function () {
    expect(SanitizedHtml::clean('<div>Text<!-- secret --></div>'))->toBe('<div>Text</div>');
});

test('keeps unicode characters as they are', function () {
    expect(SanitizedHtml::clean('<div>Price ₹1,250 — café</div>'))->toBe('<div>Price ₹1,250 — café</div>');
});

test('treats empty input as no content', function (?string $html) {
    expect(SanitizedHtml::clean($html))->toBeNull();
})->with([
    'null' => null,
    'empty string' => '',
    'whitespace' => "  \n ",
    'only a script' => '<script>alert(1)</script>',
]);

test('keeps plain text without tags', function () {
    expect(SanitizedHtml::clean('Plain text & more'))->toBe('Plain text &amp; more');
});
