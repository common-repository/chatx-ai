<?php
/**
 * @var $widgets array
 * @var $date_text string
 * @var $date_current string
 * @var $date_tabs array
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<?php if ( !$is_registered ): ?>
	<div class="wrap chatxai-page chatxai-page--dashboard">
		<div class="chatxai-register-form"></div>

		<div class="chatxai-register-form-info">
			<h1 style="color: #303E56;">REGISTER / LOG IN</h1>
			<p style="color: #303E56;">In order to be able to use <strong>ChatX.ai</strong> you need to register first. To do this, <strong>press the button below</strong> and you will be taken to <strong>Log in with Facebook</strong> and select your shop's Facebook page.</p>
			<a href="<?php echo $register_url; ?>" class="button-primary" target="_blank">Connect with ChatX.ai</a>
		</div>
	</div>
<?php else: ?>

	<style type="text/css">
		#wpbody{
		    position: absolute;
		    left: 160px;
		    right: 0;
		    top: 0;
		    bottom: 0;
		}
		#wpbody-content{
			height: 100%;
		    position: absolute;
		    top: 0;
		    left: 0;
		    right: 0;
		    bottom: 0;
		    padding-bottom: 0;
		}
	</style>

	<iframe src="<?php echo $dashboardUrl; ?>" width="100%" height="100%" style="height: 100%; width: 100%; position: relative; z-index: 213456786543;"></iframe>

<?php endif; ?>
