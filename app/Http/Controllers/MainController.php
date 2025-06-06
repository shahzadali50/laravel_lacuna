<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Brand;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class MainController extends Controller
{

    public function cacheClear()
    {
        try {
            // Run the Artisan command to clear caches
            Artisan::call('optimize:clear');
            // Return success message using Inertia flash data
            Log::info('Application cache cleared successfully!');
            return redirect()->back()->with('success', 'Application cache cleared successfully!');
        } catch (\Exception $e) {
            Log::error('Cache clear error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    public function migrate()
    {
        try {
            // Run the Artisan command to clear caches
            Artisan::call('migrate');
            // Return success message using Inertia flash data
            Log::info('Database migrated successfully.');
            return redirect()->back()->with('success', 'Database migrated successfully.');
        } catch (\Exception $e) {
            Log::error('Migration error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Migration failed: ' . $e->getMessage());
        }
    }
        public function storageLink()
        {
            try {
                Artisan::call('storage:link');
                Log::info('Storage link created successfully.');
                return redirect()->back()->with('success', 'Storage link created successfully.');
            } catch (\Exception $e) {
                Log::error('Storage link error: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Storage link failed: ' . $e->getMessage());
            }
        }
    public function checkRole()
    {
        if (auth()->check()) {
            if (auth()->user()->role == "admin") {

                return redirect()->route('admin.dashboard');
            } else {

                return redirect()->route('profile.edit');
            }
        } else {

            return redirect()->route('login');
        }
    }
    public function dashboard()
    {
        $brands = Brand::where('user_id', Auth::id())->count();
        $totalProduct = Product::where('user_id', Auth::id())->count();
        $category = Category::where('user_id', Auth::id())->count();

        // Send all orders instead of filtering them
        $orders = Order::where('user_id', Auth::id())->select('total_price', 'created_at')->get();

        $products = Product::where('user_id', Auth::id())
            ->with(['purchaseProducts' => function ($query) {
                $query->select('product_id', 'stock');
            }])
            ->get()
            ->map(function ($product) {
                $product->total_stock = $product->purchaseProducts->sum('stock');
                return $product;
            });

            return Inertia::render('admin/Dashboard', [
                'brands' => $brands,
                'totalProduct' => $totalProduct,
                'category' => $category,
                'orders' => $orders,
                'products' => $products,
                'translations' => __('messages'),
                'locale' => App::getLocale(),
            ]);
        }
    }

