
(() => {
    const DB_NAME = 'kfhi-offline-attendance';
    const STORE_NAME = 'attendance_queue';

    function openDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, 1);

            request.onupgradeneeded = () => {
                const db = request.result;

                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    const store = db.createObjectStore(STORE_NAME, {
                        keyPath: 'event_uuid',
                    });

                    store.createIndex(
                        'event_child',
                        ['event_id', 'child_code'],
                        { unique: true }
                    );
                }
            };

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    function newUuid() {
        if (crypto.randomUUID) {
            return crypto.randomUUID();
        }

        const bytes = crypto.getRandomValues(new Uint8Array(16));
        bytes[6] = (bytes[6] & 0x0f) | 0x40;
        bytes[8] = (bytes[8] & 0x3f) | 0x80;

        const hex = [...bytes]
            .map(byte => byte.toString(16).padStart(2, '0'))
            .join('');

        return [
            hex.slice(0, 8),
            hex.slice(8, 12),
            hex.slice(12, 16),
            hex.slice(16, 20),
            hex.slice(20),
        ].join('-');
    }

    function save(eventId, childCode, attendanceDate, deviceId) {
        return openDatabase().then(db => new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            const lookup = store.index('event_child')
                .get([Number(eventId), childCode]);

            let result;

            lookup.onsuccess = () => {
                if (lookup.result) {
                    result = {
                        saved: false,
                        record: lookup.result,
                    };
                    return;
                }

                const record = {
                    event_uuid: newUuid(),
                    event_id: Number(eventId),
                    child_code: childCode,
                    attendance_date: attendanceDate,
                    attended_at: new Date().toISOString(),
                    device_id: deviceId,
                    sync_status: 'pending',
                };

                store.add(record);
                result = { saved: true, record };
            };

            tx.oncomplete = () => {
                db.close();
                resolve(result);
            };

            tx.onerror = () => {
                db.close();
                reject(tx.error || new Error('Could not save attendance.'));
            };
        }));
    }

    function getPending() {
        return openDatabase().then(db => new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const request = tx.objectStore(STORE_NAME).getAll();

            request.onsuccess = () => {
                resolve(request.result.filter(
                    item => item.sync_status === 'pending'
                ));
            };

            tx.oncomplete = () => db.close();
            tx.onerror = () => {
                db.close();
                reject(tx.error);
            };
        }));
    }

    function setStatus(eventUuid, status, message = '') {
        return openDatabase().then(db => new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            const request = store.get(eventUuid);

            request.onsuccess = () => {
                if (request.result) {
                    store.put({
                        ...request.result,
                        sync_status: status,
                        sync_message: message,
                    });
                }
            };

            tx.oncomplete = () => {
                db.close();
                resolve();
            };

            tx.onerror = () => {
                db.close();
                reject(tx.error);
            };
        }));
    }

    window.KFHIOfflineAttendance = {
        save,
        getPending,
        setStatus,
    };
})();
