<?php


use League\Csv\Writer;
use Filters\Filters\UserFilter;
use v2\Models\Wallet\ChartOfAccount;
use v2\Models\Wallet\AcBooksSettings;
use v2\Models\Wallet\BasicAccountType;
use v2\Models\Wallet\CompanyAccountType;
use v2\Models\Wallet\AcDashboardSettings;
use Illuminate\Database\Capsule\Manager as DB;
use v2\Models\Wallet\Classes\TransactionGenerator;
use v2\Models\Wallet\GeneratedTransaction;

/**
 *
 */
class AccountsController extends controller
{
	public function __construct()
	{
		// $this->middleware('current_user')->mustbe_loggedin();
		$this->company = ChartOfAccount::getCompany();
		$this->company_id = $this->company->id;
	}


	public function push_gen($id, $status)
	{
		GeneratedTransaction::pushStatus($id, $status);
		Redirect::back();
	}

	public function generate_transactions()
	{

		echo "<pre>";
		print_r($_POST);
		$validator = new Validator;
		$validator->check($_POST, [
			'account_number' => [
				'required' => true,
				'exist' => ChartOfAccount::class . "|account_number",
			],
		]);

		if (!$validator->passed()) {
			Session::putFlash("danger", Input::inputErrors());
			Redirect::back();
		}


		$account = ChartOfAccount::where('account_number', $_POST['account_number'])->first();


		$generator = new TransactionGenerator;
		$generator->setAccount($account)
			->setSettings($_POST)
			->generateTransactions();;

		Redirect::back();
	}



	public function retrieve_account()
	{
		$query = $_GET['search'];

		$sieve = ['name' => $query];
		$response = ChartOfAccount::InvokeQuery($sieve)['query']->with('owner')->get();

		header("content-type:application/json");

		$line = "";

		foreach ($response as $key => $account) {
			$account_fullname = $account->owner->fullname ?? '';
			@$line .= "<option value='{$account->account_number}'>{$account->currency}- ({$account->owner->username}) 
			{$account_fullname}, {$account->account_name}: {$account->custom_account_type->name} -
			{$account->custom_account_type->basic_account->name}
			</option>";
		}



		$data = array_map(function ($account) {
			@$text = <<<EL
{$account['owner']['firstname']} {$account['owner']['lastname']}, {$account['account_name']}:{$account['account_number']}, 
EL;
			return [
				'id' => $account['id'],
				'text' => $text,
				'full' => $account
			];
		}, $response->toArray());

		echo json_encode(compact('data', 'response', 'line'));
	}


	private function users_matters($extra_sieve)
	{
		$sieve = $_REQUEST;
		$sieve = array_merge($sieve, $extra_sieve);

		$query = User::latest();
		// ->where('status', 1);  //in review
		$sieve = array_merge($sieve);
		$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$per_page = 50;
		$skip = (($page - 1) * $per_page);

		$filter = new  UserFilter($sieve);

		$data = $query->Filter($filter)->count();

		$sql = $query->Filter($filter);

		$users = $query->Filter($filter)
			->offset($skip)
			->take($per_page)
			->get();  //filtered


		$note = MIS::filter_note($users->count(), $data, User::count(), $sieve, 1);


		return compact('users', 'sieve', 'data', 'per_page', 'note');
	}



	public function search($query = null)
	{
		$compact = $this->users_matters(['name' => $query]);
		$users = $compact['users'];
		$line = "";
		foreach ($users as $key => $user) {
			$username = $user->username;
			$fullname = $user->fullname;
			$line .= "<option value='$username'> $fullname ($username)</option>";
		}

		header("content-type:application/json");
		echo json_encode(compact('line'));
	}


	public function fetch_dashboard_graph_data_revenues_and_expenses()
	{

		//1 is the id for expense in basic accounts table
		$expenses = ChartOfAccount::where('basic_account_type_id', 1)
			->where('company_id', $this->company_id)
			->sum('current_balance');


		//3 is the id for revenue in basic accounts table
		$revenues = ChartOfAccount::where('basic_account_type_id', 3)
			->where('company_id', $this->company_id)
			->sum('current_balance');

		$output2['labels'][] = 'Expenses';
		$output2['data'][] =  $expenses;


		$output2['labels'][] = 'Revenues';
		$output2['data'][] =  $revenues;




		header("content-type:application/json");

		print_r(json_encode($output2));
	}

