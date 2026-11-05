<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Enumerator Dashboard - {{ $enumerator->name }} ({{ $enumerator->enumerator_code }})</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #eef7f1;
            color: #1f3d2b;
            min-height: 100vh;
            padding: 30px 15px 60px;
        }

        .container {
            width: min(1140px, 100%);
            margin: 0 auto;
        }

        .header-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 24px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
            border-left: 6px solid #2e7d32;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .brand-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2e7d32;
            background: #e8f5e9;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 8px;
        }

        h1 {
            color: #1e4b26;
            font-size: 26px;
            margin-bottom: 6px;
        }

        .enum-info {
            color: #5c7063;
            font-size: 14px;
        }

        .enum-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .skip-link {
            position: absolute;
            left: -9999px;
            top: auto;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }

        .skip-link:focus {
            left: 16px;
            top: 16px;
            width: auto;
            height: auto;
            padding: 10px 14px;
            background: #1e4b26;
            color: #fff;
            border-radius: 8px;
            z-index: 1000;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            border-radius: 10px;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: 0.15s ease;
        }

        .btn:focus-visible,
        a:focus-visible,
        button:focus-visible {
            outline: 3px solid rgba(46, 125, 50, 0.35);
            outline-offset: 2px;
        }

        .btn-primary { background: #2e7d32; color: #ffffff; }
        .btn-primary:hover { background: #246328; }
        .btn-secondary { background: #e8f3eb; color: #2e7d32; }
        .btn-secondary:hover { background: #d7ebd9; }
        .btn-danger { background: #fee2e2; color: #dc2626; }
        .btn-danger:hover { background: #fecaca; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 26px;
        }

        .stat-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 22px 24px;
            box-shadow: 0 4px 18px rgba(46, 125, 50, 0.06);
            border: 1px solid #e1eee4;
        }

        .stat-num {
            font-size: 28px;
            font-weight: 800;
            color: #2e7d32;
        }

        .stat-label {
            font-size: 13px;
            color: #627467;
            margin-top: 4px;
            font-weight: 600;
        }

        .panel {
            background: #ffffff;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.08);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            border-bottom: 1px solid #edf4ee;
            padding-bottom: 14px;
        }

        .panel-header h2 {
            font-size: 20px;
            color: #1e4b26;
        }

        .q-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .q-card {
            border: 1px solid #dcece1;
            border-radius: 16px;
            padding: 22px;
            background: #fbfdfc;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: 0.15s ease;
        }

        .q-card:hover {
            transform: translateY(-2px);
            border-color: #a8d5b4;
            box-shadow: 0 8px 24px rgba(46, 125, 50, 0.10);
        }

        .q-title {
            font-size: 18px;
            font-weight: 700;
            color: #204928;
            margin-bottom: 6px;
        }

        .q-event {
            font-size: 13px;
            color: #66796d;
            margin-bottom: 10px;
        }

        .q-desc {
            font-size: 14px;
            color: #526658;
            line-height: 1.5;
            margin-bottom: 18px;
            flex: 1;
        }

        .q-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            border-top: 1px solid #edf4ee;
            padding-top: 14px;
            flex-wrap: wrap;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 22px;
            font-weight: 600;
        }

        .alert-info {
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 22px;
            font-weight: 600;
        }
    </style>
</head>
<body>
<a href="#main-content" class="skip-link">Skip to dashboard content</a>

<main id="main-content" class="container" tabindex="-1">

    <header class="header-card" aria-label="Enumerator dashboard header">
        <div>
            <span class="brand-badge">Field Survey Enumerator Portal</span>
            <h1>Welcome, {{ $enumerator->name }}</h1>
            <div class="enum-info">
                Verified ID: <strong>{{ $enumerator->enumerator_code }}</strong>
                · Status: <span style="color: #2e7d32; font-weight: 700;">Active</span>
                @if($enumerator->phone) · Phone: {{ $enumerator->phone }} @endif
            </div>
        </div>

        <div class="enum-actions">
            <button type="button" class="btn btn-primary" id="syncAssignedChildren">⬇ Sync allowed children to this phone</button>
            <a href="{{ route('enumerator.switch') }}" class="btn btn-secondary">
                🔄 Switch Identity
            </a>

            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-danger">
                    🚪 Logout
                </button>
            </form>
        </div>
    </header>

    <p id="childSyncStatus" role="status" style="margin:0 0 18px;color:#42634c"></p>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="alert-info">{{ session('info') }}</div>
    @endif

    {{-- PROGRESS METRICS --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-num">{{ $stats['total_submitted'] }}</div>
            <div class="stat-label">Total Submissions Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #1b5e20;">{{ $stats['today_submitted'] }}</div>
            <div class="stat-label">Submissions Completed Today</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #e65100;">{{ $stats['awaiting_review'] }}</div>
            <div class="stat-label">Submissions Awaiting Review</div>
        </div>
        <div class="stat-card">
            <div class="stat-num" style="color: #b91c1c;">{{ $stats['returned_or_rejected'] }}</div>
            <div class="stat-label">Returned / Rejected</div>
        </div>
    </div>

    {{-- ASSIGNED QUESTIONNAIRES --}}
    <div class="panel">
        <div class="panel-header">
            <h2>Assigned Field Questionnaires ({{ $questionnaires->count() }})</h2>
        </div>

        @if($questionnaires->count())
            <div class="q-grid">
                @foreach($questionnaires as $q)
                    <div class="q-card">
                        <div>
                            <div class="q-title">{{ $q->title }}</div>
                            @if($q->event)
                                <div class="q-event">📁 Event: <strong>{{ $q->event->title }}</strong></div>
                            @endif
                            <div class="q-desc">
                                {{ $q->description ?: 'No description provided.' }}
                            </div>
                        </div>

                        <div class="q-footer">
                            <span style="font-size: 12px; font-weight: 700; color: #2e7d32; background: #e8f5e9; padding: 4px 8px; border-radius: 6px;">
                                {{ $q->questions->count() }} Questions
                            </span>

                            <a href="{{ route('enumerator.survey.create', $q) }}" class="btn btn-primary">
                                📷 Scan QR & Start Survey →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 40px 20px; color: #6a7d71;">
                <div style="font-size: 40px; margin-bottom: 10px;">📋</div>
                <h3>No questionnaires currently assigned</h3>
                <p style="margin-top: 6px;">Your supervisor or Child Officer will assign published questionnaires to your Enumerator ID ({{ $enumerator->enumerator_code }}).</p>
            </div>
        @endif
    </div>

</main>

<script src="{{ rtrim(request()->getBaseUrl(), '/') }}/js/offline-child-cache.js"></script>
<script>
(() => {
    const button = document.getElementById('syncAssignedChildren');
    const status = document.getElementById('childSyncStatus');
    const enumeratorId = @json($enumerator->id);
    const databaseName = 'kfhi-offline-child-records';

    function openDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(databaseName, 1);
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

    function saveBatch(database, records) {
        return new Promise((resolve, reject) => {
            const tx = database.transaction(['children', 'metadata'], 'readwrite');
            const children = tx.objectStore('children');
            records.forEach(record => {
                const get = children.get(record.id);
                get.onsuccess = () => {
                    const old = get.result || {};
                    const allowed = new Set((old.allowed_enumerator_ids || []).map(Number));
                    allowed.add(Number(enumeratorId));
                    const merged = { ...old, ...record, allowed_enumerator_ids: [...allowed] };
                    if (!Object.prototype.hasOwnProperty.call(record, 'guardians')) delete merged.guardians;
                    children.put(merged);
                };
            });
            tx.objectStore('metadata').put({ key: 'last_enumerator_sync', value: new Date().toISOString() });
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
            tx.onabort = () => reject(tx.error || new Error('Local save was cancelled.'));
        });
    }

    function reconcileAllowedChildren(database, allowedIds) {
        const allowed = new Set((allowedIds || []).map(Number));
        return new Promise((resolve, reject) => {
            const tx = database.transaction('children', 'readwrite');
            const store = tx.objectStore('children');
            const request = store.getAll();
            request.onsuccess = () => {
                request.result.forEach(record => {
                    const ids = (record.allowed_enumerator_ids || []).map(Number);
                    if (!ids.includes(Number(enumeratorId)) || allowed.has(Number(record.id))) return;
                    const remaining = ids.filter(id => id !== Number(enumeratorId));
                    if (remaining.length) store.put({ ...record, allowed_enumerator_ids: remaining });
                    else store.delete(record.id);
                });
            };
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
            tx.onabort = () => reject(tx.error || new Error('Could not update this phone’s allowed child list.'));
        });
    }

    button.addEventListener('click', async () => {
        button.disabled = true;
        let database;
        let afterId = 0;
        let count = 0;
        try {
            database = await openDatabase();
            while (true) {
                status.textContent = `Downloading allowed child records… ${count} saved.`;
                const response = await fetch(@json(route('enumerator.child-sync.download')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ after_id: afterId })
                });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(result.message || 'Could not sync child records.');
                if (afterId === 0 && Array.isArray(result.authorized_child_ids)) {
                    await reconcileAllowedChildren(database, result.authorized_child_ids);
                }
                const records = result.data || [];
                const preparedRecords = window.KFHIChildOfflineCache
                    ? await Promise.all(records.map(record => window.KFHIChildOfflineCache.prepareRecord(record)))
                    : records;
                await saveBatch(database, preparedRecords);
                count += result.count || 0;
                if (result.next_after_id === null || result.next_after_id === undefined) break;
                afterId = result.next_after_id;
            }
            status.textContent = count
                ? `Done. ${count} child record(s) saved on this phone for offline use.`
                : 'No children have been allowed for your account yet. Ask your supervisor to assign children.';
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register(@json(rtrim(request()->getBaseUrl(), '/') . '/service-worker.js'), {
                    scope: @json(rtrim(request()->getBaseUrl(), '/') . '/')
                }).catch(() => {});
            }
        } catch (error) {
            status.textContent = error.message || 'Sync failed. Your existing saved records were kept.';
        } finally {
            if (database) database.close();
            button.disabled = false;
        }
    });
})();
</script>

</body>
</html>
