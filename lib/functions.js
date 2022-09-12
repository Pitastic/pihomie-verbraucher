function drawLineChart(ctx, koordinaten, koordinaten2, vName, labels) {
    // Settings und Input
    var highlightFont = "blue";
    var LineChartData = {
        labels: labels,
        datasets: [
            {
                label: "Zählerstand",
                data: koordinaten,
                yAxisID: "y-axis-0",
            },{
                label: "Verbrauch",
                data: koordinaten2,
                yAxisID: "y-axis-1",
                borderColor: highlightFont,
                borderWidth: 2,
            }
        ]
    }

    // Init
    var myChart = new Chart(ctx, {
        type: "line",
        data: LineChartData,
        options: {
            elements : {
                line : {
                    fill : false,
                },
            },
            legend : {display: false},
            scales: {
                yAxes: [
                    {
                        id: "y-axis-0",
                        gridLines: {display:false},
                    },{
                        id: "y-axis-1",
                        position: "right",
                        gridLines: {display:true},
                        ticks: {
                            beginAtZero: true,
                            fontColor: highlightFont,
                            min: 1,
                            }
                    },
                ],
                xAxes: [{
                    gridLines: {display:false},
                }],
            },
            layout : {padding : 20,},
        },
    });
    return;
}


function toggleModal(id, close) {
    if (close) {
        document.getElementById(id).style.display = 'none';
    }else{
        document.getElementById(id).style.display = 'block';
    }
    return;
}