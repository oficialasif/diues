/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './pages/**/*.{js,ts,jsx,tsx,mdx}',
    './components/**/*.{js,ts,jsx,tsx,mdx}',
    './app/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          blue: '#1D4ED8',
          'blue-dark': '#0D47A1',
        },
        neon: {
          green: '#22C55E',
          'green-glow': '#22C55E',
        },
        dark: {
          DEFAULT: '#0F172A',
          secondary: '#111827',
        },
        cyber: {
          'neon-blue': '#00F5FF',
          'neon-purple': '#8B5CF6',
          'neon-pink': '#EC4899',
        }
      },
      fontFamily: {
        'orbitron': ['Orbitron', 'monospace'],
        'russo': ['Russo One', 'sans-serif'],
        'audiowide': ['Audiowide', 'cursive'],
        'poppins': ['Poppins', 'sans-serif'],
        'rajdhani': ['Rajdhani', 'sans-serif'],
      },
      animation: {
        'glow': 'glow 2s ease-in-out infinite alternate',
        'pulse-glow': 'pulse-glow 1.5s ease-in-out infinite',
        'float': 'float 6s ease-in-out infinite',
        'glitch': 'glitch 0.3s ease-in-out infinite',
        'neon-flicker': 'neon-flicker 1.5s infinite alternate',
        'rotate-slow': 'rotate-slow 20s linear infinite',
        'slide-up': 'slide-up 0.8s ease-out',
        'slide-down': 'slide-down 0.8s ease-out',
        'fade-in': 'fade-in 1s ease-out',
        'scale-in': 'scale-in 0.5s ease-out',
      },
      keyframes: {
        glow: {
          '0%': { boxShadow: '0 0 5px #22C55E, 0 0 10px #22C55E, 0 0 15px #22C55E' },
          '100%': { boxShadow: '0 0 10px #22C55E, 0 0 20px #22C55E, 0 0 30px #22C55E' },
        },
        'pulse-glow': {
          '0%, 100%': { boxShadow: '0 0 5px #22C55E, 0 0 10px #22C55E' },
          '50%': { boxShadow: '0 0 20px #22C55E, 0 0 30px #22C55E' },
        },
        float: {
          '0%, 100%': { transform: 'translateY(0px)' },
          '50%': { transform: 'translateY(-20px)' },
        },
        glitch: {
          '0%, 100%': { transform: 'translate(0)' },
          '20%': { transform: 'translate(-2px, 2px)' },
          '40%': { transform: 'translate(-2px, -2px)' },
          '60%': { transform: 'translate(2px, 2px)' },
          '80%': { transform: 'translate(2px, -2px)' },
        },
        'neon-flicker': {
          '0%': { opacity: '1' },
          '100%': { opacity: '0.8' },
        },
        'rotate-slow': {
          '0%': { transform: 'rotate(0deg)' },
          '100%': { transform: 'rotate(360deg)' },
        },
        'slide-up': {
          '0%': { transform: 'translateY(100px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        'slide-down': {
          '0%': { transform: 'translateY(-100px)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        'fade-in': {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
        'scale-in': {
          '0%': { transform: 'scale(0.8)', opacity: '0' },
          '100%': { transform: 'scale(1)', opacity: '1' },
        },
      },
      backgroundImage: {
        'cyber-grid': 'linear-gradient(rgba(29, 78, 216, 0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(29, 78, 216, 0.1) 1px, transparent 1px)',
        'neon-gradient': 'linear-gradient(45deg, #1D4ED8, #22C55E, #8B5CF6)',
        'dark-gradient': 'linear-gradient(135deg, #0F172A 0%, #111827 100%)',
      },
      backgroundSize: {
        'grid': '50px 50px',
      },
    },
  },
  plugins: [],
}
