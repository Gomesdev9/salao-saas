import { http } from '@inertiajs/react';

type HttpClient = Extract<Parameters<typeof http.setClient>[0], { request: unknown }>;
type HttpRequestConfig = Parameters<HttpClient['request']>[0];
type HttpResponse = Awaited<ReturnType<HttpClient['request']>>;

/**
 * Modo demonstração, usado só na cópia do sistema publicada no GitHub Pages (npm run build com VITE_DEMO=true).
 *
 * No GitHub Pages não existe servidor: cada tela foi salva antes como um arquivo page.json,
 * com os dados do salão de exemplo. Aqui as visitas do Inertia leem esses arquivos em vez de
 * chamar o Laravel, e o que salvaria algo (formulários, excluir, sair) só mostra um aviso.
 */

/** Pasta de uma URL com parâmetros: /agenda?data=2026-11-05 fica em /agenda/_q/data_3d2026-11-05/. Igual ao .github/pages/export.mjs. */
export function demoQueryKey(search: string): string {
    const params = [...new URLSearchParams(search).entries()].filter(([, value]) => value !== '');

    params.sort(([a], [b]) => (a < b ? -1 : a > b ? 1 : 0));

    return params
        .map(([key, value]) => `${key}=${value}`)
        .join('&')
        .replace(/[^A-Za-z0-9-]/g, (char) => `_${char.charCodeAt(0).toString(16)}`);
}

function jsonPath(url: URL, withQuery: boolean): string {
    const path = url.pathname.replace(/\/$/, '');
    const key = withQuery ? demoQueryKey(url.search) : '';

    return key ? `${path}/_q/${key}/page.json` : `${path}/page.json`;
}

async function loadPage(url: URL): Promise<string | null> {
    for (const candidate of [jsonPath(url, true), jsonPath(url, false)]) {
        const response = await fetch(candidate);

        if (response.ok) {
            return response.text();
        }
    }

    return null;
}

function showNotice(text: string): void {
    const notice = document.createElement('div');

    notice.textContent = text;
    notice.setAttribute('role', 'status');
    notice.style.cssText =
        'position:fixed;left:50%;bottom:24px;transform:translateX(-50%);z-index:9999;max-width:calc(100% - 32px);' +
        'background:#3b2a20;color:#fff;padding:12px 18px;border-radius:12px;font-size:14px;box-shadow:0 8px 24px rgba(0,0,0,.2)';
    document.body.appendChild(notice);
    window.setTimeout(() => notice.remove(), 3500);
}

/** Para onde vai cada envio de formulário na demonstração (o resto continua na mesma tela). */
function redirectAfterSubmit(url: URL): string | null {
    const base = (document.querySelector('meta[name="demo-base"]') as HTMLMetaElement | null)?.content ?? '';
    const path = url.pathname.slice(base.length);

    if (path === '/login' || path === '/register') {
        return `${base}/dashboard`;
    }

    if (path === '/logout') {
        return `${base}/`;
    }

    return null;
}

function inertiaResponse(data: string): HttpResponse {
    return { status: 200, data, headers: { 'x-inertia': 'true' } };
}

export function setupDemo(): void {
    http.setClient({
        async request(config: HttpRequestConfig): Promise<HttpResponse> {
            const url = new URL(config.url, window.location.href);
            const method = config.method.toLowerCase();

            if (method === 'get') {
                const page = await loadPage(url);

                if (page) {
                    return inertiaResponse(page);
                }

                showNotice('Esta tela não faz parte da demonstração.');
            } else {
                const target = redirectAfterSubmit(url);

                if (target) {
                    const page = await loadPage(new URL(target, window.location.href));

                    if (page) {
                        return inertiaResponse(page);
                    }
                }

                showNotice('Demonstração: as alterações não são salvas.');
            }

            const current = await loadPage(new URL(window.location.href));

            return inertiaResponse(current ?? '{}');
        },
    });
}
