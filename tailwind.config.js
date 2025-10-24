/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Http/Livewire/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        // 虹宇職訓品牌色系
        primary: {
          DEFAULT: '#4F46E5',  // 虹宇紫藍色
          dark: '#4338CA',      // 深紫藍（hover）
          light: '#818CF8',     // 淺紫藍（背景）
        },
        secondary: '#10B981',   // 綠色（成功）
        accent: '#F59E0B',      // 橙色（警告/強調）
        danger: '#EF4444',      // 紅色（錯誤）
      },
      animation: {
        'slide-in-up': 'slideInUp 0.3s ease-out',
        'slide-in-right': 'slideInRight 0.3s ease-out',
      },
      keyframes: {
        slideInUp: {
          '0%': { transform: 'translateY(100%)', opacity: '0' },
          '100%': { transform: 'translateY(0)', opacity: '1' },
        },
        slideInRight: {
          '0%': { transform: 'translate(100%, 100%)', opacity: '0' },
          '100%': { transform: 'translate(0, 0)', opacity: '1' },
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
