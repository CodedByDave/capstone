<?php

namespace App\Http\Controllers\Shop\Operations;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Promotion;
use App\Models\Shop;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PromotionController extends Controller
{
    public function __construct(
        private readonly PromotionService $promotionService,
    ) {}

    private function getShop(): Shop
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return Shop::where('owner_id', $user->id)->firstOrFail();
        }

        $employee = Employee::where('user_id', $user->id)->firstOrFail();
        return Shop::findOrFail($employee->shop_id);
    }

    private function base(): string
    {
        return auth()->user()->role === 'owner' ? '/shop' : '/staff';
    }

    public function index(Request $request)
    {
        $shop     = $this->getShop();
        $archived = $request->boolean('archived');

        return Inertia::render('shop/operations/promotions/Index', [
            'promotions' => $archived
                ? $this->promotionService->archived($shop)
                : $this->promotionService->paginate($shop),
            'stats'    => $this->promotionService->getStats($shop),
            'archived' => $archived,
        ]);
    }

    public function create()
    {
        return Inertia::render('shop/operations/promotions/Create');
    }

    public function store(Request $request)
    {
        $shop = $this->getShop();

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:500'],
            'type'             => ['required', 'in:percentage,fixed'],
            'value'            => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'is_active'        => ['boolean'],
            'starts_at'        => ['nullable', 'date'],
            'ends_at'          => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $this->promotionService->create($shop, $data);

        return redirect("{$this->base()}/operations/promos")
            ->with('toast', ['type' => 'success', 'message' => 'Promotion created successfully.']);
    }

    public function edit(Promotion $promotion)
    {
        $shop = $this->getShop();
        abort_if($promotion->shop_id !== $shop->id, 403);

        return Inertia::render('shop/operations/promotions/Edit', [
            'promotion' => $promotion,
        ]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $shop = $this->getShop();
        abort_if($promotion->shop_id !== $shop->id, 403);

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:500'],
            'type'             => ['required', 'in:percentage,fixed'],
            'value'            => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'is_active'        => ['boolean'],
            'starts_at'        => ['nullable', 'date'],
            'ends_at'          => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $this->promotionService->update($promotion, $data);

        return redirect("{$this->base()}/operations/promos")
            ->with('toast', ['type' => 'success', 'message' => 'Promotion updated.']);
    }

    public function toggleActive(Promotion $promotion)
    {
        $shop = $this->getShop();
        abort_if($promotion->shop_id !== $shop->id, 403);

        $promo = $this->promotionService->toggleActive($promotion);
        $label = $promo->is_active ? 'activated' : 'deactivated';

        return back()->with('toast', ['type' => 'success', 'message' => "Promotion {$label}."]);
    }

    public function destroy(Promotion $promotion)
    {
        $shop = $this->getShop();
        abort_if($promotion->shop_id !== $shop->id, 403);

        $this->promotionService->delete($promotion);

        return back()->with('toast', ['type' => 'success', 'message' => 'Promotion archived.']);
    }

    public function restore(Promotion $promotion)
    {
        $shop = $this->getShop();
        abort_if($promotion->shop_id !== $shop->id, 403);

        $this->promotionService->restore($promotion);

        return back()->with('toast', ['type' => 'success', 'message' => 'Promotion restored.']);
    }
}
