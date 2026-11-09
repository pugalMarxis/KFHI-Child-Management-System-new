(() => {
    const CACHE = window.KFHIChildOfflineCache;
    const form = document.getElementById('childForm');
    const message = document.getElementById('pageMessage');
    const connection = document.getElementById('connectionStatus');
    const childList = document.getElementById('savedChildren');
    const saveButton = document.getElementById('saveOnPhone');
    const queueDbName = 'kfhi-offline-profile-queue';
    const queueStore = 'operations';
    const profileSyncUrl = new URL('admin/child-sync/profile-sync', window.location.href);
    const csrfUrl = new URL('attendance/sync/token', window.location.href);
    let queueDb;
    let currentChild = null;

    function show(text, type = '') {
        message.textContent = text;
        message.className = `status ${type}`;
    }

    function updateConnection() {
        connection.textContent = navigator.onLine
            ? 'Internet available. Saved changes can sync.'
            : 'Offline. Changes will stay on this phone until internet returns.';
    }

    function newUuid() {
        if (crypto.randomUUID) return crypto.randomUUID();
        const bytes = crypto.getRandomValues(new Uint8Array(16));
        bytes[6] = (bytes[6] & 0x0f) | 0x40;
        bytes[8] = (bytes[8] & 0x3f) | 0x80;
        const hex = [...bytes].map(byte => byte.toString(16).padStart(2, '0')).join('');
        return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
    }

    function openQueueDb() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(queueDbName, 1);
            request.onupgradeneeded = () => {
                if (!request.result.objectStoreNames.contains(queueStore)) {
                    request.result.createObjectStore(queueStore, { keyPath: 'operation_uuid' });
                }
            };
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    function allOperations() {
        return new Promise((resolve, reject) => {
            const request = queueDb.transaction(queueStore).objectStore(queueStore).getAll();
            request.onsuccess = () => resolve(request.result || []);
            request.onerror = () => reject(request.error);
        });
    }

    function putOperation(operation) {
        return new Promise((resolve, reject) => {
            const tx = queueDb.transaction(queueStore, 'readwrite');
            tx.objectStore(queueStore).put(operation);
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
            tx.onabort = () => reject(tx.error || new Error('Could not save this change on the phone.'));
        });
    }

    function formData() {
        const values = Object.fromEntries(new FormData(form).entries());
        values.child_code = values.child_code.trim();
        for (const key of ['middle_name', 'school_name', 'grade', 'district', 'gn_division', 'address', 'notes']) {
            values[key] = values[key].trim() || null;
        }
        return values;
    }

    form.addEventListener('invalid', event => {
        const labels = {
            child_code: 'child code',
            first_name: 'first name',
            last_name: 'last name',
            date_of_birth: 'date of birth',
            gender: 'gender',
        };
        const fieldName = event.target.name;
        show(`Please fill in the required ${labels[fieldName] || 'field'}.`, 'error');
    }, true);

    function fullName(child) {
        return [child.first_name, child.middle_name, child.last_name].filter(Boolean).join(' ');
    }

    async function renderChildren() {
        const records = await CACHE.allSavedChildren();
        records.sort((a, b) => String(a.full_name || a.child_code).localeCompare(String(b.full_name || b.child_code)));
        childList.replaceChildren();
        if (!records.length) {
            const empty = document.createElement('li');
            empty.textContent = 'No child records are saved on this phone. Add a child or download children while online.';
            childList.append(empty);
            return;
        }

        for (const child of records) {
            const item = document.createElement('li');
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'secondary';
            const state = child.profile_sync_status === 'conflict'
                ? ' — needs review'
                : child.profile_sync_status === 'rejected'
                    ? ' — sync problem'
                    : child.id?.toString().startsWith('offline:')
                        ? ' — not sent yet'
                        : '';
            button.textContent = `${child.child_code} — ${child.full_name || fullName(child)}${state}`;
            button.addEventListener('click', () => openChild(child));
            item.append(button);
            childList.append(item);
        }
    }

    function clearForm() {
        form.reset();
        currentChild = null;
        form.elements.child_code.readOnly = false;
        document.getElementById('formTitle').textContent = 'Add a child';
        document.getElementById('conflictNotice')?.remove();
        renderQr('');
    }

    function openChild(child) {
        currentChild = child;
        document.getElementById('formTitle').textContent = `Edit ${child.child_code}`;
        for (const [key, value] of Object.entries(child)) {
            if (form.elements[key]) form.elements[key].value = value ?? '';
        }
        form.elements.child_code.readOnly = !String(child.id).startsWith('offline:');
        document.getElementById('conflictNotice')?.remove();

        if (child.profile_sync_status === 'conflict' && child.server_profile_conflict) {
            const server = child.server_profile_conflict;
            const notice = document.createElement('p');
            notice.id = 'conflictNotice';
            notice.className = 'status error wide';
            notice.textContent = `The server copy changed. Server details: ${server.full_name}; born ${server.date_of_birth || 'not set'}; ${server.gender || 'gender not set'}; school ${server.school_name || 'not set'}; grade ${server.grade || 'not set'}; district ${server.district || 'not set'}; division ${server.gn_division || 'not set'}. Compare with the form. Saving submits the form against version ${server.profile_version}.`;
            form.prepend(notice);
        } else if (child.profile_sync_status === 'rejected' && child.profile_sync_message) {
            const notice = document.createElement('p');
            notice.id = 'conflictNotice';
            notice.className = 'status error wide';
            notice.textContent = child.profile_sync_message;
            form.prepend(notice);
        }

        renderQr(child.child_code);
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function renderQr(code) {
        const target = document.getElementById('qrPreview');
        target.replaceChildren();
        if (!code || typeof window.QRCode !== 'function') return;
        new window.QRCode(target, {
            text: code,
            width: 128,
            height: 128,
            correctLevel: window.QRCode.CorrectLevel.M,
        });
    }

    async function saveLocal() {
        if (!queueDb || !CACHE) {
            show('Child storage is still opening. Wait a moment and try again.', 'error');
            return;
        }
        if (!form.reportValidity()) {
            show('Please fill in the required child details marked on the form.', 'error');
            return;
        }

        saveButton.disabled = true;
        const values = formData();
        try {
            const existing = await CACHE.allSavedChildren();
            const duplicate = existing.find(child =>
                child.child_code.toLowerCase() === values.child_code.toLowerCase() && child.id !== currentChild?.id
            );
            if (duplicate) {
                show('This child code is already saved on this phone. Check the code.', 'error');
                return;
            }

            const operations = await allOperations();
            if (currentChild && !String(currentChild.id).startsWith('offline:')) {
                const uncertain = operations.find(item =>
                    item.action === 'update' && Number(item.child_id) === Number(currentChild.id)
                    && item.status === 'pending' && item.attempted_at
                );
                if (uncertain) {
                    show('This child has a change waiting for a sync result. Sync it before editing again.', 'error');
                    return;
                }
            }

            let operation = null;
            const isLocalNew = !currentChild || String(currentChild.id).startsWith('offline:');
            if (isLocalNew && currentChild) {
                operation = operations.find(item => item.action === 'create'
                    && item.local_child_id === currentChild.id
                    && item.status === 'pending' && !item.attempted_at);
            } else if (currentChild) {
                operation = operations.find(item => item.action === 'update'
                    && Number(item.child_id) === Number(currentChild.id)
                    && item.status === 'pending' && !item.attempted_at);
            }

            const operationUuid = operation?.operation_uuid || newUuid();
            let localId;
            let baseVersion;
            let action;
            if (isLocalNew) {
                action = 'create';
                localId = currentChild?.id || `offline:${operationUuid}`;
            } else {
                action = 'update';
                localId = currentChild.id;
                baseVersion = currentChild.server_profile_conflict?.profile_version ?? currentChild.profile_version ?? 1;
            }

            const queuedOperation = {
                operation_uuid: operationUuid,
                action,
                status: 'pending',
                local_child_id: action === 'create' ? localId : undefined,
                child_id: action === 'update' ? Number(localId) : undefined,
                base_profile_version: action === 'update' ? Number(baseVersion) : undefined,
                ...values,
            };
            await putOperation(queuedOperation);

            const localRecord = {
                ...(currentChild || {}),
                ...values,
                id: localId,
                full_name: fullName(values),
                profile_sync_status: 'pending',
                profile_sync_message: '',
            };
            delete localRecord.server_profile_conflict;
            await CACHE.saveChild(localRecord);
            currentChild = localRecord;
            renderQr(values.child_code);
            await renderChildren();
            show(`Saved ${values.child_code} on this phone. Its QR uses that permanent code.`, 'success');
            if (navigator.onLine) await syncPending();
        } catch (error) {
            show(error.message || 'Could not save. Check phone storage and try again.', 'error');
        } finally {
            saveButton.disabled = false;
        }
    }

    async function syncPending() {
        updateConnection();
        if (!queueDb) return;
        if (!navigator.onLine) {
            show('Offline. Saved child changes will sync when internet returns.');
            return;
        }

        let operations;
        try {
            operations = (await allOperations()).filter(item => item.status === 'pending');
        } catch (error) {
            show(error.message || 'Could not read the saved child changes.', 'error');
            return;
        }
        if (!operations.length) {
            show('There are no child changes waiting to sync.', 'success');
            return;
        }

        const button = document.getElementById('syncProfiles');
        button.disabled = true;
        try {
            const tokenResponse = await fetch(csrfUrl, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            if (!tokenResponse.ok) throw new Error('Sign in while online before syncing child changes. Your saved changes are still on this phone.');
            const token = await tokenResponse.json();

            for (let start = 0; start < operations.length; start += 50) {
                const batch = operations.slice(start, start + 50);
                const attemptedAt = new Date().toISOString();
                for (const operation of batch) {
                    operation.attempted_at = operation.attempted_at || attemptedAt;
                    await putOperation(operation);
                }

                const response = await fetch(profileSyncUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token.csrf_token,
                    },
                    body: JSON.stringify({ operations: batch }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Child changes could not be synced. They remain saved on this phone.');

                for (const result of data.results || []) {
                    const operation = batch.find(item => item.operation_uuid === result.operation_uuid);
                    if (!operation) continue;
                    const savedOperation = {
                        ...operation,
                        status: result.status === 'accepted' ? 'synced' : result.status,
                        sync_message: result.message || '',
                        resolved_at: new Date().toISOString(),
                    };
                    if (result.child) savedOperation.server_child = result.child;
                    await putOperation(savedOperation);

                    const local = (await CACHE.allSavedChildren()).find(item => item.id === operation.local_child_id)
                        || (operation.child_id ? (await CACHE.allSavedChildren()).find(item => Number(item.id) === Number(operation.child_id)) : null);
                    if (result.status === 'accepted' && result.child) {
                        if (operation.action === 'create') await CACHE.removeChild(operation.local_child_id);
                        await CACHE.saveChild({
                            ...(local || {}),
                            ...result.child,
                            staff_downloaded: true,
                            profile_sync_status: 'synced',
                            profile_sync_message: '',
                        });
                    } else if (local) {
                        await CACHE.saveChild({
                            ...local,
                            profile_sync_status: result.status === 'conflict' ? 'conflict' : 'rejected',
                            profile_sync_message: result.message || 'This change needs review.',
                            ...(result.status === 'conflict' && result.child
                                ? { server_profile_conflict: result.child }
                                : {}),
                        });
                    }
                }
            }

            await renderChildren();
            const stillPending = (await allOperations()).filter(item => item.status === 'pending').length;
            show(stillPending ? `${stillPending} change(s) still need to sync.` : 'Sync finished. Check any rejected or conflicting changes in the child list.', 'success');
        } catch (error) {
            show(error.message || 'Sync failed. Changes remain saved on this phone.', 'error');
        } finally {
            button.disabled = false;
        }
    }

    form.elements.child_code.addEventListener('input', event => renderQr(event.target.value.trim()));
    saveButton.addEventListener('click', saveLocal);
    form.addEventListener('submit', event => {
        event.preventDefault();
        saveLocal();
    });
    document.getElementById('newChild').addEventListener('click', clearForm);
    document.getElementById('syncProfiles').addEventListener('click', syncPending);
    window.addEventListener('online', syncPending);
    window.addEventListener('offline', updateConnection);

    openQueueDb().then(async database => {
        queueDb = database;
        updateConnection();
        await renderChildren();
        saveButton.disabled = false;
        const saved = await allOperations();
        const syncedLocalIds = new Set(saved
            .filter(item => item.action === 'create' && item.status === 'synced')
            .map(item => item.local_child_id));
        for (const operation of saved.filter(item =>
            item.action === 'create' && item.status !== 'synced' && !syncedLocalIds.has(item.local_child_id)
        )) {
            const child = (await CACHE.allSavedChildren()).find(item => item.id === operation.local_child_id);
            if (!child) {
                await CACHE.saveChild({
                    ...operation,
                    id: operation.local_child_id,
                    full_name: fullName(operation),
                    profile_sync_status: operation.status === 'pending' ? 'pending' : operation.status,
                    profile_sync_message: operation.sync_message || '',
                });
            }
        }
        await renderChildren();
        if (navigator.onLine) syncPending();
    }).catch(error => show(error.message || 'Offline child storage is unavailable in this browser.', 'error'));
})();
