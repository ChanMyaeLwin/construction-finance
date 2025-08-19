<?php

// app/Http/Controllers/AccountCodeTypeController.php
namespace App\Http\Controllers;

use App\Models\AccountCodeType;
use Illuminate\Http\Request;

class AccountCodeTypeController extends Controller
{
    // If you use the admin-only trait, add it:
    // use \App\Http\Controllers\Concerns\UsesAdminAuth;

    public function index()
    {
        $types = AccountCodeType::orderBy('name')->paginate(20);
        return view('account-code-types.index', compact('types'));
    }

    public function create()
    {
        return view('account-code-types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:account_code_types,name',
            'description' => 'nullable|string|max:500',
        ]);
        AccountCodeType::create($data);
        return redirect()->route('account-code-types.index')->with('success', 'Type created.');
    }

    public function edit(AccountCodeType $account_code_type)
    {
        return view('account-code-types.edit', ['type' => $account_code_type]);
    }

    public function update(Request $request, AccountCodeType $account_code_type)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:account_code_types,name,' . $account_code_type->id,
            'description' => 'nullable|string|max:500',
        ]);
        $account_code_type->update($data);
        return redirect()->route('account-code-types.index')->with('success', 'Type updated.');
    }

    public function destroy(AccountCodeType $account_code_type)
    {
        if ($account_code_type->accountCodes()->exists()) {
            return back()->with('error', 'Cannot delete: this type is used by one or more account codes.');
        }
        $account_code_type->delete();
        return redirect()->route('account-code-types.index')->with('success', 'Type deleted.');
    }
}