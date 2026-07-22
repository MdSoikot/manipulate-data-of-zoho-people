import path from 'node:path'
import fs from 'node:fs'
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { viteStaticCopy } from 'vite-plugin-static-copy'
import autoprefixer from 'autoprefixer'

const hotFile = path.resolve(__dirname, 'hot')

// Laravel-style hot file so PHP can detect a running dev server
const wpDevServer = () => ({
  name: 'bitwelzp-hot-file',
  configureServer(server) {
    server.httpServer?.once('listening', () => {
      const { port } = server.httpServer.address()
      fs.writeFileSync(hotFile, `http://localhost:${port}`)
    })
    const clean = () => { if (fs.existsSync(hotFile)) fs.unlinkSync(hotFile) }
    process.on('exit', clean)
    process.on('SIGINT', () => process.exit())
    process.on('SIGTERM', () => process.exit())
  },
})

// drop the empty JS stub emitted for the CSS-only "bitwelzp" entry
const dropCssEntryStub = () => ({
  name: 'bitwelzp-drop-css-stub',
  generateBundle(_, bundle) {
    for (const key of Object.keys(bundle)) {
      if (bundle[key].type === 'chunk' && bundle[key].name === 'bitwelzp') delete bundle[key]
    }
  },
})

export default defineConfig(({ mode }) => ({
  // relative base: lazy chunks resolve against the URL of index.js, so the
  // per-install assets URL needs no runtime public-path code
  base: './',
  publicDir: false, // public/wp_index.html is a PHP template, not a static asset
  plugins: [
    react({
      babel: {
        plugins: [['@wordpress/babel-plugin-makepot', { output: path.resolve(__dirname, 'locale.pot') }]],
      },
    }),
    viteStaticCopy({
      targets: [
        { src: 'public/wp_index.html', dest: '../views', rename: 'view-root.php' },
        { src: 'manifest.json', dest: 'js' },
        { src: 'logo.svg', dest: 'img' },
        { src: 'redirect.php', dest: 'js', rename: 'index.php' },
      ],
    }),
    wpDevServer(),
    dropCssEntryStub(),
  ],
  define: {
    'process.env.NODE_ENV': JSON.stringify(mode === 'production' ? 'production' : 'development'),
    'process.env': JSON.stringify({ NODE_ENV: mode === 'production' ? 'production' : 'development' }),
  },
  resolve: {
    // wp.i18n is provided by the wp-i18n script WordPress enqueues
    alias: { '@wordpress/i18n': path.resolve(__dirname, 'src/Utils/wp-i18n-external.js') },
  },
  css: {
    postcss: { plugins: [autoprefixer()] },
  },
  optimizeDeps: {
    esbuildOptions: { loader: { '.js': 'jsx' } },
  },
  esbuild: mode === 'production' ? { drop: ['console', 'debugger'] } : {},
  server: {
    port: 5173,
    strictPort: true,
    cors: true,
    origin: 'http://localhost:5173',
  },
  build: {
    outDir: '../assets',
    emptyOutDir: true, // assets/ is entirely build output (gitignored)
    assetsInlineLimit: 3000,
    rollupOptions: {
      input: {
        index: path.resolve(__dirname, 'src/index.jsx'),
        bitwelzp: path.resolve(__dirname, 'src/resource/sass/app.scss'),
      },
      output: {
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: (info) => {
          const name = info.names?.[0] ?? info.name
          // stable name: PHP enqueues css/bitwelzp.css directly
          if (name === 'bitwelzp.css') return 'css/bitwelzp.css'
          if (/\.css$/.test(name)) return 'css/[name]-[hash][extname]'
          return 'img/[name]-[hash][extname]'
        },
      },
    },
  },
}))
