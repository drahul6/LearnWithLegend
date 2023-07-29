<?php

use v2\Models\Wallet\Journals;
use v2\Models\Wallet\ChartOfAccount;
use v2\Models\Wallet\JournalInvolvedAccounts;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * this class is the default controller of our application,
 * 
 */
class JournalsController extends controller
{


	public function __construct()
	{

		// $this->middleware('current_user')->mustbe_loggedin();

		$this->company = ChartOfAccount::getCompany();
		$this->company_id = $this->company->id;
		// echo "<pre>";

	}


	public function fetch_manual_journals($page = null)
	{

		$journals = Journals::where('company_id', $this->company_id)->get();

		header("Content-type:application/json");
		echo "$journals";
	}


	public function new($source = '')
	{

		$journal =	Journals::create([
			'user_id' => $this->id ?? null,
			'company_id' => $this->company_id,
		]);

		if ($source == 'admin') {
			Redirect::to("admin/edit_journal/{$journal->id}/edit");
		} else {

			Redirect::to("journals/{$journal->id}/edit");
		}
		//set the goal for this journal
	}

	public function update_journal()
	{

		$data = json_decode($_POST['journal'], true);

		// return;

		if (isset($_POST['involved_accounts'])) {
			$involved_accounts = json_decode($_POST['involved_accounts'], true)['$lines'];
		} else {
			$involved_accounts = $data['involved_accounts'];
		}

		$journal = Journals::find($data['id']);

		//stop if journal is final
		if (!$journal->is_editable()) {
			\Session::putFlash("danger", "This journal is not editable");
			return;
		}


		$validator = new Validator;

		$validator->check($data, Journals::$validator_rules);
		// print_r(Journals::$validator_rules);

		//check that involved accounts is not empty
		if (count($involved_accounts)  == 0) {
			$validator->addError('Line items', "You must add line items.");
		}



		//check if credit balances debits
		$involved_accounts_count = 1;
		$credits = [];
		$debits = [];


		//validate involved account
		$account_numbers = collect($involved_accounts)->pluck('chart_of_account_number')->toArray();
		$chart_of_accounts = ChartOfAccount::whereIn('account_number', $account_numbers)->get()->keyBy('account_number')->toArray();


		$involved_accounts = array_map(function ($item) use ($chart_of_accounts) {
			$item['chart_of_account_id'] = $chart_of_accounts[$item['chart_of_account_number']]['id'];
			return $item;
		}, $involved_accounts);


		foreach ($involved_accounts as $key => $value) {

			$chart_of_account = ChartOfAccount::find($value['chart_of_account_id']);


			if ($value['description'] == '') {
				$validator->addError('Chart of Accounts', "<b> {$involved_accounts_count})</b> Must have description.");
			}


			if ($chart_of_account == null) {
				$validator->addError('Chart of Accounts', "<b> {$involved_accounts_count})</b> You must select chart of account.");
			} else {

				if (!$chart_of_account->is_open()) {
					$validator->addError('Closed Account', "<b> {$chart_of_account->account_name}</b> is currently closed.");
				}
			}

			if (($value['credit'] != 0) && ($value['debit'] != 0)) {

				$validator->addError('Invalid Post', "<b> {$involved_accounts_count})</b> You must Post to Either Credit or Debit.");
				break;
			}

			//check for available balance;
			if (method_exists($chart_of_account, "get_balance")) {
				$balance = $chart_of_account->get_balance()['available_balance'];
			} else {
				$balance = -1;
				$validator->addError("No account selected:", "<b> {$involved_accounts_count})</b> Please select an account");
			}



			if (ChartOfAccount::$enforce_balance_suffiency) {
				if ($chart_of_account->is_credit_balance()) {
					if ($value['debit'] > $balance) {
						$validator->addError("Insufficient Balance: $balance", "<b> {$involved_accounts_count})</b> You cannot debit more than balance .");
						break;
					}
				} else {
					if ($value['credit'] > $balance) {
						$validator->addError("Insufficient Balance: $balance", "<b> {$involved_accounts_count})</b> You cannot credit more than balance .");
						break;
					}
				}
			}


			if ($value['credit'] == 0) {
				$debits[] = $value['debit'];
			} else {
				$credits[] = $value['credit'];
			}

			$involved_accounts_count++;
		}

		if (array_sum($credits) != array_sum($debits)) {
			$validator->addError('Double Entry', "Total Credits must be equal to Total Debits.");
		}

		// print_r($involved_accounts_errors)

		if (!$validator->passed()) {
			Session::putFlash('danger', $this->inputErrors());
			return [];
		}


		header("Content-Type:application/json");
		DB::beginTransaction();

		try {
			$journal->update([
				'notes'				  =>	$data['notes'],
				'currency'				  =>	$data['currency'],
				'journal_date'	  	  =>	substr($data['journal_date'], 0, 10)
			]);


			//update involved accounts
			$journal->remove_line_items($data['published_status']);


			foreach ($involved_accounts as  $involved_account) {
				JournalInvolvedAccounts::create_involved_account($involved_account, $journal);
			}



			//upload attachments
			if (isset($_FILES['attachments'])) {
				$attachments = $journal->upload_attachments($_FILES['attachments']);
			}



			$response =	$journal->attemptPublish($data['published_status']);

			if (!$response) {
				throw new \Exception("Error Processing Request attempt publish", 1);
			}



			DB::commit();
			Session::putFlash('success', 'Updated Successfully');
			echo json_encode(['journal_link' => $journal->viewLink]);

			// all good
		} catch (\Exception $e) {
			DB::rollback();
			Session::putFlash('danger', 'Update Failed');
			print_r($e->getMessage());
			// something went wrong
		}
	}


