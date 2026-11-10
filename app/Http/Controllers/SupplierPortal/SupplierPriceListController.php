<?php

namespace App\Http\Controllers\SupplierPortal;

use App\Exceptions\SupplierPortalException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureActiveSupplier;
use App\Http\Requests\SupplierPortal\StoreSupplierPriceListRequest;
use App\Models\SupplierPriceList;
use App\Services\SupplierPriceListService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierPriceListController extends Controller
{
    public function __construct(private readonly SupplierPriceListService $priceLists) {}

    public function store(StoreSupplierPriceListRequest $request): RedirectResponse
    {
        try {
            $priceList = $this->priceLists->upload($this->supplier($request), $request->file('photo'));
        } catch (SupplierPortalException $exception) {
            return back()->withErrors(['photo' => $exception->getMessage()]);
        }

        return back()->with('toast', $this->resultToast($priceList));
    }

    public function recheck(Request $request, string $priceList): RedirectResponse
    {
        $checked = $this->priceLists->recheck($this->supplier($request), $priceList);

        return back()->with('toast', $this->resultToast($checked));
    }

    public function photo(Request $request, string $priceList): StreamedResponse
    {
        $path = $this->priceLists->photoPathForSupplier($this->supplier($request), $priceList);

        return Storage::disk(SupplierPriceListService::DISK)->response($path);
    }

    private function resultToast(SupplierPriceList $priceList): array
    {
        return match ($priceList->status) {
            SupplierPriceList::STATUS_VERIFIED => ['type' => 'success', 'message' => 'Price list verified: it matches your supplies.'],
            SupplierPriceList::STATUS_MISMATCH => ['type' => 'warning', 'message' => 'Price list saved, but some items or prices do not match your supplies.'],
            default => ['type' => 'warning', 'message' => 'Price list saved, but the photo could not be read. Try a clearer, well-lit photo.'],
        };
    }

    private function supplier(Request $request)
    {
        return $request->attributes->get(EnsureActiveSupplier::ATTRIBUTE);
    }
}
