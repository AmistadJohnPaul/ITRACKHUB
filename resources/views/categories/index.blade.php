@extends('layouts.itrack')
@section('title', 'Categories')

@section('content')
    <div class="page-head">
        <div><h1>Settings</h1><p>Store information, users, categories and barcode options.</p></div>
    </div>
    @include('partials.settings-tabs')

    <div class="two-col" style="grid-template-columns:1fr 1.6fr">
        <div class="card">
            <div class="card-title">Add Category</div>
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf
                <label class="form-label" style="margin-top:0" for="name">Category name</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Beverages" required>
                @error('name') <div class="error-text">{{ $message }}</div> @enderror
                <div class="form-actions"><button class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add</button></div>
            </form>
        </div>

        <div class="card">
            <div class="card-title">All Categories</div>
            <table class="table">
                <thead><tr><th>Name</th><th>Products</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse ($categories as $cat)
                        <tr>
                            <td><strong>{{ $cat->name }}</strong></td>
                            <td>{{ $cat->products_count }}</td>
                            <td>
                                <button type="button" class="icon-btn" title="Rename"
                                        data-id="{{ $cat->id }}" data-name="{{ $cat->name }}" onclick="openEdit(this)">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" class="icon-btn del" title="Delete"
                                        data-name="{{ $cat->name }}"
                                        onclick="confirmDelete('cat-del-{{ $cat->id }}', this.dataset.name, 'Products in this category will stay but become uncategorized.')">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <form id="cat-del-{{ $cat->id }}" method="POST" action="{{ route('categories.destroy', $cat) }}" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty" style="padding:14px">No categories yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-backdrop" id="editModal">
        <form class="modal" method="POST" id="editForm">
            @csrf @method('PUT')
            <h3>Rename Category</h3>
            <label class="form-label">Name</label>
            <input type="text" name="name" id="editName" class="form-control" required>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('editModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function openEdit(btn) {
        document.getElementById('editForm').action = @json(url('/categories')) + '/' + btn.dataset.id;
        document.getElementById('editName').value = btn.dataset.name;
        document.getElementById('editModal').classList.add('open');
    }
</script>
@endpush