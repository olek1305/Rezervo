import { createI18n } from 'vue-i18n';
import en from './lang/en/en.json';
import pl from './lang/pl/pl.json';

const i18n = createI18n({
    legacy: false,
    locale: localStorage.getItem('lang') || 'en',
    fallbackLocale: 'en',
    messages: {
        en,
        pl,
    },
});

export default i18n;
