<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Offline Child Sync | KFHI-CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; background: #f3f8f4; color: #24352a; font-family: Arial, sans-serif; }
        main { max-width: 1100px; margin: auto; }
        h1 { margin: 0 0 8px; color: #245c3a; }
        p { color: #607267; line-height: 1.5; }
        .panel { background: #fff; border-radius: 14px; padding: 20px; margin: 18px 0; box-shadow: 0 4px 16px #23482b10; }
        .filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px; align-items: end; }
        label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; }
        input, select { width: 100%; padding: 11px; border: 1px solid #cbd8ce; border-radius: 8px; background: white; }
        button, .link-button { display: inline-block; border: 0; border-radius: 8px; padding: 11px 15px; background: #287344; color: white; font-weight: 700; text-decoration: none; cursor: pointer; }
        button:disabled { opacity: .55; cursor: wait; }
        .secondary { background: #e9f2eb; color: #245c3a; }
        .warning { background: #fff3d8; color: #674b0a; }
        .status { padding: 12px; background: #f1f6f2; border-radius: 8px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 650px; }
        th, td { padding: 11px; border-bottom: 1px solid #e6eee8; text-align: left; }
        th { color: #45624e; background: #f5f9f6; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
        .private-choice { display:flex; align-items:center; gap:8px; margin-top:15px; }
        .private-choice input { width:auto; }
        @media (max-width: 600px) { body { padding: 12px; } .panel { padding: 15px; } }
    </style>
</head>
<body>
<main>
    <a class="link-button secondary" href="{{ auth()->user()->role === 'child_officer' ? route('child_officer.children.index') : route('children.index') }}">← Back to children</a>
    <section class="panel">
        <h1>Prepare child details for offline use</h1>
        <p>Download children to this device. You can also allow one, several, or all active enumerators to sync these children onto their own phones.</p>
        <div class="status" id="syncStatus" role="status">Checking this device’s saved child records…</div>
        @if(auth()->user()->role === 'admin')
        <div class="actions">
            <a class="link-button secondary" href="{{ rtrim(request()->getBaseUrl(), '/') }}/offline-child-cards.html">Open saved child cards</a>
        </div>
        @endif
    </section>

    <section class="panel">
        <div style="max-width: 520px; margin: 16px 0;">
            <strong style="display:block;margin-bottom:8px">Optional: allow enumerators to sync these children to their phones</strong>
            <label style="display:flex;align-items:center;gap:8px;margin:8px 0;font-weight:600">
                <input id="allowAllEnumerators" type="checkbox" style="width:auto">
                Allow all active enumerators
            </label>
            <div style="max-height:180px;overflow:auto;border:1px solid #cbd8ce;border-radius:8px;padding:8px">
                @forelse($enumerators as $enumerator)
                    <label style="display:flex;align-items:center;gap:8px;margin:7px 0;font-weight:normal">
                        <input class="enumerator-choice" type="checkbox" value="{{ $enumerator->id }}" style="width:auto">
                        {{ $enumerator->name }} ({{ $enumerator->enumerator_code }})
                    </label>
                @empty
                    <span>No active enumerators are available.</span>
                @endforelse
            </div>
            <small>Select none to download only to this device. “Allow all” includes every currently active enumerator.</small>
        </div>
        <form id="searchForm" class="filters">
            <div>
                <label for="district">District</label>
                <select id="district" name="district">
                    <option value="">All districts</option>
                    @foreach($districts as $district)
                        <option value="{{ $district }}">{{ $district }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="division">Division</label>
                <select id="division" name="gn_division">
                    <option value="">All divisions</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division }}">{{ $division }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="search">Child name or code</label>
                <input id="search" name="search" type="search" maxlength="100" placeholder="Search child">
            </div>
            <button type="submit">Find children</button>
        </form>
        <label class="private-choice">
            <input type="checkbox" id="includeFamily">
            Include guardian contact details (private)
        </label>
        <p id="resultSummary">Search to see matching children.</p>
        <div class="table-wrap">
            <table>
                <thead><tr><th><input type="checkbox" id="selectPage" aria-label="Select children on this page"></th><th>Child code</th><th>Name</th><th>District</th><th>Division</th></tr></thead>
                <tbody id="childrenRows"><tr><td colspan="5">No search yet.</td></tr></tbody>
            </table>
        </div>
        <div class="actions">
            <button type="button" id="syncSelected" class="secondary" disabled>Download selected children</button>
            <button type="button" id="syncFiltered" class="secondary">Download all matching filters</button>
            <button type="button" id="syncAll" class="warning">Download all children</button>
        </div>
    </section>
</main>
<script src="{{ rtrim(request()->getBaseUrl(), '/') }}/js/offline-child-cache.js"></script>
<script>
(() => {
    const searchUrl = @json(route('admin.child-sync.search'));
    const downloadUrl = @json(route('admin.child-sync.download'));
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const serviceWorkerUrl = @json(rtrim(request()->getBaseUrl(), '/') . '/service-worker.js');
    const serviceWorkerScope = @json(rtrim(request()->getBaseUrl(), '/') . '/');
    const dbName = 'kfhi-offline-child-records';
    let db;
    let currentPage = 1;
    let lastPage = 1;
    let currentTotal = 0;
    const selected = new Set();
    const allEnumeratorIds = @json($enumerators->pluck('id')->values());

    function enumeratorAccessSelection() {
        const allowAll = document.getElementById('allowAllEnumerators').checked;
        const ids = allowAll
            ? allEnumeratorIds.map(Number)
            : [...document.querySelectorAll('.enumerator-choice:checked')].map(input => Number(input.value));

        return {
            ids,
            payload: allowAll
                ? { allow_all_enumerators: true }
                : (ids.length ? { enumerator_ids: ids } : {}),
        };
    }

    const status = (message, error = false) => {
        const el = document.getElementById('syncStatus');
        el.textContent = message;
        el.style.background = error ? '#fdecec' : '#f1f6f2';
        el.style.color = error ? '#9b1c1c' : '#24352a';
    };

    function openDb() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(dbName, 1);
            request.onupgradeneeded = () => {
                const database = request.result;
                if (!database.objectStoreNames.contains('children')) {
                    const store = database.createObjectStore('children', { keyPath: 'id' });
                    store.createIndex('child_code', 'child_code', { unique: true });
                    store.createIndex('gn_division', 'gn_division');
                }
                if (!database.objectStoreNames.contains('metadata')) {
                    database.createObjectStore('metadata', { keyPath: 'key' });
                }
            };
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    function saveRecords(records, enumeratorIds) {
        return new Promise((resolve, reject) => {
            const tx = db.transaction('children', 'readwrite');
            const store = tx.objectStore('children');
            records.forEach(record => {
                const request = store.get(record.id);
                request.onsuccess = () => {
                    const old = request.result || {};
                    const allowedIds = new Set(old.allowed_enumerator_ids || []);
                    enumeratorIds.forEach(id => allowedIds.add(Number(id)));
                    const merged = { ...old, ...record, allowed_enumerator_ids: [...allowedIds] };
                    if (!Object.prototype.hasOwnProperty.call(record, 'guardians')) delete merged.guardians;
                    store.put(merged);
                };
            });
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
            tx.onabort = () => reject(tx.error || new Error('Local save was cancelled.'));
        });
    }

    function saveMetadata(value) {
        return new Promise((resolve, reject) => {
            const tx = db.transaction('metadata', 'readwrite');
            tx.objectStore('metadata').put(value);
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
        });
    }

    function countRecords() {
        return new Promise((resolve, reject) => {
            const request = db.transaction('children').objectStore('children').count();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    function filters() {
        const form = document.getElementById('searchForm');
        return Object.fromEntries(new FormData(form).entries());
    }

    async function loadPage(page = 1) {
        currentPage = page;
        const params = new URLSearchParams({ ...filters(), page: String(page) });
        const response = await fetch(`${searchUrl}?${params}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Could not load the child list. Check your connection and access.');
        const result = await response.json();
        lastPage = result.last_page;
        currentTotal = result.total;
        selected.clear();
        document.getElementById('syncSelected').disabled = true;
        document.getElementById('selectPage').checked = false;
        document.getElementById('resultSummary').textContent = `${result.total} matching children. Page ${result.current_page} of ${result.last_page}.`;
        const tbody = document.getElementById('childrenRows');
        tbody.replaceChildren();
        if (!result.data.length) {
            tbody.innerHTML = '<tr><td colspan="5">No matching children.</td></tr>';
            return;
        }
        result.data.forEach(child => {
            const row = document.createElement('tr');
            const checkboxCell = document.createElement('td');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = child.id;
            checkbox.setAttribute('aria-label', `Select ${child.name}`);
            checkbox.addEventListener('change', () => {
                checkbox.checked ? selected.add(child.id) : selected.delete(child.id);
                document.getElementById('syncSelected').disabled = selected.size === 0;
            });
            checkboxCell.append(checkbox);
            row.append(checkboxCell);
            [child.child_code, child.name, child.district || '—', child.gn_division || '—'].forEach(value => {
                const cell = document.createElement('td');
                cell.textContent = value;
                row.append(cell);
            });
            tbody.append(row);
        });
        renderPagination();
    }

    function renderPagination() {
        document.getElementById('pagination')?.remove();
        if (lastPage <= 1) return;
        const nav = document.createElement('div');
        nav.id = 'pagination';
        nav.className = 'actions';
        const previous = document.createElement('button');
        previous.className = 'secondary';
        previous.textContent = 'Previous';
        previous.disabled = currentPage <= 1;
        previous.onclick = () => loadPage(currentPage - 1).catch(error => status(error.message, true));
        const next = document.createElement('button');
        next.className = 'secondary';
        next.textContent = 'Next';
        next.disabled = currentPage >= lastPage;
        next.onclick = () => loadPage(currentPage + 1).catch(error => status(error.message, true));
        nav.append(previous, next);
        document.getElementById('childrenRows').closest('.table-wrap').after(nav);
    }

    async function postBatch(payload, enumeratorIds) {
        const response = await fetch(downloadUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            body: JSON.stringify(payload),
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'The child records could not be downloaded.');
        const records = window.KFHIChildOfflineCache
            ? await Promise.all(result.data.map(record => window.KFHIChildOfflineCache.prepareRecord(record)))
            : result.data;
        await saveRecords(records, enumeratorIds);
        return result;
    }

    async function syncSelected() {
        if (!selected.size) return;
        const access = enumeratorAccessSelection();
        const ids = [...selected];
        status(`Downloading ${ids.length} selected child record(s)…`);
        try {
            await postBatch({ all: false, child_ids: ids, ...access.payload, include_family_details: document.getElementById('includeFamily').checked }, access.ids);
            await saveMetadata({ key: 'last_selected_sync', value: new Date().toISOString(), count: ids.length });
            status(`Saved ${ids.length} selected child record(s) on this device. You can now use them offline.`);
        } catch (error) { status(error.message, true); }
    }

    async function syncAll(filtersToUse, label) {
        const access = enumeratorAccessSelection();
        const count = filtersToUse.district || filtersToUse.gn_division || filtersToUse.search ? currentTotal : null;
        const prompt = count === null
            ? 'Download every child record to this device? This includes private child information.'
            : `Download all ${count} children matching these filters to this device?`;
        if (!window.confirm(prompt)) return;
        const button = document.activeElement;
        if (button) button.disabled = true;
        let afterId = 0;
        let downloaded = 0;
        const family = document.getElementById('includeFamily').checked;
        try {
            await saveMetadata({ key: 'sync_state', value: 'in_progress', started_at: new Date().toISOString(), label });
            while (true) {
                status(`Downloading ${downloaded} child record(s)… Keep this page open until it finishes.`);
                const result = await postBatch({ all: true, ...filtersToUse, ...access.payload, after_id: afterId, include_family_details: family }, access.ids);
                downloaded += result.count;
                if (result.next_after_id === null || result.next_after_id === undefined) break;
                afterId = result.next_after_id;
            }
            const finishedAt = new Date().toISOString();
            await saveMetadata({ key: 'last_successful_sync', value: finishedAt, count: downloaded, label });
            await saveMetadata({ key: 'sync_state', value: 'complete', finished_at: finishedAt, count: downloaded, label });
            status(`Finished. Downloaded ${downloaded} child record(s) to this device for ${label}.`);
        } catch (error) {
            await saveMetadata({ key: 'sync_state', value: 'failed', failed_at: new Date().toISOString(), count: downloaded, label }).catch(() => {});
            status(`Download stopped after ${downloaded} record(s): ${error.message} Reconnect and try again.`, true);
        } finally { if (button) button.disabled = false; }
    }

    document.getElementById('searchForm').addEventListener('submit', event => {
        event.preventDefault();
        loadPage(1).catch(error => status(error.message, true));
    });
    document.getElementById('selectPage').addEventListener('change', event => {
        document.querySelectorAll('#childrenRows input[type="checkbox"]').forEach(box => {
            box.checked = event.target.checked;
            box.dispatchEvent(new Event('change'));
        });
    });
    document.getElementById('syncSelected').addEventListener('click', syncSelected);
    document.getElementById('syncFiltered').addEventListener('click', () => syncAll(filters(), 'the current filters'));
    document.getElementById('syncAll').addEventListener('click', () => syncAll({}, 'all active children'));
    document.getElementById('allowAllEnumerators').addEventListener('change', event => {
        document.querySelectorAll('.enumerator-choice').forEach(input => {
            input.disabled = event.target.checked;
            if (event.target.checked) input.checked = false;
        });
    });

    openDb().then(async database => {
        db = database;
        const count = await countRecords();
        status(`${count} child record(s) are saved on this device. Search online to add or update records.`);
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register(serviceWorkerUrl, { scope: serviceWorkerScope })
                .catch(() => status('Child records are saved, but the offline card page could not be prepared. Check that this site uses HTTPS (or localhost).', true));
        }
    }).catch(() => status('This browser cannot save offline records. Check storage settings or use a supported browser.', true));
})();
</script>
</body>
</html>
