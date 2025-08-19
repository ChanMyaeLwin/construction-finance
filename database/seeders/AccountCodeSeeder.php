<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountCode;

class AccountCodeSeeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            ['code' => 'AC-10050', 'name' => 'Main Cash','account_code_type_id' => 5],
            ['code' => 'AC-10051', 'name' => 'Petty Cash','account_code_type_id' => 5],
            ['code' => 'AC-10052', 'name' => 'AYA','account_code_type_id' => 5],
            ['code' => 'AC-10053', 'name' => 'KBZ','account_code_type_id' => 5],
            ['code' => 'AC-10054', 'name' => 'KBZ-USD','account_code_type_id' => 5],

            ['code' => 'AC-10001', 'name' => 'Tool & Equipment A/C','account_code_type_id' => 1],
            ['code' => 'AC-10002', 'name' => 'Computer A/C','account_code_type_id' => 1],
            ['code' => 'AC-10003', 'name' => 'Table A/C','account_code_type_id' => 1],
            ['code' => 'AC-10004', 'name' => 'Printer A/C','account_code_type_id' => 1],
            ['code' => 'AC-10005', 'name' => 'Inventer A/C','account_code_type_id' => 1],

            ['code' => 'AC-10100', 'name' => 'Prepaid Expenses - Miscellaneous','account_code_type_id' => 6],
            ['code' => 'AC-10101', 'name' => 'Prepaid Expenses - Office Rental','account_code_type_id' => 6],
            ['code' => 'AC-10102', 'name' => 'Advance corporate income tax','account_code_type_id' => 4],
            ['code' => 'AC-10103', 'name' => 'Advance commercial tax','account_code_type_id' => 4],
            ['code' => 'AC-10104', 'name' => 'Trade Receivable','account_code_type_id' => 3],

            ['code' => 'AC-20001', 'name' => 'Capital','account_code_type_id' => 7],
            ['code' => 'AC-20002', 'name' => 'Retained Earnings','account_code_type_id' => 7],

            ['code' => 'AC-20100', 'name' => 'Deferred Income A/C','account_code_type_id' => 10],

            ['code' => 'AC-20201', 'name' => 'Trade Payable','account_code_type_id' => 8],

            ['code' => 'AC-20301', 'name' => 'Provision for Commercial Tax','account_code_type_id' => 9],
            ['code' => 'AC-20302', 'name' => 'Provision for Corporate Income Tax','account_code_type_id' => 9],

            ['code' => 'AC-20401', 'name' => 'Accrued Salary & Wages','account_code_type_id' => 11],
            ['code' => 'AC-20402', 'name' => 'Realized Ex-Gain/(Loss)','account_code_type_id' => 11],
            ['code' => 'AC-20403', 'name' => 'Provision for PIT','account_code_type_id' => 11],
            ['code' => 'AC-20404', 'name' => 'Accrued Rental','account_code_type_id' => 11],
            ['code' => 'AC-20405', 'name' => 'Accrued Expenses','account_code_type_id' => 11],

            ['code' => 'AC-20501', 'name' => 'Accumulative Depreciation A/C (Equipment)','account_code_type_id' => 1],
            ['code' => 'AC-20502', 'name' => 'Accumulative Depreciation A/C ( Computer )','account_code_type_id' => 1],

            ['code' => 'AC-40001', 'name' => 'Income','account_code_type_id' => 12],
            ['code' => 'AC-40002', 'name' => 'Discount','account_code_type_id' => 12],

            ['code' => 'AC-50001', 'name' => 'Rebar','account_code_type_id' => 13],
            ['code' => 'AC-50002', 'name' => 'Backhoe','account_code_type_id' => 13],

            ['code' => 'AC-50003', 'name' => 'Direct Labour','account_code_type_id' => 15],
            ['code' => 'AC-50004', 'name' => 'Direct Material','account_code_type_id' => 13],
            ['code' => 'AC-50005', 'name' => 'OT','account_code_type_id' => 15],
            ['code' => 'AC-50006', 'name' => 'Brick Work','account_code_type_id' => 15],

            ['code' => 'AC-60001', 'name' => 'General Expenses', 'account_code_type_id' => 14],
            ['code' => 'AC-60002', 'name' => 'Meal expenses Office', 'account_code_type_id' => 14],
            ['code' => 'AC-60003', 'name' => 'Gasoline and  Fuel', 'account_code_type_id' => 14],
            ['code' => 'AC-60004', 'name' => 'Employer Allowance (Meal Expenses)', 'account_code_type_id' => 15],
            ['code' => 'AC-60005', 'name' => 'Rental Fees - Vehicles', 'account_code_type_id' => 14],
            ['code' => 'AC-60006', 'name' => 'Electricity Expenses', 'account_code_type_id' => 14],
            ['code' => 'AC-60007', 'name' => 'Telephone Expenses A/C', 'account_code_type_id' => 14],
            ['code' => 'AC-60008', 'name' => 'Transportation Expenses', 'account_code_type_id' => 14],
            ['code' => 'AC-60009', 'name' => 'Email and  Internet Expenses', 'account_code_type_id' => 14],
            ['code' => 'AC-60010', 'name' => 'Repair & Maintenance', 'account_code_type_id' => 14],
            ['code' => 'AC-60011', 'name' => 'Printing & Stationery', 'account_code_type_id' => 14],
            ['code' => 'AC-60012', 'name' => 'Condo Committee', 'account_code_type_id' => 14],
            ['code' => 'AC-60013', 'name' => 'Fixed Asset', 'account_code_type_id' => 14],
            ['code' => 'AC-60014', 'name' => 'Professional Service Fees A/C', 'account_code_type_id' => 16],
            ['code' => 'AC-60015', 'name' => 'Salary & Wages A/C', 'account_code_type_id' => 15],
            ['code' => 'AC-60016', 'name' => 'Staff Welfare', 'account_code_type_id' => 15],
            ['code' => 'AC-60017', 'name' => 'Seminars Registration Fees', 'account_code_type_id' => 14],
            ['code' => 'AC-60018', 'name' => 'Commercial  Tax', 'account_code_type_id' => 19],
            ['code' => 'AC-60019', 'name' => 'YCDC Tax', 'account_code_type_id' => 14],
            ['code' => 'AC-60020', 'name' => 'Bank Charges', 'account_code_type_id' => 14],
            ['code' => 'AC-60021', 'name' => 'Travelling Expenses', 'account_code_type_id' => 14],
            ['code' => 'AC-60022', 'name' => 'Unrealized_Ex_ Gain /(Loss) A/C', 'account_code_type_id' => 14],
            ['code' => 'AC-60023', 'name' => 'Gift & Donation', 'account_code_type_id' => 14],
            ['code' => 'AC-60024', 'name' => 'Office Rental', 'account_code_type_id' => 14],
            ['code' => 'AC-60025', 'name' => 'Government Tax', 'account_code_type_id' => 14],
            ['code' => 'AC-60026', 'name' => 'Corporate income tax', 'account_code_type_id' => 19],
            ['code' => 'AC-60027', 'name' => 'Ex_ Gain /(Loss) A/C', 'account_code_type_id' => 14],
            ['code' => 'AC-60028', 'name' => 'Computer & Accessories', 'account_code_type_id' => 14],
            ['code' => 'AC-60029', 'name' => 'Land Lease Fee', 'account_code_type_id' => 14],

            ['code' => 'AC-60101', 'name' => 'Entertainment', 'account_code_type_id' => 14],
            ['code' => 'AC-60102', 'name' => 'Documentation Fee', 'account_code_type_id' => 14],

            ['code' => 'AC-60201', 'name' => 'Depreciation (Equipment)', 'account_code_type_id' => 18],
            ['code' => 'AC-60202', 'name' => 'Depreciation (Computer & Accessories)', 'account_code_type_id' => 18],
        ];

        foreach ($codes as $c) {
            AccountCode::updateOrCreate(
                ['code' => trim($c['code'])],
                ['name' => $c['name'], 'account_code_type_id' => $c['account_code_type_id']]
            );
        }
    }
}