<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Calificaciones</title>
    <style>
        @page { margin: 1.6cm 1.8cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 16px; }
        .header h1 { margin: 0; font-size: 13px; text-transform: uppercase; color: #1e3a8a; }
        .header h2 { margin: 3px 0 0; font-size: 11.5px; color: #111827; }
        .header p { margin: 3px 0 0; font-size: 9px; color: #4b5563; }

        .student-box { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 9.5px; }
        .student-box td { padding: 6px 8px; border: 1px solid #d1d5db; background-color: #f9fafb; }
        .label { font-weight: bold; color: #374151; }

        .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .grades-table th { background-color: #1e3a8a; color: #ffffff; padding: 7px 6px; border: 1px solid #1e3a8a; font-size: 8.5px; text-transform: uppercase; }
        .grades-table td { padding: 7px 6px; border: 1px solid #d1d5db; }
        .grades-table tr:nth-child(even) td { background-color: #f9fafb; }

        .text-center { text-align: center; }
        .mono { font-family: DejaVu Sans Mono, monospace; }
        .passed { color: #065f46; font-weight: bold; }
        .failed { color: #b91c1c; font-weight: bold; }
        .final-col { background-color: #eef2ff !important; font-weight: bold; }

        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 36px; font-size: 9.5px; }
        .summary-table td { border: 1px solid #d1d5db; padding: 8px; text-align: center; background-color: #f3f4f6; }

        .signatures { width: 100%; border-collapse: collapse; margin-top: 44px; }
        .signatures td { width: 50%; text-align: center; padding: 0 24px; }
        .sig-line { border-top: 1px solid #374151; padding-top: 5px; font-size: 9px; color: #374151; }

        .note { font-size: 8.5px; color: #4b5563; border-left: 3px solid #1e3a8a; padding-left: 8px; margin-bottom: 24px; }
        .footer { position: fixed; bottom: -0.7cm; left: 0; right: 0; font-size: 8px; color: #6b7280; text-align: right; }
    </style>
</head>
<body>
    @php
        $student = $report['student'];
        $section = $report['section'];
    @endphp

    <div class="header">
        <h1>República de El Salvador — Sistema de Gestión Académica EduMetrics SV</h1>
        <h2>BOLETA INDIVIDUAL DE CALIFICACIONES</h2>
        <p>Año Lectivo: {{ $academicYear?->name ?? date('Y') }} · Escala Oficial de Evaluación: 0.00 a 10.00 (Mínima Aprobatoria: 6.00)</p>
    </div>

    <table class="student-box">
        <tr>
            <td style="width: 65%;"><span class="label">Estudiante:</span> {{ $student->full_name }}</td>
            <td style="width: 35%;"><span class="label">NIE:</span> <span class="mono">{{ $student->nie }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Nivel / Grado:</span> {{ $section->gradeLevel->name }}</td>
            <td><span class="label">Sección:</span> "{{ $section->name }}"</td>
        </tr>
    </table>

    <table class="grades-table">
        <thead>
            <tr>
                <th style="text-align: left;">Asignatura</th>
                @foreach ($report['periods'] as $period)
                    <th style="width: 64px;">
                        {{ $period->name }}<br>
                        <span style="font-weight: normal; font-size: 7.5px;">({{ number_format($period->weight_percentage, 0) }}%)</span>
                    </th>
                @endforeach
                <th style="width: 72px;">Nota Final</th>
                <th style="width: 78px;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report['subjects'] as $row)
                <tr>
                    <td>
                        <strong>{{ $row['subject']->name }}</strong><br>
                        <span style="font-size: 8px; color: #6b7280;">Docente: {{ $row['subject']->teacher?->name ?? 'Sin asignar' }}</span>
                    </td>
                    @foreach ($report['periods'] as $period)
                        @php
                            $pScore = $row['periods'][$period->id] ?? ['grade' => 0, 'has_scores' => false];
                        @endphp
                        <td class="text-center">
                            @if ($pScore['has_scores'])
                                <span class="{{ $pScore['grade'] >= 6 ? '' : 'failed' }}">
                                    {{ number_format($pScore['grade'], 2) }}
                                </span>
                            @else
                                <span style="color: #9ca3af;">—</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="text-center final-col {{ $row['passed'] ? 'passed' : 'failed' }}">
                        {{ number_format($row['final_grade'], 2) }}
                    </td>
                    <td class="text-center {{ $row['passed'] ? 'passed' : 'failed' }}">
                        {{ $row['passed'] ? 'APROBADO' : 'REPROBADO' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td><strong>Promedio General Acumulado:</strong> {{ number_format($report['general_average'], 2) }}</td>
            <td><strong>Asignaturas Aprobadas:</strong> {{ $report['approved_subjects'] }} / {{ $report['subjects']->count() }}</td>
            <td><strong>Asignaturas Reprobadas:</strong> {{ $report['failed_subjects'] }}</td>
        </tr>
    </table>

    <div class="note">
        <strong>Observación Normativa:</strong> Conforme a la normativa de evaluación del Ministerio de Educación de El Salvador, cada período aporta el 25% a la calificación anual y la nota mínima de promoción por asignatura es de <strong>6.00</strong>.
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">
                    <strong>Coordinación Académica</strong><br>
                    Firma y Sello
                </div>
            </td>
            <td>
                <div class="sig-line">
                    <strong>Dirección del Centro Educativo</strong><br>
                    Firma y Sello Oficial
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento emitido el {{ $generatedAt->format('d/m/Y H:i') }} por {{ $generatedBy->name }} · EduMetrics SV
    </div>
</body>
</html>