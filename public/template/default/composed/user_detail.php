<table class="table table-striped table-sm">

  <tr>
    <td>Profile(Verification)</td>
    <td><?= $user->VerifiedBagde; ?></td>
  </tr>
  <tr>
    <td>Name (F L)</td>
    <td><?= $user->fullname; ?> <i style="text-decoration:underline;">@<?= $user->username; ?></i></td>
  </tr>
  <tr>
    <td>Email</td>
    <td><?= $user->email; ?> <?= $user->emailVerificationStatus; ?></td>
  </tr>
  <tr>
    <td>Phone</td>
    <td><?= $user->phone; ?> <?= $user->phoneVerificationStatus; ?></td>
  </tr>
  <tr>
    <td>Webtalk</td>
    <td><?= $auth->details['webtalk_link'] ?? ''; ?></td>
  </tr>
  <tr>
    <td>Registration date</td>
    <td><?= date("M j, Y", strtotime($user->created_at)); ?></td>
  </tr>
</table>