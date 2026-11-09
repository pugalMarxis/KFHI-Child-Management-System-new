(() => {
    const DB_NAME = 'kfhi-offline-child-records';

    function openDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, 2);
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

    async function allSavedChildren() {
        const database = await openDatabase();
        try {
            return await new Promise((resolve, reject) => {
                const request = database.transaction('children').objectStore('children').getAll();
                request.onsuccess = () => resolve(request.result || []);
                request.onerror = () => reject(request.error);
            });
        } finally {
            database.close();
        }
    }

    async function findByCode(code, enumeratorId) {
        const normalized = String(code || '').trim().toLowerCase();
        if (!normalized) return null;
        const rows = await allSavedChildren();
        return rows.find(row =>
            String(row.child_code || '').trim().toLowerCase() === normalized &&
            (!enumeratorId || (
                Array.isArray(row.allowed_enumerator_ids) &&
                row.allowed_enumerator_ids.map(Number).includes(Number(enumeratorId))
            ))
        ) || null;
    }

    async function saveChild(record) {
        const database = await openDatabase();
        try {
            await new Promise((resolve, reject) => {
                const tx = database.transaction('children', 'readwrite');
                tx.objectStore('children').put(record);
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error);
                tx.onabort = () => reject(tx.error || new Error('Could not save the child on this phone.'));
            });
        } finally {
            database.close();
        }
    }

    async function removeChild(id) {
        const database = await openDatabase();
        try {
            await new Promise((resolve, reject) => {
                const tx = database.transaction('children', 'readwrite');
                tx.objectStore('children').delete(id);
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error);
            });
        } finally {
            database.close();
        }
    }

    async function findByScannedValue(value) {
        const scanned = String(value || '').trim();
        if (!scanned) return null;

        let childCode = scanned;
        if (scanned.startsWith('{')) {
            try {
                childCode = JSON.parse(scanned).code || '';
            } catch (error) {
                return null;
            }
        } else {
            try {
                const url = new URL(scanned, window.location.origin);
                const match = url.pathname.match(/\/qr\/scan\/([^/]+)$/);
                if (match) {
                    if (!crypto.subtle) return null;
                    const token = decodeURIComponent(match[1]);
                    const digest = await crypto.subtle.digest(
                        'SHA-256',
                        new TextEncoder().encode(token)
                    );
                    const tokenHash = Array.from(new Uint8Array(digest))
                        .map(byte => byte.toString(16).padStart(2, '0'))
                        .join('');
                    const rows = await allSavedChildren();
                    return rows.find(row =>
                        row.status === 'active' && (
                            row.qr_token_hash === tokenHash ||
                            (Array.isArray(row.qr_token_hashes) && row.qr_token_hashes.includes(tokenHash))
                        )
                    ) || null;
                }
            } catch (error) {
                // Plain child codes are not URLs.
            }
        }

        const child = await findByCode(childCode);
        return child && child.status === 'active' ? child : null;
    }

    async function prepareRecord(record) {
        if (!record.profile_photo_url || record.profile_photo_data) return record;
        try {
            const response = await fetch(record.profile_photo_url, { credentials: 'same-origin' });
            if (!response.ok) return record;
            const blob = await response.blob();
            if (blob.size > 2 * 1024 * 1024) return record;
            const data = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = () => reject(reader.error);
                reader.readAsDataURL(blob);
            });
            return { ...record, profile_photo_data: data };
        } catch (error) {
            return record;
        }
    }

    window.KFHIChildOfflineCache = {
        allSavedChildren,
        findByCode,
        findByScannedValue,
        prepareRecord,
        saveChild,
        removeChild,
    };
})();
