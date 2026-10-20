<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITrackHub - Price Check</title>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { font-family: sans-serif; text-align: center; background:#111; color:#fff; margin:0; padding:16px; }
        #reader { width: 100%; max-width: 400px; margin: 0 auto; border-radius: 12px; overflow:hidden; }
        #card { display:none; background:#222; padding:20px; border-radius:12px; max-width:320px; margin:16px auto; }
        #card h2 { margin:0 0 8px; }
        #card .price { font-size:28px; color:#4ade80; font-weight:bold; }
        #card .aisle { font-size:18px; color:#facc15; margin-top:8px; }
        #err { color:#f87171; margin-top:16px; }
    </style>
</head>
<body>
    <h2>🏷️ Price &amp; Aisle Check</h2>
    <div id="reader"></div>
    <div id="err">Point camera at a barcode…</div>

    <div id="card">
        <h2 id="c_name"></h2>
        <div class="price" id="c_price"></div>
        <div class="aisle" id="c_aisle"></div>
    </div>

    <script>
        const err = document.getElementById('err');
        const card = document.getElementById('card');
        let lastScan = '', lastTime = 0;

        function onScanSuccess(decodedText) {
            const now = Date.now();
            if (decodedText === lastScan && (now - lastTime) < 3000) return;
            lastScan = decodedText; lastTime = now;

            fetch("{{ route('lookup.check') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({ barcode: decodedText })
            })
            .then(r => r.json().then(data => ({ status: r.status, data })))
            .then(({ status, data }) => {
                if (status === 404) {
                    card.style.display = 'none';
                    err.textContent = 'Item not found.';
                } else {
                    err.textContent = '';
                    card.style.display = 'block';
                    document.getElementById('c_name').textContent = data.name;
                    document.getElementById('c_price').textContent = '₱' + data.price;
                    document.getElementById('c_aisle').textContent = '📍 ' + data.aisle;
                }
            })
            .catch(() => { err.textContent = 'Network error.'; card.style.display = 'none'; });
        }

        const html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 150 } },
            onScanSuccess
        ).catch(e => { err.textContent = 'Camera error: ' + e; });
    </script>
</body>
</html>
