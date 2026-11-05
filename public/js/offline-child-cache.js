(() => {
    const DB_NAME = 'kfhi-offline-child-records';

    function openDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, 1);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    async function findByCode(code, enumeratorId) {
        const normalized = String(code || '').trim().toLowerCase();
        if (!normalized) return null;
        const database = await openDatabase();
        try {
            const rows = await new Promise((resolve, reject) => {
                const request = database.transaction('children').objectStore('children').getAll();
                request.onsuccess = () => resolve(request.result || []);
                request.onerror = () => reject(request.error);
            });
            return rows.find(row =>
                String(row.child_code || '').trim().toLowerCase() === normalized &&
                (!enumeratorId || (
                    Array.isArray(row.allowed_enumerator_ids) &&
                    row.allowed_enumerator_ids.map(Number).includes(Number(enumeratorId))
                ))
            ) || null;
        } finally {
            database.close();
        }
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

    window.KFHIChildOfflineCache = { findByCode, prepareRecord };
})();
