<?php
namespace App\Imports;

//use App\User;
use App\Models\Award;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AwardImport implements ToModel,WithHeadingRow
{
    public function model(array $row)
    {
        return new Award([
            'emp_id'     => $row[0],
            'award_type'    => $row[1],
            'amount' => $row[2],
            'fiscal_year' => $row[3],
            'brief_reason' => $row[4],
        ]);
    }
}
