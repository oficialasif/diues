/** @type {import('next').NextConfig} */
const nextConfig = {
  images: {
    domains: ['localhost', 'localhost:8080', 'diu-esports-backend.onrender.com'],
    unoptimized: true,
  },
  webpack: (config) => {
    config.module.rules.push({
      test: /\.(glsl|vs|fs|vert|frag)$/,
      exclude: /node_modules/,
      use: ['raw-loader', 'glslify-loader'],
    });
    return config;
  },
}

module.exports = nextConfig
