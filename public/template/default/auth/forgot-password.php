<?php
$page_title = "Login";
include 'includes/auth_header.php'; ?>


<h2 class="mt-3 text-center">Forgot Password?</h2>
<p class="text-center">To reset your password, please enter the email associated with your account.</p>




<div class="" style="padding-top: 0px;">
    <form data-toggle="validator" class="form-horizontal form-simple ajax_form" id="loginform" action="<?= domain; ?>/forgot-password/send_link" method="post">


        <fieldset class="form-group position-relative has-icon-left mb-1">
            <label>Your E-mail Address</label>
            <input type="email" class="form-control" placeholder="Email Address" name="user" required>
        </fieldset>

        <fieldset class="form-group position-relative has-icon-left">

            <div class="g-recaptcha form-group" data-sitekey="<?= SiteSettings::site_settings()['google_re_captcha_site_key']; ?>">
            </div>

        </fieldset>

        <button type="submit" class="btn btn-primary btn-lg btn-block"> Submit</button>
    </form>
</div>
<p class="text-center">Don't have an account ? <a href="<?= domain; ?>/register" class="card-link">Register</a></p>
<p class="text-center">Go back to <a href="<?= domain; ?>/login" class="card-link"> Log In</a></p>

<?php include 'includes/auth_footer.php'; ?>