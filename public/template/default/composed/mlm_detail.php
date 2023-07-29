<?php
$sponsor = User::where('mlm_id', $user->introduced_by)->first();
$upline = User::where('mlm_id', $user->referred_by)->first();
$today = date("Y-m-d");

$week = MIS::date_range($today, 'week', true);


$already_paid = 0;

$total_volume_left = 0;
$total_volume_right = 0;

$weekly_left =  $total_volume_left - $already_paid;
$weekly_right =  $total_volume_right - $already_paid;



//for the rank
$life_left_volume = 0;
$life_right_volume = 0;



$membership_status = $user->MembershipStatusDisplay;
$binary_status = $user->BinaryStatusDisplay;; ?>


<a class="dropdown-item text-sm" href="#" style="padding: 0px;">
  <table class="mlm_detail table table-borderless " style="border: none;margin: 0px;margin-bottom: 3px;">
    <tr>
      <td colspan="2" style="text-align: center; color:#073f2dc7; border-bottom: 1px solid #073f2dc7;"> <b><?= $user->fullname; ?> </b></td>
    </tr>

    <tr>
      <td>
        <small class="label">User</small><br>
        <em class="label-value">- <?= $user->username; ?></em>

      </td>
      <td>

        <small class="label">Sponsor</small><br>
        <em class="label-value">- <?= $sponsor->username ?? 'Nil'; ?></em>
      </td>
    </tr>
    <tr>
      <td>
        <small class="label">Upline</small><br>
        <em class="label-value">- <?= $upline->username; ?></em>

      </td>
    </tr>

    <!-- 

    <tr>
      <td>
        <small class="label">Membership</small> <br>
        <em class="label-value">- <b><?= $membership_status; ?></b></em>
      </td>


      <td>
        <small class="label">Pack</small><br>
        <em class="label-value">- <?= $pack->ExtraDetailArray['investment']['name'] ?? 'Nil'; ?></em>
      </td>
    </tr>

    <tr>
      <td>
        <small class="label">Binary</small><br>
        <em class="label-value">- <b><?= $binary_status; ?></b></em>

      </td>

      <td>

        <em>
          <small class="label">Rank</small><br>
          <em class="label-value">- <?= $user->TheRank['name']; ?>
          </em>
        </em>
      </td>
    </tr>
 -->


  </table>

  <!-- 
  <table class="mlm_detail table table-borderless " style="border: none;margin: 0px;margin-bottom: 3px; margin-top: 7px;margin-bottom: 10px;">
    <tr>
      <td colspan="2" style="text-align: center; color:#073f2dc7;"> <b>VOLUMES </b></td>
    </tr>


    <tr>
      <td>
        <small class="label">
          Left Points
        </small> <br> <em class="label label-sm">
          <?= ($weekly_left); ?>
        </em>
      </td>

      <td>
        <small class="label">
          Right Points

        </small><br>
        <em class="label label-sm">
          <?= ($weekly_right); ?>
        </em>
      </td>
    </tr>
    <tr>
      <td>
        <small class="label">Total Left</small><br>
        <em class="label label-sm"><?= ($life_left_volume); ?></em>
      </td>
      <td>
        <small class="label">Total Right</small><br>
        <em class="label label-sm"><?= ($life_right_volume); ?></em>
      </td>
    </tr>


  </table> -->
</a>