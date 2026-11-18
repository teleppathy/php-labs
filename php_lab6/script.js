'use strict';

const API_URL = 'api.php?resource=articles';

const resultsEl = document.getElementById('results');
const logEl = document.getElementById('log');
const addForm = document.getElementById('add-form');
const getForm = document.getElementById('get-form');

/* ---------- Допоміжні функції ---------- */

function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatDate(isoDate) {
    const [y, m, d] = String(isoDate).split('-');
    return `${d}.${m}.${y}`;
}

function showState(text, isError = false) {
    resultsEl.innerHTML =
        `<tr class="state-row ${isError ? 'error' : ''}"><td colspan="5">${escapeHtml(text)}</td></tr>`;
}

/**
 * Універсальний виклик API: показує метод, URL, код статусу й тіло відповіді в блоці «Відповідь API».
 * Повертає { status, ok, body }; при мережевій помилці — кидає Error.
 */
async function callApi(url, options = {}) {
    const method = options.method || 'GET';

    try {
        const response = await fetch(url, options);
        const text = await response.text();

        let body = null;
        let pretty = text;
        try {
            body = JSON.parse(text);
            pretty = JSON.stringify(body, null, 2);
        } catch (e) {
            // відповідь не є JSON — показуємо як є
        }

        const cls = response.ok ? 'status-ok' : 'status-fail';
        logEl.innerHTML =
            `${escapeHtml(method)} ${escapeHtml(url)}\n` +
            `<span class="${cls}">HTTP ${response.status} ${escapeHtml(response.statusText)}</span>\n\n` +
            escapeHtml(pretty);

        return { status: response.status, ok: response.ok, body };
    } catch (error) {
        logEl.innerHTML =
            `${escapeHtml(method)} ${escapeHtml(url)}\n` +
            `<span class="status-fail">Помилка мережі: не вдалося зв'язатися із сервером.</span>`;
        throw error;
    }
}

/* ---------- GET: список ---------- */

async function loadList(keepLog = false) {
    showState('Завантаження…');
    const savedLog = logEl.innerHTML;

    try {
        const result = await callApi(API_URL);

        if (!result.ok || !result.body || !result.body.success) {
            const msg = (result.body && result.body.error) || `Помилка сервера (HTTP ${result.status}).`;
            showState(msg, true);
            return;
        }

        const articles = result.body.data;
        if (articles.length === 0) {
            showState('Статей поки немає.');
            return;
        }

        resultsEl.innerHTML = '';
        for (const article of articles) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${Number(article.id)}</td>
                <td><strong>${escapeHtml(article.title)}</strong></td>
                <td>${escapeHtml(article.author)}</td>
                <td>${escapeHtml(formatDate(article.published_at))}</td>
                <td>
                    <button type="button" class="btn btn-light btn-sm" data-publish="${Number(article.id)}">
                        Опублікувати
                    </button>
                </td>`;
            resultsEl.appendChild(tr);
        }
    } catch (error) {
        showState('Помилка мережі: не вдалося зв\'язатися із сервером.', true);
    } finally {
        // після publish/створення у блоці лишається відповідь на ту дію, а не на оновлення списку
        if (keepLog) {
            logEl.innerHTML = savedLog;
        }
    }
}

/* ---------- POST action=publish ---------- */

resultsEl.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-publish]');
    if (!button) return;

    button.disabled = true;
    try {
        const id = button.dataset.publish;
        await callApi(`${API_URL}&action=publish&id=${encodeURIComponent(id)}`, { method: 'POST' });
        await loadList(true); // оновлюємо таблицю, відповідь publish лишається в блоці
    } catch (error) {
        // повідомлення вже показано в блоці «Відповідь API»
    } finally {
        button.disabled = false;
    }
});

/* ---------- GET: одна стаття ---------- */

getForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const id = document.getElementById('get-id').value;
    try {
        await callApi(`${API_URL}&id=${encodeURIComponent(id)}`);
    } catch (error) { /* показано в логу */ }
});

document.getElementById('get-bad').addEventListener('click', async () => {
    try {
        await callApi(`${API_URL}&id=999999`);
    } catch (error) { /* показано в логу */ }
});

/* ---------- POST: створення ---------- */

addForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const payload = {
        title: addForm.elements.title.value,
        content: addForm.elements.content.value,
        author: addForm.elements.author.value,
        published_at: addForm.elements.published_at.value,
    };

    try {
        const result = await callApi(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        if (result.status === 201) {
            addForm.reset();
            await loadList(true);
        }
    } catch (error) { /* показано в логу */ }
});

/* ---------- Старт ---------- */

loadList();
