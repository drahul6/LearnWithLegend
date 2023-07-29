<?php include 'inc/headers.php';
use v2\Models\Wallet\ChartOfAccount;
;?>




    <div class="row">

        <div class="col-md-8">
            <h3>Manual Journals</h3>            
            <small><?= $note; ?> </small>
        </div>
       
        <div class="col-md-4">
            <div class="btn-group">
                <a href="<?= domain; ?>/journals/new" class="btn btn-white">
                    + New Journal
                </a>
            </div>
        </div>

        <table id="charts_of_accounts_table" class="table table-hover">
            <thead>
                <tr>
                    <th>DATE</th>
                    <th>REF#</th>
                    <th>STATUS</th>
                    <th>AMOUNT (<?= ChartOfAccount::$base_currency; ?>)</th>
                    <th>NOTES</th>
                    <th>ATTACHMENTS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>


                <?php foreach ($journals as $journal) : ?>
                    <tr>
                        <td><?= $journal->created_at->toFormattedDateString(); ?></td>
                        <td><?= $journal->id; ?></td>
                        <td><?= $journal->publishedState; ?></td>
                        <td><?= MIS::money_format($journal->amount ?? 0); ?></td>
                        <td><?= $journal->notes; ?></td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                    <i class="fa fa-paperclip"></i> <span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <?php foreach ($journal->attachments as $file) :
                                        $filename = end(explode('/', $file));
                                    ?>
                                        <li><a target="_blank" href="<?= domain; ?>/<?= $file; ?>"><?= $filename; ?></a></li>
                                    <?php endforeach; ?>

                                </ul>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-white btn-xs dropdown-toggle" data-toggle="dropdown">
                                    <span class="caret"></span>Action</button>
                                <div class="dropdown-menu" role="menu">
                                    <?php if (!$journal->is_published()) : ?>
                                        <a class="dropdown-item" href="<?= $journal->editLink; ?>">Edit</a>
                                    <?php endif; ?>
                                    <a class="dropdown-item" 
                                    onclick="$confirm_dialog = new ConfirmationDialog('<?= $journal->declineLink; ?>')">Decline</a>
                                    <a class="dropdown-item" href="<?= $journal->viewLink; ?>">View</a>

                                </div>
                            </div>
                        </td>
                    </tr>

                <?php endforeach; ?>

            </tbody>
        </table>

        <div>
            <nav class="pagination pagination-sm">
              <?= $this->pagination_links($data, $per_page);?>
            </nav>
        </div>


    </div>



<?php include 'inc/footers.php'; ?>