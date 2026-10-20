<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITrackHub Scanner</title>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { font-family: sans-serif; text-align: center; background:#111; color:#fff; margin:0; padding:16px; }
        #reader { width: 100%; max-width: 400px; margin: 0 auto; border-radius: 12px; overflow:hidden; }
        select, button { font-size: 18px; padding: 10px; margin: 8px; border-radius: 8px; border:none; }
        #result { margin-top: 16px; font-size: 18px; min-height: 40px; }
        .ok { color: #4ade80; }
        .err { color: #f87171; }
    </style>
</head>
<body>
    <h2>📱 ITrackHub Scanner</h2>

    <div>
        <label>Mode:</label>
        <select id="mode">
            <option value="in">Stock IN</option>
            <option value="out">Stock OUT</option>
        </select>
    </div>

    <div id="reader"></div>
    <div id="result">Point camera at a barcode…</div>

    <script>
        const resultDiv = document.getElementById('result');
        const modeSelect = document.getElementById('mode');
        let lastScan = '';
        let lastTime = 0;

        function onScanSuccess(decodedText) {
            const now = Date.now();
            if (decodedText === lastScan && (now - lastTime) < 3000) return;
            lastScan = decodedText;
            lastTime = now;

            resultDiv.textContent = 'Sending ' + decodedText + '…';
            resultDiv.className = '';

            fetch("{{ route('scan.handle') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    barcode: decodedText,
                    type: modeSelect.value,
                    quantity: 1
                })
            })
            .then(r => r.json())
            .then(data => {
                resultDiv.textContent = data.message;
                resultDiv.className = data.success ? 'ok' : 'err';
            })
            .catch(() => {
                resultDiv.textContent = 'Network error — check laptop connection.';
                resultDiv.className = 'err';
            });
        }

        const html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 150 } },
            onScanSuccess
        ).catch(err => {
            resultDiv.textContent = 'Camera error: ' + err;
            resultDiv.className = 'err';
        });
    </script>
</body>
</html>