import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    safelist: [
        'transition-page',
    ],
    theme: {
        extend: {
            // Add custom theme if needed
        },
    },
    plugins: [],
}