	public function fetch_dashboard_graph_data()
	{
		$closables =  BasicAccountType::closables()->get()->pluck('id')->toArray();
		$dashboard_setting = ChartOfAccount::whereIn('basic_account_type_id', $closables)
			->where('company_id', $this->company_id)->orderBy('account_name')->get();

		$i = 1;
		foreach ($dashboard_setting as $chart_of_account) {
			$output[$i]['labels'] = $chart_of_account->account_name;
			$output[$i]['data'] =  ChartOfAccount::account_format($chart_of_account->current_balance);


			$output2['labels'][] = $chart_of_account->account_name;
			$output2['data'][] =  ChartOfAccount::account_format($chart_of_account->current_balance);



			$i++;
		}
		header("content-type:application/json");

		print_r(json_encode($output2));
	}


	public function delete_metric($metric_id)
	{
		$metric = AcDashboardSettings::where('company_id', $this->company_id)
			->where('id', $metric_id)->first();


		if ($metric == null) {
			Session::putFlash('danger', 'Invalid Request');
			Redirect::back();
		}


		$metric->delete();
		Session::putFlash('success', 'Metric Deleted');
		Redirect::back();
	}



	public function create_dashbaord_metric()
	{
		$validator =  new Validator();

		$validator->check(['name' => $_POST['label']], [
			'name' => [
				'unique' => 'AcDashboardSettings',
			],
		]);

		$more_than_one_accounts = (count($_POST['accounts_ids']) > 1);

		if ($more_than_one_accounts && ($_POST['label'] == '')) {
			$validator->addError('label', "Label is required");
		}

		if (!$validator->passed()) {
			Session::putFlash('danger', Input::inputErrors());
			Redirect::back();
		}



		DB::beginTransaction();

		try {
			AcDashboardSettings::create([
				'company_id' 	=> $this->company_id,
				'name'	 		=> $_POST['label'],
				'accounts_ids' 	=> json_encode($_POST['accounts_ids']),
			]);

			DB::commit();
			Session::putFlash('success', "Metric Created Successfully.");
		} catch (\Exception $e) {
			DB::rollback();
			Session::putFlash('danger', "Metric Could not be Created");
			// something went wrong
		}

		Redirect::back();
	}




	public function switch_business()
	{
		$updated = $this->company->update(['company_id' => $_POST['company_id']]);

		if ($updated) {
			Session::putFlash('success', 'Switched successfully');
		} else {
			Session::putFlash('danger', 'Switch unsuccessful');
		}
		Redirect::back();
	}


	public function pdf($chart_of_account_id = null)
	{
		$data = $this->chart_of_account_transactions($chart_of_account_id, 'pdf');
		// print_r($data['transactions']);
		$this->view('accounts/pdf_ac_chart_of_account_transactions', $data);
	}


	public function export_transaction_to_csv($chart_of_account_id)
	{
		$data = $this->chart_of_account_transactions($chart_of_account_id, 'csv');

		$balance_bf = ($data['transactions']->first()->formattedPriorBalance);


		$csv_records[] = [
			"",
			"",
			"Balance B/F",
			"",
			"",
			$balance_bf,
		];

		foreach ($data['transactions'] as $transaction) {
			$second_leg = $transaction->get_second_leg();

			$debits[] =  $transaction['debit'];
			$credits[] = $transaction['credit'];

			$csv_records[] = [
				$transaction['journal_id'],
				"{$second_leg->chart_of_account->account_name}",
				"{$transaction->description}",
				$this->money_format($transaction->debit),
				$this->money_format($transaction->credit),
				$this->money_format($transaction->post_balance),
			];
		}

		$csv_records[] = [
			"",
			"",
			"Total",
			$this->money_format(array_sum($debits)),
			$this->money_format(array_sum($credits)),
			"",
		];



		//we create the CSV into memory
		$csv = Writer::createFromFileObject(new SplTempFileObject());

		//we insert the CSV header
		$csv->insertOne(['Ref', "Account", 'Description', 'Debits', 'Credits', 'Balance']);

		// The PDOStatement Object implements the Traversable Interface
		// that's why Writer::insertAll can directly insert
		// the data into the CSV
		$csv->insertAll($csv_records);

		// Because you are providing the filename you don't have to
		// set the HTTP headers Writer::output can
		// directly set them for you
		// The file is downloadable


		$company = $this->company;
		$chart_of_account = $data['chart_of_account'];
		$from = $data['from'];
		$to = $data['to'];
		$csv->output("{$chart_of_account->account_name} $from to $to -transactions.csv");
	}




