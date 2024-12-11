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
        $result = array();

        $products = $request->input("products");

        $ProductIds = array_column($products, "id");
        $ProductQty = array_column($products, "qty");

        foreach ($ProductIds as $Index => $ProductId) {
            $Product = Product::with('materials')->find($ProductId);

            $ProductMaterials = array();
            foreach ($Product->materials as $Material) {
                $MaterialId = $Material->id;
                $MaterialName = $Material->name;
                $MaterialQty = $Material->pivot->quantity;
                $MaterialQty = $MaterialQty * $ProductQty[$Index];
                $CommonQuantity = $MaterialQty;

                $WAREHOUSE = Werehouse::where("material_id", $MaterialId)->get();

                foreach ($WAREHOUSE as $WareHouse) {

                    $RemainQuantity = $WareHouse->remainder;

                    if ($CommonQuantity > 0) {
                        $QuantityTake = min($RemainQuantity, $CommonQuantity);
                        $ProductMaterials [] = [
                            "warehouse_id" => $WareHouse->id,
                            "material_name" => $MaterialName,
                            "quantity" => $QuantityTake,
                            "price" => $WareHouse->price
                        ];
                        $CommonQuantity = $CommonQuantity - $QuantityTake;
                    }
                }

                if ($CommonQuantity > 0) {
                    $ProductMaterials [] = [
                        "warehouse_id" => null,
                        "material_name" => $MaterialName,
                        "quantity" => $CommonQuantity,
                        "price" => null
                    ];
                }
            }

            $result [] = [
                "product_name" => $Product->name,
                "product_qty" => $ProductQty[$Index],
                "product_materials" => $ProductMaterials
            ];


        }
        return response()->json($result);
    }

}
