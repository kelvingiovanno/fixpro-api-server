<?php

namespace App\Services;

class QuickChartService
{
    public function ratio_doughnut(int $segment, int $total): string
    {
        $percent = $total > 0 ? round(($segment / $total) * 100, 2) : 0;

        $color = sprintf("#%02x%02x%02x", random_int(0, 255), random_int(0, 255), random_int(0, 255));

        $chart_config = [
            'type' => 'doughnut',
            'data' => [
                'datasets' => [[
                    'data' => [$segment, max(0, $total - $segment)],
                    'backgroundColor' => [$color, '#e8e8e8'],
                    'borderWidth' => 0,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => false,
                    'doughnutlabel' => [
                        'labels' => [[
                            'text' => $percent . '%',
                            'font' => ['size' => 60],
                        ]],
                    ],
                    'datalabels' => ['display' => false],
                ],
                'cutoutPercentage' => 70,
            ],
        ];

        return 'https://quickchart.io/chart?c=' . urlencode(json_encode($chart_config));
    }

    public function piechart(array $datasets, array $labels)
    {
        $background_colors = collect($datasets)->map(function () {
            return sprintf("#%02x%02x%02x", random_int(0, 255), random_int(0, 255), random_int(0, 255));
        })->toArray();

        $chart_config = [
            'type' => 'outlabeledPie',
            'data' => [
                'labels' => $labels,
                'datasets' => [[ 
                    'backgroundColor' => $background_colors,
                    'data' => $datasets,
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => false,
                    'outlabels' => [
                        'text' => '%l %p',
                        'color' => 'white',
                        'stretch' => 35,
                        'font' => [
                            'resizable' => true,
                            'minSize' => 14,
                            'maxSize' => 15
                        ]
                    ]
                ]
            ]
        ];

        return 'https://quickchart.io/chart?c=' . urlencode(json_encode($chart_config));
    }
}
