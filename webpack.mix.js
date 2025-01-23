const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
    .js('resources/js/chat.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        require('tailwindcss'),
    ])
    .version();

// Add notification sound to public directory
mix.copy('resources/audio/notification.mp3', 'public/audio');