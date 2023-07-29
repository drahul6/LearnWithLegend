<?php
$page_title = 'Journals';
include 'includes/header.php';

use v2\Models\Wallet\ChartOfAccount;

?>


<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-6  mb-2">
                <?php include 'includes/breadcrumb.php'; ?>

                <h3 class="content-header-title mb-0" style="display:inline;">Journals</h3>
            </div>

            <div class="content-header-right col-6">
                <div class="btn-group float-right" role="group" aria-label="Button group with nested dropdown">
                    <small class="float-right"></small>
                    <a href="<?= domain; ?>/journals/new/admin" class="btn btn-outline-dark">
                        + New Journal
                    </a>
                </div>
            </div>
        </div>
        <div class="content-body">


            <section class="card">
                <div class="card-header">

                    <?php $this->view('composed/filters/journals', compact('sieve')); ?>
                    <!-- <h4 class="card-title" style="display: inline;"></h4> -->
                    <span class="float-right"> <?= $note; ?></span>
                </div>
                <div class="card-content">
                    <div class="card-body table-responsive">


                        <table id="charts_of_accounts_table" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>DATE</th>
                                    <th>REF#</th>
                                    <th>STATUS</th>
                                    <th>AMOUNT (<?= ChartOfAccount::$base_currency; ?>)</th>
                                    <th>NOTES</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>


                                <?php foreach ($journals as $journal) : ?>
                                    <tr>
                                        <td><?= date("M d, Y", strtotime($journal->created_at)); ?></td>
                                        <td><?= $journal->id; ?></td>
                                        <td><?= $journal->publishedState; ?></td>
                                        <td><?= MIS::money_format($journal->amount ?? 0); ?></td>
                                        <td><?= $journal->notes; ?></td>
                                        <td>

                                            <div class="btn-group btn-group-sm">
                                                <?php if ($journal->is_editable()) : ?>
                                                    <a class="btn btn-outline-dark" href="<?= domain ?>/admin/edit_journal/<?= $journal->id; ?>">Edit</a>
                                                <?php endif; ?>
                                                <a class="btn btn-outline-dark" href="<?= domain ?>/admin/view_journal/<?= $journal->id; ?>">View</a>

                                                <?php if ($journal->is_pending()) : ?>
                                                    <a class="btn btn-outline-dark" onclick="$confirm_dialog = new ConfirmationDialog('<?= domain ?>/admin/complete_journal/<?= $journal->id; ?>', 
                                                    'Complete journal#<?= $journal->id; ?> ?')">Complete</a>

                                                    <a class="btn btn-outline-dark" onclick="$confirm_dialog = new ConfirmationDialog('<?= domain ?>/admin/decline_journal/<?= $journal->id; ?>', 'Decline journal?')">Decline</a>
                                                <?php endif; ?>

                                                <?php if ($journal->is_reversible()) : ?>
                                                    <!--  <a class="btn btn-outline-dark" onclick="$confirm_dialog = new ConfirmationDialog('<?= domain ?>/admin/reverse_journal/<?= $journal->id; ?>',
                                                    'Reverse journal#<?= $journal->id; ?> ?')">Reverse</a> -->
                                                <?php endif; ?>

                                            </div>


                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            </tbody>
                        </table>



                    </div>
                </div>
            </section>

            <ul class="pagination">
                <?= $this->pagination_links($data, $per_page); ?>
            </ul>
        </div>
    </div>
</div>
<!-- END: Content-->


<div id="new_category_app"></div>

<?php include 'includes/footer.php'; ?>