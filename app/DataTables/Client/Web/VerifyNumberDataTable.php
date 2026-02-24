<?php

namespace App\DataTables\Client\Web;

use App\Models\Campaign;
use App\Models\VerifyNumber;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VerifyNumberDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('name', function ($query) {
                return view('backend.client.web.verify-number.partials.name', compact('query'));
            })
            ->addColumn('total_contact', function ($query) {
                return $query->total_contact;
            })
            ->addColumn('total_verify', function ($query) {
                return $query->total_verify;
            })
            ->addColumn('total_unverify', function ($query) {
                return $query->total_unverify;
            })
            ->addColumn('status', function ($query) {
                return view('backend.client.web.verify-number.partials.status', compact('query'));
            })
            ->addColumn('action', function ($query) {
                return view('backend.client.web.verify-number.partials.action', compact('query'));
            })
            ->setRowId('id');
    }

    public function query(VerifyNumber $model)
    {
        $query = $model->where('client_id', auth()->user()->client_id)->latest();

        $query->when($this->request->name, function ($query, $name) {
            $query->where('name', 'like', "%$name%");
        })
        ->when($this->request->contact_list_id, function ($query, $contact_list_id) {
            if ($contact_list_id) {
                $query->whereRaw('JSON_CONTAINS(contact_list_ids, ?)', [json_encode($contact_list_id)]);
            }
        })
        ->when($this->request->segment_id, function ($query, $segment_id) {
            if ($segment_id) {
                $query->whereRaw('JSON_CONTAINS(segment_ids, ?)', [json_encode($segment_id)]);
            }
        })
        ->when(request('search')['value'] ?? false, function ($query, $search) {
            $query->where('name', 'like', "%$search%");
        });

        return $query;
    }

    private function parseDate($date_range)
    {
        $dates = explode('to', $date_range);

        if (count($dates) == 1) {
            $dates[1] = $dates[0];
        }

        $start_date = trim($dates[0]);
        $end_date   = trim($dates[1]);

        $start_date = $start_date . ' 00:00:00';
        $end_date   = $end_date . ' 23:59:59';

        return [
            Carbon::parse($start_date)->format('Y-m-d H:i:s'),
            Carbon::parse($end_date)->format('Y-m-d H:i:s'),
        ];
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->setTableAttribute('style', 'width:99.8%')
            ->footerCallback('function ( row, data, start, end, display ) {
                $(".dataTables_length select").addClass("form-select form-select-lg without_search mb-3");
                selectionFields();
            }')
            ->parameters([
                'dom'        => 'Blfrtip',
                'buttons'    => [[]],
                'lengthMenu' => [[10, 25, 50, 100, 250], [10, 25, 50, 100, 250]],
                'language'   => [
                    'searchPlaceholder' => __('search'),
                    'lengthMenu'        => '_MENU_ '.__('verify_number_per_page'),
                    'search'            => '',
                ],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('id')->data('DT_RowIndex')->title('#')->width(10),
            Column::computed('name')->title(__('name')),
            Column::computed('total_contact')->title(__('total_contact')),
            Column::computed('total_verify')->title(__('total_verify')),
            Column::computed('total_unverify')->title(__('total_unverify')),
            Column::computed('status')->title(__('status')),
            Column::computed('action')->title(__('action'))
                ->exportable(false)
                ->printable(false)
                ->searchable(false)
                ->addClass('action-card')
                ->width(10),
        ];
    }

    protected function filename(): string
    {
        return 'campaign_' . date('YmdHis');
    }
}
