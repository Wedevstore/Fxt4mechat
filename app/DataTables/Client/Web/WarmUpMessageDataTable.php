<?php

namespace App\DataTables\Client\Web;

use App\Models\WhatsAppWarmupMessage;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class WarmUpMessageDataTable extends DataTable
{
    protected $warmupId;

    public function with(array|string $key, array|string|null $value = null): static
    {
        // Support both array and key-value usage
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->{$k} = $v;
            }
        } else {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * Build DataTable.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('status', function ($row) {
                $color = match ($row->status) {
                    'sent' => 'success',
                    'failed' => 'danger',
                    'pending' => 'warning',
                    default => 'secondary'
                };
                return "{$row->status}";
                // return "<span class='badge bg-{$color} text-capitalize'>{$row->status}</span>";
            })
            ->editColumn('created_at', fn($row) => $row->created_at->format('Y-m-d H:i'))
            ->rawColumns(['status'])
            ->setRowId('id');
    }

    /**
     * Query for DataTable.
     */
    public function query(WhatsAppWarmupMessage $model): QueryBuilder
    {
        $client = auth()->user()->client;
// dd($this->warmupId);
        return $model->newQuery()
            ->where('client_id', $client->id)
            ->where('warmup_id', $this->warmupId)
            ->orderByDesc('id');
    }

    /**
     * Define table columns.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#')->width(10),
            Column::make('phone_number')->title('Phone Number'),
            Column::make('message')->title('Message'),
            Column::make('status')->title('Status'),
            Column::make('created_at')
                ->title('Sent At')
                ->addClass('text-center') // 👈 align header & body text to right
        ];
    }


    /**
     * Optional HTML builder setup.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('warmup-message-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->parameters([
                'pageLength' => 25,
                'language' => [
                    'searchPlaceholder' => __("Search"),
                    'lengthMenu'        => '_MENU_ '.__('Records per page'),
                    'search'            => '',
                ],
            ]);
    }

    protected function filename(): string
    {
        return 'warmup_messages_' . date('YmdHis');
    }
}
