(() => {
    const DB_NAME = 'kfhi-offline-child-records';
    const status = document.getElementById('status');
    const list = document.getElementById('list');
    let database;
    let records = [];

    function openDatabase() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, 2);
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
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
        });
    }

    function readRecords() {
        return new Promise((resolve, reject) => {
            const request = database.transaction('children').objectStore('children').getAll();
            request.onsuccess = () => resolve(request.result || []);
            request.onerror = () => reject(request.error);
        });
    }

    function renderList() {
        const term = document.getElementById('filter').value.trim().toLowerCase();
        const matching = records.filter(child => {
            const label = `${child.child_code} ${child.full_name}`.toLowerCase();
            return label.includes(term);
        });
        list.replaceChildren();
        matching.forEach(child => {
            const button = document.createElement('button');
            button.className = 'child-item';
            button.textContent = `${child.child_code} — ${child.full_name}`;
            button.addEventListener('click', () => showCard(child));
            list.append(button);
        });
        status.textContent = records.length
            ? `${matching.length} shown; ${records.length} child record(s) are saved on this phone.`
            : 'No child records are saved yet. Connect to the internet and use the staff sync page first.';
    }

    async function showCard(child) {
        const card = document.getElementById('card');
        document.getElementById('cardName').textContent = child.full_name || child.child_code;
        document.getElementById('cardCode').textContent = child.child_code;
        document.getElementById('cardDivision').textContent = child.gn_division || child.district || '—';
        document.getElementById('cardDistrict').textContent = child.district || '—';
        const photo = document.getElementById('cardPhoto');
        const initial = (child.first_name || child.full_name || 'C').trim().charAt(0).toUpperCase();
        const placeholderPhoto = `data:image/svg+xml,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" width="200" height="280"><rect width="100%" height="100%" fill="#e8f3eb"/><text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="100" fill="#287344">${initial}</text></svg>`)}`;
        photo.src = child.profile_photo_data || child.profile_photo_url || placeholderPhoto;
        photo.onerror = () => { photo.onerror = null; photo.src = placeholderPhoto; };

        const qr = document.getElementById('qr');
        qr.replaceChildren();
        if (typeof window.QRCode !== 'function') {
            status.textContent = 'The local QR library did not load. Reconnect and reopen this page.';
            return;
        }
        new window.QRCode(qr, {
            text: child.child_code,
            width: 128,
            height: 128,
            correctLevel: window.QRCode.CorrectLevel.M,
        });
        card.style.display = 'grid';
        document.getElementById('cardActions').hidden = false;
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        card.dataset.childCode = child.child_code || 'child-id';

        if (!child.profile_photo_data && child.id) {
            try {
                const photoUrl = new URL(`./admin/child-sync/children/${encodeURIComponent(child.id)}/photo`, window.location.href);
                const response = await fetch(photoUrl, {
                    credentials: 'same-origin',
                    headers: { Accept: 'image/*' },
                });
                if (!response.ok) throw new Error('The server did not return this child photo.');
                const blob = await response.blob();
                if (blob.size > 2 * 1024 * 1024) throw new Error('This photo is too large to save for offline printing.');
                const photoData = await new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => resolve(reader.result);
                    reader.onerror = () => reject(reader.error);
                    reader.readAsDataURL(blob);
                });
                photo.src = photoData;
                child.profile_photo_data = photoData;
                if (database) {
                    await new Promise((resolve, reject) => {
                        const tx = database.transaction('children', 'readwrite');
                        tx.objectStore('children').put(child);
                        tx.oncomplete = resolve;
                        tx.onerror = () => reject(tx.error);
                    });
                }
            } catch (error) {
                status.textContent = 'Could not load this child’s photo. Confirm the photo was uploaded and try again while online.';
            }
        }
    }

    function loadImage(source) {
        return new Promise((resolve, reject) => {
            const image = new Image();
            image.onload = () => resolve(image);
            image.onerror = reject;
            image.src = source;
        });
    }

    async function downloadCardPng() {
        const card = document.getElementById('card');
        const canvas = document.createElement('canvas');
        canvas.width = 1011;
        canvas.height = 638;
        const context = canvas.getContext('2d');
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.strokeStyle = '#cfe0d4';
        context.lineWidth = 8;
        context.strokeRect(5, 5, canvas.width - 10, canvas.height - 10);

        try {
            const logo = await loadImage('./images/logo.png');
            context.drawImage(logo, 52, 42, 120, 85);
        } catch (error) {}
        context.fillStyle = '#287344';
        context.font = 'bold 48px Arial';
        context.fillText('KFHI', 190, 77);
        context.fillStyle = '#718278';
        context.font = '27px Arial';
        context.fillText('Friends of Hope International', 190, 116);
        context.textAlign = 'right';
        context.fillStyle = '#287344';
        context.font = 'bold 32px Arial';
        context.fillText('CHILD ID CARD', 958, 77);
        context.fillStyle = '#718278';
        context.font = '23px Arial';
        context.fillText('KFHI-CMS', 958, 111);
        context.textAlign = 'left';
        context.strokeStyle = '#e0ece3';
        context.lineWidth = 4;
        context.beginPath(); context.moveTo(50, 150); context.lineTo(960, 150); context.stroke();

        try {
            const photo = await loadImage(document.getElementById('cardPhoto').src);
            context.save();
            context.beginPath(); context.roundRect(52, 194, 210, 280, 18); context.clip();
            const scale = Math.max(210 / photo.width, 280 / photo.height);
            const width = photo.width * scale, height = photo.height * scale;
            context.drawImage(photo, 52 + (210 - width) / 2, 194 + (280 - height) / 2, width, height);
            context.restore();
        } catch (error) {
            context.fillStyle = '#eef4ef'; context.fillRect(52, 194, 210, 280);
        }

        context.fillStyle = '#829187';
        context.font = '23px Arial';
        context.fillText('CHILD ID', 300, 218);
        context.fillStyle = '#246b43';
        context.font = 'bold 40px Arial';
        context.fillText(document.getElementById('cardCode').textContent, 300, 264);
        context.fillStyle = '#273f32';
        context.font = 'bold 34px Arial';
        const name = document.getElementById('cardName').textContent;
        const nameLines = [];
        let currentLine = '';
        name.split(/\s+/).forEach(word => {
            const candidate = currentLine ? `${currentLine} ${word}` : word;
            if (context.measureText(candidate).width > 450 && currentLine) {
                nameLines.push(currentLine); currentLine = word;
            } else currentLine = candidate;
        });
        if (currentLine) nameLines.push(currentLine);
        nameLines.slice(0, 2).forEach((line, index) => context.fillText(line, 300, 334 + index * 42));

        context.fillStyle = '#829187'; context.font = '20px Arial';
        context.fillText('GN Division', 300, 430);
        context.fillText('District', 300, 478);
        context.fillStyle = '#314b3b'; context.font = 'bold 23px Arial';
        context.fillText(document.getElementById('cardDivision').textContent, 300, 455);
        context.fillText(document.getElementById('cardDistrict').textContent, 300, 503);

        const qrGraphic = document.querySelector('#qr canvas, #qr img');
        if (qrGraphic) context.drawImage(qrGraphic, 735, 235, 190, 190);
        context.textAlign = 'center'; context.fillStyle = '#718278'; context.font = '19px Arial';
        context.fillText('Scan for secure identification', 830, 450);
        context.textAlign = 'left';
        context.strokeStyle = '#e0ece3'; context.lineWidth = 4;
        context.beginPath(); context.moveTo(50, 570); context.lineTo(960, 570); context.stroke();
        context.fillStyle = '#718278'; context.font = '20px Arial';
        context.fillText('Secure Child Identification', 52, 608);
        context.fillStyle = '#287344'; context.font = 'bold 20px Arial'; context.textAlign = 'right';
        context.fillText('KFHI-CMS', 958, 608);
        context.textAlign = 'left';

        canvas.toBlob(blob => {
            if (!blob) { status.textContent = 'Could not create the PNG image.'; return; }
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `${card.dataset.childCode || 'child-id'}-child-id.png`;
            link.click();
            URL.revokeObjectURL(url);
        }, 'image/png');
    }

    async function load() {
        try {
            if (!database) database = await openDatabase();
            records = await readRecords();
            records.sort((a, b) => (a.full_name || '').localeCompare(b.full_name || ''));
            renderList();
        } catch (error) {
            status.textContent = error.message || 'Could not open saved records in this browser.';
        }
    }

    async function deleteSavedChildren() {
        if (!window.confirm('Remove every synchronized child record from this device? This will not delete children from the CMS server. You must sync again to use these child records offline.')) {
            return;
        }

        try {
            if (!database) database = await openDatabase();
            await new Promise((resolve, reject) => {
                const tx = database.transaction('children', 'readwrite');
                tx.objectStore('children').clear();
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error);
                tx.onabort = () => reject(tx.error || new Error('Could not remove the saved child records.'));
            });
            records = [];
            document.getElementById('card').style.display = 'none';
            document.getElementById('cardActions').hidden = true;
            renderList();
            status.textContent = 'All synchronized child records were removed from this device. The server records were not changed.';
        } catch (error) {
            status.textContent = error.message || 'Could not remove the saved child records.';
        }
    }

    document.getElementById('refresh').addEventListener('click', load);
    document.getElementById('deleteSavedChildren').addEventListener('click', deleteSavedChildren);
    document.getElementById('filter').addEventListener('input', renderList);
    document.getElementById('closeCard').addEventListener('click', () => {
        document.getElementById('card').style.display = 'none';
        document.getElementById('cardActions').hidden = true;
    });
    document.getElementById('downloadPng').addEventListener('click', () => {
        downloadCardPng().catch(() => { status.textContent = 'Could not create the PNG image on this browser.'; });
    });
    load();
})();
