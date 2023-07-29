<?php


use Illuminate\Database\Eloquent\Model as Eloquent;
use  v2\Models\HotWallet;
use  v2\Models\Commission;
// use  User, SubscriptionOrder;
use Illuminate\Database\Capsule\Manager as DB;

class Rank 
{
	

	private $user;
	private $all_ranks;
	private $rank_qualifications;

	private $rank = -1;


	public function __construct()
	{

		$rank_setting = SiteSettings::find_criteria('leadership_ranks')->settingsArray;
		// print_r($rank_setting);

		$this->all_ranks = $rank_setting['all_ranks'];
		$this->rank_qualifications = $rank_setting['rank_qualifications'];
		krsort($this->rank_qualifications);


		echo "<pre>";

		// print_r($this->rank_qualifications);

	}


	public function setUser($user)
	{
		 $this->user = $user;
		return $this;
	}


	public function __get($property)
	{
		return $this->$property;
	}


	public function setUserRank()
	{
		echo $this->rank;

		if (($this->rank == -1) ||($this->rank === null)) {
			return;
		}


		
		if ($this->rank <= $this->user->rank) {
			return;
		}


		$rank = $this->all_ranks[$this->rank];
		$cash_rewards = $this->rank_qualifications[$this->rank]['cash_rewards'];
		$amount = $cash_rewards['amount'];

		if (isset($cash_rewards['perks']) && ($cash_rewards['perks'] !='')) {

			$perks =  $cash_rewards['perks'];
		}else{
			$perks = "Nil";
		}
		

		$comment = "Cash Reward $amount for reaching {$rank['name']} and perks: $perks";


		$extra_detail = json_encode([
			'reason' => 'rank'
		]);


		$today = date("Y-m-d H:i:s");


		$pay_date = date("Y-m-t");



		$identifier = "rank{$this->rank}";
		DB::beginTransaction();

		try {

			$rank_history = $this->user->RankHistoryArray;

			$rank_history[$today] = $this->rank;
			
			$update = $this->user->update([
						'rank'=> $this->rank,
						'rank_history'=> json_encode($rank_history),
					]);


			$leadership_bonus =	Commission::createTransaction(
									'credit',
									$this->user->id,
									null,
									$amount,
									'completed',
									'rank',
									$comment,
									$identifier, 
									null, 
									null,
									$extra_detail,
									$pay_date
								);


			//update this user's last subscription expires at to next 30days
			if ($this->rank == User::$rank_to_start_auto_membership) {
				//update subscription to expire in 30days
				
				$subscription =  SubscriptionOrder::where('user_id', $this->user->id)->Paid()->latest('paid_at')->first();

				if ($subscription) {
			        $expires_at = date("Y-m-d", strtotime("+30 days"));

					$subscription->update(['expires_at' => $expires_at]);


				}


			}

			DB::commit();
		} catch (Exception $e) {
			DB::rollback();
			// print_r($e->getMessage());
		}

	}


	public  function determineRank()
	{
		$possible_ranks = [];

		$allowed=['rating','points_volume'];

		foreach ($this->rank_qualifications as $rank => $requirements) {

			foreach ($requirements as $requirement => $detail) {

				if((method_exists($this, $requirement)) && (in_array($requirement, $allowed))){

					$response = $this->$requirement($detail, $rank);
					if ($response===false) {
						// echo "$requirement for $rank <br>";
						$possible_ranks[$rank][] = 0;
						continue 2;

					}else{
						
						$possible_ranks[$rank][] = 1;
					}
				}

			}


		}

		// print_r($possible_ranks);

		foreach ($possible_ranks as $rank => $value) {
			if (array_sum($value) == count($allowed)) {
				$this->rank = $rank;
				break;
			}
		}

		return  $this;
	}




	public function rating($detail, $index)
	{
		//activities
		$activity = $detail['activity']['action'];
		if ($activity==null) {

		}else{

			$to_do_activities = explode(",", $activity);
			foreach ($to_do_activities as $key => $action) {

				switch ($action) {
					case 'buy_package':
						//check if user ever bought a package
						$purchased_packages = HotWallet::for($this->user->id)->Credit()->Category('investment')->Paid()->count();
						if ($purchased_packages == 0) {
							return false;
							$fail['activity'] = 0;
							break;
						}

						break;
					
					default:
						# code...
						break;
				}

			}

		}


		//in_team
		$in_team = $detail['in_team'];
		foreach ($in_team as $key => $team_requirement) {
			$member_rank = $team_requirement['member_rank'];
			if (($member_rank=='') || ($team_requirement['count'] == '')) { continue;}

			$count = $this->user->find_rank_in_team('binary', $member_rank);
		
			if ($count < $team_requirement['count']) {
				return false;
			}

		}




		//direct_lines
		$direct_line = $detail['direct_line'];
		foreach ($direct_line as $key => $direct_line_requirement) {


			$position = ['right'=>1,'left'=>0][$direct_line_requirement['position']];

			//direct_lines
			$direct_line = $this->user->all_downlines_at_position(0, 'binary')->where('introduced_by', $this->user->mlm_id);


			$member_rank = $direct_line_requirement['member_rank'] ;
			if ($member_rank != '') {

				$direct_line = $direct_line->where('rank', $member_rank);
			}


			$count = $direct_line_requirement['count'];
			if ($direct_line->count() < $count) {
			    return false;
			}




		}


		return true;
	}



	public function points_volume($detail, $index)
	{

		$activity = $detail['activity']['action'];
		if ($activity==null) {

		}else{

			$to_do_activities = explode(",", $activity);
			foreach ($to_do_activities as $key => $action) {

				switch ($action) {
					case 'buy_package':
						//check if user ever bought a package
						$purchased_packages = HotWallet::for($this->user->id)->Credit()->Category('investment')->Paid()->count();
						if ($purchased_packages == 0) {
							return false;
							break;
						}

						break;
					
					default:
						# code...
						break;
				}

			}

		}

		//points
		 $points_volume = $detail['points'];

		if ($points_volume != '') {

			$left_volume = ($this->user->total_volumes(0, 'binary'));
			$right_volume = ($this->user->total_volumes(1, 'binary'));

			$weaker = min($left_volume, $right_volume);

			if ($weaker < $points_volume ) {
				return false;
			}
		}

		return true;

	}




	public static function getVolume($user_id)
	{
		$volume =  HotWallet::for($user_id)->Credit()->Category('investment')->Paid()->sum('cost');
		return $volume;
	}



}


















?>