	public function find($journal_id)
	{
		header("Content-type: application/json");

		$charts_of_account_options = ChartOfAccount::charts_of_account_options($this->company_id);
		$journal =  Journals::with(['involved_accounts'])->find($journal_id);


		$journal->createddate = $journal->createddate;


		echo json_encode(compact('journal', 'charts_of_account_options'));
	}




	public function index($journal_id = null, $action = null)
	{

		if ($journal_id !== null) {
			$journal = Journals::where('id', $journal_id)->where('company_id', $this->company_id)->first();

			switch ($action) {
				case 'edit':

					if (!$journal->is_editable()) {
						Session::putFlash('info', "This Journal cannot be edited");
						Redirect::back();
					}

					$charts_of_account_options = ChartOfAccount::charts_of_account_options($this->company_id);
					$this->view('accounts/ac_edit_journal', [
						'journal' => $journal,
						'charts_of_account_options' => $charts_of_account_options,
					]);
					return;
					break;


				case 'view':
					$this->view('accounts/ac_view_journal', ['journal' => $journal]);
					return;
					break;

				case 'decline':

					$journal->decline();

					break;

				default:
					# code...
					break;
			}

			Redirect::back();
		}


		$journals = Journals::where('company_id', $this->company_id)->get();

		$this->view('accounts/manual_journals', compact('journals'));
	}


	public function set_journals_list_filters($value = '')
	{
		$_SESSION['journals_list_filters'] = $_REQUEST;
		Redirect::back();
	}



	public function lists()
	{


		$sieve = $_REQUEST;
		extract(Journals::InvokeQuery($sieve));
		$this->view('accounts/ac_manual_journals', get_defined_vars());


		return;

		$per_page = 20;
		$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$skip = ($page - 1) * $per_page;


		$sql = Journals::CompanyJournals($this->company_id);

		$total_journals = $sql->count();

		$journals = $sql->offset($skip)
			->take($per_page)
			->get();

		// print_r($_SESSION['journals_list_filters']);
		$this->view('accounts/ac_manual_journals', compact(
			'journals',
			'total_journals',
			'per_page'
		));
	}
}
