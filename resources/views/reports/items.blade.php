<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Botiquín — MedShelf</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            background-color: #ffffff;
            padding: 30px;
        }

        /* ── Header ─────────────────────────────────────────── */
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 22px;
            font-weight: bold;
            color: #1e40af;
        }

        .header .subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
        }

        /* ── Summary ────────────────────────────────────────── */
        .summary {
            display: block;
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 24px;
        }

        .summary-row {
            display: block;
            margin-bottom: 4px;
        }

        .summary-label { color: #6b7280; }
        .summary-value { font-weight: bold; color: #1e40af; }

        /* ── Table ──────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        thead tr {
            background-color: #2563eb;
            color: #ffffff;
        }

        thead th {
            padding: 8px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }

        tbody tr:nth-child(even) {
            background-color: #f0f9ff;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        tbody td {
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        /* ── Status badges ──────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-ok       { background-color: #dcfce7; color: #166534; }
        .badge-warning  { background-color: #fef9c3; color: #854d0e; }
        .badge-expired  { background-color: #fee2e2; color: #991b1b; }

        /* ── Footer ─────────────────────────────────────────── */
        .footer {
            margin-top: 30px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ── Header ──────────────────────────────────────────── --}}
    <div class="header">
        <h1>MedShelf — Reporte de Botiquín</h1>
        <p class="subtitle">
            Generado el {{ $generatedAt }}
            &nbsp;|&nbsp;
            Total de medicamentos: <strong>{{ count($items) }}</strong>
        </p>
    </div>

    {{-- ── Summary ───────────────────────────────────────────── --}}
    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Medicamentos vigentes: </span>
            <span class="summary-value">{{ $okCount }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Por vencer (≤ 30 días): </span>
            <span class="summary-value">{{ $warningCount }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Vencidos: </span>
            <span class="summary-value">{{ $expiredCount }}</span>
        </div>
    </div>

    {{-- ── Table ─────────────────────────────────────────────── --}}
    @if(count($items) > 0)
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Medicamento</th>
                <th>Lugar</th>
                <th>Contenido disponible</th>
                <th>Vencimiento</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            @php
                $expDate = \Carbon\Carbon::parse($item->expirationDate);
                $now     = \Carbon\Carbon::now();
                $daysLeft = $now->diffInDays($expDate, false);
                if ($daysLeft < 0) {
                    $statusLabel = 'Vencido';
                    $badgeClass  = 'badge-expired';
                } elseif ($daysLeft <= 30) {
                    $statusLabel = 'Por vencer';
                    $badgeClass  = 'badge-warning';
                } else {
                    $statusLabel = 'Vigente';
                    $badgeClass  = 'badge-ok';
                }
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $item->product->name }}</strong></td>
                <td>{{ $item->place->name }}</td>
                <td>{{ number_format($item->availableContent, 2) }}</td>
                <td>{{ $expDate->format('d/m/Y') }}</td>
                <td>
                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align:center; color:#6b7280; margin-top:40px;">
        No hay medicamentos registrados en tu botiquín.
    </p>
    @endif

    {{-- ── Footer ───────────────────────────────────────────── --}}
    <div class="footer">
        MedShelf &mdash; Gestión inteligente de medicamentos
    </div>

</body>
</html>
