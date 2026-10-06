<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $query = Product::with(['category', 'stockBalances']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::all();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'required|string|max:100',
            'mrp' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'gst_percent' => 'required|numeric|in:0,5,12,18,28',
            'hsn_code' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:50',
            'minimum_stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $productCode = 'MH-PRD-' . strtoupper(Str::random(5));
        $validated['product_code'] = $productCode;
        $validated['slug'] = Str::slug($validated['name']) . '-' . strtolower($productCode);
        $validated['is_active'] = $request->has('is_active');

        $product = Product::create($validated);

        AuditLog::log('created', $product, "Product {$product->name} ({$product->sku}) created");

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'variants', 'batches.warehouse', 'stockBalances.warehouse', 'stockMovements.warehouse']);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'brand' => 'required|string|max:100',
            'mrp' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'gst_percent' => 'required|numeric|in:0,5,12,18,28',
            'hsn_code' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:50',
            'minimum_stock' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $oldValues = $product->toArray();
        $product->update($validated);

        AuditLog::log('updated', $product, "Product {$product->name} updated", $oldValues, $product->toArray());

        return redirect()->route('products.show', $product->id)->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        AuditLog::log('deleted', $product, "Product {$product->name} deleted");
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }
}
