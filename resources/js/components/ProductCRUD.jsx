import { useState, useEffect } from 'react';

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

const emptyForm = { id: null, name: '', barcode: '', quantity: 0, price: 0, location: '', aisle: '' };

export default function ProductCRUD() {
    const [products, setProducts] = useState([]);
    const [form, setForm] = useState(emptyForm);
    const [editing, setEditing] = useState(false);
    const [loading, setLoading] = useState(true);

    const apiHeaders = {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
    };

    function loadProducts() {
        setLoading(true);
        fetch('/admin/api/products', { headers: apiHeaders })
            .then(r => r.json())
            .then(data => { setProducts(data); setLoading(false); });
    }

    useEffect(() => { loadProducts(); }, []);

    function handleChange(e) {
        setForm({ ...form, [e.target.name]: e.target.value });
    }

    function handleSubmit(e) {
        e.preventDefault();
        const url = editing ? `/admin/api/products/${form.id}` : '/admin/api/products';
        const method = editing ? 'PUT' : 'POST';

        fetch(url, {
            method,
            headers: apiHeaders,
            body: JSON.stringify(form),
        })
            .then(r => r.json())
            .then(() => {
                setForm(emptyForm);
                setEditing(false);
                loadProducts();
            });
    }

    function handleEdit(product) {
        setForm({
            id: product.id,
            name: product.name,
            barcode: product.barcode,
            quantity: product.quantity,
            price: product.price,
            location: product.location || '',
            aisle: product.aisle || '',
        });
        setEditing(true);
    }

    function handleDelete(id, name) {
        if (!confirm(`Delete "${name}"? This cannot be undone.`)) return;

        fetch(`/admin/api/products/${id}`, {
            method: 'DELETE',
            headers: apiHeaders,
        })
            .then(r => r.json())
            .then(() => loadProducts());
    }

    function cancelEdit() {
        setForm(emptyForm);
        setEditing(false);
    }

    return (
        <div style={{ fontFamily: 'sans-serif' }}>
            <h3>{editing ? 'Edit Product' : 'Add Product'} (React CRUD)</h3>

            <form onSubmit={handleSubmit} style={{ background: '#fff', padding: 16, borderRadius: 8, marginBottom: 20, maxWidth: 400 }}>
                <input name="name" placeholder="Name" value={form.name} onChange={handleChange} required style={{ display: 'block', width: '100%', marginBottom: 8, padding: 8 }} />
                <input name="barcode" placeholder="Barcode" value={form.barcode} onChange={handleChange} required style={{ display: 'block', width: '100%', marginBottom: 8, padding: 8 }} />
                <input name="quantity" type="number" placeholder="Quantity" value={form.quantity} onChange={handleChange} required style={{ display: 'block', width: '100%', marginBottom: 8, padding: 8 }} />
                <input name="price" type="number" step="0.01" placeholder="Price" value={form.price} onChange={handleChange} required style={{ display: 'block', width: '100%', marginBottom: 8, padding: 8 }} />
                <input name="location" placeholder="Location" value={form.location} onChange={handleChange} style={{ display: 'block', width: '100%', marginBottom: 8, padding: 8 }} />
                <input name="aisle" placeholder="Aisle" value={form.aisle} onChange={handleChange} style={{ display: 'block', width: '100%', marginBottom: 8, padding: 8 }} />

                <button type="submit" style={{ padding: '8px 16px', marginRight: 8 }}>{editing ? 'Update' : 'Add'}</button>
                {editing && <button type="button" onClick={cancelEdit} style={{ padding: '8px 16px' }}>Cancel</button>}
            </form>

            {loading ? <p>Loading…</p> : (
                <table border="1" cellPadding="8" style={{ borderCollapse: 'collapse', width: '100%', background: '#fff' }}>
                    <thead>
                        <tr><th>Name</th><th>Barcode</th><th>Qty</th><th>Price</th><th>Aisle</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        {products.map(p => (
                            <tr key={p.id}>
                                <td>{p.name}</td>
                                <td>{p.barcode}</td>
                                <td>{p.quantity}</td>
                                <td>₱{p.price}</td>
                                <td>{p.aisle || '-'}</td>
                                <td>
                                    <button onClick={() => handleEdit(p)}>Edit</button>{' '}
                                    <button onClick={() => handleDelete(p.id, p.name)}>Delete</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
        </div>
    );
}