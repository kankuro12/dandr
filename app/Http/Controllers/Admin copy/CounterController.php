<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use App\Models\CounterStatus;
use App\Models\PosSetting;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.counter.index');
    }

    public function list(Request $request)
    {
        $date=str_replace('-','',$request->date);
        return view('admin.counter.list',['date'=>$date,'counters'=>Counter::all()]);

    }

    public function add(Request $request)
    {
        $counter = new Counter();
        $counter->name = $request->name;
        $counter->save();
        $date=str_replace("-",'', $request->date);
        return view('admin.counter.single', compact('counter','date'));
    }
    public function update($id, Request $request)
    {
        $counter = Counter::find($id);
        $counter->name = $request->name;
        $counter->save();
        return redirect()->back();
        // return view('admin.counter.single',compact('counter'));

    }

    //XXX Counter Day Management
    public function day(Request $request)
    {
        $setting = PosSetting::first();
        return view('admin.counter.day.index', compact('setting'));
    }

    function getStatus(Counter $counter)
    {
        return view('admin.counter.status', ['status' => $counter->currentStatus()]);
    }

    public function dayOpen(Request $request)
    {
        $date = str_replace('-', '', $request->date);

        $setting = PosSetting::first();
        if ($setting == null) {
            $setting = new PosSetting();
            $setting->date = $date;
            $setting->direct = $request->direct ?? 0;
            $setting->open = 1;
            $setting->save();
        } else {
            if ($setting->open) {
                $date = $setting->date;
                $counters = CounterStatus::where('status', '<', 3)->where('date', $date)->count();
                if ($counters > 0 && env('use_opening', false)) {
                    return redirect()->back()->withErrors([$counters . ' Counters Are Not Closed.Please Close These Counters To Close Day.']);
                } else {
                    $setting->open = 0;
                    $setting->save();
                }
            } else {
                $setting->date = $date;
                $setting->direct = $request->direct ?? 0;
                $setting->open = 1;
                $setting->save();
            }
        }
        if (!env('use_opening', false) && $setting->open == 1) {
            foreach (Counter::all() as $key => $counter) {
                $status=CounterStatus::where('date',$setting->date)->where('counter_id',$counter->id)->first();
                if($status==null){
                    $status=new CounterStatus();
                    $status->date=$setting->date;
                    $status->counter_id=$counter->id;
                    $status->status=2;
                    $status->save();
                }else{
                    $status->status=2;
                    $status->save();
                }
            }
        }
        return redirect()->back();
    }

    public function dayApprove(Request $request)
    {
        $req = CounterStatus::find($request->id);
        $req->opening = $request->amount;
        if ($req->current < $request->amount) {
            $req->current = $request->amount;
        }
        $req->status = 2;
        $req->save();
        return response('ok');
    }

    public function dayReopen(Request $request)
    {
        $status = CounterStatus::find($request->id);
        $status->status = 2;
        $status->save();
        return view('admin.counter.status', ['status' => $status]);
    }
}
