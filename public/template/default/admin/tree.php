<?php
$page_title = "Referral Tree";
include 'includes/header.php';

$max_uplevel = $user->max_uplevel('binary')['max_level'];

?>


<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="content-header-left col-md-6 mb-2">
                <?php include 'includes/breadcrumb.php'; ?>

                <h3 class="content-header-title mb-0">Referral Tree</h3>
            </div>
            <div class="dropdown float-right col-md-6 ">
                <button type="button" style="cursor: pointer;" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-chevron-down "></i> Filters
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item " href="<?= domain; ?>/genealogy/placement/company/binary/2">
                        <i class="fa fa-sitemap "></i> Root Genealogy </a>
                    <a class="dropdown-item " href="<?= domain; ?>/genealogy/last/0/<?= $tree_key; ?>/<?= $user->username; ?>/1"> <i class=" ft-chevrons-left"></i> Last Left</a>
                    <a class="dropdown-item " href="<?= domain; ?>/genealogy/last/1/<?= $tree_key; ?>/<?= $user->username; ?>/1"><i class=" ft-chevrons-right"></i>Last Right</a>
                    <a href="#" id="gfilter" class="dropdown-item " class="text-center">

                        <i class="ft-corner-left-up "></i>
                        <label class="">Level up</label>
                        <form action="<?= domain; ?>/genealogy/up/1" method="post">
                            <div class="input-group col-12" style="padding: 0px;">
                                <input type="number" min="0" max="<?= $max_uplevel; ?>" required="" class="form-control form-control-sm" name="level_up" placeholder="x-level up" aria-describedby="button-addon2">
                                <input type="hidden" name="tree_key" value="<?= $tree_key; ?>">
                                <input type="hidden" name="user_id" value="<?= $user->id; ?>">
                                <div class="input-group-append" id="button-addon2">
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">Up</button>
                                </div>
                            </div>
                        </form>
                    </a>
                </div>
            </div>

        </div>
        <div class="content-body">

            <section id="video-gallery" class="card">
                <!--   <div class="card-header">
                    <h4 class="card-title">blank</h4>
                    <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                    <div class="heading-elements">
                        <ul class="list-inline mb-0">
                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                            <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                        </ul>
                    </div>
                </div> -->
                <div class="card-content">
                    <div class="card-body">


                        <form action="<?= domain; ?>/genealogy/showout/admin" method="post" style="display: inline;">
                            <div class="input-group col-12">
                                <input type="text" required class="form-control" name="username" onkeyup="populate_option(this.value)" list="my_downlines" placeholder="Search your downline" aria-describedby="button-addon2">
                                <input type="hidden" name="tree_key" value="<?= $tree_key; ?>">
                                <div class="input-group-append" id="button-addon2">
                                    <button class="btn btn-secondary" type="submit">Go</button>
                                </div>
                            </div>
                        </form>

                        <datalist id="my_downlines">
                            <option value=""></option>
                        </datalist>

                        <section class="card">
                            <div class="card-content">
                                <div class="card-body">
                                    <style>
                                        #gfilter:hover {
                                            background: transparent;
                                        }

                                        .mlm_detail>tbody>tr>td {
                                            padding-top: 0px;
                                            padding-bottom: 0px;
                                        }


                                        .drop-down {
                                            position: relative !important;
                                        }

                                        .label {
                                            color: #63b4b4;
                                            font-size: 12px;
                                        }

                                        .label-value {
                                            color: <?= $light; ?>;
                                            font-size: 12px;
                                        }

                                        em {
                                            font-style: normal !important;
                                        }
                                    </style>



                                    <center style="overflow-x: scroll;">
                                        <ul class="tree" id="tree" style="width:100%;">
                                        </ul>
                                    </center>

                                    <script>
                                        $('.dropdown-menu').click(function(e) {
                                            e.stopPropagation();
                                        });
                                        $.ajax({
                                            type: "POST",
                                            url: $base_url + "/genealogy/fetch/<?= $user->id; ?>/<?= $tree_key; ?>/4/admin",
                                            data: null,
                                            contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
                                            processData: false, // NEEDED, DON'T OMIT THIS
                                            cache: false,
                                            success: function(data) {
                                                $("#tree").html(data.list);
                                            },
                                            error: function(data) {},
                                            complete: function() {}
                                        });


                                        populate_option = function($query) {

                                            if ($query.length < 2) {
                                                return;
                                            }

                                            $.ajax({
                                                type: "POST",
                                                url: "<?= domain; ?>/genealogy/search/" + $query + "/<?= $tree_key; ?>",
                                                data: null,
                                                success: function(data) {

                                                    $('#my_downlines').html(data.line);
                                                },
                                                error: function(data) {},
                                                complete: function() {}
                                            });


                                        }
                                    </script>

                                </div>
                            </div>
                        </section>






                    </div>
                </div>
            </section>



        </div>
    </div>
</div>
<!-- END: Content-->

<?php include 'includes/footer.php'; ?>