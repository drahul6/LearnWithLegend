<table class="table table-striped table-sm">
  <?php

  if ($which == 'pending') {
    $documents = $user->pending_documents;
  } else {
    $documents = $user->approved_documents;
  }



  $i = 1;
  foreach ($documents as $key => $doc) :

    if (!$doc->isDocument()) {
      continue;
    }
  ?>

    <tr>
      <td><?= $i; ?>) <?= $doc->DisplayStatus; ?>
        <a href="<?= domain; ?>/<?= $doc->path; ?>" target="_blank" class="float-right"><?= $doc->Type['name']; ?></a>
      </td>

      <!-- <a href="javascript:void(0);" onclick="open_smodal('<?= domain; ?>/<?= $doc->path; ?>')" class="float-right"><?= $doc->Type['name']; ?></a></td> -->
    </tr>
  <?php $i++;
  endforeach; ?>
  <ul class="card">Profile Links
    <?php
    $links = $document->Links;
    foreach ($links as $index => $link) : ?>
      <li class="list-group-ite"><span class="badge badge-dark"><?= $index; ?></span> <a href="<?= $link; ?>" target="_blank"> <?= $link; ?></a></li>
    <?php endforeach; ?>
  </ul>



  <div class="modal fade" id="myModdoc">
    <div class="modal-dialog modal-lg" style="z-index: 999;">
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Document </h4>
          <button type="button" class="close" onclick="$('#myModdoc').modal('hide')">&times;</button>
        </div>

        <!-- Modal body -->
        <div class="modal-body">
          <img id="simage" src="<?= domain; ?>/<?= $doc->path; ?>" style="width: 100%;">

        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>

      </div>
    </div>
  </div>

</table>

<script>
  open_smodal = function($src) {

    $('#myModdoc').modal('show');
    $('#simage').attr('src', $src);
  }
</script>