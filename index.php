<?php
/* index.php — Akilli Dolap & AI Sef arayuzu (giris gerektirir) */
require __DIR__ . '/auth.php';
require_login();
$username = current_username();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akilli Dolap &amp; AI Sef</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800">

    <header class="bg-emerald-600 text-white py-5 shadow">
        <div class="max-w-6xl mx-auto px-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Akilli Dolap &amp; AI Sef</h1>
                <p class="text-emerald-100 text-sm">Dolabindaki malzemelerle ne pisirebilirsin?</p>
            </div>
            <div class="text-right text-sm">
                <span class="block"><?= htmlspecialchars($username) ?></span>
                <a href="logout.php" class="text-emerald-100 hover:text-white underline">Cikis yap</a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6 grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- KOLON 1: Dolap (envanter) -->
        <section class="bg-white rounded-xl shadow p-5">
            <h2 class="text-lg font-semibold mb-4">Dolabim</h2>

            <form id="addForm" class="space-y-2 mb-4">
                <input type="text" id="name" placeholder="Malzeme (or. Domates)" required
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
                <div class="flex gap-2">
                    <input type="number" id="quantity" placeholder="Miktar" step="0.1" min="0" required
                           class="w-1/2 border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
                    <select id="unit"
                            class="w-1/2 border rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-emerald-400 outline-none">
                        <option value="adet">adet</option>
                        <option value="gram">gram</option>
                        <option value="kg">kg</option>
                        <option value="litre">litre</option>
                        <option value="ml">ml</option>
                        <option value="paket">paket</option>
                        <option value="dilim">dilim</option>
                        <option value="demet">demet</option>
                        <option value="dis">dis</option>
                        <option value="su bardagi">su bardagi</option>
                        <option value="yemek kasigi">yemek kasigi</option>
                        <option value="cay kasigi">cay kasigi</option>
                        <option value="tutam">tutam</option>
                    </select>
                </div>
                <button type="submit"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg py-2 text-sm font-medium transition">
                    + Dolaba Ekle
                </button>
            </form>

            <ul id="inventoryList" class="space-y-2 text-sm"></ul>
        </section>

        <!-- KOLON 2: AI Sef -->
        <section class="bg-white rounded-xl shadow p-5">
            <h2 class="text-lg font-semibold mb-4">AI Sef</h2>
            <p class="text-sm text-slate-500 mb-3">
                Dolabindaki malzemelere gore sana bir tarif onersin.
            </p>
            <label class="block text-sm text-slate-600 mb-1">Hangi ogun icin?</label>
            <select id="mealType"
                    class="w-full border rounded-lg px-3 py-2 text-sm bg-white mb-3 focus:ring-2 focus:ring-indigo-400 outline-none">
                <option value="farketmez">Farketmez</option>
                <option value="kahvalti">Kahvalti</option>
                <option value="gec kahvalti (brunch)">Gec kahvalti (brunch)</option>
                <option value="ogle yemegi">Ogle yemegi</option>
                <option value="aksam yemegi">Aksam yemegi</option>
                <option value="cay saati ikrami (misafire)">Cay saati ikrami (misafire)</option>
                <option value="atistirmalik">Atistirmalik</option>
                <option value="tatli">Tatli</option>
            </select>
            <button id="generateBtn"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg py-3 font-medium transition flex items-center justify-center gap-2">
                Tarif Oner
            </button>

            <div id="spinner" class="hidden mt-6 flex flex-col items-center text-indigo-600">
                <svg class="animate-spin h-8 w-8" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="text-sm mt-2">Sef dusunuyor...</span>
            </div>
        </section>

        <!-- KOLON 3: Tarif sonucu -->
        <section class="bg-white rounded-xl shadow p-5">
            <h2 class="text-lg font-semibold mb-4">Tarif</h2>
            <div id="recipeArea" class="text-sm text-slate-500">
                Henuz bir tarif olusturulmadi.
            </div>
        </section>
    </main>

    <!-- Gecmis tarifler (tam genislik) -->
    <section class="max-w-6xl mx-auto px-4 pb-10">
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-lg font-semibold mb-4">Gecmis Tarifler</h2>
            <ul id="historyList" class="space-y-2 text-sm"></ul>
        </div>
    </section>

<script>
const $ = (id) => document.getElementById(id);
let currentRecipe = null;

async function loadInventory() {
    const res  = await fetch('manage_inventory.php?action=list');
    const data = await res.json();
    const list = $('inventoryList');
    list.innerHTML = '';

    if (!data.success || data.items.length === 0) {
        list.innerHTML = '<li class="text-slate-400">Dolap bos.</li>';
        return;
    }
    data.items.forEach(item => {
        const li = document.createElement('li');
        li.className = 'flex justify-between items-center border rounded-lg px-3 py-2';
        li.innerHTML = `
            <span><strong>${item.name}</strong> — ${item.quantity} ${item.unit}</span>
            <button data-id="${item.id}" class="del text-red-500 hover:text-red-700 text-xs">Sil</button>`;
        list.appendChild(li);
    });

    document.querySelectorAll('.del').forEach(btn => {
        btn.addEventListener('click', () => deleteItem(btn.dataset.id));
    });
}

