{$font_name = 'firasans'}
{$fonts = ['cyrillic','cyrillic-ext','latin', 'latin-ext']}
{$unicode_range = [
    'cyrillic-ext' => 'U+0460-052F, U+1C80-1C8A, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F',
    'cyrillic'     => 'U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116',
    'latin-ext'    => 'U+0100-02BA, U+02BD-02C5, U+02C7-02CC, U+02CE-02D7, U+02DD-02FF, U+0304, U+0308, U+0329, U+1D00-1DBF, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20C0, U+2113, U+2C60-2C7F, U+A720-A7FF',
    'latin'        => 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD'
]}


{foreach $fonts as $font}
    <link rel="preload" href="design/{$settings->theme|escape}/fonts/{$font_name}/{$font_name}-{$font}-400.woff2" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="design/{$settings->theme|escape}/fonts/{$font_name}/{$font_name}-{$font}-500.woff2" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="design/{$settings->theme|escape}/fonts/{$font_name}/{$font_name}-{$font}-700.woff2" as="font" type="font/woff2" crossorigin="anonymous">
{/foreach}

<style>
{foreach $fonts as $font}

@font-face { font-family: 'FiraSans'; font-style: normal; font-weight: 400; font-display: swap; src: url('design/{$settings->theme|escape}/fonts/{$font_name}/{$font_name}-{$font}-400.woff2') format('woff2'); unicode-range: {$unicode_range[$font]}; }
@font-face { font-family: 'FiraSans'; font-style: normal; font-weight: 500; font-display: swap; src: url('design/{$settings->theme|escape}/fonts/{$font_name}/{$font_name}-{$font}-500.woff2') format('woff2'); unicode-range: {$unicode_range[$font]}; }
@font-face { font-family: 'FiraSans'; font-style: normal; font-weight: 700; font-display: swap; src: url('design/{$settings->theme|escape}/fonts/{$font_name}/{$font_name}-{$font}-700.woff2') format('woff2'); unicode-range: {$unicode_range[$font]}; }

{/foreach}

html, body { font-family: 'FiraSans', sans-serif; }
</style>