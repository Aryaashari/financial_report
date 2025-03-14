<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\ChartOfAccount;
use App\Models\Transaction;
use App\Rules\DebitXorCredit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{

    public function index()
    {
        $transactions = Transaction::with('chartOfAccount')->where('user_id', Auth::user()->id)->get();
        $coa = ChartOfAccount::select(['code', 'name'])->where('user_id', Auth::user()->id)->get();
        $coaOptions = collect($coa)->pluck('code', 'name')->toArray();

        return view('transaction.index', compact('transactions', 'coaOptions'));
    }

    public function create()
    {
        $coa = ChartOfAccount::select(['code', 'name'])->where('user_id', Auth::user()->id)->get();
        $coaOptions = collect($coa)->pluck('name', 'code')->toArray();

        return view('transaction.create', compact('coaOptions'));
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'coa_code' => ['required', 'integer'],
                'date' => ['required', 'date'],
                'description' => ['required'],
                'debit' => ['integer', 'min_digits:0', new DebitXorCredit],
                'credit' => [ 'integer', 'min_digits:0'],
            ]);

        
            if ($validator->fails()) {
                return ResponseHelper::SendValidationError($validator->errors());
            }


            Transaction::create([
                'user_id' => Auth::user()->id,
                'coa_code' => $request->coa_code,
                'date' => $request->date,
                'debit' => $request->debit,
                'credit' => $request->credit,
                'description' => $request->description
            ]);

            return ResponseHelper::SendSuccess("create transaction successfully");
        } catch (Exception $error) {
            return ResponseHelper::SendInternalServerError($error);
        }
    }

    public function edit(Transaction $transaction)
    {
        $coa = ChartOfAccount::select(['code', 'name'])->where('user_id', Auth::user()->id)->get();
        $coaOptions = collect($coa)->pluck('name', 'code')->toArray();

        return view('transaction.edit', compact('transaction', 'coaOptions'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        try {

            $validator = Validator::make($request->all(), [
                'coa_code' => ['required', 'integer'],
                'date' => ['required', 'date'],
                'description' => ['required'],
                'debit' => ['integer', 'min_digits:0', new DebitXorCredit],
                'credit' => ['integer', 'min_digits:0']
            ]);

            if ($validator->fails()) {
                return ResponseHelper::SendValidationError($validator->errors());
            }

            $transaction->update([
                'user_id' => Auth::user()->id,
                'coa_code' => $request->coa_code,
                'date' => $request->date,
                'debit' => $request->debit,
                'credit' => $request->credit,
                'description' => $request->description
            ]);

            return ResponseHelper::SendSuccess("update transaction successfully", [
                'coa_code' => $transaction->coa_code,
                'date' => $transaction->date,
                'debit' => $transaction->debit,
                'credit' => $transaction->credit,
                'description' => $transaction->description,
            ]);
        } catch (Exception $error) {
            return ResponseHelper::SendInternalServerError($error);
        }
    }

    public function destroy(int $id)
    {
        try {
            Transaction::destroy($id);

            return ResponseHelper::SendSuccess("delete transaction successfully");
        } catch (Exception $error) {
            return ResponseHelper::SendInternalServerError($error);
        }
    }
}
