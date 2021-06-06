let initialized = false;
let initializing = false;
let data: LocaleData;

type LocaleType = 'act' | 'err' | 'lbl' | 'loc' | 'msg';
type LocaleData = {
    [type in LocaleType]: {
        [key: string]: string;
    };
};

async function init(): Promise<void> {
    if (typeof window.jsData.LANGUAGE === 'undefined') {
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

export async function get(type: LocaleType, key: string): Promise<string> {
    // initialize  if needed
    if (!initialized && !initializing) {
        console.log('calling init');
        await init();
    }

    if (typeof data[type] === 'undefined' || typeof data[type][key] === 'undefined') {
        return '{$' + type + key + '}';
    }

    return data[type][key];
}

export function act(key: string): Promise<string> {
    return get('act', key);
}

export function err(key: string): Promise<string> {
    return get('err', key);
}

export function lbl(key: string): Promise<string> {
    return get('lbl', key);
}

export function loc(key: string): Promise<string> {
    return get('loc', key);
}

export function msg(key: string): Promise<string> {
    return get('msg', key);
}
