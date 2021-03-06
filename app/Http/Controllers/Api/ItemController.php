<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\LedgerManage;
use App\Models\Center;
use App\Models\CenterStock;
use App\Models\Counter;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Ledger;
use App\Models\PosBill;
use App\Models\PosBillItem;
use App\Models\PosSetting;
use App\Models\User;
use App\NepaliDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    //
    public function index(Request $request)
    {
        $items = DB::select('select id,title,sell_price,wholesale,stock ,number as barcode,unit,taxable,tax,conversion_id,
        (select IFNULL(sum(amount),0) from center_stocks where item_id=items.id and center_id=?) as center_stock,
        (select IFNULL(sum(rate),0) from center_stocks where item_id=items.id and center_id=?) as center_rate,
        (select IFNULL(sum(wholesale),0) from center_stocks where item_id=items.id and center_id=?) as center_wholesale
        from items ', [$request->center_id, $request->center_id, $request->center_id]);

        return response(json_encode($items, JSON_PRESERVE_ZERO_FRACTION));
    }

    public function variants(Request $request){
        $variants=DB::select('select * from item_variants');
        $variantsPrices=DB::select('select v.ratio,v.unit,vp.wholesale,vp.price,vp.id,v.item_id
        from item_variant_prices vp
        join item_variants v on vp.item_variant_id=v.id
        join items i on v.item_id=i.id
        where vp.center_id=?',[$request->center_id]);
        return response(json_encode($variantsPrices, JSON_PRESERVE_ZERO_FRACTION));

    }

    public function syncBills(Request $request)
    {

        $pointSetting=getSetting('point')??(object)([
            'type'=>0,
            'point'=>0,
            'per'=>0
        ]);
        $setting = PosSetting::first();
        $fy = $setting->fiscalYear();
        $bill = new PosBill();

        $_bill = (object)$request->bill;
        $bis = [];
        $items = [];
        $centers = [];
        $counter = Counter::where('id', $_bill->counter_id)->first();
        $status = $counter->currentStatus($_bill->date);
        try {
            if (PosBill::where('fiscal_year_id', $fy->id)->where('bill_no', $_bill->bill_no)->count() > 0) {
                return response()->json([
                    'status' => true,
                    'msg' => "Bill Saved Sucessfully old",
                    'bill_id' => PosBill::where('fiscal_year_id', $fy->id)->where('bill_no', $_bill->bill_no)->select('id')->first()->id
                ]);
                // $bill=PosBill::where('fiscal_year_id', $fy->id)->where('bill_no', $_bill->bill_no)->first();
            }
            $point=0;
            $bill->bill_no = $_bill->bill_no;
            $bill->date = $_bill->date;
            $bill->counter_id = $_bill->counter_id;
            $bill->center_id = $request->center_id;
            $bill->counter_name = $_bill->counter_name;
            $bill->fiscal_year_id = $fy->id;

            if ($_bill->customer_id != null) {
                $customer = (object)$request->customer;

                $new_cus = Customer::where('foreign_id', $customer->id)->where('center_id', $request->center_id)->first();
                if ($new_cus == null) {
                    $cus_user = User::where('phone', $customer->phone)->first();
                    if ($cus_user == null) {
                        $cus_user = new User();
                        $cus_user->password = bcrypt($customer->phone);
                        $cus_user->role = 5;
                    }
                    $cus_user->name = $customer->name;
                    $cus_user->address = $customer->address??"";
                    $cus_user->phone = $customer->phone;
                    $cus_user->save();
                    $new_cus=Customer::where('user_id',$cus_user->id)->first();
                    if($new_cus==null){
                        $new_cus = new Customer();
                    }
                    $new_cus->panvat = $customer->panvat;
                    $new_cus->center_id = $request->center_id;
                    $new_cus->user_id = $cus_user->id;
                    $new_cus->foreign_id = $customer->id;
                    $new_cus->save();

                }
                // $cus_user=User::where('phone',$request)
                $bill->customer_name = $customer->name;
                $bill->customer_address = $customer->address;
                $bill->customer_phone = $customer->phone;
                $bill->customer_pan = $customer->panvat;
                $bill->customer_id = $new_cus->id;
            } else {
                $bill->customer_name = $_bill->customer_name;
            }
            $bill->total = $_bill->total;
            $bill->discount = $_bill->discount;
            $bill->taxable = $_bill->taxable;
            $bill->tax = $_bill->tax;
            $bill->rounding = $_bill->rounding;
            $bill->grandtotal = $_bill->grandtotal;
            $bill->paid = $_bill->paid;
            $bill->due = $_bill->due;
            $bill->ldiscount = $_bill->ldiscount;
            $bill->return = $_bill->return;
            $bill->sync_id = $_bill->id;
            $user = User::where('phone', $_bill->user_id)->first();
            if ($user == null) {
                $user = Auth::user();
            }
            $bill->user_id = $user->id;
            $bill->save();
            if($pointSetting->type==1){
                $point=$bill->grandtotal/$pointSetting->per*$pointSetting->point;
            }
            $item_ids=[];
            $variants=[];

            foreach ($request->items as $key => $_bi) {
                if ($_bi != null) {
                    $bi = new PosBillItem();
                    $bi->pos_bill_id = $bill->id;
                    $bi->qty = $_bi['qty'];
                    $bi->rate = $_bi['rate'];
                    $bi->name = $_bi['name'];
                    $bi->item_id = $_bi['item_id'];
                    $bi->amount = $_bi['amount'];
                    $bi->discount = $_bi['discount'];
                    $bi->taxable = $_bi['taxable'];
                    $bi->tax = $_bi['tax'];
                    $bi->tax_per = $_bi['tax_per'];
                    $bi->total = $_bi['total'];
                    $bi->use_tax = $_bi['use_tax'];
                    $bi->item_variant_id=$_bi['variant_id'];
                    $soldQty=$bi->qty;
                    if($bi->item_variant_id!=null){

                        $soldQty=(DB::table('item_variants')->where('id',$bi->item_variant_id)->first(['ratio'])->ratio)*$soldQty;
                    }
                    $item = Item::where('id', $_bi['item_id'])->select('id', 'title', 'wholesale', 'sell_price', 'stock', 'trackstock','points')->first();
                    if ($item->trackstock == 1) {
                        $item->stock -= $soldQty;
                        $item->save();
                        if($pointSetting->type==2){
                            $point+=$soldQty*$item->points;
                        }
                        array_push(
                            $items,
                            [
                                'item' => $item,
                                'qty' => $soldQty
                            ]
                        );
                        $center_stock = CenterStock::where('center_id', $request->center_id)->where('item_id', $item->id)->first();
                        if ($center_stock == null) {
                            $center_stock = new CenterStock();
                            $center_stock->center_id = $request->center_id;
                            $center_stock->item_id = $item->id;
                            $center_stock->wholesale = $item->wholesale;
                            $center_stock->rate = $item->sell_price;
                            $center_stock->amount = -1 * $soldQty;
                            $center_stock->save();
                        } else {
                            $center_stock->amount -= $soldQty;
                            $center_stock->save();
                        }
                        array_push($centers, [
                            'stock' => $center_stock,
                            'qty' => $soldQty
                        ]);
                    }
                    $bi->save();
                    array_push($bis, $bi);
                }
            }



            $bill->points=$point;
            $bill->save();

            if ($bill->customer_id != null) {

                if($point>0){
                    DB::update('update customers set points = ifnull(points,0) + ? where id = ?', [$point,$bill->customer_id]);
                }
            }
            $status->current += ($bill->paid-$bill->return);
            $status->save();
        } catch (\Throwable $th) {
            if ($bill->id != null && $bill->id != 0) {
                foreach ($items as $key => $item_holder) {
                    $item = $item_holder['item'];
                    $item->stock += $item_holder['qty'];
                    $item->save();
                }
                foreach ($centers as $key => $center_holder) {
                    $center_Stock = $center_holder['qty'];
                    $center_Stock->amount += $center_holder['qty'];
                    $center_Stock->save();
                }

                DB::table('pos_bill_items')->where('pos_bill_id', $bill->id)->delete();
                $bill->delete();
            }
            return response()->json([
                'status' => false,
                'msg' => "Bill Cannot be Saved, " . $th->getMessage()
            ]);
        }
        return response()->json([
            'status' => true,
            'msg' => "Bill Saved Sucessfully new",
            'bill_id' => $bill->id
        ]);
    }

    public function syncLedger(Request $request)
    {
        $customer = (object)$request->customer;
        $ledger = (object) $request->ledger;
        $new_cus = Customer::where('foreign_id', $customer->id)->where('center_id', $request->center_id)->first();
        if ($new_cus == null) {
            $cus_user = User::where('phone', $customer->phone)->first();
            if ($cus_user == null) {
                $cus_user = new User();
                $cus_user->password = bcrypt($customer->phone);
                $cus_user->role = 5;

            }
            $cus_user->name = $customer->name;
            $cus_user->address = $customer->address??"";
            $cus_user->phone = $customer->phone;
            $cus_user->save();

            $new_cus=Customer::where('user_id',$cus_user->id)->first();
            if($new_cus==null){
                $new_cus = new Customer();
            }
            $new_cus->panvat = $customer->panvat;
            $new_cus->center_id = $request->center_id;
            $new_cus->user_id = $cus_user->id;
            $new_cus->foreign_id = $customer->id;
            $new_cus->save();
        }

        try {
            $nepalidate = new NepaliDate($ledger->date);
            $title=$ledger->particular;
            if(strlen($title)>100){
                $title=substr($title,0,100)."...";
            }
            $l = new \App\Models\Ledger();
            $l->amount = $ledger->amount;
            $l->title = $title;
            $l->date = $ledger->date;
            $l->identifire = $ledger->identifire;
            $l->foreign_key = $ledger->foreign_id;
            $l->user_id = $new_cus->user_id;
            $l->year = $nepalidate->year;
            $l->month = $nepalidate->month;
            $l->session = $nepalidate->session;
            $l->type = $ledger->type;
            $l->out=1;
            $l->save();
            return response()->json([
                'status' => true,
                'msg' => "Ledger Saved Sucessfully",
                'ledger_id' => $l->id
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'msg' => $th->getMessage(),
                'ledger_id' => null
            ]);
        }


        return response($l);
    }

    public function showLedger(Request $request)
    {
        $customer = Customer::where('foreign_id', $request->id)->where('center_id', $request->center_id)->select('user_id')->first();
        if($customer==null){
            abort(404,"No User Found");

        }else{
            $ledgers=DB::table('ledgers')
            ->where('user_id',$customer->user_id)
            ->orderBy('date')->orderBy('id')
            ->select('id','date','title','type','amount')
            ->get();
            return response()->json($ledgers);
        }
    }
}
