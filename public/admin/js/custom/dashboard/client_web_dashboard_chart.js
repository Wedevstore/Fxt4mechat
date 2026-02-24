const charts = JSON.parse(document.getElementById('chart_data').value);

document.addEventListener("DOMContentLoaded", function() {
    var statisticsItem = document.getElementById("audience_growth");

    if (statisticsItem) {
        var data = {
            labels: charts.labels,
            datasets: [{
                label: 'Total Contacts',
                backgroundColor: '#3F52E3',
                data: charts.total_contacts
            }, {
                label: 'New Contacts',
                backgroundColor: '#25AB7C',
                data: charts.new_contacts
            }]
        };

        var options = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };
        var earningChartBar = new Chart(statisticsItem, {
            type: 'line',
            data: data,
            options: options
        });
    }
});

document.addEventListener("DOMContentLoaded", function() {
    var statisticsItem = document.getElementById("campaign_statistic");

    if (statisticsItem) {
        var currentDate = new Date();
        var currentYear = currentDate.getFullYear();
        var currentMonth = currentDate.getMonth() + 1;

        var startMonth = (currentMonth + 1) % 12 || 12;
        var startYear = currentYear - 1;

        var labels = [];
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        for (var i = 0; i < 12; i++) {
            var monthIndex = (startMonth + i - 1) % 12;
            var year = startYear + Math.floor((startMonth + i - 1) / 12);
            labels.push(months[monthIndex] + ' ' + year);
        }

        var whatsAppCampaignData = [];
        for (var j = 0; j < 12; j++) {
            var monthIndex = (startMonth + j - 1) % 12;
            whatsAppCampaignData.push(charts.whatsapp_campaign[monthIndex]);
        }

        var data = {
            labels: labels,
            datasets: [{
                label: 'WhatsApp Web',
                backgroundColor: '#25d366',
                data: whatsAppCampaignData
            }]
        };

        var options = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };
        var earningChartBar = new Chart(statisticsItem, {
            type: 'bar',
            data: data,
            options: options
        });
    }
});


document.addEventListener("DOMContentLoaded", function() {
    var statisticsItem = document.getElementById("conversation_statistic");

    if (statisticsItem) {
        var currentDate = new Date();
        var currentYear = currentDate.getFullYear();
        var currentMonth = currentDate.getMonth() + 1;

        var startMonth = (currentMonth + 1) % 12 || 12;
        var startYear = currentYear - 1;

        var labels = [];
        var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        for (var i = 0; i < 12; i++) {
            var monthIndex = (startMonth + i - 1) % 12;
            var year = startYear + Math.floor((startMonth + i - 1) / 12);
            labels.push(months[monthIndex] + ' ' + year);
        }

        var whatsAppConversationData = [];
        for (var j = 0; j < 12; j++) {
            var monthIndex = (startMonth + j - 1) % 12;
            whatsAppConversationData.push(charts.whatsapp_conversation[monthIndex]);
        }

        var data = {
            labels: labels,
            datasets: [{
                label: 'WhatsApp Web',
                backgroundColor: '#25d366',
                data: whatsAppConversationData
            }]
        };

        var options = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };
        var earningChartBar = new Chart(statisticsItem, {
            type: 'bar',
            data: data,
            options: options
        });
    }
});
