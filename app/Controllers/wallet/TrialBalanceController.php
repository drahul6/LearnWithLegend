<?php


use League\Csv\Reader;
use League\Csv\Writer;
use League\Csv\Statement;
use v2\Models\Wallet\AcBooksSettings;
use v2\Models\Wallet\BasicAccountType;
use v2\Models\Wallet\CompanyAccountType;
use v2\Models\Company;
use v2\Models\Wallet\ChartOfAccount;

/**
 * this class is the default controller of our application,
 * 
 */
class TrialBalanceController extends controller
{


	public function __construct()
	{

		// $this->middleware('current_user')->mustbe_loggedin();
		$this->company = ChartOfAccount::getCompany();
	}




	public function export_to_csv()
	{

		$response = $this->subcat(2);
		$records = $response['trial_balance'];
		$company = $this->company;

		$from = $response['from'];
		$as_of_date = $response['as_of_date'];

		foreach ($records as $basic_account_id => $subcategories) {
			$basic_account = BasicAccountType::find($basic_account_id);
			$csv_records[] = [
				$basic_account['code'],
				$basic_account['name'],
				$basic_account['debit_balance'],
				$basic_account['credit_balance'],
			];


			foreach ($subcategories as $subcategory_id => $accounts) {

				$subcategory = CompanyAccountType::find($subcategory_id);
				$csv_records[] = [
					$subcategory['code'],
					$subcategory['name'],
					$subcategory['debit_balance'],
					$subcategory['credit_balance'],
				];
				foreach ($accounts as $key => $account) {
					$total_debits[] = $account['raw_debit_balance'];
					$total_credits[] = $account['raw_credit_balance'];
					$csv_records[] = [
						$account['account_code'],
						$account['account_name'],
						$account['debit_balance'],
						$account['credit_balance'],
					];
				}
			}
		}

		$csv_records[] = [
			'',
			'Total',
			array_sum($total_debits),
			array_sum($total_credits),
		];


		// print_r($csv_records);

		// return;
		//we create the CSV into memory
		$csv = Writer::createFromFileObject(new SplTempFileObject());

		//we insert the CSV header
		$csv->insertOne(['Code', 'Account', 'Debits', 'Credits']);

		// The PDOStatement Object implements the Traversable Interface
		// that's why Writer::insertAll can directly insert
		// the data into the CSV
		$csv->insertAll($csv_records);

		// Because you are providing the filename you don't have to
		// set the HTTP headers Writer::output can
		// directly set them for you
		// The file is downloadable
		$company = $this->company;
		$csv->output("{$company->name} $from to $as_of_date -trial_balance.csv");
	}





	public function subcat($print = 0)
	{

		$as_of_date = date("Y-m-d");


		$response = $this->company->get_trial_balance($as_of_date);

		extract($response);



		switch ($print) {
			case  0:

				$this->view(
					'accounts/ac_trial_balance_with_subcategory',
					compact('trial_balance', 'sorted_into_subcategories', 'as_of_date')
				);
				break;

			case  1:

				echo $this->buildView(
					'accounts/print_trial_balance_with_subcategory',
					compact('trial_balance', 'sorted_into_subcategories', 'as_of_date')
				);
				break;
			case  2:

				return [
					'trial_balance' => $sorted_into_subcategories,
					'as_of_date' => $as_of_date
				];
				break;

			default:
				# code...
				break;
		}
	}

	public function index($date = null)
	{

		if ($date == null) {
			$as_of_date = date("Y-m-d");
		} else {

			$as_of_date = $date;
		}



		$trial_balance = $this->company->get_trial_balance($as_of_date);


		$this->view('accounts/trial_balance', compact('trial_balance', 'as_of_date'));
	}


	public function export_to_pdf()
	{

		$as_of_date = $_SESSION['trial_balance_filters']['as_of_date'] ?? '';

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
		$mpdf->SetWatermarkText("Confidential");
		$mpdf->showWatermarkText = true;
		$mpdf->watermark_font = 'DejaVuSansCondensed';
		$mpdf->watermarkTextAlpha = 0.1;
		$mpdf->SetDisplayMode('fullpage');

		$date_now = date('Y-m-d H:i:s');

		$mpdf->SetFooter("Date Generated: " . $date_now . " - {PAGENO} of {nbpg}");

		ob_start();

		$this->subcat(1);

		echo $html  = ob_get_clean();


		die();
		$mpdf->WriteHTML($html);
		$company = $this->company;

		$mpdf->Output("{$company->name}-TrialBalance.pdf", \Mpdf\Output\Destination::DOWNLOAD);

		// $mpdf->Output();

	}
}
