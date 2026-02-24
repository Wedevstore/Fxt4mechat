<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use phpDocumentor\Reflection\Types\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FilteredContact implements  FromView, WithTitle, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $contacts;

    public function __construct($contacts)
    {
        $this->contacts = $contacts;
    }

    public function title(): string
    {
        return 'contacts';
    }

    public function view(): View
    {
        $contacts = $this->contacts->latest()->limit(8000)->get();
        return view('backend.exports.filtered-contact',compact('contacts'));
    }


    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
