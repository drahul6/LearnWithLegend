<?php
$page_title = "Identity Verification";
include 'includes/header.php';
?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Identity Verification</h3>
                <!-- 
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="">Blank</a>
                                    </li>
                                </ol>
                            </nav>
                        </div> -->
            </div>
            <div class="col-5 text-right">
                <small><?= $auth->VerifiedBagde; ?></small>

                <!--  <div class="customize-input float-right">
                    <select class="custom-select custom-select-set form-control bg-white border-0 custom-shadow custom-radius">
                        <option selected>Aug 19</option>
                        <option value="1">July 19</option>
                        <option value="2">Jun 19</option>
                    </select>
                </div> -->
            </div>
        </div>
    </div>


    <div class="container-fluid">

        <div class="col-md-12">
            <?php foreach (v2\Models\UserDocument::$document_types as $key => $type) :
                if ($type['type'] != 'document') {
                    continue;
                }
            ?>
                <div class=" card">
                    <div class="card-header">
                        <h4 class="card-title" style="display: inlin;">
                            <a data-toggle="collapse" title="click to see uploaded documents" href="#collapse1<?= $key; ?>">

                                <i class="ft-caret"></i> <?= $type['name']; ?>
                            </a>

                            <form class="ajax_for float-right" method="post" action="<?= domain; ?>/user_doc_crud/upload_document" enctype="multipart/form-data">
                                <input style="display:none; " type="file" name="document" onchange="form.submit();">
                                <?php
                                $document = $auth->documents->where('document_type', $key)->first();
                                if ((($document != null) && (!$document->is_status(2))) || ($document == null)) : ?>
                                    <button class="btn btn-dark btn-sm" type="button" onclick="form.document.click();">+ Upload</button>
                                <?php endif; ?>
                                <input type="hidden" name="type" value="<?= $key; ?>">
                            </form>

                        </h4>

                    </div>
                    <div id="collapse1<?= $key; ?>" class=" collapse show">
                        <div class="">
                            <ul class="list-group list-group-flush">
                                <?php $i = 1;
                                foreach ($auth->documents->where('document_type', $key) as $key => $doc) : ?>
                                    <li class="list-group-item"><?= $i; ?>) <?= $doc->DisplayStatus; ?> <a href="<?= domain; ?>/<?= $doc->path; ?>" target="_blank" class="float-right">Open</a></li>
                                <?php break;
                                    $i++;
                                endforeach; ?>
                                <?php if (!isset($doc)) : ?>
                                    <li class="list-group-item text-center text-small">Your <?= $type['name']; ?> will show here</li>
                                <?php endif; ?>
                            </ul>
                            <div class="card-footer"> <small>*<?= $type['instruction']; ?></small></div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
            <?php
            $document = $auth->documents->where('document_type', 3)->first();;

            ?>

            <div class=" card">
                <div class="card-header">
                    <h4 class="card-title" style="display: inlin;">
                        <a data-toggle="collapse" title="click to see uploaded documents" href="#collapse1_links">

                            <i class="ft-caret"></i> Profile Links
                        </a>
                        <?= $document->DisplayStatus ?? ''; ?>
                    </h4>

                </div>

                <div id="collapse1_links" class=" collapse show">
                    <div class="card-body">
                        <form method="post" action="<?= domain; ?>/user_doc_crud/update_links">

                            <div class="alert alert-secondary"> <small>*<?= $type['instruction']; ?></small></div>

                            <!--  <div class="form-group">
                                <label>* Webtalk profile link</label>
                                <input type="url" class="form-control" value="<?= $document->Links['webtalk'] ?? ''; ?>" name="links[webtalk]" placeholder="https://www.webtalk.co/username" required>
                            </div> -->
                            <div class="form-group">
                                <label>* Viiral Legends profile link</label>
                                <input type="url" class="form-control" value="<?= $document->Links['viiral'] ?? ''; ?>" name="links[viiral]" placeholder="" required>
                            </div>

                            <div class="form-group">
                                <label>* Unifying Legends profile link</label>
                                <input type="url" class="form-control" value="<?= $document->Links['unifying'] ?? ''; ?>" name="links[unifying]" placeholder="" required>
                            </div>

                            <button class="btn btn-outline-dark">Save</button>
                        </form>

                    </div>

                </div>
            </div>

        </div>


    </div>

    <?php include 'includes/footer.php'; ?>