$('addForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = new URLSearchParams({
        action:   'add',
        name:     $('name').value,
        quantity: $('quantity').value,
        unit:     $('unit').value || 'adet',
    });
    const res  = await fetch('manage_inventory.php', { method: 'POST', body });
    const data = await res.json();
    if (data.success) {
        $('name').value = '';
        $('quantity').value = '';
        $('unit').value = 'adet';
        loadInventory();
    } else {
        alert(data.error || 'Eklenemedi.');
    }
});

async function deleteItem(id) {
    const body = new URLSearchParams({ action: 'delete', id });
    await fetch('manage_inventory.php', { method: 'POST', body });
    loadInventory();
}

// Onerilen tarif basliklari — "Baska Tarif Oner"de bunlari haric tutariz
let suggestedTitles = [];

async function generateRecipe() {
    $('spinner').classList.remove('hidden');
    $('recipeArea').innerHTML = '';
    try {
        const res  = await fetch('ai_recipe.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ exclude: suggestedTitles, meal: $('mealType').value }),
        });
        const data = await res.json();
        if (!data.success) {
            $('recipeArea').innerHTML = `<p class="text-red-500">${data.error}</p>`;
            return;
        }
        currentRecipe = data.recipe;
        if (data.recipe && data.recipe.title) suggestedTitles.push(data.recipe.title);
        renderRecipe(data.recipe, data.demo);
    } catch (err) {
        $('recipeArea').innerHTML = `<p class="text-red-500">Baglanti hatasi: ${err}</p>`;
    } finally {
        $('spinner').classList.add('hidden');
    }
}

$('generateBtn').addEventListener('click', generateRecipe);

function renderRecipe(r, demo, readOnly) {
    const ing = r.ingredients.map(i =>
        `<li>${i.name} — ${i.quantity} ${i.unit || ''}</li>`).join('');
    const steps = r.steps.map(s => `<li>${s}</li>`).join('');

    // Dolapta olmayan, alinmasi gereken malzemeler (varsa)
    const missingBlock = (r.missing && r.missing.length)
        ? `<p class="font-semibold text-amber-600 mt-3">Eksik (almaniz gerekenler):</p>
           <ul class="list-disc list-inside mb-3 text-amber-700">` +
          r.missing.map(i =>
            `<li>${i.name}${i.quantity ? ' — ' + i.quantity + ' ' + (i.unit || '') : ''}</li>`).join('') +
          `</ul>`
        : '';

    $('recipeArea').innerHTML = `
        ${demo ? '<p class="text-xs bg-amber-100 text-amber-700 rounded px-2 py-1 mb-2">DEMO MODU</p>' : ''}
        <h3 class="text-base font-bold text-slate-800">${r.title}</h3>
        <p class="text-slate-500 mb-3">${r.description || ''}</p>
        <p class="font-semibold">Malzemeler (dolabinda var):</p>
        <ul class="list-disc list-inside mb-1">${ing}</ul>
        ${missingBlock}
        <p class="font-semibold">Yapilisi:</p>
        <ol class="list-decimal list-inside mb-4 space-y-1">${steps}</ol>
        ${readOnly ? '' : `<button id="madeBtn"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg py-2 font-medium transition">
            Bu Tarifi Yaptim!
        </button>
        <button id="otherBtn"
                class="w-full mt-2 border border-indigo-500 text-indigo-600 hover:bg-indigo-50 rounded-lg py-2 font-medium transition">
            Begenmedim, Baska Tarif Oner
        </button>`}`;

    if (!readOnly) {
        $('madeBtn').addEventListener('click', consumeRecipe);
        $('otherBtn').addEventListener('click', generateRecipe);
    }
}

async function consumeRecipe() {
    if (!currentRecipe) return;
    const res = await fetch('consume.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ recipe: currentRecipe }),
    });
    const data = await res.json();
    if (data.success) {
        $('recipeArea').innerHTML =
            '<p class="text-emerald-600 font-medium">Afiyet olsun! Malzemeler dolaptan dusuldu. Tarif gecmise eklendi.</p>';
        currentRecipe = null;
        loadInventory();
        loadHistory();
    } else {
        alert(data.error || 'Islem basarisiz.');
    }
}

// Gecmis tarifleri yukle ve listele
async function loadHistory() {
    const res  = await fetch('recipes.php');
    const data = await res.json();
    const list = $('historyList');
    list.innerHTML = '';
    if (!data.success || data.recipes.length === 0) {
        list.innerHTML = '<li class="text-slate-400">Henuz kaydedilmis tarif yok. Bir tarif yapinca burada birikir.</li>';
        return;
    }
    data.recipes.forEach(row => {
        const li = document.createElement('li');
        li.className = 'flex justify-between items-center border rounded-lg px-3 py-2';
        const date = (row.created_at || '').replace('T', ' ').slice(0, 16);
        li.innerHTML =
            `<span><strong>${row.title}</strong> <span class="text-slate-400 text-xs">${date}</span></span>
             <button class="view text-indigo-600 hover:text-indigo-800 text-xs">Goster</button>`;
        li.querySelector('.view').addEventListener('click', () => {
            try {
                renderRecipe(JSON.parse(row.data), false, true);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (e) { /* bozuk kayit, atla */ }
        });
        list.appendChild(li);
    });
}

loadInventory();
loadHistory();
</script>
</body>
</html>
