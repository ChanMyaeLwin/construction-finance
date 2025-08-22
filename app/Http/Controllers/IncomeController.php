<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function update(Request $request, Income $income)
    {
        $data = $request->validate([
            'account_code_id' => 'required|exists:account_codes,id',
            'income_date'     => 'required|date',
            'amount'          => 'required|numeric|min:0',
            'description'     => 'nullable|string|max:255',
        ]);

        $income->update($data);

        return back()->with('success', 'Income updated.');
    }

    public function destroy(Income $income)
    {
        $income->delete();

        return back()->with('success', 'Income deleted.');
    }
}