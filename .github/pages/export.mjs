/**
 * Salva o sistema como site estático para o GitHub Pages.
 *
 * Com o Laravel rodando (php artisan serve) e o banco com o salão de demonstração, este script:
 *   1. abre cada tela da lista do urls.php (as públicas sem login, as do painel logado como dono@salao.test);
 *   2. segue os links que aparecem nos dados das telas (ex.: os horários do agendamento online);
 *   3. grava cada tela como index.html (para abrir direto) e page.json (para o lib/demo.ts navegar sem recarregar);
 *      as telas do agendamento online achadas pelos links (cada dia, profissional e horário) ficam só com o page.json,
 *      para o site não pesar;
 *   4. troca o endereço local pelo endereço do GitHub Pages.
 *
 * Uso: node .github/pages/export.mjs <urls.json> <pasta de saída> <endereço do Pages>
 * Ex.: node .github/pages/export.mjs urls.json _site https://gomesdev9.github.io/salao-saas
 */

import { cpSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';

const [urlsFile, outDir, siteUrl] = process.argv.slice(2);
const origin = process.env.APP_ORIGIN ?? 'http://127.0.0.1:8000';
const maxPages = 20000;

if (!urlsFile || !outDir || !siteUrl) {
    console.error('Uso: node export.mjs <urls.json> <pasta de saída> <endereço do Pages>');
    process.exit(1);
}

const site = new URL(siteUrl.replace(/\/$/, '') + '/');
const basePath = site.pathname.replace(/\/$/, '');
const urls = JSON.parse(readFileSync(urlsFile, 'utf8'));

/** Mesma regra do demoQueryKey em resources/js/lib/demo.ts. */
function queryKey(search) {
    const params = [...new URLSearchParams(search).entries()].filter(([, value]) => value !== '');

    params.sort(([a], [b]) => (a < b ? -1 : a > b ? 1 : 0));

    return params
        .map(([key, value]) => `${key}=${value}`)
        .join('&')
        .replace(/[^A-Za-z0-9-]/g, (char) => `_${char.charCodeAt(0).toString(16)}`);
}

function folderFor(url) {
    const path = url.pathname.replace(/\/$/, '');
    const key = queryKey(url.search);

    return key ? `${path}/_q/${key}` : path;
}

/** Troca http://127.0.0.1:8000 pelo endereço do Pages, também na forma com barras escapadas do JSON. */
function rewrite(text) {
    const target = site.href.replace(/\/$/, '');
    const escape = (value) => value.replaceAll('/', '\\/');

    return text
        .replaceAll(origin, target)
        .replaceAll(escape(origin), escape(target))
        .replaceAll('"port":8000', '"port":null');
}

class Session {
    cookies = new Map();

    remember(response) {
        for (const cookie of response.headers.getSetCookie()) {
            const [pair] = cookie.split(';');
            const index = pair.indexOf('=');
            this.cookies.set(pair.slice(0, index), pair.slice(index + 1));
        }
    }

    header() {
        return [...this.cookies].map(([name, value]) => `${name}=${value}`).join('; ');
    }

    async get(url) {
        const response = await fetch(url, { headers: { cookie: this.header() }, redirect: 'manual' });
        this.remember(response);

        return response;
    }

    async login(email, password) {
        await this.get(`${origin}/login`);
        const token = decodeURIComponent(this.cookies.get('XSRF-TOKEN') ?? '');
        const response = await fetch(`${origin}/login`, {
            method: 'POST',
            redirect: 'manual',
            headers: {
                cookie: this.header(),
                'content-type': 'application/json',
                accept: 'text/html',
                'x-xsrf-token': token,
            },
            body: JSON.stringify({ email, password }),
        });
        this.remember(response);

        if (response.status !== 302 || !response.headers.get('location')?.endsWith('/dashboard')) {
            throw new Error(`Não consegui entrar como ${email} (status ${response.status}).`);
        }
    }
}

const pageScript = /(<script[^>]*data-page="app"[^>]*>)([\s\S]*?)(<\/script>)/;

function pageFromHtml(html) {
    const script = html.match(pageScript);

    return script ? JSON.parse(script[2]) : null;
}

/** A tela com o endereço dentro da pasta do Pages (/dashboard vira /salao-saas/dashboard), para o navegador mostrar a URL certa. */
function withBasePath(page) {
    return { ...page, url: `${basePath}${page.url}` };
}

function pageJson(page) {
    return JSON.stringify(page).replaceAll('/', '\\/').replaceAll('<', '\\u003c');
}

/** Os endereços do próprio sistema que aparecem nos dados da tela (links prontos vindos do Laravel). */
function linksIn(value, found = new Set()) {
    if (typeof value === 'string') {
        if (value.startsWith(`${origin}/`) && !value.startsWith(`${origin}/build/`)) {
            found.add(value.split('#')[0]);
        }
    } else if (value && typeof value === 'object') {
        for (const item of Object.values(value)) {
            linksIn(item, found);
        }
    }

    return found;
}

/** Só segue links do agendamento online e das telas da lista; o resto do painel já vem do urls.php. */
function shouldFollow(url) {
    const path = new URL(url).pathname;

    return path.startsWith('/agendar/') && !path.includes('/calendario');
}

const saved = new Set();
let skipped = 0;

function write(path, content) {
    mkdirSync(dirname(path), { recursive: true });
    writeFileSync(path, content);
}

function headScript() {
    // Ao abrir direto uma URL com parâmetros (ex.: /agenda?data=...), vai para a pasta salva dela
    return `<script>(function(){var s=location.search;if(!s)return;var p=[];new URLSearchParams(s).forEach(function(v,k){if(v!=='')p.push([k,v])});if(!p.length)return;p.sort(function(a,b){return a[0]<b[0]?-1:a[0]>b[0]?1:0});var k=p.map(function(x){return x[0]+'='+x[1]}).join('&').replace(/[^A-Za-z0-9-]/g,function(c){return '_'+c.charCodeAt(0).toString(16)});location.replace(location.pathname.replace(/\\/$/,'')+'/_q/'+k+'/'+location.hash)})();</script>`;
}

async function crawl(session, startUrls) {
    const queue = [...startUrls];

    while (queue.length > 0 && saved.size < maxPages) {
        const item = queue.shift();
        const url = typeof item === 'string' ? item : item.url;
        const jsonOnly = typeof item !== 'string' && item.jsonOnly;
        const parsed = new URL(url);
        const folder = folderFor(parsed);

        if (saved.has(folder)) {
            continue;
        }

        saved.add(folder);
        const response = await session.get(url);

        if (response.status !== 200) {
            skipped++;
            console.warn(`  pulada (${response.status}): ${url}`);
            continue;
        }

        const html = await response.text();
        const original = pageFromHtml(html);

        if (!original) {
            skipped++;
            console.warn(`  sem tela do Inertia: ${url}`);
            continue;
        }

        const target = join(outDir, folder);
        const page = withBasePath(original);
        const withMeta = html.replace(pageScript, (_, open, __, close) => `${open}${pageJson(page)}${close}`).replace('<head>', `<head>\n        <meta name="demo-base" content="${basePath}">\n        ${headScript()}`);

        if (!jsonOnly) {
            write(join(target, 'index.html'), rewrite(withMeta));
        }

        write(join(target, 'page.json'), rewrite(pageJson(page)));

        // Os dados da cliente para cada horário livre (a tela de horário monta esse link no navegador)
        if (page.component === 'booking/time') {
            for (const period of page.props.periods) {
                for (const slot of period.slots.filter((option) => option.free)) {
                    const query = new URLSearchParams({ ...page.props.params, hora: slot.time });
                    queue.push({ url: `${page.props.nextUrl}?${query}`, jsonOnly: true });
                }
            }
        }

        for (const link of linksIn(page)) {
            if (shouldFollow(link)) {
                queue.push({ url: link, jsonOnly: new URL(link).search !== '' });
            }
        }
    }
}

mkdirSync(outDir, { recursive: true });
cpSync('public', outDir, {
    recursive: true,
    filter: (source) => !source.endsWith('index.php') && !source.endsWith('/hot') && !source.endsWith('/storage'),
});

console.log('Telas do painel…');
const owner = new Session();
await owner.login('dono@salao.test', 'password');
await crawl(owner, urls.private);

console.log('Telas públicas…');
await crawl(new Session(), urls.public);

// Endereço que não foi salvo: tenta a mesma tela sem os parâmetros e, se não der, a página inicial
write(
    join(outDir, '404.html'),
    `<!DOCTYPE html><meta charset="utf-8"><title>Salão SaaS</title><script>var p=location.pathname.split('/_q/');location.replace(p.length>1?p[0]+'/':${JSON.stringify(site.href)})</script>`,
);
write(join(outDir, '.nojekyll'), '');

console.log(`Pronto: ${saved.size - skipped} telas salvas em ${outDir} (${skipped} puladas).`);
