<style>
ul.anomify-form-error {

}
ul.anomify-form-error li {
	margin-left: 1.5em;
	list-style: disc;
}
</style>
<div class="wrap">
<h1>Anomify Options</h1>
<p>Your Metric Data Key and Metric Data URL are provided in your Anomify account.</p>
<?php if (true == Anomify_Config::getInstance()->getIsValid()): ?>
<p><strong>Your config is valid.</strong></p>
<?php else: ?>
<p>If you have not yet signed up for Anomify, <a href="https://dashboard.anomify.ai/" title="Sign up for Anomify">sign up here</a>.</p>
<?php endif; ?>
<?php if (0 != ($iFormErrors = Anomify_Config::getInstance()->getNumFormErrors())): ?>
<p><strong>There were errors in your information and your details were not updated:</strong></p>
<ul class="anomify-form-error">
<?php

	foreach (Anomify_Config::getInstance()->getFormErrors() as $sKey=>$sError) {
		printf("<li>%s</li>\n", $sError);
	}

?>
</ul>
<?php endif; ?>
<form method="POST">
<input type="hidden" name="_section" value="Main" />
<table class="form-table" role="presentation">
<tbody>
<tr>
	<th scope="row"><label for="<?php echo Anomify_Config::FORM_FIELD_ENABLED; ?>">Enabled</label></th>
	<td><select id="<?php echo Anomify_Config::FORM_FIELD_ENABLED; ?>" name="<?php echo Anomify_Config::FORM_FIELD_ENABLED; ?>">
		<option value="1"<?php echo (true == Anomify_Config::getInstance()->getEnabled()) ? ' selected="selected"' : ''; ?>>Yes</option>
		<option value="0"<?php echo (false == Anomify_Config::getInstance()->getEnabled()) ? ' selected="selected"' : ''; ?>>No</option>
	</select></td>
</tr>
<tr>
	<th scope="row"><label for="<?php echo Anomify_Config::FORM_FIELD_API_KEY; ?>">Metric Data Key</label></th>
	<td><input size="64" id="<?php echo Anomify_Config::FORM_FIELD_API_KEY; ?>" name="<?php echo Anomify_Config::FORM_FIELD_API_KEY; ?>" value="<?php echo Anomify_Config::getInstance()->getApiKey(); ?>" /></td>
</tr>
<tr>
	<th scope="row"><label for="<?php echo Anomify_Config::FORM_FIELD_DATA_URL; ?>">Metric Data Endpoing</label></th>
	<td><input size="64" id="<?php echo Anomify_Config::FORM_FIELD_DATA_URL; ?>" name="<?php echo Anomify_Config::FORM_FIELD_DATA_URL; ?>" value="<?php echo Anomify_Config::getInstance()->getDataUrl(); ?>" /></td>
</tr>
<tr><th colspan="2">Third-party plugin integrations</th></tr>
<tr><td colspan="2">Anomify can send metrics created by the following third-party plugins to the Anomify service to run anomaly detection on them. These plugins must be installed separately first.</td></tr>
<?php foreach (Anomify_Config::getInstance()->get3pPluginIntegrationsAvailable() as $s3pPluginKey => $s3pPluginName): ?>
<tr>
	<th scope="row"><label for="3p-plugin-<?php echo $s3pPluginKey; ?>-enabled"><a href="https://wordpress.org/plugins/<?php echo $s3pPluginKey; ?>/"><?php echo $s3pPluginName; ?></a></label></th>
	<td><select id="3p-plugin-<?php echo $s3pPluginKey; ?>-enabled" name="3p-plugin-<?php echo $s3pPluginKey; ?>-enabled">
		<option value="1"<?php echo (true == Anomify_Config::getInstance()->get3pPluginIntegrationEnabled($s3pPluginKey)) ? ' selected="selected"' : ''; ?>>Enabled</option>
		<option value="0"<?php echo (false == Anomify_Config::getInstance()->get3pPluginIntegrationEnabled($s3pPluginKey)) ? ' selected="selected"' : ''; ?>>Disabled</option>
	</select></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes" /></p>
</form>
<?php if (0): ?>
<h2>Info/Debug</h2>
<table class="form-table" role="presentation">
<tr>
	<th scope="row">cURL is available</th>
	<td><?php echo (true == Anomify_Utils::curlIsAvailable()) ? 'Yes' : 'No'; ?></td>
</tr>
<tr>
	<th scope="row">Data directory exists and is writable</th>
	<td><?php echo (true == Anomify_Utils::dataDirIsWritable()) ? 'Yes' : 'No'; ?></td>
</tr>
</table>
<?php endif; ?>
</div>