	public function export_transaction_to_pdf($chart_of_account_id)
	{
		$data = $this->chart_of_account_transactions($chart_of_account_id, 'pdf');


		$mpdf = new \Mpdf\Mpdf([
			'margin_left' => 15,
			'margin_right' => 15,
			'margin_top' => 10,
			'margin_bottom' => 20,
			'margin_header' => 10,
			'margin_footer' => 10
		]);
		$mpdf->SetProtection(array('print'));
		$mpdf->SetTitle("");
		$mpdf->SetAuthor("");
		$mpdf->SetWatermarkText($this->company->name);
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'Arial';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');

		$date_now = (date('Y-m-d H:i:s'));

		$mpdf->SetFooter("Page {PAGENO} of {nbpg}");


		echo $html = $this->buildView('accounts/account_statement', $data);

		die();
		$mpdf->WriteHTML($html);
		$company = $this->company;
		$chart_of_account = $data['chart_of_account'];

		$mpdf->Output("{$chart_of_account->account_name}-Statement.pdf", \Mpdf\Output\Destination::DOWNLOAD);
	}




	public function books_settings()
	{
		foreach (BasicAccountType::all() as $base_account) {
			foreach (CompanyAccountType::for_company($this->company_id)->get() as $subcategory) {
				foreach (ChartOfAccount::for_company($this->company_id)->get() as $account) {
					if (($account->basic_account_type_id == $base_account->id) &&
						($subcategory->id == $account->company_customised_account_id)
					) {
						$output[$base_account['id']][$subcategory['id']][] = $account->toArray();
					}
				}
			}
		}

		$this->view("accounts/ac_settings", compact('output'));
	}




	public function update_chart_of_account()
	{
		return;

		DB::beginTransaction();

		try {
			$chart_of_account = ChartOfAccount::where('id', $_POST['id'])
				->where(
					'company_id',
					$this->company_id
				)
				->first();


			$account_type = CompanyAccountType::find($_POST['account_type']);

			$basic_account_type_id 	= $account_type->basic_account->id;
			$company_customised_account_id = $account_type->id;




			if ($chart_of_account == null) {
				Redirect::back();
			}

			$updated =	$chart_of_account->update([
				// 'basic_account_type_id' => $basic_account_type_id,
				'company_customised_account_id' => $company_customised_account_id,
				'account_name' => $_POST['name'],
				'currency' => $_POST['currency'],
			]);

			echo "<pre>";


			//perfrom opening balance update
			echo $chart_of_account->opening_balance;
			echo "<br>";
			if ($chart_of_account->opening_balance != $_POST['opening_balance']) {
				$new_opening_balance = $_POST['opening_balance'];
				$plus = ($new_opening_balance >
					$chart_of_account->opening_balance);

				echo $offset = abs($new_opening_balance -
					$chart_of_account->opening_balance);

				if ($plus) {
					echo $chart_of_account->add_to_opening_balance($offset);
				} else {
					$chart_of_account->subtract_opening_balance($offset);
				}
			}

			DB::commit();
			Session::putFlash('success', 'Account Detail updated Successfully.');
			// all good
		} catch (\Exception $e) {
			DB::rollback();
			Session::putFlash('info', 'Account Detail not updated Successfully.');
			// something went wrong
		}



		Redirect::back();
	}




