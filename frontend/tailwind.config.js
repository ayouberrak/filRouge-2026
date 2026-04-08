/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'bg-main': 'var(--bg-main)',
        'card-bg': 'var(--card-bg)',
        'accent-primary': 'var(--accent-primary)',
        'accent-secondary': 'var(--accent-secondary)',
        'accent-danger': 'var(--accent-danger)',
        'text-primary': 'var(--text-primary)',
        'text-secondary': 'var(--text-secondary)',
      },
      fontFamily: {
        'display': ['var(--font-display)', 'Poppins', 'sans-serif'],
        'body': ['var(--font-body)', 'Roboto', 'sans-serif'],
      }
    },
  },
  plugins: [],
}

