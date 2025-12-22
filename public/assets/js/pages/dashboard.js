document.addEventListener('DOMContentLoaded', function () {
    var chartElement = document.querySelector("#pieChart");
    
    if (chartElement && typeof pieData !== 'undefined') {
        var labels = Object.keys(pieData);
        var series = Object.values(pieData).map(Number);

        var options = {
            chart: {
                height: 320,
                type: 'pie',
            },
            series: series,
            labels: labels,
            dataLabels: {
                formatter: function (val, opts) {
                    return opts.w.config.labels[opts.seriesIndex] + ": " + opts.w.config.series[opts.seriesIndex]
                }
            },
            colors: ["#1989df", "#7f56da", "#f95c5c", "#f9b931", "#1bb394"],
            legend: {
                show: true,
                position: 'left',
                horizontalAlign: 'center',
                verticalAlign: 'middle',
                floating: false,
                fontSize: '18px',
                offsetX: 0,
                offsetY: 7
            },
            responsive: [{
                breakpoint: 600,
                options: {
                    chart: {
                        height: 240
                    },
                    legend: {
                        show: false
                    }
                }
            }]
        };

        var chart = new ApexCharts(chartElement, options);
        chart.render();
    }

    var columnChartElement = document.querySelector("#columnChart1");
    
    if (columnChartElement && typeof columnChartData !== 'undefined') {
        var options = {
            chart: {
                height: 350,
                type: 'bar',
                toolbar: {
                    show: false,
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '45%',
                    endingShape: 'rounded',
                    dataLabels: {
                        position: 'top', // top, center, bottom
                    },
                },
            },
            dataLabels: {
                enabled: true,
                offsetY: -20,
                style: {
                    fontSize: '12px',
                    colors: ["#304758"]
                }
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            series: [{
                name: 'Total Problems',
                data: columnChartData.total
            }, {
                name: 'Closed Problems',
                data: columnChartData.closed
            }],
            colors: ["#f9b931", "#1bb394"],
            xaxis: {
                categories: columnChartData.labels,
            },
            yaxis: {
                title: {
                    text: 'Problems'
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " problems"
                    }
                }
            }
        };

        var chart = new ApexCharts(columnChartElement, options);
        chart.render();
    }
});
