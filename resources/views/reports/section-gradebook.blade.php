<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta Oficial de Calificaciones</title>
    <style>
        @page { margin: 1.4cm 1.5cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #1f2937; }
        .header { text-align: center; border-bottom: 2px solid #1e3a8a; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { margin: 0; font-size: 13px; text-transform: uppercase; color: #1e3a8a; letter-spacing: 0.5px; }
        .header h2 { margin: 2px 0 0; font-size: 11px; font-weight: bold; color: #111827; }
        .header p { margin: 2px 0 0; font-size: 8.5px; color: #4b5563; }

        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9px; }
        .meta-table td { padding: 4px 6px; border: 1px solid #d1d5db; background-color: #f9fafb; }
        .meta-label { font-weight: bold; color: #374151; }

        .grades-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .grades-table th { background-color: #1e3a8a; color: #ffffff; font-weight: bold; padding: 6px 5px; border: 1px solid #1e3a8a; font-size: 8.5px; text-transform: uppercase; }
        .grades-table td { padding: 5px; border: 1px solid #d1d5db; }
        .grades-table tr:nth-child(even) td { background-color: #f9fafb; }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .mono { font-family: DejaVu Sans Mono, monospace; font-size: 8.5px; }
        .passed { color: #065f46; font-weight: bold; }
        .failed { color: #b91c1c; font-weight: bold; }
        .highlight-col { background-color: #eef2ff !important; font-weight: bold; }

        .summary-box { width: 100%; border-collapse: collapse; margin-bottom: 28px; font-size: 9px; }
        .summary-box td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: center; background-color: #f3f4f6; }

        .signatures { width: 100%; border-collapse: collapse; margin-top: 32px; }
        .signatures td { width: 33.33%; text-align: center; padding: 0 18px; vertical-align: bottom; }
        .sig-line { border-top: 1px solid #374151; padding-top: 4px; font-size: 8.5px; color: #374151; }

        .footer { position: fixed; bottom: -0.6cm; left: 0; right: 0; font-size: 7.5px; color: #6b7280; text-align: right; }
    </style>
</head>
<body>
    @php
        $summary = $matrix['summary'];
    @endphp

    <div class="header">
        <h1>República de El Salvador — Sistema de Gestión Académica EduMetrics SV</h1>
        <h2>ACTA OFICIAL DE REGISTRO DE CALIFICACIONES</h2>
        <p>
            Año Lectivo: {{ $academicYear?->name ?? date('Y') }} ·
            Corte Evaluativo: {{ $isAnnualMode ? 'CONSOLIDADO ANUAL (100%)' : strtoupper($selectedPeriod->name) . ' (' . number_format($selectedPeriod->weight_percentage, 0) . '%)' }}
        </p>
    </div>

    <table class="meta-table">
        <tr>
            <td><span class="meta-label">Asignatura:</span> {{ $subject->name }} ({{ $subject->code }})</td>
            <td><span class="meta-label">Nivel / Grado:</span> {{ $subject->gradeLevel->name }}</td>
            <td><span class="meta-label">Sección:</span> "{{ $section->name }}"</td>
        </tr>
        <tr>
            <td><span class="meta-label">Docente Titular:</span> {{ $subject->teacher?->name ?? 'Sin asignar' }}</td>
            <td><span class="meta-label">Ponderación Evaluada:</span> {{ number_format($summary['planned_weight'], 2) }}%</td>
            <td><span class="meta-label">Nota Mínima de Aprobación:</span> 6.00 (Escala 0.00 - 10.00)</td>
        </tr>
    </table>

    @if ($matrix['mode'] === 'period')
        <table class="grades-table">
            <thead>
                <tr>
                    <th style="width: 24px;">#</th>
                    <th style="width: 68px;">NIE</th>
                    <th class="text-left">Nombre Completo del Estudiante</th>
                    @foreach ($matrix['assessments'] as $assessment)
                        <th>
                            {{ $assessment->title }}<br>
                            <span style="font-weight: normal; font-size: 7.5px;">({{ number_format($assessment->weight_percentage, 2) }}%)</span>
                        </th>
                    @endforeach
                    <th style="width: 65px;">Nota Período</th>
                    <th style="width: 65px;">Aporte ({{ number_format($selectedPeriod->weight_percentage, 0) }}%)</th>
                    <th style="width: 72px;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($matrix['rows'] as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center mono">{{ $row['student']->nie }}</td>
                        <td>{{ $row['student']->full_name }}</td>
                        @foreach ($matrix['assessments'] as $assessment)
                            @php
                                $cell = $row['scores'][$assessment->id] ?? ['raw' => null];
                            @endphp
                            <td class="text-center">
                                @if ($cell['raw'] !== null)
                                    <span class="{{ $cell['raw'] >= 6 ? '' : 'failed' }}">
                                        {{ number_format($cell['raw'], 2) }}
                                    </span>
                                @else
                                    <span style="color: #9ca3af;">S/N</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-center highlight-col {{ $row['passed'] ? 'passed' : 'failed' }}">
                            {{ number_format($row['final_grade'], 2) }}
                        </td>
                        <td class="text-center">{{ number_format($row['annual_contribution'], 2) }}</td>
                        <td class="text-center {{ $row['passed'] ? 'passed' : 'failed' }}">
                            {{ $row['passed'] ? 'APROBADO' : 'REPROBADO' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 14px;">No hay estudiantes activos registrados en esta sección.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table class="grades-table">
            <thead>
                <tr>
                    <th style="width: 24px;">#</th>
                    <th style="width: 68px;">NIE</th>
                    <th class="text-left">Nombre Completo del Estudiante</th>
                    @foreach ($matrix['periods'] as $period)
                        <th>
                            {{ $period->name }}<br>
                            <span style="font-weight: normal; font-size: 7.5px;">({{ number_format($period->weight_percentage, 0) }}%)</span>
                        </th>
                    @endforeach
                    <th style="width: 75px;">Nota Final Anual</th>
                    <th style="width: 78px;">Estado Final</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($matrix['rows'] as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center mono">{{ $row['student']->nie }}</td>
                        <td>{{ $row['student']->full_name }}</td>
                        @foreach ($matrix['periods'] as $period)
                            @php
                                $pData = $row['periods'][$period->id] ?? ['grade' => 0];
                            @endphp
                            <td class="text-center {{ $pData['grade'] >= 6 ? '' : 'failed' }}">
                                {{ number_format($pData['grade'], 2) }}
                            </td>
                        @endforeach
                        <td class="text-center highlight-col {{ $row['passed'] ? 'passed' : 'failed' }}">
                            {{ number_format($row['final_grade'], 2) }}
                        </td>
                        <td class="text-center {{ $row['passed'] ? 'passed' : 'failed' }}">
                            {{ $row['passed'] ? 'APROBADO' : 'REPROBADO' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 14px;">No hay estudiantes activos registrados en esta sección.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <table class="summary-box">
        <tr>
            <td><strong>Total Estudiantes:</strong> {{ $summary['total_students'] }}</td>
            <td><strong>Promedio de Sección:</strong> {{ number_format($summary['section_average'], 2) }}</td>
            <td><strong>Aprobados (≥ 6.00):</strong> {{ $summary['approved_count'] }} ({{ $summary['approval_rate'] }}%)</td>
            <td><strong>Reprobados (&lt; 6.00):</strong> {{ $summary['failed_count'] }}</td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">
                    <strong>{{ $subject->teacher?->name ?? 'Docente Titular' }}</strong><br>
                    Docente Responsable de Asignatura
                </div>
            </td>
            <td>
                <div class="sig-line">
                    <strong>Coordinación Académica</strong><br>
                    Revisión y Validación
                </div>
            </td>
            <td>
                <div class="sig-line">
                    <strong>Dirección del Centro Educativo</strong><br>
                    Sello y Firma Autorizada
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Generado por {{ $generatedBy->name }} el {{ $generatedAt->format('d/m/Y H:i') }} · EduMetrics SV
    </div>
</body>
</html>