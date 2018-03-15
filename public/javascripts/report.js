const setupReport = function () {

    "use strict";

    let statsChart;

    const loadReport = function (report) {

        const hype = byId("chat-hype");
        removeAllChildren(hype);
        hype.appendChild(asTxt(report.hype));

        const labels = [];
        const data = [];

        report.hist.forEach(function (hist) {

            const ref_hour = new Date(hist.ref_hour);

            labels.push(localeDateTime(ref_hour));
            data.push(hist.hype);
        });

        statsChart.data.labels = labels;
        statsChart.data.datasets[0].data = data;

        statsChart.update();
    };

    const setupChart = function () {

        const ctx = byId("hype-chart").getContext("2d");

        statsChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: [],
                datasets: [{
                    label: "Msg/Sec",
                    backgroundColor: "blue",
                    borderColor: "blue",
                    fill: false,
                    data: []
                }]
            },
            options: {
                // responsive: false,
                title: {
                    display: false
                },
                legend: {
                    display: false
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
    };

    const setupModal = function () {

        const link = byId("chat-stats-nav-link");

        let reportPath;

        link.addEventListener("click", function (evt) {

            evt.preventDefault();

            const chatId = link.dataset.chatId;

            reportPath = `${currentUrl()}/messages/report?chat_id=${chatId}`;

            if (!chatId) {
                alert("Please, select a broadcast with a live chat");
                return;
            }

            fetch(reportPath)
                .then(res => res.json())
                .then(loadReport);

            $("#chat-stats-modal").modal("show");
        });

        byId("chat-stats-json").addEventListener("click", function () {
            if (reportPath) {
                window.location.href = reportPath;
            }
        });
    };

    setupModal();
    setupChart();
};
