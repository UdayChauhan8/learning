<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    
    public function index(): JsonResponse
    {
        // add limit and pagination
        //add max limit for name string
            //no negative value for order
            // add a contraint for position entered by admin like limit of 10
            //admin is able to enter value  in "" check it
            //format the code well
            //add authentication using laravel passport
        $items = Order::orderByRaw('`order` ASC')->paginate(10);

        return response()->json($items);
    }

    
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            
            'name'  => 'required|string|',
            'order' => 'required|integer',  
        ]);

        try {
            $orderexists= Order::where('order', $validated['order'])->exists();
        if ($orderexists) {
        
        Order::where('order', '>=', $validated['order'])
             ->increment('order');
 
          }
        $item = Order::create($validated);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong, please try again later','errorCode'=>0001]);
        }
        return response()->json($item, 201);
    }

    public function show(Order $item): JsonResponse
    {
        return response()->json($item);
    }

    public function update(Request $request, Order $item): JsonResponse
    {
        $validated = $request->validate([
            'name'  => 'required|string|',
            'order' => 'required|integer',
        ]);

        //info(["user input"=>$item]);
        $old=$item->order;
        $new=$validated['order'];
        if ($old != $new) {
            //info("old is != new");
            if($new < $old){
                Order::where('order','>=',$new)->where('order','<',$old)->where('id','!=',$item->id)->increment('order');
            }
            else{
                //info("else case enters");
                Order::where('order', '>', $old)->where('order', '<=', $new)->where('id', '!=', $item->id)->decrement('order');
            }
        }
        $item->update($validated);
        //info(["user input updated"=>$item]);
        $all = Order::orderByRaw('`order` ASC')->paginate(10);
        //info(["user input final response"=>$all]);
        return response()->json($all);
    }

    public function destroy(Order $item): JsonResponse
    {
        $item->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}