	public function settings($chart_of_account_id = null)
	{
		$chart_of_account = ChartOfAccount::where('id', $chart_of_account_id)
			->where('company_id', $this->company_id)
			->first();

		if ($chart_of_account == null) {
			Redirect::back();
		}

		if ($chart_of_account->is_credit_balance()) {
			$basic_accounts = BasicAccountType::CreditBalances()->get();
		} else {
			$basic_accounts = BasicAccountType::DebitBalances()->get();
		}

		$options = ChartOfAccount::charts_of_account_options_at_edit($this->company_id, $chart_of_account_id);

		$this->view('accounts/ac_chart_of_account_settings', compact(
			'chart_of_account',
			'basic_accounts',
			'options'
		));
	}


	public function chart_of_account_transactions($chart_of_account_id = null, $export = null)
	{
		$chart_of_account = ChartOfAccount::where('id', $chart_of_account_id)
			->where('company_id', $this->company_id)
			->first();


		$per_page = 50;
		$page = (isset($_GET['page'])) ? $_GET['page'] : 1;

		$sieve = $_REQUEST;
		$journal_sieve  = $sieve['journal'] ?? [];
		$line_items_sieve  = $sieve['line_items'] ?? [];

		$transactions = $chart_of_account->transactions($per_page, $page, $journal_sieve, $line_items_sieve);

		$data = compact(
			'chart_of_account',
			'transactions',
			'per_page',
			'page'
		);


		switch ($export) {
			case 'pdf':

				return $data;

				break;
			case 'csv':

				return $data;
				break;

			default:
				$this->view('accounts/ac_chart_of_account_transactions', $data);
				break;
		}
	}



	public function delete_customised_account($customised_account_id = null)
	{
		$customised_account = CompanyAccountType::where('id', $customised_account_id)
			->where('company_id', $this->company_id)
			->first();
		if ($customised_account == null) {
			Session::putFlash('danger', 'Invalid Request');
			Redirect::back();
		}


		try {
			$customised_account->delete();
			Session::putFlash('success', 'Deleted Successfully.');
		} catch (Exception $e) {
			Session::putFlash('danger', "Could not Delete Account <code>$customised_account->name</code>");
		}


		Redirect::back();
	}

	public function update_customised_account()
	{
		$customised_account = CompanyAccountType::where('id', $_POST['customised_account_id'])
			->where('company_id', $this->company_id)
			->first();


		if ($customised_account == null) {
			Session::putFlash('danger', 'Invalid Request');
			Redirect::back();
		}

		$validator =  new Validator;
		$validator->check(
			$_POST,
			[
				'name' => [
					'required' =>  true,
					//ensure uniqueness in the db
					'replaceable' =>  CompanyAccountType::class . "| {$customised_account->id}",
				],

				'account_type' => [
					'required' =>  true,
				],
			]
		);

		if ($validator->passed()) {
			$customised_account->update([
				'name' => $_POST['name'],
				'basic_account_id' => $_POST['account_type'],
			]);




			Session::putFlash('success', 'Account Category Updated Successfully.');
		} else {
			Session::putFlash('danger', $this->inputErrors());
		}

		Redirect::back();
	}

	public function edit_customised_account($customised_account_id = null)
	{
		$customised_account = CompanyAccountType::where('id', $customised_account_id)
			->where('company_id', $this->company_id)
			->first();
		if ($customised_account == null) {
			Session::putFlash('danger', 'Invalid Request');
			Redirect::back();
		}


		if ($customised_account->basic_account->is_credit_balance()) {
			$basic_accounts = BasicAccountType::CreditBalances()->get();
		} else {
			$basic_accounts = BasicAccountType::DebitBalances()->get();
		}

		$this->view('accounts/ac_edit_customised_account', compact('customised_account', 'basic_accounts'));
	}

	public function create_chart_of_accounts_categories()
	{
		$validator =  new Validator;
		$validator->check($_POST, CompanyAccountType::$validator_rules);

		DB::beginTransaction();

		try {
			$new_account =	CompanyAccountType::create([
				'name' => $_POST['name'],
				'basic_account_id' => $_POST['account_type'],
				'company_id' => $this->company_id,
			]);
			DB::commit();
			// all good
			Session::putFlash('success', 'Account Category Created Successfully.');
		} catch (\Exception $e) {
			DB::rollback();
			Session::putFlash('danger', 'Account Category Not Created.');
			// something went wrong
		}

		header("content-type:application/json");
		echo $new_account;
	}



