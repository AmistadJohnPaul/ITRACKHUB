@extends('layouts.itrack')
@section('title', 'Scan / POS')

@section('content')
    <div class="page-head">
        <div>
            <h1>Scan / POS</h1>
            <p>Scan an item to check its price and aisle, then add it to the cart.</p>
        </div>
    </div>

    <div class="pos-grid">
        {{-- LEFT: scanner --}}
        <div class="card">
            <div class="card-title">Barcode Scanner</div>
            <div id="reader" class="reader"></div>
            <button type="button" id="camBtn" class="btn btn-primary btn-block"><i class="bi bi-camera"></i> Start Camera</button>

            <label class="form-label" for="manual">Or type / scan the barcode and press Enter</label>
            <input type="text" id="manual" class="form-control" placeholder="Enter barcode manually" autocomplete="off" autofocus>
            <div id="msg" class="pos-msg"></div>
        </div>

        {{-- RIGHT: details + cart --}}
        <div>
            <div class="card">
                <div class="card-title">Product Details</div>

                <div id="emptyState" class="empty"><i class="bi bi-upc-scan"></i>Scan an item to see its details</div>

                <div id="productInfo" class="hidden">
                    <div class="prod-row">
                        <div class="prod-img" id="pImg"></div>
                        <div class="prod-meta">
                            <h3 id="pName"></h3>
                            <div>Barcode: <span id="pBarcode"></span></div>
                            <div>Category: <span id="pCategory"></span></div>
                            <div><i class="bi bi-geo-alt"></i> Aisle: <strong id="pAisle"></strong></div>
                            <div class="price" id="pPrice"></div>
                            <div>Stock: <strong id="pStock"></strong></div>
                        </div>
                    </div>
                    <div class="qty-row">
                        <div class="stepper">
                            <button type="button" id="minus">&minus;</button>
                            <input type="number" id="qty" value="1" min="1">
                            <button type="button" id="plus">+</button>
                        </div>
                        <button type="button" id="addBtn" class="btn btn-primary"><i class="bi bi-cart-plus"></i> Add to Cart</button>
                    </div>
                </div>
            </div>

            <div class="card mt">
                <div class="card-title">Cart (<span id="cartCount">0</span>)</div>
                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>Item</th><th>Qty</th><th class="num">Subtotal</th><th></th></tr></thead>
                        <tbody id="cartBody"></tbody>
                    </table>
                </div>

                <label class="form-label" for="payment">Payment Method</label>
                <select id="payment" class="form-control">
                    <option>Cash</option><option>GCash</option><option>Card</option>
                </select>

                <div class="cart-total"><span>Total</span><span id="cartTotal">&#8369;0.00</span></div>
                <div class="cart-buttons">
                    <button type="button" id="checkoutBtn" class="btn btn-primary"><i class="bi bi-bag-check"></i> Checkout</button>
                    <button type="button" id="clearBtn" class="btn btn-danger"><i class="bi bi-trash"></i> Clear Cart</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const LOOKUP_URL = @json(route('pos.lookup'));
    const CHECKOUT_URL = @json(route('pos.checkout'));
    const ADD_URL = @json(route('products.create'));
    const CAN_ADD = @json(auth()->user()->isAdmin());
    const AUTO_ADD = @json($autoAdd);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    const $ = function (id) { return document.getElementById(id); };
    const money = function (n) { return '\u20B1' + Number(n).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}); };
    const esc = function (s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
        });
    };

    let cart = [];      // {id, name, price, stock, qty}
    let current = null; // the product on screen

    function say(text, cls, isHtml) {
        const m = $('msg');
        m.className = 'pos-msg ' + (cls || '');
        if (isHtml) m.innerHTML = text; else m.textContent = text;
    }

    // ---------- lookup ----------
    async function handleBarcode(code) {
        code = (code || '').trim();
        if (!code) return;
        say('Looking up ' + code + '...');
        try {
            const res = await fetch(LOOKUP_URL + '?barcode=' + encodeURIComponent(code), {headers: {'Accept': 'application/json'}});
            const data = await res.json();

            if (!res.ok || !data.found) {
                current = null;
                showProduct(null);
                let html = esc(data.message || 'Item not found.');
                if (CAN_ADD) html += ' <a href="' + ADD_URL + '?barcode=' + encodeURIComponent(code) + '"><strong>Add it as a new product</strong></a>';
                say(html, 'err', true);
                return;
            }

            current = data;
            showProduct(data);
            if (AUTO_ADD) { addToCart(1); } else { say('Found: ' + data.name, 'ok'); }
        } catch (e) {
            say('Could not reach the server. Check your connection and try again.', 'err');
        }
    }

    function showProduct(p) {
        $('emptyState').classList.toggle('hidden', !!p);
        $('productInfo').classList.toggle('hidden', !p);
        if (!p) return;

        $('pName').textContent = p.name;
        $('pBarcode').textContent = p.barcode;
        $('pCategory').textContent = p.category;
        $('pAisle').textContent = p.aisle;
        $('pPrice').textContent = money(p.price);
        $('pStock').textContent = p.stock > 0 ? p.stock + ' units' : 'Out of stock';
        $('pImg').innerHTML = p.image ? '<img src="' + esc(p.image) + '" alt="">' : '<i class="bi bi-box-seam"></i>';
        $('qty').value = 1;
        $('addBtn').disabled = p.stock <= 0;
    }

    // ---------- cart ----------
    function addToCart(q) {
        if (!current) return;
        q = q || parseInt($('qty').value, 10) || 1;
        const line = cart.find(function (l) { return l.id === current.id; });
        const have = line ? line.qty : 0;

        if (current.stock <= 0) { say(current.name + ' is out of stock.', 'err'); return; }
        if (have + q > current.stock) { say('Only ' + current.stock + ' in stock for ' + current.name + '.', 'err'); return; }

        if (line) line.qty += q;
        else cart.push({id: current.id, name: current.name, price: current.price, stock: current.stock, qty: q});

        say('Added ' + q + ' x ' + current.name + ' to the cart.', 'ok');
        renderCart();
    }

    function removeLine(i) { cart.splice(i, 1); renderCart(); }

    function renderCart() {
        let total = 0, count = 0, html = '';
        cart.forEach(function (l, i) {
            const sub = l.price * l.qty;
            total += sub; count += l.qty;
            html += '<tr><td>' + esc(l.name) + '<br><span class="hint">' + money(l.price) + ' each</span></td>'
                 + '<td>' + l.qty + '</td><td class="num">' + money(sub) + '</td>'
                 + '<td><button type="button" class="icon-btn del" onclick="removeLine(' + i + ')"><i class="bi bi-x-lg"></i></button></td></tr>';
        });
        $('cartBody').innerHTML = html || '<tr><td colspan="4"><div class="empty" style="padding:14px">Cart is empty</div></td></tr>';
        $('cartTotal').textContent = money(total);
        $('cartCount').textContent = count;
    }

    async function checkout() {
        if (!cart.length) { say('The cart is empty.', 'err'); return; }
        const btn = $('checkoutBtn');
        btn.disabled = true;
        try {
            const res = await fetch(CHECKOUT_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF},
                body: JSON.stringify({
                    items: cart.map(function (l) { return {product_id: l.id, quantity: l.qty}; }),
                    payment_method: $('payment').value
                })
            });
            const data = await res.json();

            if (!res.ok || !data.success) {
                Swal.fire({icon: 'error', title: 'Checkout failed', text: data.message || 'Please try again.'});
            } else {
                Swal.fire({icon: 'success', title: 'Sale completed',
                    html: 'Receipt <b>' + esc(data.receipt_no) + '</b><br>Total: <b>' + money(data.total) + '</b>'});
                cart = []; current = null;
                showProduct(null); renderCart(); say('', '');
            }
        } catch (e) {
            Swal.fire({icon: 'error', title: 'Network error', text: 'Check your connection and try again.'});
        }
        btn.disabled = false;
        $('manual').focus();
    }

    // ---------- events ----------
    $('manual').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const code = this.value;
            this.value = '';
            handleBarcode(code);
        }
    });
    $('addBtn').addEventListener('click', function () { addToCart(); });
    $('minus').addEventListener('click', function () { $('qty').value = Math.max(1, (parseInt($('qty').value, 10) || 1) - 1); });
    $('plus').addEventListener('click', function () {
        const max = current ? current.stock : 1;
        $('qty').value = Math.min(max, (parseInt($('qty').value, 10) || 1) + 1);
    });
    $('checkoutBtn').addEventListener('click', checkout);
    $('clearBtn').addEventListener('click', function () {
        if (!cart.length) return;
        Swal.fire({title: 'Clear the cart?', icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, clear it'})
            .then(function (r) { if (r.isConfirmed) { cart = []; renderCart(); } });
    });

    // ---------- camera ----------
    let scanner = null, running = false, lastCode = '', lastTime = 0;

    $('camBtn').addEventListener('click', async function () {
        const btn = this;
        if (running) {
            try { await scanner.stop(); } catch (e) {}
            running = false;
            btn.innerHTML = '<i class="bi bi-camera"></i> Start Camera';
            return;
        }
        scanner = scanner || new Html5Qrcode('reader');
        try {
            await scanner.start({facingMode: 'environment'}, {fps: 10, qrbox: {width: 260, height: 140}}, function (text) {
                const now = Date.now();
                if (text === lastCode && now - lastTime < 2500) return; // ignore repeats
                lastCode = text; lastTime = now;
                handleBarcode(text);
            });
            running = true;
            btn.innerHTML = '<i class="bi bi-stop-circle"></i> Stop Camera';
        } catch (e) {
            say('Camera not available. Use https (your ngrok link) and allow camera access, or type the barcode.', 'err');
        }
    });

    renderCart();
</script>
@endpush