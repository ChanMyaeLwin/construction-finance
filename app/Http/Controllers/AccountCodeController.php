<?php
namespace App\Http\Controllers;

use App\Models\AccountCode;
use Illuminate\Http\Request;
use App\Models\AccountCodeType;

class AccountCodeController extends Controller
{

    public function index()
    {
        $codes = AccountCode::orderBy('code')->paginate(20);
        return view('codes.account.index', compact('codes'));
    }

    public function create()
    {
        $types = AccountCodeType::orderBy('name')->get();
        return view('codes.account.create', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_code_type_id' => 'nullable|exists:account_code_types,id',
            'code' => 'required|string|max:50|unique:account_codes,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        AccountCode::create($data);
        return redirect()->route('account-codes.index')->with('success', 'Account Code created.');
    }

    public function edit(AccountCode $account_code)
    {  
        $types = AccountCodeType::orderBy('name')->get();
        return view('codes.account.edit', ['code' => $account_code, 'types' => $types]);
    }

    public function update(Request $request, AccountCode $account_code)
    {
        $data = $request->validate([
            'account_code_type_id' => 'nullable|exists:account_code_types,id',
            'code' => 'required|string|max:50|unique:account_codes,code,' . $account_code->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $account_code->update($data);
        return redirect()->route('account-codes.index')->with('success', 'Account Code updated.');
    }

    public function destroy(AccountCode $account_code)
    {
        $account_code->delete();
        return redirect()->route('account-codes.index')->with('success', 'Account Code deleted.');
    }
}