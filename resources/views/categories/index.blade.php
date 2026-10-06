@extends('layouts.app')

@section('title', 'Product Categories — MantraHeal CRM')

@section('content')
<div class="space-y-6" x-data="{ showCreateModal: false, editCategory: null }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('products.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Products
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-xs text-slate-500">Categories</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Product Categories</h1>
            <p class="text-sm text-slate-500">Organize herbal wellness formulations into therapeutic segments</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="btn btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Category
        </button>
    </div>

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($categories as $cat)
            <div class="card p-5 space-y-3 relative group">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">{{ $cat->name }}</h3>
                        <div class="text-xs text-slate-400 font-mono">/{{ $cat->slug }}</div>
                    </div>
                    <span class="badge bg-emerald-50 text-emerald-800 font-bold text-xs">
                        {{ $cat->products_count }} products
                    </span>
                </div>

                <p class="text-xs text-slate-600 line-clamp-2 min-h-[32px]">
                    {{ $cat->description ?? 'Therapeutic wellness category for specialized herbal remedies.' }}
                </p>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <a href="{{ route('products.index', ['category' => $cat->id]) }}" class="text-emerald-700 font-semibold hover:underline">
                        View Products ({{ $cat->products_count }}) →
                    </a>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="editCategory = {{ json_encode($cat) }}" class="text-slate-500 hover:text-slate-700">
                            Edit
                        </button>
                        @if($cat->products_count === 0)
                            <form action="{{ route('categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete this category?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full card p-12 text-center text-slate-500">
                <p>No product categories created yet.</p>
            </div>
        @endforelse
    </div>

    <!-- Create Category Modal -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-md w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="showCreateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Add New Category</h3>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Category Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Immunity & Vitality" class="form-control w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Therapeutic purpose and indications..." class="form-control w-full text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCreateModal = false" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Create Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div x-show="editCategory" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4" style="display: none;">
        <div class="card max-w-md w-full p-6 space-y-4 shadow-2xl bg-white" @click.away="editCategory = null">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Edit Category</h3>
                <button type="button" @click="editCategory = null" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form :action="'{{ url('categories') }}/' + (editCategory ? editCategory.id : '')" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Category Name *</label>
                    <input type="text" name="name" :value="editCategory ? editCategory.name : ''" required class="form-control w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
                    <textarea name="description" rows="2" :value="editCategory ? editCategory.description : ''" class="form-control w-full text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editCategory = null" class="btn btn-secondary text-xs">Cancel</button>
                    <button type="submit" class="btn btn-primary text-xs">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
