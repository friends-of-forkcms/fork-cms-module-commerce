let initialized = false;
let initializing = false;
let data: LocaleData;

type LocaleType = 'act' | 'err' | 'lbl' | 'loc' | 'msg';
type LocaleData = {
    [type in LocaleType]: {
        [key: string]: string;
    };
};

export async function init(): Promise<void> {
    if (typeof window.jsData.LANGUAGE === 'undefined') {
        return;
    }

    if (initialized || initializing) {
        return;
    }

    initializing = true;
    const language = window.jsData.LANGUAGE;

    try {
        const response = await fetch(`/src/Frontend/Cache/Locale/${language}.json`);
        data = (await response.json()) as unknown as LocaleData;
        initialized = true;
        initializing = true;
    } catch (error) {
        throw new Error('Regenerate your locale-files.');
    }
}

export function get(type: LocaleType, key: string): string {
    if (typeof data[type] === 'undefined' || typeof data[type][key] === 'undefined') {
        return '{$' + type + key + '}';
    }

    return data[type][key];
}

export function act(key: string): string {
    return get('act', key);
}

export function err(key: string): string {
    return get('err', key);
}

export function lbl(key: string): string {
    return get('lbl', key);
}

export function loc(key: string): string {
    return get('loc', key);
}

export function msg(key: string): string {
    return get('msg', key);
}
