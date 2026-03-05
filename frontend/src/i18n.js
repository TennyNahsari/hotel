import { createI18n } from 'vue-i18n'
import en from './locales/en.json'
import id from './locales/id.json'

// Get saved locale from localStorage or use browser default
const savedLocale = localStorage.getItem('locale')
const browserLocale = navigator.language.split('-')[0]
const defaultLocale = savedLocale || (browserLocale === 'id' ? 'id' : 'en')

const i18n = createI18n({
  legacy: false,
  locale: defaultLocale,
  fallbackLocale: 'en',
  messages: {
    en,
    id
  }
})

export default i18n
