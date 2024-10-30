<?php
// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
	echo 'Hi there! I’m just a plugin, not much I can do when called directly.';
	exit;
}

$error = null;

if (isset($_POST['_wpnonce'])) {
	check_admin_referer('lickstats_log_in');
	if (isset($_POST['lickstats_log_in'], $_POST['lickstats_email'], $_POST['lickstats_password'])) {
		try {
			$data = Lickstats::log_in($_POST['lickstats_email'], $_POST['lickstats_password']);
		} catch (Exception $e) {
			$error = $e->getMessage();
		}
        if (isset($data) and isset($data->user->id) and isset($data->user->label) and isset($data->user->accountType)) {
            update_option('lickstats_active', true, true);
            update_option('lickstats_account_id', $data->user->id, true);
            update_option('lickstats_account_label', $data->user->label, true);
            update_option('lickstats_account_type', $data->user->accountType, true);
            update_option('lickstats_crossdomains', array(), true);
        }
	} else if (isset($_POST['lickstats_log_out'])) {
		update_option('lickstats_active', false, true);
		update_option('lickstats_account_id', null, true);
        update_option('lickstats_account_label', null, true);
		update_option('lickstats_account_type', null, true);
        update_option('lickstats_crossdomains', array(), true);
	} else if (isset($_POST['lickstats_save'], $_POST['lickstats_crossdomains'])) {
        try {
            $_domains = explode(',', $_POST['lickstats_crossdomains']);
            $domains  = array();
            foreach($_domains as $domain){
                $_domain = str_replace(array('http://', 'https://'), '', esc_url($domain));
                if(empty($_domain) or in_array($_domain, $domains)){
                    continue; //Domain exists, skipping
                }
                if(!preg_match('/^[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,24}$/', $_domain)){
                    throw new \Exception('Invalid domain ' . $_domain);
                }
                $domains[] = $_domain;
            }
            update_option('lickstats_crossdomains', count($domains) > 0 ? $domains : array(), true);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
	}
}
$account_id    = get_option('lickstats_account_id', null);
$account_label = get_option('lickstats_account_label', null);
$account_type  = get_option('lickstats_account_type', null);
$active        = get_option('lickstats_active', false);
$crossdomains  = get_option('lickstats_crossdomains', false);
?>
<h1>Lickstats plugin configuration</h1>
<form method="POST">
<?php wp_nonce_field('lickstats_log_in');?>
<?php if(empty($account_id) || empty($account_type)):?>
	<h3>Log in</h3>
	<table class="form-table">
	    <tbody>
	        <tr>
			    <th scope="row">
			        <label for="lickstats_email">Username</label>
			    </th>
			    <td>
			    	<input type="email" value="" id="lickstats_email" name="lickstats_email">
			    </td>
			</tr>
			<tr>
			    <th scope="row">
			        <label for="lickstats_password">Password</label>
			    </th>
			    <td>
			    	<input type="password" value="" id="lickstats_password" name="lickstats_password">
			    </td>
			</tr>
	    </tbody>
	</table><br>
    <?php if(!empty($error)):?>
        <div style="background:#f2dede; padding:12px;"><?php echo $error?></div><br>
    <?php endif;?>
    <button type="submit" name="lickstats_log_in" class="button-primary">Log in</button><br><br>
    <a href="https://app.lickstats.com/forgot" target="_blank">Forgot your password?</a><br><br>
    <a href="https://lickstats.com" target="_blank">Don’t have an account?</a>
<?php else:?>
	<h3>Account details</h3>
    <p><strong>Hello <?php echo htmlspecialchars($account_label);?></strong></p>
    <h3>Settings</h3>
	<table class="form-table">
	    <tbody>
            <tr>
                <th scope="row">
                    <label>Plugin status</label>
                </th>
                <td>
                    Enabled
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="lickstats_crossdomains">Cross-domains</label>
                </th>
                <td>
                    <input type="text" value="<?php echo htmlspecialchars(implode(', ', $crossdomains))?>" id="lickstats_crossdomains" name="lickstats_crossdomains"><br>
                    <span class="description">Comma separated domains</span>
                </td>
            </tr>
	    </tbody>
	</table>
    <?php if(!empty($error)):?>
        <div style="background: #f2dede; padding: 12px;"><?php echo $error?></div><br>
    <?php endif;?>
    <button type="submit" name="lickstats_save" class="button-primary">Save</button>
    <button type="submit" name="lickstats_log_out" class="button-secondary">Log out</button>
<?php endif;?>
</form>
