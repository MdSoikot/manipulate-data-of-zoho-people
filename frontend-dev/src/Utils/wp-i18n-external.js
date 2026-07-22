// Bridges '@wordpress/i18n' imports to the wp-i18n script WordPress enqueues.
// Wired up via the resolve.alias in vite.config.js.
const i18n = (typeof window !== 'undefined' && window.wp && window.wp.i18n) || null

export const __ = i18n ? i18n.__ : (text) => text
export const sprintf = i18n ? i18n.sprintf : (fmt) => fmt
export default i18n
