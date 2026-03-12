<?php

namespace App\DataTables;

use App\Models\PedidoEspecial;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PedidosEspecialesDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('created_at',function($q){
                return \Carbon::createFromFormat('Y-m-d H:i:s',$q->created_at)->format('d/m/Y h:i A');
            })
            ->editColumn('capturo',function($q){
                if(isset($q->creador))
                    return $q->creador->name;
                else
                    return "Sin usuario asignado";
            })
            ->addColumn('accion',function($q){
                return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic outlined example">
                  <a href="'.route('pedidos_especiales.ver',$q->id).'" type="button" class="btn btn-warning btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar información"><i class="bi bi-eye"></i></a>
                  <button type="button" class="btn btn-danger btn-sm eliminar_registro" data-id="'.$q->id.'" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar pedido"><i class="bi bi-person-x"></i></button>
                </div>';
            })
            ->rawColumns(['accion'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(PedidoEspecial $model): QueryBuilder
    {
        return $model->where('deleted_at',null);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('peidos-especial-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return [
            Column::computed('accion')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
                   
                    Column::make('id'),
                    Column::make('cliente'),
                    Column::make('capturo'),
                    Column::make('gran_total'),
                    Column::make('created_at'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'PedidosEspeciales_' . date('YmdHis');
    }
}
