<?php

namespace App\DataTables;

use App\Models\PedidoSaePendiente;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PedidosSaePendientesDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('created_at', function($q) {
                return $q->created_at ? \Carbon::parse($q->created_at)->format('d/m/Y H:i') : '';
            })
            ->editColumn('completed_at', function($q) {
                return $q->completed_at ? \Carbon::parse($q->completed_at)->format('d/m/Y H:i') : '—';
            })
            ->editColumn('empresa', function($q) {
                if ($q->empresa == 1) return '<span class="badge bg-primary">E01 Factura</span>';
                if ($q->empresa == 3) return '<span class="badge bg-warning text-dark">E03 Remision</span>';
                return $q->empresa;
            })
            ->editColumn('estado', function($q) {
                $clases = [
                    'pendiente'  => 'bg-warning text-dark',
                    'en_proceso' => 'bg-info text-dark',
                    'completado' => 'bg-success',
                    'fallido'    => 'bg-danger',
                ];
                $clase = $clases[$q->estado] ?? 'bg-secondary';
                return '<span class="badge ' . $clase . '">' . strtoupper($q->estado) . '</span>';
            })
            ->editColumn('ultimo_error', function($q) {
                if (!$q->ultimo_error) return '—';
                $corto = mb_strlen($q->ultimo_error) > 80
                    ? mb_substr($q->ultimo_error, 0, 80) . '…'
                    : $q->ultimo_error;
                return '<span title="' . e($q->ultimo_error) . '">' . e($corto) . '</span>';
            })
            ->addColumn('folio_pedido_web', function($q) {
                return $q->id_pedido_web ?? '—';
            })
            ->addColumn('accion', function($q) {
                $puedeReintentar = in_array($q->estado, ['pendiente','fallido','en_proceso']);
                $puedeCancelar   = in_array($q->estado, ['pendiente','en_proceso','fallido']);

                $html = '<div class="btn-group btn-group-sm" role="group">';
                $html .= '<button type="button" class="btn btn-outline-primary ver_detalle" data-id="'.$q->id.'" title="Ver detalle"><i class="bi bi-eye"></i></button>';
                if ($puedeReintentar) {
                    $html .= '<button type="button" class="btn btn-outline-success reintentar_ahora" data-id="'.$q->id.'" title="Reintentar ahora"><i class="bi bi-arrow-repeat"></i></button>';
                }
                if ($puedeCancelar) {
                    $html .= '<button type="button" class="btn btn-outline-danger cancelar_pendiente" data-id="'.$q->id.'" title="Marcar como fallido"><i class="bi bi-x-circle"></i></button>';
                }
                $html .= '</div>';
                return $html;
            })
            ->setRowClass(function($row) {
                return match ($row->estado) {
                    'completado' => 'table-success',
                    'fallido'    => 'table-danger',
                    'en_proceso' => 'table-info',
                    default      => '',
                };
            })
            ->rawColumns(['empresa','estado','ultimo_error','accion'])
            ->setRowId('id');
    }

    public function query(PedidoSaePendiente $model): QueryBuilder
    {
        $estado = request('estado_filtro');

        $q = $model->newQuery();
        if ($estado && in_array($estado, ['pendiente','en_proceso','completado','fallido'])) {
            $q->where('estado', $estado);
        }
        return $q;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('pedidos-sae-pendientes-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('reload'),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('cliente')->title('Cliente'),
            Column::make('empresa')->title('Empresa'),
            Column::make('estado')->title('Estado'),
            Column::make('intentos')->title('Intentos'),
            Column::make('ultimo_error')->title('Ultimo error'),
            Column::make('folio_sae')->title('Folio SAE'),
            Column::computed('folio_pedido_web')->title('PedidoWeb id'),
            Column::make('created_at')->title('Encolado'),
            Column::make('completed_at')->title('Completado'),
            Column::computed('accion')
                  ->exportable(false)
                  ->printable(false)
                  ->width(120)
                  ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'PedidosSaePendientes_' . date('YmdHis');
    }
}
