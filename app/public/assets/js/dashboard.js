(function () {
    const dataElement = document.getElementById("dashboard-data");
    if (!dataElement) return;

    if (!window.Chart) {
        throw new Error("Chart.js is required to render the admin dashboard charts.");
    }

    const report = JSON.parse(dataElement.textContent || "{}");
    const colors = ["#a855f7", "#ec4899", "#06b6d4", "#eab308", "#6366f1"];
    const money = new Intl.NumberFormat("vi-VN", { maximumFractionDigits: 0 });
    const units = new Intl.NumberFormat("vi-VN");
    const charts = [];

    function themeColor(name, fallback) {
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;
    }

    function gradient(context, color, opacity) {
        const chart = context.chart;
        const area = chart.chartArea;
        if (!area) return "rgba(" + color + ", " + opacity + ")";
        const fill = chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
        fill.addColorStop(0, "rgba(" + color + ", " + opacity + ")");
        fill.addColorStop(1, "rgba(" + color + ", 0)");
        return fill;
    }

    const tooltipConfig = {
        backgroundColor: "rgba(11, 5, 21, .94)",
        titleColor: "#fff",
        bodyColor: "#e2e8f0",
        borderColor: "rgba(255, 255, 255, .12)",
        borderWidth: 1,
        padding: 10,
        cornerRadius: 8,
        bodyFont: { family: "Inter, sans-serif", size: 12 },
        boxPadding: 5,
        usePointStyle: true
    };

    Chart.defaults.color = themeColor("--app-muted", "#94a3b8");
    Chart.defaults.font.family = "Inter, sans-serif";
    Chart.defaults.font.size = 12;

    const glowPlugin = {
        id: "liquidGlow",
        beforeDatasetDraw: function (chart, args) {
            chart.ctx.save();
            chart.ctx.shadowColor = colors[args.index % colors.length];
            chart.ctx.shadowBlur = 10;
            chart.ctx.shadowOffsetY = 3;
        },
        afterDatasetDraw: function (chart) {
            chart.ctx.restore();
        }
    };

    function axisStyle() {
        return {
            x: {
                grid: { color: "rgba(255, 255, 255, .04)" },
                border: { display: false },
                ticks: {
                    color: themeColor("--app-subtle", "#64748b"),
                    font: { family: "Inter, sans-serif", size: 12 },
                    maxRotation: 0,
                    autoSkip: true,
                    maxTicksLimit: 12
                }
            },
            y: {
                beginAtZero: true,
                grid: { color: "rgba(255, 255, 255, .05)" },
                border: { display: false },
                ticks: {
                    color: themeColor("--app-subtle", "#64748b"),
                    font: { family: "Inter, sans-serif", size: 12 },
                    callback: function (value) { return units.format(value) + "M"; }
                }
            }
        };
    }

    function renderRevenue() {
        const canvas = document.querySelector("#revenue-chart canvas");
        if (!canvas) return;

        const values = Object.values(report.buckets || {});
        const scales = axisStyle();
        scales.y1 = {
            type: "linear",
            display: false,
            position: "right",
            beginAtZero: true,
            grid: { drawOnChartArea: false },
            border: { display: false }
        };

        charts.push(new Chart(canvas, {
            type: "line",
            data: {
                labels: report.labels || [],
                datasets: [
                    {
                        label: "Doanh thu",
                        data: values.map(function (item) { return Number(item.revenue) / 1000000; }),
                        borderColor: "#a855f7",
                        backgroundColor: function (context) { return gradient(context, "168, 85, 247", .4); },
                        borderWidth: 2,
                        tension: .4,
                        fill: true,
                        pointBackgroundColor: themeColor("--app-bg", "#0b0515"),
                        pointBorderColor: "#a855f7",
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        yAxisID: "y"
                    },
                    {
                        label: "Số đơn",
                        data: values.map(function (item) { return Number(item.orders); }),
                        borderColor: "#22d3ee",
                        backgroundColor: function (context) { return gradient(context, "34, 211, 238", .12); },
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: .4,
                        fill: false,
                        pointBackgroundColor: themeColor("--app-bg", "#0b0515"),
                        pointBorderColor: "#22d3ee",
                        pointBorderWidth: 2,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        yAxisID: "y1"
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: "index", intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: Object.assign({}, tooltipConfig, {
                        callbacks: {
                            label: function (context) {
                                const value = Number(context.raw);
                                return context.datasetIndex === 0
                                    ? " Doanh thu: " + money.format(value * 1000000) + " đ"
                                    : " Số đơn: " + units.format(value);
                            }
                        }
                    })
                },
                scales: scales,
                animation: { duration: 750, easing: "easeOutQuart" }
            },
            plugins: [glowPlugin]
        }));
    }

    function renderBrand() {
        const container = document.getElementById("brand-donut");
        const canvas = container && container.querySelector("canvas");
        const center = container && container.querySelector(".donut-center");
        const centerValue = center && center.querySelector("strong");
        const centerCaption = center && center.querySelector("span");
        const legend = document.getElementById("brand-legend");
        if (!canvas || !centerValue || !centerCaption || !legend) return;

        const brands = report.brandRevenue || [];
        const total = brands.reduce(function (sum, brand) {
            return sum + Number(brand.revenue);
        }, 0);
        const visibleBrands = brands.slice(0, 4).map(function (brand) {
            return { name: brand.name, revenue: Number(brand.revenue) };
        });
        const remainingRevenue = brands.slice(4).reduce(function (sum, brand) {
            return sum + Number(brand.revenue);
        }, 0);
        if (remainingRevenue > 0) {
            visibleBrands.push({ name: "Khác", revenue: remainingRevenue });
        }

        legend.replaceChildren();
        if (!visibleBrands.length || total <= 0) {
            centerValue.textContent = "Chưa có";
            centerCaption.textContent = "doanh thu";
            return;
        }

        centerValue.textContent = money.format(total) + " đ";
        centerCaption.textContent = "Tổng doanh thu";
        visibleBrands.forEach(function (brand, index) {
            const item = document.createElement("li");
            const dot = document.createElement("i");
            dot.className = "legend-color";
            dot.style.backgroundColor = colors[index % colors.length];
            const name = document.createElement("span");
            name.textContent = brand.name;
            const share = document.createElement("strong");
            share.textContent = ((brand.revenue / total) * 100).toLocaleString("vi-VN", {
                maximumFractionDigits: 1
            }) + "%";
            item.append(dot, name, share);
            legend.appendChild(item);
        });

        charts.push(new Chart(canvas, {
            type: "doughnut",
            data: {
                labels: visibleBrands.map(function (brand) { return brand.name; }),
                datasets: [{
                    data: visibleBrands.map(function (brand) { return brand.revenue; }),
                    backgroundColor: colors.slice(0, visibleBrands.length),
                    borderWidth: 2,
                    borderColor: themeColor("--app-bg", "#0b0515"),
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "72%",
                plugins: {
                    legend: { display: false },
                    tooltip: Object.assign({}, tooltipConfig, {
                        callbacks: {
                            label: function (context) {
                                const value = Number(context.raw);
                                const share = total > 0 ? (value / total) * 100 : 0;
                                return " " + context.label + ": " + money.format(value) + " đ ("
                                    + share.toLocaleString("vi-VN", { maximumFractionDigits: 1 }) + "%)";
                            }
                        }
                    })
                },
                animation: { animateScale: true, animateRotate: true, duration: 750 }
            }
        }));
    }

    function renderCharts() {
        charts.splice(0).forEach(function (chart) { chart.destroy(); });
        Chart.defaults.color = themeColor("--app-muted", "#94a3b8");
        renderRevenue();
        renderBrand();
    }

    renderCharts();
    document.addEventListener("themechange", renderCharts);
})();
