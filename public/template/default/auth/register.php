<?php

$page_title = "Get Started";
include 'includes/auth_header.php'; ?>


<h2 class="mt-3 text-center">Register</h2>
<div class="" style="padding-top: 0px;">


    <!-- <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">Notice!</h4>
        Registration is currently on hold!
    </div>
 -->


    <form data-toggle="validator" class="" id="loginform" action="<?= domain; ?>/register/register" method="post">

        <?= $this->csrf_field(); ?>

        <div class="row">

            <fieldset class="form-group col-md-12">
                <label>Username</label>
                <input type="" required="" class="form-control " value="<?= @Input::old('username'); ?>" name="username" placeholder="User Name">
                <span class="text-danger"><?= @$this->inputError('username'); ?></span>
            </fieldset>



            <fieldset class="form-group col-md-6">
                <label>First name</label>
                <input type="" required="" class="form-control " value="<?= @Input::old('firstname'); ?>" name="firstname" placeholder="First Name">
                <span class="text-danger"><?= @$this->inputError('firstname'); ?></span>
            </fieldset>



            <fieldset class="form-group col-md-6">
                <label>Last name</label>
                <input type="" required="" class="form-control " value="<?= @Input::old('lastname'); ?>" name="lastname" placeholder="Last Name">
                <span class="text-danger"><?= @$this->inputError('lastname'); ?></span>
            </fieldset>


            <fieldset class="form-group col-md-6">
                <label>E-mail</label>
                <input type="email" required="" class="form-control " value="<?= @Input::old('email'); ?>" name="email" placeholder="Email">
                <span class="text-danger"><?= @$this->inputError('email'); ?></span>
            </fieldset>


            <fieldset class="form-group col-md-6">
                <label>Phone</label>
                <input type="" required="" class="form-control " value="<?= @Input::old('phone'); ?>" name="phone" placeholder="Phone">
                <span class="text-danger"><?= @$this->inputError('phone'); ?></span>
            </fieldset>

            <!-- 
                                <fieldset class="form-group col-md-12">
                                    <input type="date" required="" class="form-control " value="<?= @Input::old('birthdate'); ?>" name="birthdate" placeholder="Birth Date">
                                    <span class="text-danger"><?= @$this->inputError('birthdate'); ?></span>
                                </fieldset> -->


            <?php
            $cookie_name = Config::cookie_name();
            if (isset($_COOKIE[$cookie_name])) {
                $introduced_by = $_COOKIE[$cookie_name];
                $readonly   = "readonly='readonly'";
            } else {
                $introduced_by = Input::old('introduced_by');
                $readonly = '';
            }; ?>




            <fieldset class="form-group col-md-6">
                <label>Sponsor</label>
                <input type="text" <?= $readonly; ?> class="form-control " value="<?= $introduced_by; ?>" name="introduced_by" placeholder="Sponsor">
                <span class="text-danger"><?= @$this->inputError('introduced_by'); ?></span>
            </fieldset>


            <!-- 
                                <fieldset class="form-group col-md-12">
                                   <select class="form-control " name="country" required="">
                                    <option value="">Select Country</option>
                                    <?php ""; ?> foreach (World\Country::all() as $key => $country) : ?>
                                      <option <?= (Input::old('country') == $country->id) ? 'selected' : ''; ?> value="<?= $country->id; ?>"><?= $country->name; ?></option>
                                    <?php ""; ?>
                                     endforeach; ?>
                                  </select>
                                    <span class="text-danger"><?= @$this->inputError('country'); ?></span>

                                </fieldset>
    -->



            <fieldset class="form-group col-md-6">
                <label>Password</label>
                <input type="password" name="password" class="form-control " placeholder="Enter Password" required>
            </fieldset>


            <?php if ($settings['only_paid_members_registration'] == true) : ?>



                <fieldset class="form-group col-md-12 mt-3">
                    <div class="">
                        <div class="-body">
                            <h4 class="card-title">Join Learn with Legends Beta team</h4>
                            <!--   <p class="card-text">This is a wider card with supporting text below as a natural
                                lead-in to additional content. This card has even longer content than the first
                                to show that equal height action.</p>
                            -->
                            <ul>
                                <li>
                                    Just $20 one-time fee to join
                                </li>
                                <li>
                                    Buy and sell on our marketplace
                                </li>
                                <li>
                                    Play a valuable role in a community-based project
                                </li>
                                <li>
                                    Get a share of our revenue monthly, up to $5000P/M
                                </li>
                                <li>
                                    Be the first to use our resources before we go live
                                </li>
                                <li>
                                    Learn the skills to earn online in your own time
                                </li>
                                <li>
                                    And much more...
                                </li>
                            </ul>

                            <fieldset class="form-group col-md-12">
                                <!-- <input class="form-control" type="hidden" value="paypal" name="membership[payment_method]" required> -->
                                <label>Payment method *</label>
                                <select class="form-control" name="membership[payment_method]" required>
                                    <option value="">Select Payment method</option>
                                    <?php foreach ($shop->get_available_payment_methods() as $key => $option) : ?>
                                        <option value="<?= $key; ?>"><?= $option['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">* you will be redirected to pay for membership</small>

                            </fieldset>


                        </div>
                    </div>

                </fieldset>
                <fieldset class="form-group col-md-6">
                    <input class="form-control" type="hidden" value="2" name="membership[id]" required>

                    <!--
                    <label>Membership *</label>
                         <select class="form-control" name="membership[payment_method]" required>
                        <option value="">Select membership</option>
                        <?php foreach (SubscriptionPlan::PaidMembership()->get() as $key => $option) : ?>
                            <option value="<?= $key; ?>"><?= $option['name']; ?> $<?= $option['price']; ?> </option>
                        <?php endforeach; ?>
                    </select> -->
                </fieldset>

            <?php endif; ?>

            <fieldset class="form-group col-md-12 ">
                <!--                                     <label>Confirm Password</label>
                                    <input type="password" name="confirm_password" class="form-control " placeholder="Confirm Your Password" required>
    -->

                <label>
                    <input type="checkbox" name="terms_and_condition" required="" value="yes">I agree with the
                    <a href="javacript:void(0);" data-toggle="modal" data-target="#terms_and_condition">Terms and conditions</a>
                </label>
                <br>
                <label>
                    <input type="checkbox" name="affiliate_agreement" required="" value="yes">I agree with the
                    <a href="javacript:void(0);" data-toggle="modal" data-target="#affiliate_agreement">Affiliate agreement</a>
                </label>
            </fieldset>


            <fieldset class="form-group col-md-12 ">
                <div class="g-recaptcha form-group" data-sitekey="<?= SiteSettings::site_settings()['google_re_captcha_site_key']; ?>"></div>

            </fieldset>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block"> Register</button>


    </form>
</div>

<div class="modal " id="terms_and_condition">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Terms and condition</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- <iframe style="border: none; overflow-x:hidden;" src="https://onedrive.live.com/embed?cid=3B7B87099F764B86&resid=3B7B87099F764B86%219983&authkey=AExZB9O3Kx697Mg&em=2" width="100%" height="500" frameborder="0" scrolling="no"></iframe> -->

                <div style="overflow-y:scroll;max-height:500px;" class="card-body">
                    <?= CMS::fetch('terms_and_condition'); ?>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal " id="affiliate_agreement">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Affiliate Agreement</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                <!-- <div style="border: none; overflow-x:hidden;" src="https://onedrive.live.com/embed?cid=3B7B87099F764B86&resid=3B7B87099F764B86%219981&authkey=AIz4dNKma-nzDj8&em=2" width="100%" height="500" frameborder="0" scrolling="no"> -->
                <div style="overflow-y:scroll;max-height:500px;" class="card-body">
                    <?= CMS::fetch('affiliate_agreement'); ?>
                </div>
            </div>
        </div>
    </div>
</div>


<p class="col-lg-12 text-center mt-5">Already have an account ? <a href="<?= domain; ?>/login" class="card-link">Login</a></p>
<?php include 'includes/auth_footer.php'; ?>