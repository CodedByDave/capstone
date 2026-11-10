<?php

namespace App\Services;

use App\Exceptions\SupplierPortalException;
use App\Models\Supplier;
use App\Models\SupplierPriceList;
use App\Repositories\SupplierItemRepository;
use App\Repositories\SupplierPriceListRepository;
use App\Repositories\SupplierRepository;
use App\Services\Ocr\OcrSpaceClient;
use App\Services\Ocr\PriceListMatcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Stores a supplier's price-list photo, reads it with OCR and records how it compares with the
 * items the supplier typed in. Photos live on the private disk and are served only to the
 * supplier and to the shop they supply.
 */
class SupplierPriceListService
{
    public const DISK = 'local';

    public function __construct(
        private readonly SupplierPriceListRepository $priceLists,
        private readonly SupplierItemRepository $items,
        private readonly SupplierRepository $suppliers,
        private readonly OcrSpaceClient $ocr,
        private readonly PriceListMatcher $matcher,
    ) {}

    public function upload(Supplier $supplier, UploadedFile $photo): SupplierPriceList
    {
        $rows = $this->items->priceRowsForSupplier($supplier->id);

        if ($rows->isEmpty()) {
            throw new SupplierPortalException('Add the supplies you sell before uploading a price list, so it has something to be checked against.');
        }

        $text = $this->ocr->extractText($photo);
        $outcome = $this->matcher->match($text, $rows);
        $path = $photo->store("supplier-price-lists/{$supplier->id}", self::DISK);

        try {
            return DB::transaction(fn () => $this->priceLists->createForSupplier($supplier->id, [
                'photo_path' => $path,
                'ocr_text' => $text,
                'status' => $outcome['status'],
                'match_results' => $outcome['results'],
                'checked_at' => now(),
            ]));
        } catch (Throwable $e) {
            Storage::disk(self::DISK)->delete($path);

            throw $e;
        }
    }

    /** Checks a price list again, e.g. after the supplier corrected their items. */
    public function recheck(Supplier $supplier, string $publicId): SupplierPriceList
    {
        $priceList = $this->priceLists->findForSupplierOrFail($publicId, $supplier->id);
        $text = $priceList->ocr_text;

        // A photo OCR could not read the first time is sent once more.
        if (blank($text) && Storage::disk(self::DISK)->exists($priceList->photo_path)) {
            $text = $this->ocr->extractTextFromContents(
                Storage::disk(self::DISK)->get($priceList->photo_path),
                basename($priceList->photo_path),
            );
        }

        $outcome = $this->matcher->match($text, $this->items->priceRowsForSupplier($supplier->id));

        return DB::transaction(function () use ($priceList, $text, $outcome): SupplierPriceList {
            $this->priceLists->update($priceList, [
                'ocr_text' => $text,
                'status' => $outcome['status'],
                'match_results' => $outcome['results'],
                'checked_at' => now(),
            ]);

            return $priceList->refresh();
        });
    }

    /** @return list<array<string, mixed>> */
    public function recentForSupplier(Supplier $supplier): array
    {
        return $this->priceLists->recentForSupplier($supplier->id)
            ->map(fn (SupplierPriceList $priceList) => $this->present(
                $priceList,
                route('supplier-portal.price-lists.photo', $priceList->public_id),
            ))
            ->values()
            ->all();
    }

    public function photoPathForSupplier(Supplier $supplier, string $publicId): string
    {
        return $this->existingPhoto($this->priceLists->findForSupplierOrFail($publicId, $supplier->id));
    }

    public function photoPathForShop(string $supplierPublicId, string $publicId, int $shopId): string
    {
        return $this->existingPhoto($this->priceLists->findForShopOrFail($supplierPublicId, $publicId, $shopId));
    }

    /** What the shop sees on a supplier's page: their details, catalog and latest price-list check. */
    public function supplierDetailForShop(string $publicId, int $shopId, bool $forOwner): array
    {
        $supplier = $this->suppliers->detailForShopOrFail($publicId, $shopId);
        $latest = $supplier->latestPriceList;
        $photoRoute = $forOwner ? 'supplier.price-lists.photo' : 'staff.supplier.price-lists.photo';

        return [
            'public_id' => $supplier->public_id,
            'name' => $supplier->name,
            'contact_person' => $supplier->contact_person,
            'email' => $supplier->email,
            'phone' => $supplier->phone,
            'address' => $supplier->address,
            'lead_time_days' => $supplier->lead_time_days,
            'status' => $supplier->status,
            'has_account' => $supplier->user_id !== null,
            'inventory_count' => $supplier->inventory_count,
            'items' => $supplier->items->map(fn ($item) => [
                'public_id' => $item->public_id,
                'name' => $item->name,
                'unit' => $item->unit,
                'stock_quantity' => (float) $item->stock_quantity,
                'unit_price' => (float) $item->unit_price,
                'updated_at' => $item->updated_at?->toIso8601String(),
            ])->values()->all(),
            'latest_price_list' => $latest === null ? null : $this->present(
                $latest,
                route($photoRoute, ['supplier' => $supplier->public_id, 'priceList' => $latest->public_id]),
            ),
        ];
    }

    private function existingPhoto(SupplierPriceList $priceList): string
    {
        abort_unless(Storage::disk(self::DISK)->exists($priceList->photo_path), 404);

        return $priceList->photo_path;
    }

    private function present(SupplierPriceList $priceList, string $photoUrl): array
    {
        $results = collect($priceList->match_results ?? []);

        return [
            'public_id' => $priceList->public_id,
            'status' => $priceList->status,
            'photo_url' => $photoUrl,
            'uploaded_at' => $priceList->created_at?->toIso8601String(),
            'checked_at' => $priceList->checked_at?->toIso8601String(),
            'matched_count' => $results->where('result', PriceListMatcher::RESULT_MATCHED)->count(),
            'item_count' => $results->count(),
            'results' => $results->values()->all(),
        ];
    }
}
