<?php

use League\ISO3166\ISO3166;; ?>
<div class="dropdown" style="display: block; margin-left:20px;">
    <button type="button" class="btn btn-dark btn-sm dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-filter"></i>
    </button>
    <div class="dropdown-menu" style="padding: 20px;">
        <form action="<?= $action ?? ''; ?>" method="get" id="filter_form">
            <div class="row">

                <div class="form-group col-6">
                    <label>Product name </label><br>
                    <input type="" name="name" placeholder="Name" class="form-control" value="<?= $sieve['name'] ?? ''; ?>">
                </div>


                <div class=" form-group col-6">
                    <label>Product Type</label>
                    <select class="form-control" name="type_of_product">
                        <option value="">Select</option>
                        <?php foreach (Products::$types_of_product as $value) : ?>
                            <option <?= @$sieve['type_of_product'] == $value ? 'selected' : ""; ?>><?= $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>



                <div class="form-group col-md-6">
                    <label>Country (Location) </label>
                    <select class="form-control" name="location" value="">
                        <option>Select</option>
                        <?php
                        $iso = (new ISO3166);
                        foreach ($iso->all() as $key => $country) : ?>
                            <option <?= $sieve['location'] == strtolower($country['name']) ? "selected" : ""; ?> value="<?= strtolower($country['name']); ?>"><?= $country['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>




                <div class="form-group col-6">

                </div>

                <div class=" form-group col-6">
                    <label>Price(From):</label>
                    <input placeholder="Start" type="number" value="<?= $sieve['price']['start'] ?? ''; ?>" class="form-control" name="price[start]">
                </div>


                <div class=" form-group col-6">
                    <label>Price (To)</label>
                    <input type="number" placeholder="End " value="<?= $sieve['price']['end'] ?? ''; ?>" class="form-control" name="price[end]">
                </div>



                <div class=" form-group col-12">
                    Sort
                </div>
                <div class=" form-group col-6">
                    <label>Sort By</label>
                    <select class="form-control" name="sort[by]">
                        <option value="">Select</option>
                        <?php foreach (Products::$sortby as $value) : ?>
                            <option <?= @$sieve['sort']['by'] == $value ? 'selected' : ""; ?>><?= $value; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class=" form-group col-6">
                    <label>Asc or Desc</label>
                    <select class="form-control" name="sort[by]">
                        <option value="">Select</option>
                        <?php foreach (["ascending", "descending"] as $dir) : ?>
                            <option <?= @$sieve['sort']['direction'] == $dir ? 'selected' : ""; ?>><?= $dir; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>




            </div>


            <div class="form-group">
                <button type="Submit" class="btn btn-sm custom-border btn-dark">Apply</button>
                <button type="reset" class="btn-sm btn btn-outline-dark">Reset</button>
            </div>
        </form>

    </div>
</div>