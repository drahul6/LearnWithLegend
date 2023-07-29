<?php

use v2\Models\Wallet\ChartOfAccount;
use v2\Models\Wallet\BasicAccountType;
use v2\Models\Wallet\CompanyAccountType;
?>

<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">

<style>
	table tbody tr:nth-child(even) {
		background: lightgray !important;
	}

	table tbody tr td,
	table thead tr td {
		padding: 5px;

	}

	table tbody tr,
	table thead tr {
		line-height: 15px;
	}


	table thead td {
		background-color: grey;
		text-align: center;
	}
</style>


<div class="container">
	<div class="row">

		<div class="col-md-12">
			<h5 class="text-center"><?= $this->admin()->company->name; ?></h5>
			<h4 class="text-center">Trial Balance</h4>
			<h5 class="text-center">As at <?= date("M j, Y", strtotime($as_of_date)); ?>

			</h5>

			<div class="col-md-12">

				<hr>

			</div>
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="table-responsive">
							<table autosize="1" class="" width="100%">
								<thead>
									<tr>
										<td class="text-left">
											<strong>Code</strong>
										</td>
										<td><strong>Account</strong></td>
										<td class="text-right"><strong>Debits</strong></td>
										<td class="text-right"><strong>Credits</strong></td>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($sorted_into_subcategories as $basic_account_id => $subcategories) :
										$basic_account = BasicAccountType::find($basic_account_id);
									?>

										<tr>
											<td class="text-left">
												<b><?= $basic_account->code; ?></b>
											</td>
											<td>
												<strong>
													<?= $basic_account['name']; ?>
												</strong>
											</td>
											<td class="text-right"> </td>
											<td class="text-right"> </td>
										</tr>

										<?php foreach ($subcategories as $subcategory_id => $details) :
											$sub_category = CompanyAccountType::find($subcategory_id);

										?>
											<tr>
												<td class="text-">
													<!-- <b><?= $basic_account->code; ?></b> -->
												</td>
												<td>
													&nbsp;
													<strong style="margin-left: 20px;">
														<?= $sub_category['name']; ?>
													</strong>
												</td>
												<td class="text-right"> </td>
												<td class="text-right"> </td>
											</tr>

											<?php foreach ($details as $account_id => $account) :

											?>
												<tr color="#fff0f5">
													<td class="text- text-info">
														<?= $account['account_code']; ?>
													</td>
													<td class="text-info">
														&nbsp;
														&nbsp;
														<strong class="text-info" style="margin-left: 40px;">
															<?= $account['account_name']; ?>
														</strong>
													</td>
													<td class="text-right">
														<?php
														$debits[] = $account['raw_debit_balance'] ?? 0;
														echo $account['debit_balance'] ?? 0; ?></td>
													<td class="text-right">
														<?php

														$credits[] = $account['raw_credit_balance'] ?? 0;

														echo $account['credit_balance'] ?? 0; ?></td>
												</tr>
											<?php endforeach; ?>
										<?php endforeach; ?>
									<?php endforeach; ?>



									<tr>
										<td class="emptyrow text-center">
										</td>
										<td class="emptyrow  text-center"><strong>Total</strong></td>
										<td class="emptyrow text-right">
											<?= $currency; ?><?= ChartOfAccount::account_format(array_sum($debits)); ?></td>
										<td class="emptyrow text-right">
											<?= $currency; ?><?= ChartOfAccount::account_format(array_sum($credits)); ?></td>
									</tr>


								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<style>
			

				.table>tbody>tr>.emptyrow {
					border-top: none;
				}

				.table>thead>tr>.emptyrow {
					border-bottom: none;
				}

				.table>tbody>tr>.highrow {
					border-top: 3px solid;
				}
			</style>






		</div>
	</div>

</div>



<?php include 'inc/footers.php'; ?>