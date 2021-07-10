import qs from 'qs';

const FORK_AJAX_ENDPOINT = '/frontend/ajax';

export async function requestAjax(
    module: string,
    action: string,
    payload: any,
    signal: AbortSignal | null = null,
): Promise<any> {
    const data = { fork: { module, action, language: window.jsData.LANGUAGE }, ...payload };

    const response = await fetch(FORK_AJAX_ENDPOINT, {
        method: 'POST',
        cache: 'no-cache',
        ...(signal !== null ? { signal: signal } : {}),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'x-requested-with': 'XMLHttpRequest', // Make sure app.request.xmlHttpRequest works
            pragma: 'no-cache',
            'Cache-Control': 'no-cache',
        },
        body: qs.stringify(data),
    });

    return response.json();
}
