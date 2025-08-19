<?php
namespace App\Http\Controllers;

use App\Models\ExpenseCode;
use Illuminate\Http\Request;

class ExpenseCodeController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public function index()
    {
        $codes = ExpenseCode::orderBy('code')->paginate(20);
        return view('codes.expense.index', compact('codes'));
    }

    public function create() { return view('codes.expense.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:expense_codes,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        ExpenseCode::create($data);
        return redirect()->route('expense-codes.index')->with('success', 'Expense Code created.');
    }

    public function edit(ExpenseCode $expense_code)
    { return view('codes.expense.edit', ['code' => $expense_code]); }

    public function update(Request $request, ExpenseCode $expense_code)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:expense_codes,code,' . $expense_code->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $expense_code->update($data);
        return redirect()->route('expense-codes.index')->with('success', 'Expense Code updated.');
    }

    public function destroy(ExpenseCode $expense_code)
    {
        $expense_code->delete();
        return redirect()->route('expense-codes.index')->with('success', 'Expense Code deleted.');
    }
}