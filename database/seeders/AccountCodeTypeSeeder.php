<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountCodeType;

class AccountCodeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['id'=>1,'name' => 'Non-Current Assets', 'description' => null],
            ['id'=>2,'name' => 'Inventory', 'description' => null],
            ['id'=>3,'name' => 'Trade and Other Receivable', 'description' => null],
            ['id'=>4,'name' => 'Current tax assets', 'description' => null],
            ['id'=>5,'name' => 'Cash and equivalents', 'description' => null],
            ['id'=>6,'name' => 'Other Current Assets', 'description' => null],
            ['id'=>7,'name' => 'Share Capital', 'description' => null],
            ['id'=>8,'name' => 'Trade and other payables', 'description' => null],
            ['id'=>9,'name' => 'Current tax liabilities', 'description' => null],
            ['id'=>10,'name' => 'Current provisions', 'description' => null],
            ['id'=>11,'name' => 'Other current liabilities', 'description' => null],
            ['id'=>12,'name' => 'Revenue', 'description' => null],
            ['id'=>13,'name' => 'Direct operations', 'description' => null],
            ['id'=>14,'name' => 'General & administrative', 'description' => null],
            ['id'=>15,'name' => 'Staff/labour costs', 'description' => null],
            ['id'=>16,'name' => 'Management fees', 'description' => null],
            ['id'=>17,'name' => 'Interest expense', 'description' => null],
            ['id'=>18,'name' => 'Depreciation & amortization', 'description' => null],
            ['id'=>19,'name' => 'Other expenses', 'description' => null],
        ];
        foreach ($types as $t) {
            AccountCodeType::updateOrCreate(['id'=> $t['id'],'name' => $t['name']], $t);
        }
    }
}