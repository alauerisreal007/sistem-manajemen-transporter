@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Performance Trends Chart
        const trendData = @json($trendData);

        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [{
                        label: 'Average Load Time (min)',
                        data: trendData.map(d => d.avg_load),
                        borderColor: 'rgb(234, 179, 8)',
                        backgroundColor: 'rgba(234, 179, 8, 0.1)',
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Average Duration (min)',
                        data: trendData.map(d => d.avg_duration),
                        borderColor: 'rgb(168, 85, 247)',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        tension: 0.3,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'Completed',
                        data: trendData.map(d => d.count_completed),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.3,
                        yAxisID: 'y2'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y;
                                    if (context.dataset.label.includes('Time') || context.dataset.label
                                        .includes('Duration')) {
                                        label += ' min';
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Load Time (min)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Duration (min)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                    y2: {
                        type: 'linear',
                        display: false,
                        position: 'right',
                    }
                }
            }
        });

        // ===========================================
        // DATE FILTER LOGIC
        // ===========================================

        // Set tanggal dari shortcut button
        function setDate(preset) {
            const today = new Date();
            let dateObj;

            switch (preset) {
                case 'today':
                    dateObj = today;
                    break;
                case 'yesterday':
                    dateObj = new Date(today);
                    dateObj.setDate(dateObj.getDate() - 1);
                    break;
                case '7days':
                    dateObj = new Date(today);
                    dateObj.setDate(dateObj.getDate() - 7);
                    break;
                case '30days':
                    dateObj = new Date(today);
                    dateObj.setDate(dateObj.getDate() - 30);
                    break;
                default:
                    dateObj = today;
            }

            const formatted = formatDate(dateObj);

            document.getElementById('datePicker').value = formatted;
            applyDateFilter(formatted);
        }

        // Format date ke YYYY-MM-DD
        function formatDate(date) {
            if (!(date instanceof Date)) {
                console.error("formatDate bukan Date:", date);
                return '';
            }
            return date.toISOString().split('T')[0];
        }

        function applyDateFilter(dateValue) {
            showLoading(true);

            fetch(`{{ route('admin.dashboard') }}?date=${dateValue}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Network error: ' + res.status);
                    return res.json();
                })
                .then(data => {
                    updateCards(data);
                    updateChart(data.trend_data);
                    updateDateUI(dateValue, data);
                    showLoading(false);
                })
                .catch(err => {
                    console.error('Filter error:', err);
                    showLoading(false);
                    alert('Gagal memuat data. Silakan coba lagi.');
                })
        }

        // Update semua card metrics
        function updateCards(data) {
            // Completed
            document.getElementById('completedCount').textContent = data.completed_deliveries;
            document.getElementById('completedLabel').textContent = data.date_label;
            document.getElementById('targetBar').style.width = data.target_percentage + '%';
            document.getElementById('targetPct').textContent = data.target_percentage + '% dari target';

            // Load Time
            document.getElementById('avgLoadTime').textContent = Math.round(data.avg_load_time);
            document.getElementById('avgPickup').textContent = Math.round(data.avg_pickup_time);
            document.getElementById('avgDelivery').textContent = Math.round(data.avg_delivery_time);

            // Load Trend
            const trend = data.load_time_trend;
            let trendHtml = '';
            if (trend < 0) {
                trendHtml = `<span class="text-green-600">📉 ${Math.abs(trend)} min lebih cepat</span>`;
            } else if (trend > 0) {
                trendHtml = `<span class="text-red-600">📈 ${trend} min lebih lambat</span>`;
            } else {
                trendHtml = `<span class="text-gray-500">➡ Sama dengan minggu lalu</span>`;
            }
            document.getElementById('loadTrend').innerHTML = trendHtml;

            // Duration
            document.getElementById('avgDurationH').textContent = data.avg_duration_h;
            document.getElementById('avgDurationM').textContent = data.avg_duration_m;
            document.getElementById('bestH').textContent = data.best_duration_h;
            document.getElementById('bestM').textContent = data.best_duration_m;
            document.getElementById('worstH').textContent = data.worst_duration_h;
            document.getElementById('worstM').textContent = data.worst_duration_m;

            // On-Time Rate
            document.getElementById('onTimeRate').textContent = data.on_time_rate;
            const rate = data.on_time_rate;
            document.getElementById('onTimeBadge').innerHTML = rate >= 90 ?
                `<span class="text-green-600">✅ Di atas Target (90%)</span>` :
                `<span class="text-orange-600">⚠ Di bawah Target</span>`;
        }

        function updateChart(trends) {
            performanceChart.data.labels = trends.map(d => d.date);
            performanceChart.data.datasets[0].data = trends.map(d => d.avg_load);
            performanceChart.data.datasets[1].data = trends.map(d => d.avg_duration);
            performanceChart.data.datasets[2].data = trends.map(d => d.count_completed);
            performanceChart.update();
        }

        function updateDateUI(dateValue, data) {
            const isToday = data.is_today;
            const label = data.date_label;

            // Badge
            const badge = document.getElementById('dateBadge');
            badge.textContent = '📅 ' + label;
            badge.className = `px-3 py-1.5 rounded-lg text-xs font-semibold ${
                isToday ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700'
            }`;

            // Chart label
            document.getElementById('chartDateLabel').textContent = label;

            // Top Drivers Label
            const tdLabel = document.getElementById('topDriversLabel');
            if (tdLabel) {
                tdLabel.textContent = label;
                tdLabel.className = `text-xs px-2 py-1 rounded ${
                    isToday ? 'bg-blue-100 text-blue-600' : 'bg-orange-100 text-orange-600'
                }`;
            }

            // Shortcut buttons
            const todayDate = formatDate(new Date());
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            const week = new Date();
            week.setDate(week.getDate() - 7);
            const month = new Date();
            month.setDate(month.getDate() - 30);

            const activeClass = 'bg-white shadow text-blue-600';
            const inactiveClass = 'text-gray-600 hover:bg-white hover:shadow';

            document.getElementById('btn-today').className =
                `date-shortcut px-3 py-1.5 rounded-md text-xs font-medium transition ${dateValue === todayDate ? activeClass : inactiveClass}`;
            document.getElementById('btn-yesterday').className =
                `date-shortcut px-3 py-1.5 rounded-md text-xs font-medium transition ${dateValue === formatDate(yesterday) ? activeClass : inactiveClass}`;
            document.getElementById('btn-7days').className =
                `date-shortcut px-3 py-1.5 rounded-md text-xs font-medium transition ${dateValue === formatDate(week) ? activeClass : inactiveClass}`;
            document.getElementById('btn-30days').className =
                `date-shortcut px-3 py-1.5 rounded-md text-xs font-medium transition ${dateValue === formatDate(month) ? activeClass : inactiveClass}`;
        }

        // Loading overlay
        function showLoading(show) {
            document.getElementById('loadingOverlay').classList.toggle('hidden', !show);
        }

        // =======================================================
        // AUTO REFRESH EVERY 30 SECONDS
        // =======================================================
        let autoRefreshInterval = null;

        function startAutoRefresh() {
            autoRefreshInterval = setInterval(() => {
                const currentDate = document.getElementById('datePicker').value;
                const todayDate = formatDate(new Date());

                if (currentDate === todayDate) {
                    fetch('{{ route('admin.dashboardRefresh') }}')
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById('activeCount').textContent = data.active_deliveries;
                            document.getElementById('completedCount').textContent = data.completed_today;
                            document.getElementById('lastUpdate').textContent = data.timestamp + ' WIB';
                        })
                        .catch(err => console.error('Auto-refresh error:', err));
                }
            }, 30000);
        }

        startAutoRefresh();

        // Auto Refresh every 30 seconds
        setInterval(() => {
            fetch('{{ route('admin.dashboardRefresh') }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('activeCount').textContent = data.active_deliveries;
                    document.getElementById('completedCount').textContent = data.completed_today;
                    document.getElementById('lastUpdate').textContent = data.timestamp;
                })
                .catch(error => console.error('Refresh error:', error));
        }, 30000);
    </script>
@endpush
