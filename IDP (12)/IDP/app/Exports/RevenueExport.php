<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RevenueExport implements FromCollection, WithHeadings
{

    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }


    public function collection()
    {
        return Payment::with([
            'project.client',
            'project.designer'
        ])
        ->where('status','released')
        ->whereYear('released_at',$this->year)
        ->get()
        ->map(function ($payment){

            return [

                'project' => $payment->project->title ?? 'N/A',

                'client' => $payment->project->client->name ?? 'N/A',

                'designer' => $payment->project->designer->name ?? 'N/A',

                'total_amount' => $payment->amount,

                'commission' => $payment->commission_amount,

                'designer_payout' => $payment->designer_amount,

                'released_date' =>
                    optional($payment->released_at)->format('Y-m-d'),

            ];

        });
    }


    public function headings(): array
    {
        return [

            'Project',

            'Client',

            'Designer',

            'Total Amount',

            'Commission',

            'Designer Payout',

            'Released Date',

        ];
    }

}
