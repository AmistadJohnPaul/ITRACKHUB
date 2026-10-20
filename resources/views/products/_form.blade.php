@php $p = $product ?? null; @endphp

<form method="POST" enctype="multipart/form-data"
      action="{{ $p ? route('products.update', $p) : route('products.store') }}">
    @csrf
    @if ($p) @method('PUT') @endif

    <div class="form-split">
        {{-- LEFT: barcode + image --}}
        <div class="card">
            <div class="scan-tile" id="scanTile">
                <i class="bi bi-upc-scan"></i>
                <strong id="scanLabel">Scan Barcode</strong>
                <span>Click to scan using the camera</span>
            </div>
            <div id="reader" class="reader hidden"></div>

            <label class="form-label" for="barcode">Barcode (or enter manually)</label>
            <input type="text" id="barcode" name="barcode" class="form-control"
                   value="{{ old('barcode', $p?->barcode ?? request('barcode')) }}"
                   placeholder="Scan or type the barcode number" required autocomplete="off">
            @error('barcode') <div class="error-text">{{ $message }}</div> @enderror

            <label class="form-label">Product Image</label>
            <div class="upload-tile" id="uploadTile">
                @if ($p && $p->image_url)
                    <img id="preview" src="{{ $p->image_url }}" alt="">
                @else
                    <img id="preview" class="hidden" alt="">
                    <i class="bi bi-image" id="uploadIcon"></i>
                    <strong id="uploadText">Upload Product Image</strong>
                    <span>JPG, PNG, WEBP (max 2MB)</span>
                @endif
            </div>
            <input type="file" id="image" name="image" accept="image/*" hidden>
            @error('image') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        {{-- RIGHT: details --}}
        <div class="card">
            <label class="form-label" style="margin-top:0" for="name">Product Name</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $p?->name) }}" placeholder="Enter product name" required>
            @error('name') <div class="error-text">{{ $message }}</div> @enderror

            <label class="form-label" for="category_id">Category</label>
            <select id="category_id" name="category_id" class="form-control">
                <option value="">Select category</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $p?->category_id) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            @error('category_id') <div class="error-text">{{ $message }}</div> @enderror

            <div class="form-row">
                <div>
                    <label class="form-label" for="price">Price (&#8369;)</label>
                    <input type="number" step="0.01" min="0" id="price" name="price" class="form-control" value="{{ old('price', $p?->price ?? '0.00') }}" required>
                    @error('price') <div class="error-text">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label" for="quantity">{{ $p ? 'Stock' : 'Initial Stock' }}</label>
                    <input type="number" min="0" id="quantity" name="quantity" class="form-control" value="{{ old('quantity', $p?->quantity ?? 0) }}" required>
                    @error('quantity') <div class="error-text">{{ $message }}</div> @enderror
                </div>
            </div>

            <label class="form-label" for="aisle">Aisle / Location</label>
            <input type="text" id="aisle" name="aisle" class="form-control" value="{{ old('aisle', $p?->aisle) }}" placeholder="e.g. Aisle 3 - Snacks">
            @error('aisle') <div class="error-text">{{ $message }}</div> @enderror

            <label class="form-label" for="description">Description (Optional)</label>
            <textarea id="description" name="description" class="form-control" placeholder="Enter product description">{{ old('description', $p?->description) }}</textarea>

            <div class="form-actions">
                <a href="{{ route('products.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Save Product</button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const tile = document.getElementById('scanTile');
    const reader = document.getElementById('reader');
    const barcodeInput = document.getElementById('barcode');
    let qr = null, running = false;

    // A USB / phone "keyboard" scanner types the code then presses Enter:
    // don't let that Enter submit the form, move to the name field instead.
    barcodeInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('name').focus(); }
    });

    async function stopScan() {
        if (qr && running) { try { await qr.stop(); } catch (e) {} }
        running = false;
        reader.classList.add('hidden');
        document.getElementById('scanLabel').textContent = 'Scan Barcode';
    }

    tile.addEventListener('click', async function () {
        if (running) { await stopScan(); return; }
        reader.classList.remove('hidden');
        qr = qr || new Html5Qrcode('reader');
        try {
            await qr.start({facingMode: 'environment'}, {fps: 10, qrbox: {width: 260, height: 140}}, function (text) {
                barcodeInput.value = text;
                stopScan();
                toast('success', 'Barcode captured: ' + text);
                document.getElementById('name').focus();
            });
            running = true;
            document.getElementById('scanLabel').textContent = 'Tap to stop the camera';
        } catch (e) {
            reader.classList.add('hidden');
            toast('error', 'Camera not available. Type the barcode instead.');
        }
    });

    // image upload + preview
    const fileInput = document.getElementById('image');
    document.getElementById('uploadTile').addEventListener('click', function () { fileInput.click(); });
    fileInput.addEventListener('change', function () {
        const file = fileInput.files[0];
        if (!file) return;
        const reader2 = new FileReader();
        reader2.onload = function (ev) {
            const img = document.getElementById('preview');
            img.src = ev.target.result;
            img.classList.remove('hidden');
            ['uploadIcon', 'uploadText'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) el.classList.add('hidden');
            });
        };
        reader2.readAsDataURL(file);
    });
</script>
@endpush