	public function customise_charts_of_accounts_category()
	{
		$company_account_types = CompanyAccountType::for_company($this->company_id)->latest()
			->get();

		$this->view('accounts/ac_customise_charts_of_accounts_category', compact('company_account_types'));
	}

	public function fetch_chart_of_accounts()
	{
		$chart_of_accounts  = ChartOfAccount::for_company($this->company_id)
			->with(['custom_account_type', 'owner'])
			->get();
		foreach ($chart_of_accounts as  $chart) {
			// $chart->account_type;
		}

		header("content-type:application/json");
		echo $chart_of_accounts;
	}


	public function create_chart_of_accounts()
	{
		/*
        echo "<pre>";
        print_r($_POST);

        echo "here"; */


		$validator = new Validator;
		$owner = User::where('username', $_POST['username'])->first();
		$rules = ChartOfAccount::$validator_rules;
		$account_type = CompanyAccountType::find($_POST['account_type']);

		if ($owner) {
			$composite = [
				'model' => ChartOfAccount::class,
				'name' => 'Account',
				'columns_value' => [
					'account_name' => $_POST['account_name'],
					'owner_id' => $owner->id,
					'company_customised_account_id' => $account_type->id,
				],
			];

			$rules['composite_unique'] = $composite;
		} else {
			$rules['account_name']['unique'] = ChartOfAccount::class;
		}

		$validator->check($_POST, $rules);

		if (!$validator->passed()) {
			Session::putFlash('danger', $this->inputErrors());
			return;
		}

		DB::beginTransaction();

		try {
			$account_type = CompanyAccountType::find($_POST['account_type']);

			$basic_account_type_id = $account_type->basic_account->id;
			$account_code = ChartOfAccount::generate_account_code($this->company_id, $account_type);
			$account_number = ChartOfAccount::generate_account_number($this->company_id, $account_type);


			$new_chart = ChartOfAccount::create([
				'company_customised_account_id'		=> $account_type->id,
				'user_id'					=> $this->id ?? null,
				'owner_id'					=> $owner->id ?? null,
				'company_id'				=> $this->company_id,
				'account_name'				=> $_POST['account_name'],
				'account_code'				=> $account_code,
				'account_number'			=> $account_number,
				'tag'						=> $_POST['tag'],
				'description'				=> $_POST['description'],
				'currency'					=> $_POST['currency'] ?? ChartOfAccount::$base_currency,
			]);

			$new_chart->setOpeningBalance($_POST['opening_balance']);

			DB::commit();
			Session::putFlash('success', 'Chart of Account Created Successfully.');

			header("content-type:application/json");

			$new_chart->account_type;
			echo $new_chart;
			// all good
		} catch (\Exception $e) {
			print_r($e->getMessage());
			DB::rollback();
			// something went wrong
		}
	}






	public function chart_of_accounts()
	{
		$options = ChartOfAccount::charts_of_account_options_at_creation($this->company_id);

		$this->view('accounts/ac_chart_of_accounts', compact('options'));
	}


	public function index()
	{
		$this->view('accounts/index', []);
	}


	public function dashboard()
	{

		//1 is the id for expense in basic accounts table
		$expenses_custom_account_type_ids = CompanyAccountType::where('basic_account_id', 1)
			->for_company($this->company_id)
			->pluck('id')
			->toArray();


		$expenses = ChartOfAccount::whereIn('company_customised_account_id', $expenses_custom_account_type_ids)
			->where('company_id', $this->company_id)
			->sum('current_balance');


		$revenues_custom_account_type_ids = CompanyAccountType::where('basic_account_id', 3)
			->for_company($this->company_id)
			->pluck('id')
			->toArray();



		//3 is the id for revenue in basic accounts table
		$revenues = ChartOfAccount::whereIn('company_customised_account_id', $revenues_custom_account_type_ids)
			->where('company_id', $this->company_id)
			->sum('current_balance');


		$consumption = $this->money_format(($expenses / $revenues) * 100);


		$dashboard_settings = AcDashboardSettings::showables($this->company_id)->get();

		$this->view('accounts/ac_dashboard', compact(
			'dashboard_settings',
			'expenses',
			'revenues',
			'consumption'
		));
	}
}
