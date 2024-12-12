<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Werehouse;
use http\Env\Response;

use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function produce(Request $request): \Illuminate\Http\JsonResponse
    {
        $result = []; // Umumiy Natija uchun

        $MaterialBusy = []; // Band qilingan Xomashyolar uchun

        $products = $request->input("products");
        $productIds = array_column($products, "id");
        $productQty = array_column($products, "qty");


        foreach ($productIds as $index => $productId) {
            $product = Product::with('materials')->find($productId);

            $productMaterials = []; // Maxsulot Xomashyolari uchun

            foreach ($product->materials as $material) {
                $materialId = $material->id;
                $materialName = $material->name;
                $requiredQty = $material->pivot->quantity * $productQty[$index];

                $commonQuantity = $requiredQty;
                $warehouses = Werehouse::where("material_id", $materialId)->get();

                foreach ($warehouses as $warehouse) {

                    // Omborda band qilingan materialni oladi  agar mavjud bolmasa = 0
                    $alreadyReserved = $MaterialBusy[$materialId][$warehouse->id] ?? 0;

                    //Ombordagi  mavjud  xomashyoni oldin band qilganidan ayiradi
                    $remainWarehouse = $warehouse->remainder - $alreadyReserved;


                    if ($remainWarehouse > 0) {
                        $quantityTake = min($remainWarehouse, $commonQuantity);

                        // Xomashyo malumotlarini saqlash
                        $productMaterials[] = [
                            "warehouse_id" => $warehouse->id,
                            "material_name" => $materialName,
                            "quantity" => $quantityTake,
                            "price" => $warehouse->price
                        ];

                        /*
                         * Band qilingan Xomashyoni yangilash
                         * Omborda  band qilingan miqdor  mavjud bolmasa uni  = 0
                         */
                        if (!isset($MaterialBusy[$materialId][$warehouse->id])) {
                            $MaterialBusy[$materialId][$warehouse->id] = 0;
                        }
                        $MaterialBusy[$materialId][$warehouse->id] += $quantityTake;


                        $commonQuantity -= $quantityTake;


                        if ($commonQuantity <= 0) {
                            break;
                        }

                    }
                }

                // Omborda yetarlicha Xomashyolar bo'lmasa
                if ($commonQuantity > 0) {
                    $productMaterials[] = [
                        "warehouse_id" => null,
                        "material_name" => $materialName,
                        "quantity" => $commonQuantity,
                        "price" => null
                    ];
                }
            }
            $result[] = [
                "product_name" => $product->name,
                "product_qty" => $productQty[$index],
                "product_materials" => $productMaterials
            ];
        }
        return response()->json($result);
    }
}
