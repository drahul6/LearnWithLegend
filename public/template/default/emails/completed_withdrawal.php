<?php include 'includes/header.php';?>

  </td></tr></tbody></table>


    <div style="font-family: Arial, sans-serif; line-height: 20px; color: #444444; font-size: 13px;">
      <b style="color: #777777; text-transform: lowercase;"></b>

                       <?php
                                $message =  CMS::fetch('completed_withdrawal_email');

                                $timestamp = date("M j, Y H:iA", strtotime($withdrawal->paid_at));

                                $message = str_replace("[AMOUNT]", "<b>$withdrawal->amount</b>", $message);
                                $message = str_replace("[RECEIVER]", "<b>{$withdrawal->user->username}</b>", $message);
                                $message = str_replace("[ID]", "<b>{$withdrawal->id}</b>", $message);
                                $message = str_replace("[FEE]", "<b>{$withdrawal->fee}</b>", $message);
                                $message = str_replace("[TIMESTAMP]", "<b>$timestamp</b>", $message);

                                echo $message;?>
    </div>


  </td></tr></tbody></table>
</td></tr></tbody></table>
    






<?php include 'includes/footer.php';?>