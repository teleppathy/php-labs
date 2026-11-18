'use strict';

const resultsEl = document.getElementById('results');
const searchEl = document.getElementById('search');
const counterEl = document.getElementById('counter');
const messageEl = document.getElementById('message');
const formEl = document.getElementById('add-form');
const submitBtn = document.getElementById('submit-btn');

let currentQuery = '';
let requestId = 0; // щоб застарілі відповіді не перезаписували свіжіші
let totalShown = 0;

/* ---------- Допоміжні функції ---------- */

// Екранування перед вставкою в innerHTML (захист від XSS)
function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// 2026-11-18 -> 18.11.2026
function formatDate(isoDate) {
    const [y, m, d] = String(isoDate).split('-');
    return `${d}.${m}.${y}`;
}

function truncate(text, length = 60) {
    return text.length <= length ? text : text.slice(0, length).trimEnd() + '…';
}

function setToday() {
    document.getElementById('published_at').value = new Date().toISOString().slice(0, 10);
}

function updateCounter() {
    counterEl.textContent = `Знайдено: ${totalShown}`;
}

function showState(text, isError = false) {
    resultsEl.innerHTML =
        `<tr class="state-row ${isError ? 'error' : ''}"><td colspan="4">${escapeHtml(text)}</td></tr>`;
}

function showMessage(text, type) {
    messageEl.innerHTML = `<div class="alert alert-${type}">${escapeHtml(text)}</div>`;
}

function rowHtml(article) {
    return `
        <td>${Number(article.id)}</td>
        <td>
            <strong>${escapeHtml(article.title)}</strong><br>
            <span class="muted">${escapeHtml(truncate(article.content))}</span>
        </td>
        <td>${escapeHtml(article.author)}</td>
        <td>${escapeHtml(formatDate(article.published_at))}</td>`;
}

// Розбір відповіді сервера: кидає помилку з текстом від API, якщо response не ok
async function parseResponse(response) {
    let data = null;
    try {
        data = await response.json();
    } catch (e) {
        // тіло не є JSON — оброблено нижче
    }

    if (!response.ok) {
        throw new Error((data && data.error) || `Помилка сервера (HTTP ${response.status}).`);
    }
    return data;
}

/* ---------- Завантаження списку (GET) + живий пошук ---------- */

async function loadList(query = '') {
    const myRequest = ++requestId;
    showState('Завантаження…');

    try {
        const response = await fetch('api_list.php?q=' + encodeURIComponent(query));
        const articles = await parseResponse(response);

        if (myRequest !== requestId) return; // прийшла застаріла відповідь

        totalShown = articles.length;
        updateCounter();

        if (articles.length === 0) {
            showState(query ? 'За вашим запитом нічого не знайдено.' : 'Статей поки немає.');
            return;
        }

        resultsEl.innerHTML = '';
        for (const article of articles) {
            const tr = document.createElement('tr');
            tr.innerHTML = rowHtml(article);
            resultsEl.appendChild(tr);
        }
    } catch (error) {
        if (myRequest !== requestId) return;
        totalShown = 0;
        updateCounter();
        const text = error instanceof TypeError
            ? 'Помилка мережі: не вдалося зв\'язатися із сервером.'
            : error.message;
        showState(text, true);
    }
}

// Невелика затримка (debounce), щоб не надсилати запит на кожну літеру миттєво
let searchTimer = null;
searchEl.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentQuery = searchEl.value.trim();
        loadList(currentQuery);
    }, 250);
});

/* ---------- Додавання статті (POST) ---------- */

formEl.addEventListener('submit', async (event) => {
    event.preventDefault();
    messageEl.innerHTML = '';

    const payload = {
        title: formEl.elements.title.value.trim(),
        content: formEl.elements.content.value.trim(),
        author: formEl.elements.author.value.trim(),
        published_at: formEl.elements.published_at.value,
    };

    submitBtn.disabled = true;

    try {
        const response = await fetch('api_add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const article = await parseResponse(response); // очікуємо 201 + доданий запис

        formEl.reset();
        setToday();

        const matchesSearch = currentQuery === ''
            || article.title.toLowerCase().includes(currentQuery.toLowerCase());

        if (matchesSearch) {
            // Прибираємо рядок-заглушку («Статей поки немає» тощо), якщо вона є
            if (resultsEl.querySelector('.state-row')) {
                resultsEl.innerHTML = '';
            }
            const tr = document.createElement('tr');
            tr.className = 'row-new';
            tr.innerHTML = rowHtml(article);
            resultsEl.prepend(tr); // нова стаття — у верхній частині списку
            totalShown++;
            updateCounter();
            showMessage('Статтю опубліковано.', 'success');
        } else {
            showMessage('Статтю опубліковано, але вона не відповідає поточному пошуку.', 'success');
        }
    } catch (error) {
        const text = error instanceof TypeError
            ? 'Помилка мережі: не вдалося зв\'язатися із сервером.'
            : error.message;
        showMessage(text, 'error');
    } finally {
        submitBtn.disabled = false;
    }
});

/* ---------- Старт ---------- */

setToday();
loadList();
