(() => {
    const status = document.getElementById('status');
    const startButton = document.getElementById('startCamera');
    const stopButton = document.getElementById('stopCamera');
    const childCard = document.getElementById('child');
    let scanner = null;
    let running = false;
    let busy = false;

    function message(text, type = '') {
        status.textContent = text;
        status.className = type;
    }

    function showChild(child) {
        document.getElementById('childName').textContent = child.full_name || 'Child record';
        document.getElementById('childCode').textContent = child.child_code || '—';
        document.getElementById('childGender').textContent = child.gender || '—';
        document.getElementById('childDob').textContent = child.date_of_birth || '—';
        document.getElementById('childSchool').textContent = child.school_name || '—';
        document.getElementById('childGrade').textContent = child.grade || '—';
        document.getElementById('childGn').textContent = child.gn_division || '—';
        document.getElementById('childDistrict').textContent = child.district || '—';
        document.getElementById('childState').textContent = child.status || '—';
        childCard.style.display = 'block';
    }

    async function lookup(value) {
        const scanned = String(value || '').trim();
        if (!scanned) return;
        childCard.style.display = 'none';

        if (navigator.onLine) {
            try {
                const tokenResponse = await fetch(new URL('attendance/sync/token', document.baseURI), {
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json' },
                });
                if (tokenResponse.ok) {
                    const token = await tokenResponse.json();
                    const response = await fetch(new URL('child-officer/qr/lookup', document.baseURI), {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': token.csrf_token,
                        },
                        body: JSON.stringify({ token: scanned }),
                    });
                    const data = await response.json().catch(() => ({}));
                    if (response.ok && data.success && data.child) {
                        showChild(data.child);
                        message('Child found on the CMS server.', 'success');
                        return;
                    }
                    if (response.status === 404 || response.status === 400) {
                        message(data.message || 'This QR code was not recognized.', 'error');
                        return;
                    }
                }
            } catch (error) {
                // If the connection drops during lookup, check the local child list below.
            }
        }

        try {
            const child = await window.KFHIChildOfflineCache.findByScannedValue(scanned);
            if (child) {
                showChild(child);
                message('Child found in the saved records on this device.', 'success');
                return;
            }
        } catch (error) {
            message('Saved child records could not be opened on this device.', 'error');
            return;
        }

        message(navigator.onLine
            ? 'No matching child was found.'
            : 'This child is not saved on this device. Sync authorized child records while online first.', 'error');
    }

    async function stopCamera() {
        if (!scanner || !running) return;
        try {
            await scanner.stop();
            scanner.clear();
        } catch (error) {
            // Clear the state even if the camera stopped between browser callbacks.
        }
        scanner = null;
        running = false;
        stopButton.disabled = true;
        startButton.disabled = false;
        message('Camera stopped.');
    }

    startButton.addEventListener('click', async () => {
        if (running) return;
        if (!window.Html5Qrcode) {
            message('The camera scanner library did not load. Reload this page while online.', 'error');
            return;
        }
        startButton.disabled = true;
        try {
            scanner = new Html5Qrcode('reader');
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                value => {
                    if (busy) return;
                    busy = true;
                    lookup(value).finally(() => setTimeout(() => { busy = false; }, 1200));
                },
                () => {}
            );
            running = true;
            stopButton.disabled = false;
            message('Camera is ready. Scan the child QR code.', 'success');
        } catch (error) {
            scanner = null;
            running = false;
            startButton.disabled = false;
            const secure = window.isSecureContext;
            message(secure
                ? 'Camera could not start. Allow camera permission and try again.'
                : 'Camera access needs HTTPS when this CMS is hosted. Use localhost for local testing.', 'error');
        }
    });

    stopButton.addEventListener('click', stopCamera);
    document.getElementById('codeForm').addEventListener('submit', event => {
        event.preventDefault();
        lookup(document.getElementById('childCodeInput').value);
    });
    window.addEventListener('online', () => message('Internet available. Scans will check the server first.'));
    window.addEventListener('offline', () => message('Offline. Scans will use child records saved on this device.'));

    message(navigator.onLine
        ? 'Ready. Start the camera or enter a child code.'
        : 'Offline. Start the camera to search saved child records.');
})();
