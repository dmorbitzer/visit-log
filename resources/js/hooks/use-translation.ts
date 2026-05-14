import { usePage } from '@inertiajs/react';

type Replacements = Record<string, string | number>;

export function useTranslation() {
    const { translations } = usePage().props as {
        translations: Record<string, string>;
    };

    const t = (key: string, replacements?: Replacements): string => {
        let translation = translations?.[key] ?? key;

        if (replacements) {
            Object.entries(replacements).forEach(([placeholder, value]) => {
                translation = translation.replace(
                    `:${placeholder}`,
                    String(value),
                );
            });
        }

        return translation;
    };

    return { t };
}
