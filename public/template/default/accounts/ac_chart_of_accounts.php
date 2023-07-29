<?php

use v2\Models\Wallet\ChartOfAccount;

include 'inc/headers.php'; ?>

<script src="<?= $this_folder; ?>/assets/angularjs/chart_of_accounts.js"></script>

<div ng-controller="ChartOfAccountController" ng-cloak>
    <div class="row">

        <div class="col-xs-8">
            <!-- <h3>Chart of Accounts</h3> -->
        </div>
        <div class="col-xs-4">
            <div class="btn-group">
                <button type="button" class="btn btn-white" data-toggle="modal" data-target="#create_chart_of_account">
                    + New Account
                </button>
            </div>
        </div>
    </div>

    <br>
    <div class="table-responsive">
        <table id="charts_of_accounts_table" class="table table-hover">
            <thead>
                <tr>
                    <th></th>
                    <th>ACCOUNT NAME</th>
                    <th>USER</th>
                    <th>CODE - CUR</th>
                    <th>CATEGORY</th>
                    <th>TYPE</th>
                    <th>*</th>
                </tr>
            </thead>
            <tbody>

                <tr ng-repeat="($index, $chart) in $charts_of_accounts | orderBy:'created_at':true">
                    <td>{{$index+1}}</td>
                    <td>{{$chart.account_name}} #{{$chart.id}} <br> {{$chart.account_number}} </td>
                    <td>{{$chart.owner.username}}</td>
                    <td>{{$chart.account_code}} - {{$chart.currency}}</td>
                    <td>{{$chart.custom_account_type.name}} #{{$chart.custom_account_type.id}}</td>
                    <td>{{$chart.custom_account_type.basic_account.name}}</td>
                    <td>
                        <a href="<?= domain; ?>/accounts/settings/{{$chart.id}}"><i class="fa fa-cog"></i></a>
                        <a href="<?= domain; ?>/accounts/chart_of_account_transactions/{{$chart.id}}">
                            Show Transactions
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>



    <!-- Modal -->
    <div id="create_chart_of_account" class="modal fade " role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Create Account</h4>
                </div>
                <div class="modal-body">
                    <form id="create_chart_of_account_form" method="POST" action="<?= domain; ?>/accounts/create_chart_of_accounts">
                        <div class="form-group">
                            <label>Account Type *</label>


                            <select name="account_type" class="form-control">
                                <?php foreach ($options as $base_category => $sub_categories) : ?>
                                    <optgroup label="<?= $base_category; ?>">
                                        <?php foreach ($sub_categories as $sub_category) : ?>

                                            <option value="<?= $sub_category['id']; ?>">
                                                <?= $sub_category['name']; ?>
                                            </option>

                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Account Name*</label>
                            <input type="" name="account_name" required="" class="form-control">
                        </div>


                        <datalist id="usernames">
                        </datalist>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="" name="username" list="usernames" class="form-control" onkeyup="populate_option(this.value)">
                        </div>



                        <div class="form-group">
                            <label>Opening Balance*</label>
                            <input type="number" step="0.01" name="opening_balance" class="form-control">
                        </div>


                        <div class="form-group">
                            <label>Currency *</label>
                            <input type="" name="currency" value="<?= ChartOfAccount::$base_currency; ?>" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Description *</label>
                            <textarea class="form-control" rows="3" name="description"></textarea>
                        </div>

                        <!-- 
                        <div class="form-group">
                            <label>Add to the watchlist on my Dashboard</label>
                            <input type="checkbox" name="add_to_watch_list" class="">
                        </div> -->


                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger">Save</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            populate_option = function($query) {

                if ($query.length < 3) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: "<?= domain; ?>/accounts/search/" + $query,
                    data: null,
                    success: function(data) {
                        $('#usernames').html(data.line);
                    },
                    error: function(data) {},
                    complete: function() {}
                });

            }
            $("body").on("submit", "#create_chart_of_account_form", function(e) {
                e.preventDefault();


                dataString = $("#create_chart_of_account_form").serialize();

                $.ajax({
                    type: "POST",
                    url: $base_url + "/accounts/create_chart_of_accounts/",
                    data: dataString,
                    cache: false,
                    success: function(data) {
                        console.log(data);

                        if (typeof(data) == 'object') {
                            $scope = angular.element($('#charts_of_accounts_table')).scope();
                            $scope.$charts_of_accounts.push(data);
                            $scope.$apply();
                        }
                        window.notify();

                    },
                    error: function(data) {}
                });
            });
        </script>

    </div>




    <?php include 'inc/footers.php'; ?>






</div>
<?php include 'inc/footers.php'; ?>