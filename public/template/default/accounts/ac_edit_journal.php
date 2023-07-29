<?php include 'inc/headers.php';

use v2\Models\Wallet\ChartOfAccount;
?>
<script>
    $journal_id = <?= $journal->id; ?>
</script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script src="<?= $this_folder; ?>/assets/angularjs/journal.js"></script>

<div class="" ng-controller="JournalController">



    <div class="row">

        <div class="col-xs-8">
            <h3>Journals #{{$journal.$data.id}}<?= $journal->publishedState; ?></h3>
        </div>
    </div>
    <hr>
    <form class="col-md-8">

        <div class="form-group ">
            <label>Date*</label>
            <input type="date" class="form-control" ng-model="$journal.$data.journal_date">
        </div>

        <!-- 
        <div class="form-group ">
            <label>Currency*</label>
            <input type="text" class="form-control" ng-model="$journal.$data.currency">
        </div>
 -->

        <div class="form-group ">
            <label>Notes*</label>
            <textarea class="form-control" ng-model="$journal.$data.notes"></textarea>
        </div>

    </form>
    <div class="col-md-4">
        <ul>Attached File(s) <span class="badge"><?= count($journal->attachments); ?></span>
            <?php foreach ($journal->attachments as $file) :
                $filename = end(explode('/', $file));
            ?>
                <li><a target="_blank" href="<?= domain; ?>/<?= $file; ?>"><?= $filename; ?></a></li>
            <?php endforeach; ?>


        </ul>
    </div>

    <div class="col-md-12" id="journal_table">
        <table class="table table-hover ">
            <thead>
                <th>Account</th>
                <th>Description</th>
                <th>Debits</th>
                <th>Credits</th>
                <th>*</th>
            </thead>


            <tr ng-repeat="($index , $involved_account) in $journal.$involved_accounts.$lines">

                <td>
                    <div ng-hide="true">
                        <ul class="list-group list-group-flush tx-13">
                            <li class="list-group-item d-flex pd-sm-x-20">
                                <div class="pd-sm-l-10">
                                    <p class="tx-medium mg-b-2"><i class="fa fa-user"></i> James Michael #00910</p>
                                    <small class="tx-12 tx-color-03 mg-b-0">Acct. No.: 2345678903</small>
                                </div>
                                <div class="mg-l-auto text-right">
                                    <small class="tx-12 tx-danger mg-b-0">remove</small>
                                    <p class="tx-12 tx-color-03 mg-b-2">Avail. Bal.: $22342316.50</p>
                                    <!-- <p class="tx-medium mg-b-2">Avail. Bal.: $22342316.50</p> -->
                                </div>
                            </li>
                        </ul>
                    </div>


                    <input list="chart_of_accounts_available" ng-model="$involved_account.chart_of_account_number" class="form-control" ng-keyup="$journal.$involved_accounts.populate_options($involved_account.chart_of_account_number);">
                    <datalist id="chart_of_accounts_available">
                    </datalist>


                    <!-- <input type="" name="" style="background: red;"> -->
                    <select ng-hide="true" ng-model="$involved_account.chart_of_account_id" data_line_index="{{$index}}" class="form-control js-example-basic-single">
                        <option ng-repeat="($index, $option) in $charts_of_accounts" value="{{$option.id}}" ng-selected="$option.id==$involved_account.chart_of_account_id">
                            {{$option.text}}
                        </option>



                        <!--  <optgroup ng-repeat="(key, $optgroup) in $charts_of_account_options" label="{{key}}">

                            <option ng-repeat="($index, $option) in $optgroup" value="{{$option.id}}" ng-selected="$option.id==$involved_account.chart_of_account_id">
                                {{$option.id}}
                                {{$option.account_name}}
                                {{$option.owner.name}}
                                -
                                {{$option.currency}}
                            </option>


                        </optgroup> -->
                    </select>
                </td>



                <td>
                    <textarea rows="3" ng-model="$involved_account.description" class="form-control">{{$involved_account.description}}</textarea>
                </td>

                <td>
                    <input type="" ng-blur="$journal.$involved_accounts.sumCreditAndDebit();" ng-model="$involved_account.debit" class="form-control">
                </td>

                <td><input type="" ng-blur="$journal.$involved_accounts.sumCreditAndDebit();" ng-model="$involved_account.credit" class="form-control"></td>



                <td>
                    <a ng-click="$journal.$involved_accounts.remove_line($involved_account);" href="javascript:void;" class="fa fa-times text-danger"></a>
                </td>
            </tr>

            <tr>
                <td colspan="2">
                    <button ng-click="$journal.$involved_accounts.add_line();" class="btn btn-white text-danger">+ Add Another Line</button>
                </td>

                <td colspan="5">
                    <table class="table table-hover">
                        <tr>
                            <td>Sub Total</td>
                            <td>{{$journal.$involved_accounts.$subtotal_debit}}</td>
                            <td>{{$journal.$involved_accounts.$subtotal_credit}}</td>
                        </tr>

                        <tr>
                            <td>Total({{$journal.$data.currency}})</td>
                            <td>{{$journal.$involved_accounts.$total_debit}}</td>
                            <td>{{$journal.$involved_accounts.$total_credit}}</td>
                        </tr>

                    </table>

                </td>

            </tr>


        </table>

        <!--  <div>
            <input onchange="angular.element(this).scope().$journal.add_files(this)" type="file" multiple="" name="" id="journal_attached_files" style="display: none;">

            Attach File(s)
            <button onclick="$('#journal_attached_files').click()">Upload File</button>
            <span>{{$journal.$attached_files.length}} file(s) attached</span>
            <br>
            <small>You can upload a Maximum of 5 files, 5MB each</small>
        </div> -->

        <hr>
        <div class="row">

            <button ng-click="$journal.attempt_save(3)" class="btn btn-success btn-sm">Complete</button>
            <!-- <button ng-click="$journal.save(2)" class="btn btn-warning btn-sm">Pending</button> -->
            <button ng-click="$journal.save(1)" class="btn btn-white btn-sm">Save as Draft</button>
        </div>
    </div>

</div>
<script type="text/javascript">

</script>
<?php include 'inc/footers.php'; ?>