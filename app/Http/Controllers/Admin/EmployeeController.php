<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\LedgerManage;
use App\Models\Advance;
use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\EmployeeSession;
use App\Models\Ledger;
use App\Models\SalaryPayment;
use App\Models\User;
use App\NepaliDate;
use App\PaymentManager;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('admin.emp.index');
    }

    public function add(Request $request)
    {
        $user=User::where('phone',$request->phone)->first();
        if($user==null){
            $user = new User();
            $user->role = 4;
            $user->password = bcrypt($request->phone);
        }
        $user->phone = $request->phone;
        $user->name = $request->name;
        $user->address = $request->address;
        $user->save();

        $emp = new Employee();
        $emp->user_id = $user->id;
        $emp->salary = $request->salary;
        $emp->acc = $request->acc;
        $emp->start = $request->filled('start')?str_replace("-","", $request->start):null;
        $emp->enddate = $request->filled('end')?str_replace("-","", $request->end):null;
        $emp->save();
        $emp->user = $user;

        return view('admin.emp.single', compact('emp'));
    }

    public function update(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        $user->phone = $request->phone;
        $user->name = $request->name;
        $user->address = $request->address;
        $user->role = 4;

        $user->password = bcrypt($request->phone);
        $user->save();
        $emp = Employee::where('user_id', $user->id)->first();
        $emp->salary = $request->salary;
        $emp->acc = $request->acc;
        $emp->start = $request->filled('start')?str_replace("-","", $request->start):null;
        $emp->enddate = $request->filled('end')?str_replace("-","", $request->end):null;
        $emp->save();
        $emp->user = $user;
        return view('admin.emp.single', compact('emp'));

    }

    public function list()
    {
        $emps = Employee::with('user')->get();
        return view('admin.emp.list', compact('emps'));
    }

    public function delete(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        $emp = Employee::where('user_id', $user->id)->first();
        if ($emp != null) {
            $emp->delete();
        }
        $user->delete();
    }

    public function detail($id)
    {
        $user = User::where('id', $id)->first();
        // return view('admin.emp.detail',compact('user'));
        return view('admin.emp.detail1', compact('user'));
    }

    public function loadData(Request $request)
    {


        $year = $request->year;
        $month = $request->month;
        $week = $request->week;
        $session = $request->session;
        $type = $request->type;
        $range = [];
        $data = [];
        $date = 1;
        $title = "";
        $ledger = Ledger::where('user_id', $request->user_id);
        if ($type == 0) {
            $range = NepaliDate::getDate($request->year, $request->month, $request->session);
            $ledger = $ledger->where('date', '<=', $range[2]);
            $title = "<span class='mx-2'>Year:" . $year . "</span>";
            $title .= "<span class='mx-2'>Month:" . $month . "</span>";
            $title .= "<span class='mx-2'>Session:" . $session . "</span>";
        } elseif ($type == 1) {
            $date = $date = str_replace('-', '', $request->date1);
            $ledger = $ledger->where('date', '=', $date);
            $title = "<span class='mx-2'>Date:" . _nepalidate($date) . "</span>";
        } elseif ($type == 2) {
            $range = NepaliDate::getDateWeek($request->year, $request->month, $request->week);
            $ledger = $ledger->where('date', '<=', $range[2]);
            $title = "<span class='mx-2'>Year:" . $year . "</span>";
            $title .= "<span class='mx-2'>Month:" . $month . "</span>";
            $title .= "<span class='mx-2'>Week:" . $week . "</span>";
        } elseif ($type == 3) {
            $range = NepaliDate::getDateMonth($request->year, $request->month);
            $ledger = $ledger->where('date', '<=', $range[2]);
            $title = "<span class='mx-2'>Year:" . $year . "</span>";
            $title .= "<span class='mx-2'>Month:" . $month . "</span>";
        } elseif ($type == 4) {
            $range = NepaliDate::getDateYear($request->year);
            $ledger = $ledger->where('date', '<=', $range[2]);
            $title = "<span class='mx-2'>Year:" . $year . "</span>";
        } elseif ($type == 5) {
            $range[1] = str_replace('-', '', $request->date1);;
            $range[2] = str_replace('-', '', $request->date2);;
            $ledger = $ledger->where('date', '<=', $range[2]);
            $title = "<span class='mx-2'>from:" . $request->date1 . "</span>";
            $title .= "<span class='mx-2'>To:" . $request->date2 . "</span>";
        }
        // dd($ledger->toSql(),$ledger->getBindings());
        // dd($ledgers);
        $user = User::where('id', $request->user_id)->first();
        $base = 0;
        $prev = 0;
        $closing = 0;
        $arr = [];
        $ledgers = $ledger->orderBy('date', 'asc')->orderBy('id', 'asc')->get();
        if($type>-1){

            foreach ($ledgers as $key => $l) {

                if ($l->type == 1) {
                    $base -= $l->amount;
                } else {
                    $base += $l->amount;
                }
                if ($l->date < $range[1]) {
                    $prev = $base;
                }
                if ($l->date >= $range[1] && $l->date <= $range[2]) {
                    $l->amt = $base;
                    $closing = $base;
                    array_push($arr, $l);
                }
            }
        }else{
            foreach ($ledgers as $key => $l) {

                if ($l->type == 1) {
                    $base -= $l->amount;
                } else {
                    $base += $l->amount;
                }
                $prev = $base;
                $l->amt = $base;
                $closing = $base;
                array_push($arr, $l);

            }
        }
        return view('admin.emp.data1', compact('arr', 'prev', 'type', 'user', 'title'));
    }

    // employee advance controller

    public function advance()
    {
        $emps = Employee::with('user')->get();
        // dd($emps);
        return view('admin.emp.advance.index', compact('emps'));
    }

    public function getAdvance(Request $request)
    {
        $date = str_replace('-', '', $request->date);
        $advances = EmployeeAdvance::where('date', $date)->get();

        return view('admin.emp.advance.list', compact('advances'));
    }
    public function addAdvance(Request $request)
    {

        $date = str_replace('-', '', $request->date);
        $np = new NepaliDate($date);
        $employee = Employee::where('id', $request->employee_id)->first();

        if (!($employee->sessionClosed($np->year, $np->month))) {
            return response('previous Month Not Closed', 500);
        }
        $advance = new EmployeeAdvance();
        $advance->employee_id = $request->employee_id;
        $advance->title = $request->title;
        $advance->amount = $request->amount;
        $advance->date = $date;
        $advance->save();

        $ledger = new LedgerManage($employee->user_id);
        if(env('acc_system','old')=='old'){
            $ledger->addLedger('Advance Given-(' . $request->title . ')', 1, $request->amount, $date, '112', $advance->id);
        }else{
            $ledger->addLedger('Advance Given-(' . $request->title . ')', 2, $request->amount, $date, '112', $advance->id);
        }
        new PaymentManager($request,$advance->id,112);
        return view('admin.emp.advance.single', compact('advance'));
    }

    public function editAdvance(Request $request)
    {
        $advance=Advance::where('id',$request->id)->first();
        $paymentData=PaymentManager::loadUpdateID($request->id,112);
        dd($paymentData);
        return view('admin.emp.advance.edit',compact('advance','paymentData'));

    }

    public function updateAdvance(Request $request)
    {
        $date = str_replace('-', '', $request->date);
        $advance = EmployeeAdvance::find($request->id);
        $tempamount = $advance->amount;
        $advance->title = $request->title;
        $advance->amount = $request->amount;
        $advance->save();
        $ledger = new LedgerManage($advance->employee->user_id);
        if(env('acc_system','old')=='old'){
            $ledger->addLedger('Advance Canceled', 2, $tempamount, $date, '113', $advance->id);
            $ledger->addLedger('Advance Updated-(' . $request->title . ')', 1, $request->amount, $date, '112', $advance->id);
        }else{
            $ledger->addLedger('Advance Canceled', 1, $tempamount, $date, '113', $advance->id);
            $ledger->addLedger('Advance Updated-(' . $request->title . ')', 2, $request->amount, $date, '112', $advance->id);

        }

        return response()->json(['status' => 'success']);
    }

    public function delAdvance(Request $request)
    {
        $date = str_replace('-', '', $request->date);
        $advance = EmployeeAdvance::find($request->id);
        $tempamount = $advance->amount;
        $advance->delete();
        $ledger = new LedgerManage($advance->employee->user_id);
        if(env('acc_system','old')=='old'){
            $ledger->addLedger('Advance Canceled', 2, $tempamount, $date, '113', $advance->id);
        }else{
            $ledger->addLedger('Advance Canceled', 1, $tempamount, $date, '113', $advance->id);

        }
        return response()->json(['status' => 'success']);
    }

    // employee salary pay

    public function salaryIndex()
    {
        return view('admin.emp.salarypay.index');
    }

    public function loadEmpData(Request $request)
    {
        // dd($request->all());
        $range = [];

        $employee = Employee::where('id', $request->emp_id)->first();
        $user = $employee->user;
        $salary=NepaliDate::calculateSalary($request->year, $request->month,$employee);
        $range = NepaliDate::getDateMonth($request->year, $request->month);
        $prev = Ledger::where('date', '<', $range[1])->where('type', 2)->where('user_id', $user->id)->sum('amount') - Ledger::where('date', '<', $range[1])->where('type', 1)->where('user_id', $user->id)->sum('amount');
        $ledgers = Ledger::where('date', '>=', $range[1])->where('date', '<=', $range[2])->where('user_id', $user->id)->get();
        $empSession = EmployeeSession::where('year', $request->year)->where('month', $request->month)->where('user_id', $user->id)->first();
        $year=$request->year;
        $month=$request->month;
        $totalSalaryPaid=SalaryPayment::where('date', '>=', $range[1])->where('date', '<=', $range[2])->where('user_id', $user->id)->sum('amount');
        $arr = [];
        $base = $prev;
        $salaryLoaded=false;

        foreach ($ledgers as $key => $l) {

            if ($l->type == 1) {
                $base -= $l->amount;
            } else {
                $base += $l->amount;
            }
            if($l->identifire==129){
                $salaryLoaded=true;
            }
            $l->amt = $base;
            $closing = $base;
            array_push($arr, $l);
        }
        $track=$base;



        $lastdate = NepaliDate::getDateMonthLast($request->year, $request->month);
        if(env('acc_system','old')=='old'){
            return view('admin.emp.salarypay.data_new', compact('track','arr', 'user', 'employee', 'empSession', 'prev','salaryLoaded','lastdate','salary'));
        }else{
            return view('admin.emp.salarypay.data_new_new', compact('track','arr', 'user', 'employee', 'empSession', 'prev','salaryLoaded','lastdate','salary','year','month'));
        }


    }

    public function storeSalary(Request $request)
    {
        // dd($request->all());
        $date =(int) (str_replace('-', '', $request->date));
        $employee = Employee::where('id', $request->emp_id)->first();
        // $np = new NepaliDate($date);
        $range = NepaliDate::getDateMonth($request->year, $request->month);

        if (!($employee->sessionClosed($request->year, $request->month))) {
            return response('previous Month Not Closed', 500);
        } else {
            $salaryLoaded = Ledger::where('date', '>=', $range[1])->where('date', '<=', $range[2])->where('user_id', $employee->user_id)->where('identifire',129)->count()>0;
            $ledger = new LedgerManage($employee->user_id);
            $salary=NepaliDate::calculateSalary($request->year, $request->month,$employee);

            if(!$salaryLoaded){
                if(env('acc_system','old')=='old'){
                    $ledger->addLedger('salary For (' . $request->year . "-" . ($request->month < 10 ? "0" . $request->month : $request->month) . ")", 2, $salary, $date, 129);
                }else{
                    $ledger->addLedger('salary For (' . $request->year . "-" . ($request->month < 10 ? "0" . $request->month : $request->month) . ")", 1, $salary, $date, 129);
                }

            }
            $salaryPay = new SalaryPayment();
            $salaryPay->date = $date;
            $salaryPay->year = $request->year;
            $salaryPay->month = $request->month;
            $salaryPay->amount = $request->pay;
            $salaryPay->payment_detail = $request->desc;
            $salaryPay->user_id = $employee->user_id;
            $salaryPay->save();
            if(env('acc_system','old')=='old'){

                $ledger->addLedger('Salary Paid for ' . $request->year . "-" . ($request->month < 10 ? "0" . $request->month : $request->month), 1, $request->pay, $date, '124', $salaryPay->id);
            }else{
                $ledger->addLedger('Salary Paid for ' . $request->year . "-" . ($request->month < 10 ? "0" . $request->month : $request->month), 2, $request->pay, $date, '124', $salaryPay->id);
            }
            echo 'ok';
        }
    }


    public function amountTransfer(Request $request)
    {
        // dd($request->all());
        $date = str_replace('-', '', $request->date);
        $employee = Employee::where('id', $request->emp_id)->first();
        $checkUser = SalaryPayment::where('year', $request->year)->where('month', $request->month)->where('user_id', $employee->user_id)->count();
        if ($checkUser > 0) {
            echo 'notok';
        } else {
            $salaryPay = new SalaryPayment();
            $salaryPay->date = $date;
            $salaryPay->year = $request->year;
            $salaryPay->month = $request->month;
            $salaryPay->amount = 0;
            $salaryPay->payment_detail = $request->desc;
            $salaryPay->user_id = $employee->user_id;
            $salaryPay->save();
            $ledger = new LedgerManage($employee->user_id);
            $ledger->addLedger('Salary Paid through balance transfer for:-(' . $request->year . '-' . $request->month . ')', 1, 0, $date, '124', $salaryPay->id);
            $advance = new EmployeeAdvance();
            $advance->date = $date;
            $advance->amount = $request->transfer_amount;
            $advance->employee_id = $employee->id;
            $advance->title = "Previous advance transfer";
            $advance->save();
            echo 'ok';
        }
    }

    public function paidList(Request $request)
    {
        // dd($request->all());
        if ($request->emp_id != -1) {
            $employee = Employee::where('id', $request->emp_id)->first();
            $salary = SalaryPayment::where('year', $request->year)->where('month', $request->month)->where('user_id', $employee->user_id)->get();
        } else {
            $salary = SalaryPayment::where('year', $request->year)->where('month', $request->month)->get();
        }
        return view('admin.emp.salarypay.list', compact('salary'));
    }
    //XXX Employee Month Closing
    public function closeSession(Request $request)
    {
        $employee = Employee::where('id', $request->emp_id)->first();

        if (EmployeeSession::where('year', $request->year)->where('month', $request->year)->where('user_id', $employee->user_id)->count() > 0) {
            return response("Month Already Closed");
        }
        if ($employee->sessionClosed($request->year, $request->month)) {

            if($request->amount>0){

                $l = [];

                $lm = new LedgerManage($employee->user_id);
                $lastdate = NepaliDate::getDateMonthLast($request->year, $request->month);

                if(env('acc_system','old')=='old'){
                    $l[0] = $lm->addLedger('salary Remaning  For (' . $request->year . "-" . ($request->month < 10 ? "0" . $request->month : $request->month) . ")", 2, $request->amount, $lastdate, 129);
                }else{
                    $l[0] = $lm->addLedger('salary Remaning For (' . $request->year . "-" . ($request->month < 10 ? "0" . $request->month : $request->month) . ")", 1, $request->amount, $lastdate, 129);
                }
            }

            $sessionClose = new EmployeeSession();
            $sessionClose->user_id = $employee->user_id;
            $sessionClose->month = $request->month;
            $sessionClose->year = $request->year;
            $sessionClose->save();
            return response('ok');
        } else {
            return response('previous Month Not Closed', 500);
        }
    }

    //XXX Employee Account Opening
    public function accountIndex(Request $request)
    {
        if ($request->getMethod() == "POST") {
            $date = str_replace('-', '', $request->date);
            $ledgers = Ledger::join('users', 'ledgers.user_id', '=', 'users.id')->select('ledgers.id', 'users.name', 'ledgers.amount', 'ledgers.type')->where('ledgers.date', $date)->where('ledgers.identifire', 113)->get();
            return view('admin.emp.account.list', compact('ledgers'));
        } else {
            // $emps = User::where('role', 4)->select('name', 'id')->get();
            $emps = Employee::with('user')->get();
            return view('admin.emp.account.index', compact('emps'));
        }
    }
    public function accountAdd(Request $request)
    {
        $date = str_replace('-', '', $request->date);
        $ledgerManager = new LedgerManage($request->employee);
        $ledger = $ledgerManager->addLedger('Opening Balance', $request->type, $request->amount, $date, '113');
        $ledger->name = User::where('id', $request->employee)->select('name')->first()->name;
        return view('admin.emp.account.single', compact('ledger'));
    }

    public function ret(){
        return view('admin.emp.ret.index');
    }
    public function addRet(Request $request){
        $date = str_replace('-', '', $request->date);
        $np = new NepaliDate($date);
        $employee = Employee::where('id', $request->employee_id)->first();
        $ledger = new LedgerManage($employee->user_id);
        $advance=$ledger->addLedger( $request->title , 1, $request->amount, $date, '140', );
        return view('admin.emp.ret.single', compact('advance'));
    }
    public function getRet(Request $request)
    {
        $date = str_replace('-', '', $request->date);
        $advances = Ledger::where('date', $date)->where('identifire',140)->get();
        // dd($advances);
        return view('admin.emp.ret.list', compact('advances'));
    }

    public function delRet(Request $request)
    {
        $date = str_replace('-', '', $request->date);

        $ledger = Ledger::where('id',$request->id)->delete();

        return response()->json(['status' => 'success']);
    }

    public function updateRet(Request $request)
    {
        $advance = Ledger::find($request->id);
        $advance->title = $request->title;
        $advance->amount = $request->amount;
        $advance->save();
        return response()->json(['status' => 'success']);

    }
}
