<?php use SiteSystemSynchronizer\Core;?>
<div class="row">
	<div class="col-lg-4 col-md-6">
		<form method="post" action="options.php" class="options">
			<?php
			settings_fields('s3-options');
			do_settings_sections(Core::getSlug());
			submit_button();
			?>
		</form>
	</div>
	<div class="col-md-1"></div>
	<div class="col-lg-4 col-md-6">
	
	</div>
</div>
