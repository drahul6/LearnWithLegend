<?php

use Illuminate\Database\Capsule\Manager as DB;
use v2\Models\Wallet;


/**
 *
 */
class DepositController extends controller
{

    public function __construct()
    {


        if (!$this->admin()) {
            $this->middleware('current_user')
                ->mustbe_loggedin()
                ->must_have_verified_email();
            // ->must_have_verified_company();
        }

    }


    public function push($deposit_id, $status, $wallet_key=null)
    {
        if (!$this->admin()) {
            die();
        }



        $wallet_class = Wallet::$wallet_classes[$wallet_key]['class'] ;

        if ($wallet_class == null) {
            Session::putFlash('danger', "Could not complete this request.");
            Redirect::back();
        }


        $line = $wallet_class::find($deposit_id);


        if ($line == null) {
            Session::putFlash('danger', "Invalid Request.");
            Redirect::back();
        }

/*        if ($line->is_complete()) {
            Session::putFlash('danger', "Already  completed.");
            Redirect::back();
        }
*/

        DB::beginTransaction();

        try {

            $line->update([
                'status' => $status,
                'admin_id' => $this->admin()->id,
            ]);

            $now = date("Y-m-d H:i:s");
            if(($line->is_complete())){
                $line->update(['paid_at' => $now]);
            }

            DB::commit();
            Session::putFlash('success', "Transaction marked as $status");

        } catch (Exception $e) {
            DB::rollback();
            Session::putFlash('success', "Something went wrong. Please try again.");
        }


        Redirect::back();

    }







        //receive from reipient only
        public function push_transfer($deposit_id, $status, $wallet_key=null)
        {
            $auth = $this->auth();
            $wallet_class = Wallet::$wallet_classes[$wallet_key]['class'] ;

            if ($wallet_class == null) {
                Session::putFlash('danger', "Could not complete this request.");
                Redirect::back();
            }


            $line = $wallet_class::find($deposit_id);


            if ($line == null) {
                Session::putFlash('danger', "Invalid Request.");
                Redirect::back();
            }

            if ($line->is_complete()) {
                Session::putFlash('danger', "Already  completed.");
                Redirect::back();
            }
    

            $receiver_detail = $line->ExtraDetailArray;


            $sender_wallet_class = Wallet::$wallet_classes[$receiver_detail['from_wallet']]['class'] ;

            $unique_journal_id = $receiver_detail['unique_journal_id'];
        $identfier = <<<EOL
         "unique_journal_id":"$unique_journal_id
EOL;        

         $identfier = trim($identfier);

            $sender_line = $sender_wallet_class::where('extra_detail', 'like', "%$identfier%");

            $receiver_detail['status'][] = ['status' =>$status, 
                                            'user_id' => $auth->id,
                                                'datetime' => date("Y-m-d H:i:s")
                                            ];

            $receiver_detail = json_encode($receiver_detail);

            DB::beginTransaction();

            try {

                $line->update([
                    'status' => $status,
                    'extra_detail'=>$receiver_detail
                ]);
                //look for other side and update status

                $sender_line->update([
                    'status' => $status,
                    'extra_detail'=>$receiver_detail
                ]);



                DB::commit();
                Session::putFlash('success', "Transaction marked as $status");

            } catch (Exception $e) {
                DB::rollback();
                Session::putFlash('danger', "Something went wrong. Please try again.");

                print_r($e->getMessage());
            }


            Redirect::back();

        }


        public function sender_push_transfer($deposit_id, $status, $wallet_key=null)
        {

            $auth = $this->auth();
            $wallet_class = Wallet::$wallet_classes[$wallet_key]['class'] ;

            if ($wallet_class == null) {
                Session::putFlash('danger', "Could not complete this request.");
                Redirect::back();
            }


            $sender_line = $wallet_class::find($deposit_id);




            if ($sender_line == null) {
                Session::putFlash('danger', "Invalid Request.");
                Redirect::back();
            }

            if ($sender_line->is_complete()) {
                Session::putFlash('danger', "Already  completed.");
                Redirect::back();
            }
            

            $receiver_detail = $sender_line->ExtraDetailArray;


            $receiver_wallet_class = Wallet::$wallet_classes[$receiver_detail['to_wallet']]['class'] ;

            $unique_journal_id = $receiver_detail['unique_journal_id'];
        $identfier = <<<EOL
         "unique_journal_id":"$unique_journal_id
EOL;        

         $identfier = trim($identfier);

            $receiver_line = $receiver_wallet_class::where('extra_detail', 'like', "%$identfier%")->first();


            $this-> push_transfer($receiver_line->id, $status, 'deposit');


        }

}


?>