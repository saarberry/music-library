const mix = require('laravel-mix');
const path = require('path');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .alias({
        '@': path.join(__dirname, 'resources/js')
    })
    .extract(['vue', 'axios'])
    .sass('resources/scss/app.scss', 'public/css')
    .options({ processCssUrls: false })
    .vue();

if (mix.inProduction()) {
    mix.version();
} else {
    mix.sourceMaps()
        .webpackConfig({ devtool: "inline-source-map" });
}
