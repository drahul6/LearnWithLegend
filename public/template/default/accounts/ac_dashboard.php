<?php include 'inc/headers.php'; ?>



<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<div class="">

    <div>
        <h4 class="mg-b-0 tx-spacing--1">Dashboard</h4>
    </div>

    <center>
        <h3><?= $this->company->name ?></h3>
    </center>
    <div class="row">
        <div class="col-sm-12">

            <div class="col-sm-3">
                <div class="super-shadow dashboard-stats">
                    <header class="text-center">
                        <strong>
                            %Consumption
                        </strong>
                    </header>
                    <article class="text-center">
                        <!-- <strong>0</strong>&nbsp; | &nbsp; -->
                        <strong><?=MIS::money_format($consumption ?? 0); ?>%
                        </strong>
                    </article>
                </div>
            </div>

            <?php foreach ($dashboard_settings as  $showable) : ?>
                <div class="col-sm-3">
                    <div class="super-shadow dashboard-stats">
                        <header class="text-center">
                            <strong><?= $showable->Label; ?></strong>
                        </header>
                        <article class="text-center">
                            <!-- <strong>0</strong>&nbsp; | &nbsp; -->
                            <strong><?= $currency; ?><?=MIS::money_format($showable->Balance ?? 0); ?>
                            </strong>
                        </article>
                    </div>
                </div>
            <?php endforeach; ?>


        </div>
        <div class="" style="padding: 50px; margin-top: 70px;">
            <canvas id="myChart"></canvas>
            <center>
                <i><strong>Charts For Expenses and Revenues</strong></i>

            </center>
        </div>
    </div>


</div>

<script>
    let $graph_data = '';

    $.ajax({
        type: "POST",
        url: "<?= domain; ?>" + "/accounts/fetch_dashboard_graph_data_revenues_and_expenses/",
        data: null,
        cache: false,
        success: function(data) {
            console.log(data);
            $graph_data = data;

            window.notify();
            console.log($graph_data['labels']);
            console.log($graph_data['data']);
        },
        error: function(data) {},
        async: false
    });


    var ctx = document.getElementById('myChart').getContext('2d');

    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: $graph_data['labels'],
            datasets: [{
                label: 'Balance',
                data: $graph_data['data'],
                backgroundColor: [
                    'tomato',
                    '#2a8e2a',
                ],
                borderColor: [
                    '',
                    '',
                ],
                borderWidth: 1
            }]
        },



        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
</script>


<?php include 'inc/footers.php'; ?>