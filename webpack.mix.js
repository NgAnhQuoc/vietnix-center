const mix = require("laravel-mix");
const minifier = require('minifier');
// require("laravel-mix-versionhash");
require("laravel-mix-tailwind");
require("laravel-mix-purgecss");
var fs = require("fs");

const tailwindcss = require("tailwindcss");

mix.webpackConfig({
  resolve: {
    extensions: [".*", ".wasm", ".mjs", ".js", ".jsx", ".json", "*.scss"],
  }
});

mix.setPublicPath('./build')

mix.js("assets/js/admin.js", "js");
mix.js("assets/js/vnx-app.js", "js");

mix.sass("assets/scss/vnx-general.scss", "build/css/vnx-general.css", {}, [
  tailwindcss("./config/admin/tailwind.config.js"),
]).sass("assets/scss/vnx-theme.scss", "build/css/vnx-theme.css", {}, [
  tailwindcss("./config/vietnix-theme/tailwind.config.js"),
]).sass("assets/scss/vnx-bricks.scss", "build/css/vnx-bricks.css", {}, [
  tailwindcss("./config/bricks/tailwind.config.js"),
]).sass("assets/scss/brickstailwindBase.scss", "build/css/brickstailwindBase.css", {}, [
  tailwindcss("./config/bricks/tailwind.config.js"),
]).options({
  processCssUrls: false,
});

mix.sass("assets/scss/app.scss", "build/css/app.css")
mix.sass("assets/scss/single_post.scss", "build/css/single_post.css")
mix.sass("assets/scss/single_post_v2.scss", "build/css/single_post_v2.css")
mix.sass("assets/scss/vnx-fontawesome.scss", "build/css/vnx-fontawesome.css").then(() => {
  minifier.minify('build/css/vnx-fontawesome.css')
});
const gutenbergScssFiles = fs.readdirSync('assets/scss/gutenberg');
gutenbergScssFiles.forEach(file => {
  mix.sass('assets/scss/gutenberg/' + file, 'build/css/gutenberg/');
});

const FrontendJsFiles = fs.readdirSync('assets/js/frontend');
FrontendJsFiles.forEach(file => {
  mix.js('assets/js/frontend/' + file, 'build/js/frontend/');
});

// if (mix.inProduction()) {
//   mix.versionHash();
//   mix.sourceMaps();
// }
