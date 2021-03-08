import qs from 'qs';

const FORK_AJAX_ENDPOINT = '/frontend/ajax';

export async function requestAjax(module: string, action: string, payload: any): Promise<any> {
    const data = { fork: { module, action, language: window.jsData.LANGUAGE }, ...payload };

    const response = await fetch(FORK_AJAX_ENDPOINT, {
        method: 'POST',
        cache: 'no-cache',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: qs.stringify(data),
    });

    return response.json();
}
