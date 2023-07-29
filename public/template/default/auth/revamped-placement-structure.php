<?php
$page_title = "Forced Tree";
include 'includes/header.php';

$max_uplevel = $user->max_uplevel('binary')['max_level'];
?>

<div class="page-wrapper">

    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Forced Tree</h3>
            </div>
            <div class="col-5 align-self-center">

                <div class="dropdown float-right">
                    <button type="button" style="cursor: pointer;" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-chevron-down "></i> Filters
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item " href="<?= domain; ?>/genealogy/placement/<?= $auth->username; ?>/binary/2">
                            <i class="fa fa-sitemap "></i> My Genealogy </a>
                        <a class="dropdown-item " href="<?= domain; ?>/genealogy/last/0/<?= $tree_key; ?>/<?= $user->username; ?>"> <i class=" ft-chevrons-left"></i> Last Left</a>
                        <a class="dropdown-item " href="<?= domain; ?>/genealogy/last/1/<?= $tree_key; ?>/<?= $user->username; ?>"><i class=" ft-chevrons-right"></i>Last Right</a>
                        <a href="#" id="gfilter" class="dropdown-item " class="text-center">

                            <i class="ft-corner-left-up "></i>
                            <label class="">Level up</label>
                            <form action="<?= domain; ?>/genealogy/up" method="post">
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


        <datalist id="my_downlines">
            <option value=""></option>
        </datalist>

        <section class="card">
            <div class="card-header">

                <form action="<?= domain; ?>/genealogy/showout" method="post" style="display: inline;">
                    <div class="input-group col-12">
                        <input type="text" required class="form-control" name="username" onkeyup="populate_option(this.value)" list="my_downlines" placeholder="Search your downline" aria-describedby="button-addon2">
                        <input type="hidden" name="tree_key" value="<?= $tree_key; ?>">
                        <div class="input-group-append" id="button-addon2">
                            <button class="btn btn-secondary" type="submit">Go</button>
                        </div>
                    </div>
                </form>



            </div>
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
                            url: $base_url + "/genealogy/fetch/<?= $user->id; ?>/<?= $tree_key; ?>/4",
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
                                url: `<?= domain; ?>/genealogy/search/${$query}/<?= $tree_key; ?>/`,
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

    <?php include 'includes/footer.php'; ?>