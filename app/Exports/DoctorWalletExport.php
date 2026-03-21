<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DoctorWalletExport implements FromCollection, WithHeadings, WithMapping
{
    protected $movements;

    public function __construct($movements)
    {
        $this->movements = $movements;
    }

    public function collection()
    {
        return $this->movements;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Concepto',
            'Referencia/Detalle',
            'Tipo',
            'Monto',
        ];
    }

    public function map($mov): array
    {
        if ($mov->is_payment) {
            $concepto = $mov->status == 'pending' ? 'Solicitud de Retiro' : 'Pago Realizado';
            $tipo = 'Egreso';
            $monto = '-' . $mov->display_amount;
            $detalle = $mov->reference_code;
        } else {
            $concepto = 'Honorarios por Firma';
            $tipo = 'Ingreso';
            $monto = '+' . $mov->display_amount;
            $detalle = "Paciente: " . ($mov->order->patient->user->name ?? 'N/A');
        }

        return [
            $mov->date_for_sort->format('d/m/Y H:i'),
            $concepto,
            $detalle,
            $tipo,
            $monto
        ];
    }
}
