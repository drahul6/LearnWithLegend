<?php include 'includes/header.php';?>

  </td></tr></tbody></table>


    <div style="font-family: Arial, sans-serif; line-height: 20px; color: #444444; font-size: 13px;">
      <b style="color: #777777; text-transform: lowercase;"></b>
      <p>Hello <?=$order->Buyer->fullname;?>, </p>
      <p>Your order  is here! </p>
      Order ID:<?=$order->TransactionID;?>

      <p>Please click <a href="<?=$order->after_payment_url();?>">View Content</a> to see your order.</p>

      <p></p>
      <br>
      <br>
    </div>


  </td></tr></tbody></table>
</td></tr></tbody></table>
    






<?php include 'includes/footer.php';?>