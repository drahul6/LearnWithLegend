<?php


$page_title = "Sign in";
include 'includes/auth_header.php'; ?>


<h2 class="mt-3 text-center">Sign In</h2>
<p class="text-center">Enter your email address and password.</p>

<div class="" style="padding-top: 0px;">
    <form data-toggle="validator" class="" id="loginform" action="<?= domain; ?>/login/authenticate" method="post">

        <?php if (@$this->inputError('user_login') != '') : ?>
            <center class="alert alert-danger">
                <?= $this->inputError('user_login'); ?>
            </center>
        <?php endif; ?>

        <fieldset class="form-group position-relative has-icon-left mb-1">
            <label>Username</label>
            <input type="text" class="form-control" placeholder="Username or Email" name="user">

        </fieldset>

        <fieldset class="form-group position-relative has-icon-left">
            <label>Password</label>
            <input type="password" class="form-control" name="password" placeholder="Enter Password" required>
            <a href="<?= domain; ?>/forgot-password" class="card-link"> Forgot Password ?</a>
        </fieldset>


        <fieldset class="form-group position-relative has-icon-left">

            <div class="g-recaptcha form-group" data-sitekey="<?= SiteSettings::site_settings()['google_re_captcha_site_key']; ?>">
            </div>


        </fieldset>



        <button type="submit" class="btn btn-dark  btn-block"> Log in</button>
    </form>
</div>

<p class="col-lg-12 text-center mt-5">Don't have an account? <a href="<?= domain; ?>/register" class="card-link">Register now</a></p>


<?php include 'includes/auth_footer.php'; ?>