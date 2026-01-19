const materialTheme = require('./resources/css/material-theme.js');

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: materialTheme.theme,
  plugins: [],
}
