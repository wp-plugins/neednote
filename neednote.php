<?php
/*
Plugin Name: Neednote.it
Plugin URI: http://neednote.godado.it
Description: Aggiunge automaticamente i pulsanti o i form di NeedNote.it ai post. E' possibile scegliere le posizioni in cui vuoi che lo script appaia in automatico e si può anche decidere di non farlo visualizzare in qualche post specifico.
Alta personalizzazione in base alle esigenze del proprio blog.
Version: 1.1
Author: Giuseppe Argento
Author URI: http://www.4mj.it
*/
// Pre-2.6 compatibility
if ( !defined('WP_CONTENT_URL') )
    define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
 
// Guess the location
$neednotepluginpath = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/';
function neednote_init_locale(){
	load_plugin_textdomain('neednote', $neednotepluginpath);
}
add_filter('init', 'neednote_init_locale');
$neednote_known_sites = Array(
	'Icona' => Array(
		'favicon' => '2.gif',
		'url' => 'http://neednote.godado.it/sharing-js.php?act=BTN&id=NEEDID&bt=NEEDICONA',
	),
	'Crea domanda' => Array(
		'favicon' => '2.gif',
		'url' => 'http://neednote.godado.it/sharing-js.php?act=FORM&id=NEEDID&w=NEEDLG&color=NEEDCOLORE1',
	),
	'Domande correlate' => Array(
		'favicon' => '2.gif',
		'url' => 'http://neednote.godado.it/sharing-js.php?act=LASTQUEST&id=NEEDID&w=NEEDLG2&color=NEEDCOLORE2',
	),
);
$neednote_files = Array(
	'description_selection.js',
	'neednote-admin.css',
	'neednote.php',
	'images/',
	'images/1.gif',
	'images/2.gif',
	'images/3.gif',
	'images/4.gif',
	'images/5.gif',
	'images/6.gif',
	'images/7.gif',
	'images/8.gif',
	'images/9.gif',
	'images/10.gif',
	'images/11.gif',
	'images/12.gif',
	'images/13.gif',
	'images/14.gif',
	'images/15.gif',
	'images/16.gif',
	'images/17.gif',
	'images/18.gif',
	'images/19.gif',
	'images/20.gif',
	'images/21.gif',
	'images/22.gif',
	'images/23.gif',
	'images/24.gif',
	'images/25.gif',
);
function neednote_html($display=Array()) {
	global $neednote_known_sites, $neednotepluginpath, $wp_query, $post; 
	$neednoteooffmeta = get_post_meta($post->ID,'neednoteoff',true);
	if ($neednoteooffmeta == "true") {
		return "";
	}
	$active_sites = get_option('neednote_active_sites');
	$html = "";
	$imagepath = $neednotepluginpath.'images/';
	// if no sites are specified, display all active
	// have to check $active_sites has content because WP
	// won't save an empty array as an option
	if (empty($display) and $active_sites)
		$display = $active_sites;
	// if no sites are active, display nothing
	if (empty($display))
		return "";
	// Load the post's data
	$blogname 	= urlencode(get_bloginfo('name')." ".get_bloginfo('description'));
	$post 		= $wp_query->post;
	
	$excerpt	= $post->post_excerpt;
	if ($excerpt == "") {
		$excerpt = urlencode(substr(strip_tags($post->post_content),0,250));
	}
	$excerpt	= str_replace('+','%20',$excerpt);
	
	$permalink 	= urlencode(get_permalink($post->ID));
	
	$title 		= urlencode($post->post_title);
	$title 		= str_replace('+','%20',$title);
	$html .= "\n<div class=\"neednote\">\n";
	
	$tagline = get_option("neednote_tagline");
	if ($tagline != "") {
		$html .= "<div class=\"neednote_tagline\">\n";
		$html .= stripslashes($tagline);
		$html .= "\n</div>";
	}
	$needid = get_option("neednote_needid");
	$needicona = get_option("neednote_needicona");
	$needcolore1 = get_option("neednote_needcolore1");
	$needcolore1 = str_replace('#','',$needcolore1);
	$needcolore2 = get_option("neednote_needcolore2");
	$needcolore2 = str_replace('#','',$needcolore2);
	$needlg = get_option("neednote_needlg");
	$needlg2 = get_option("neednote_needlg2");
	
	$html .= "\n<ul>\n";
	foreach($display as $sitename) {
		// if they specify an unknown or inactive site, ignore it
		if (!in_array($sitename, $active_sites))
			continue;
		$site = $neednote_known_sites[$sitename];
		$url = $site['url'];
		$url = str_replace('PERMALINK', $permalink, $url);
		$url = str_replace('NEEDID', $needid, $url);
		$url = str_replace('NEEDICONA', $needicona, $url);
		$url = str_replace('NEEDCOLORE1', $needcolore1, $url);
		$url = str_replace('NEEDCOLORE2', $needcolore2, $url);
		$url = str_replace('NEEDLG', $needlg, $url);
		$url = str_replace('NEEDLG2', $needlg2, $url);
		$url = str_replace('TITLE', $title, $url);
		$url = str_replace('BLOGNAME', $blogname, $url);
		$url = str_replace('EXCERPT', $excerpt, $url);
		if (isset($site['description']) && $site['description'] != "") {
			$description = $site['description'];
		} else {
			$description = $sitename;
		}
		$link = "<li>";		
		$link .= "<script type=\"text/javascript\"";
		$link .= " src=\"$url\">";
		$link .= "<img src=\"".$imagepath.$site['favicon']."\" title=\"$description\" alt=\"$description\" class=\"neednote-hovers";
		if ($site['class'])
			$link .= " neednote_{$site['class']}";
		$link .= "\" />";
		$link .= "</script></li>";
		
		$html .= "\t".apply_filters('neednote_link',$link)."\n";
	}
	$html .= "</ul>\n</div>\n";
	return $html;
}
// Hook the_content to output html if we should display on any page
$neednote_contitionals = get_option('neednote_conditionals');
if (is_array($neednote_contitionals) and in_array(true, $neednote_contitionals)) {
	add_filter('the_content', 'neednote_display_hook');
	add_filter('the_excerpt', 'neednote_display_hook');
	
	function neednote_display_hook($content='') {
		$conditionals = get_option('neednote_conditionals');
		if ((is_home()     and $conditionals['is_home']) or
		    (is_single()   and $conditionals['is_single']) or
		    (is_page()     and $conditionals['is_page']) or
		    (is_category() and $conditionals['is_category']) or
			(is_tag() 	   and $conditionals['is_tag']) or
		    (is_date()     and $conditionals['is_date']) or
		    (is_search()   and $conditionals['is_search'])) {
			$content .= neednote_html();
		}
		return $content;
	}
}
// Plugin config/data setup
register_activation_hook(__FILE__, 'neednote_activation_hook');
function neednote_activation_hook() {
	return neednote_restore_config(False);
}
// restore built-in defaults, optionally overwriting existing values
function neednote_restore_config($force=False) {
	// Load defaults, taking care not to smash already-set options
	global $neednote_known_sites;
	if ($force or !is_array(get_option('neednote_active_sites')))
		update_option('neednote_active_sites', array(
			'Icona',
		));
	// tagline defaults to a Hitchiker's Guide to the Galaxy reference
	if ($force or !is_string(get_option('neednote_tagline')))
		update_option('neednote_tagline', "<strong>" . __("Hai domande su questo argomento?", 'neednote') . "</strong>");
	// needid
	if ($force or !is_string(get_option('neednote_needid')))
		update_option('neednote_needid', "" . __("0", 'neednote') . "");
	// needicona
	if ($force or !is_string(get_option('neednote_needicona')))
		update_option('neednote_needicona', "" . __("1", 'neednote') . "");
	// needcolore1
	if ($force or !is_string(get_option('neednote_needcolore1')))
		update_option('neednote_needcolore1', "" . __("#000000", 'neednote') . "");
	// needcolore2
	if ($force or !is_string(get_option('neednote_needcolore2a')))
		update_option('neednote_needcolore2', "" . __("#000000", 'neednote') . "");
	// needlg
	if ($force or !is_string(get_option('neednote_needlg')))
		update_option('neednote_needlg', "" . __("200", 'neednote') . "");
	// needlg2
	if ($force or !is_string(get_option('neednote_needlg2')))
		update_option('neednote_needlg2', "" . __("200", 'neednote') . "");
	// only display on single posts and pages by default
	if ($force or !is_array(get_option('neednote_conditionals')))
		update_option('neednote_conditionals', array(
			'is_home' => False,
			'is_single' => True,
			'is_page' => True,
			'is_category' => False,
			'is_tag' => False,
			'is_date' => False,
			'is_search' => False,
		));
}
// Hook the admin_menu display to add admin page
add_action('admin_menu', 'neednote_admin_menu');
function neednote_admin_menu() {
	add_submenu_page('options-general.php', 'neednote', 'Neednote', 8, 'neednote', 'neednote_submenu');
}
// Admin page header
add_action('admin_head', 'neednote_admin_head');
function neednote_admin_head() {
	if ($_GET['page'] == 'neednote') {
		global $neednotepluginpath, $wp_version;
		if ($wp_version < "2.6") { 
			echo '<script language="JavaScript" type="text/javascript" src="'.$neednotepluginpath.'jquery/jquery.js"></script>';
		} 
	?>
	<script language="JavaScript" type="text/javascript" src="<?php echo $neednotepluginpath; ?>jquery.colorPicker.js"></script>
	<script language="JavaScript" type="text/javascript"><!--
	jQuery(document).ready(function(){
	  jQuery("#neednote_site_list").sortable({});
	});
	/* make checkbox action prettier */
	function toggle_checkbox(id) {
		var checkbox = document.getElementById(id);
		checkbox.checked = !checkbox.checked;
		if (checkbox.checked)
			checkbox.parentNode.className = 'active';
		else
			checkbox.parentNode.className = 'inactive';
	}
	--></script>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $neednotepluginpath; ?>neednote-admin.css" />
	<script language="JavaScript" type="text/javascript" src="<?php echo $neednotepluginpath; ?>jquery/ui.core.js"></script>
	<script language="JavaScript" type="text/javascript" src="<?php echo $neednotepluginpath; ?>jquery/ui.sortable.js"></script>
<?php
	}
}
function neednote_message($message) {
	echo "<div id=\"message\" class=\"updated fade\"><p>$message</p></div>\n";
}
// Sanity check the upload worked
function neednote_upload_errors() {
	global $neednote_files;
	$cwd = getcwd(); // store current dir for restoration
	if (!@chdir('../wp-content/plugins'))
		return __("Couldn't find wp-content/plugins folder. Please make sure WordPress is installed correctly.", 'neednote');
	if (!is_dir('neednote'))
		return __("Can't find neednote folder.", 'neednote');
	chdir('neednote');
	foreach($neednote_files as $file) {
		if (substr($file, -1) == '/') {
			if (!is_dir(substr($file, 0, strlen($file) - 1)))
				return __("Can't find folder:", 'neednote') . " <kbd>$file</kbd>";
		} else if (!is_file($file))
		return __("Can't find file:", 'neednote') . " <kbd>$file</kbd>";
	}
	$header_filename = '../../themes/' . get_option('template') . '/header.php';
	if (!file_exists($header_filename) or strpos(@file_get_contents($header_filename), 'wp_head()') === false)
		return __("Your theme isn't set up for neednote to load its style. Please edit <kbd>header.php</kbd> and add a line reading <kbd>&lt?php wp_head(); ?&gt;</kbd> before <kbd>&lt;/head&gt;</kbd> to fix this.", 'neednote');
	chdir($cwd); // restore cwd
	return false;
}
function neednote_meta() {
	global $post;
	$neednoteoff = false;
	$neednoteoffmeta = get_post_meta($post->ID,'neednoteoff',true);
	if ($neednoteoffmeta == "true") {
		$neednoteoff = true;
	}
	?>
	<input type="checkbox" name="neednoteoff" <?php if ($neednoteoff) { echo 'checked="checked"'; } ?>/> Disabilita neednote 
	<?php
}
function neednote_option() {
	global $post;
	$neednoteoff = false;
	$neednoteoffmeta = get_post_meta($post->ID,'neednoteoff',true);
	if ($neednoteoffmeta == "true") {
		$neednoteoff = true;
	}
	if ( current_user_can('edit_posts') ) { ?>
	<fieldset id="neednoteoption" class="dbx-box">
	<h3 class="dbx-handle">neednote</h3>
	<div class="dbx-content">
		<input type="checkbox" name="neednoteon" <?php if ($neednoteoff) { echo 'checked="checked"'; } ?>/> neednote disabled?
	</div>
	</fieldset>
	<?php 
	}
}
function neednote_meta_box() {
	// Check whether the 2.5 function add_meta_box exists, and if it doesn't use 2.3 functions.
	if ( function_exists('add_meta_box') ) {
		add_meta_box('neednote','neednote','neednote_meta','post');
		add_meta_box('neednote','neednote','neednote_meta','page');
	} else {
		add_action('dbx_post_sidebar', 'neednote_option');
		add_action('dbx_page_sidebar', 'neednote_option');
	}
}
add_action('admin_menu', 'neednote_meta_box');
function neednote_insert_post($pID) {
	if (isset($_POST['neednoteoff'])) {
		add_post_meta($pID,'neednoteoff',"true", true) or update_post_meta($pID, 'neednoteoff', "true");
	} else {
		add_post_meta($pID,'neednoteoff',"false", true) or update_post_meta($pID, 'neednoteoff', "false");
	}
}
add_action('wp_insert_post', 'neednote_insert_post');
// The admin page
function neednote_submenu() {
	global $neednote_known_sites, $neednote_date, $neednote_files, $neednotepluginpath;
	// update options in db if requested
	if ($_REQUEST['restore']) {
		check_admin_referer('neednote-config');
		neednote_restore_config(True);
		neednote_message(__("Impostazioni di Default ripristinate.", 'neednote'));
	} else if ($_REQUEST['save']) {
		check_admin_referer('neednote-config');
		// update active sites
		$active_sites = Array();
		if (!$_REQUEST['active_sites'])
			$_REQUEST['active_sites'] = Array();
		foreach($_REQUEST['active_sites'] as $sitename=>$dummy)
			$active_sites[] = $sitename;
		update_option('neednote_active_sites', $active_sites);
		// have to delete and re-add because update doesn't hit the db for identical arrays
		// (sorting does not influence associated array equality in PHP)
		delete_option('neednote_active_sites', $active_sites);
		add_option('neednote_active_sites', $active_sites);
		if ($_POST['usetargetblank']) {
			update_option('neednote_usetargetblank',true);
		} else {
			update_option('neednote_usetargetblank',false);
		}
		
		// update conditional displays
		$conditionals = Array();
		if (!$_POST['conditionals'])
			$_POST['conditionals'] = Array();
		
		$curconditionals = get_option('neednote_conditionals');
		if (!array_key_exists('is_feed',$curconditionals)) {
			$curconditionals['is_feed'] = false;
		}
		foreach($curconditionals as $condition=>$toggled)
			$conditionals[$condition] = array_key_exists($condition, $_POST['conditionals']);
			
		update_option('neednote_conditionals', $conditionals);
		// update tagline
		if (!$_REQUEST['tagline'])
			$_REQUEST['tagline'] = "";
		update_option('neednote_tagline', $_REQUEST['tagline']);
		// update needid
		if (!$_REQUEST['needid'])
			$_REQUEST['needid'] = "";
		update_option('neednote_needid', $_REQUEST['needid']);
		// update needicona
		if (!$_REQUEST['needicona'])
			$_REQUEST['needicona'] = "";
		update_option('neednote_needicona', $_REQUEST['needicona']);
		// update needcolore1
		if (!$_REQUEST['needcolore1'])
			$_REQUEST['needcolore1'] = "";
		update_option('neednote_needcolore1', $_REQUEST['needcolore1']);
		// update needcolore2
		if (!$_REQUEST['needcolore2'])
			$_REQUEST['needcolore2'] = "";
		update_option('neednote_needcolore2', $_REQUEST['needcolore2']);
		// update needlg
		if (!$_REQUEST['needlg'])
			$_REQUEST['needlg'] = "";
		update_option('neednote_needlg', $_REQUEST['needlg']);
		// update needlg2
		if (!$_REQUEST['needlg2'])
			$_REQUEST['needlg2'] = "";
		update_option('neednote_needlg2', $_REQUEST['needlg2']);
		
		neednote_message(__("Saved changes.", 'neednote'));
	}
	if ($str = neednote_upload_errors())
		neednote_message("$str</p><p>" . __("In your plugins/neednote folder, you must have these files:", 'neednote') . ' <pre>' . implode("\n", $neednote_files) ); 
	
	// show active sites first and in order
	$active_sites = get_option('neednote_active_sites');
	$active = Array(); $disabled = $neednote_known_sites;
	foreach($active_sites as $sitename) {
		$active[$sitename] = $disabled[$sitename];
		unset($disabled[$site]);
	}
	uksort($disabled, "strnatcasecmp");
	// load options from db to display
	$tagline 		= stripslashes(get_option('neednote_tagline'));
	$needid 		= stripslashes(get_option('neednote_needid'));
	$needicona 		= stripslashes(get_option('neednote_needicona'));
	$needcolore1 		= stripslashes(get_option('neednote_needcolore1'));
	$needcolore2 		= stripslashes(get_option('neednote_needcolore2'));
	$needlg 		= stripslashes(get_option('neednote_needlg'));
	$needlg2 		= stripslashes(get_option('neednote_needlg2'));
	$conditionals 	= get_option('neednote_conditionals');
	$updated 		= get_option('neednote_updated');
	$usetargetblank = get_option('neednote_usetargetblank');
	// display options
?>
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<?php
	if ( function_exists('wp_nonce_field') )
		wp_nonce_field('neednote-config');
?>
<div class="wrap">
	<h2><?php _e("Opzioni Neednote", 'neednote'); ?></h2>
	<table class="form-table">
	<tr>
		<th scope="row" valign="top">
			Id Neednote:
		</th>
		<td>
			<?php _e("Inserisci il tuo id di Neednote.", 'neednote'); ?><br/>
			<input size="80" type="text" name="needid" value="<?php echo htmlspecialchars($needid); ?>" /><br/>
			"Per conoscere il tuo ID Sharing vai alla pagina <a target=_blank" href="http://neednote.godado.it/sharing.php">http://neednote.godado.it/sharing.php</a>"
		</td>
	</tr>
	<tr>
		<th style="margin-bottom:0; border-bottom-width:0;"><?php _e("Opzioni:", "neednote"); ?></th>
		<td style="margin-bottom:0; border-bottom-width:0;"><?php _e("Seleziona il metodo che vuoi utilizzare. Puoi anche trascinare le icone per decidere l'ordine", 'neednote'); ?><br/><br/>
			<ul id="neednote_site_list">
				<?php foreach (array_merge($active, $disabled) as $sitename=>$site) { ?>
					<li style="font-size:10px;"
						id="<?php echo $sitename; ?>"
						class="neednote_site <?php echo (in_array($sitename, $active_sites)) ? "active" : "inactive"; ?>">
						<input
							type="checkbox"
							id="cb_<?php echo $sitename; ?>"
							class="checkbox"
							name="active_sites[<?php echo $sitename; ?>]"
							onclick="javascript:toggle_checkbox('<?php echo $sitename; ?>');"
							<?php echo (in_array($sitename, $active_sites)) ? ' checked="checked"' : ''; ?>
						/>
						<img src="<?php echo $neednotepluginpath.'images/'.$site['favicon']; ?>" width="16" height="16" alt="" />
						<?php print $sitename; ?>
					</li>
				<?php } ?>
			</ul>
			<input type="hidden" id="site_order" name="site_order" value="<?php echo join('|', array_keys($neednote_known_sites)) ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			Icona:<br/>
<div class="needicona"><input style="display:none" name="needicona" type="radio" id="select-i" value="<?php echo $needicona; ?>" checked><img src="<?php echo $neednotepluginpath.'images/'.$needicona.'.gif'; ?>" /></input></div>
		</th>
		<td>
			<?php _e("Utilizza l'icona che preferisci.", 'neednote'); ?><br/><br/>
<ul id="neednote_site_list2" class="ui-sortable" style="position: relative;">
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="1" ><img src="<?php echo $neednotepluginpath.'images/1.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="2" ><img src="<?php echo $neednotepluginpath.'images/2.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="3" ><img src="<?php echo $neednotepluginpath.'images/3.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="4" ><img src="<?php echo $neednotepluginpath.'images/4.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="5" ><img src="<?php echo $neednotepluginpath.'images/5.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="6" ><img src="<?php echo $neednotepluginpath.'images/6.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="7" ><img src="<?php echo $neednotepluginpath.'images/7.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="8" ><img src="<?php echo $neednotepluginpath.'images/8.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="9" ><img src="<?php echo $neednotepluginpath.'images/9.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="10" ><img src="<?php echo $neednotepluginpath.'images/10.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="11" ><img src="<?php echo $neednotepluginpath.'images/11.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="12" ><img src="<?php echo $neednotepluginpath.'images/12.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="13" ><img src="<?php echo $neednotepluginpath.'images/13.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="14" ><img src="<?php echo $neednotepluginpath.'images/14.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="15" ><img src="<?php echo $neednotepluginpath.'images/15.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="16" ><img src="<?php echo $neednotepluginpath.'images/16.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="17" ><img src="<?php echo $neednotepluginpath.'images/17.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="18" ><img src="<?php echo $neednotepluginpath.'images/18.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="19" ><img src="<?php echo $neednotepluginpath.'images/19.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="20" ><img src="<?php echo $neednotepluginpath.'images/20.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="21" ><img src="<?php echo $neednotepluginpath.'images/21.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="22" ><img src="<?php echo $neednotepluginpath.'images/22.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="23" ><img src="<?php echo $neednotepluginpath.'images/23.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="24" ><img src="<?php echo $neednotepluginpath.'images/24.gif'; ?>" /></input></li>
<li class="neednote_site active"><input name="needicona" type="radio" id="select-i" value="25" ><img src="<?php echo $neednotepluginpath.'images/25.gif'; ?>" /></input></li>
</ul>
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			Tagline:
		</th>
		<td>
			<?php _e("Modifica il testo che vuoi appaia prima dell'icona.", 'neednote'); ?><br/>
			<input size="80" type="text" name="tagline" value="<?php echo htmlspecialchars($tagline); ?>" />
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			Impostazioni avanzate:
		</th>
		<td>
			<?php _e("Form: Scegli il colore del testo e la larghezza.", 'neednote'); ?><br/>
			<div class="controlset"><input id="color1" type="text" name="needcolore1" value="<?php echo $needcolore1; ?>" /></div>
			<br/><select value="<?php echo $needlg; ?>" name="needlg"><option value="<?php echo $needlg; ?>">Attuale <?php echo $needlg; ?> px</option><option value="200">200 px</option><option value="400">400 px</option></select>
		<br/>
			<?php _e("Ultime domande: Scegli il colore del testo e la larghezza.", 'neednote'); ?><br/>
			<div class="controlset"><input id="color2" type="text" name="needcolore2" value="<?php echo $needcolore2; ?>" /></div>
			<br/><select value="<?php echo $needlg2; ?>" name="needlg2"><option value="<?php echo $needlg2; ?>">Attuale <?php echo $needlg2; ?> px</option><option value="200">200 px</option><option value="400">400 px</option></select>
		</td>
	</tr>
	<tr>
		<th scope="row" valign="top">
			<?php _e("Posizione:", "neednote"); ?>
		</th>
		<td>
			<?php _e("L'icona appare alla fine di ogni tuo post, puoi scegliere su quali pagine farlo apparire.", 'neednote'); ?><br/>
			<br/>
			<input type="checkbox" name="conditionals[is_home]"<?php echo ($conditionals['is_home']) ? ' checked="checked"' : ''; ?> /> <?php _e("Homepage", 'neednote'); ?><br/>
			<input type="checkbox" name="conditionals[is_single]"<?php echo ($conditionals['is_single']) ? ' checked="checked"' : ''; ?> /> <?php _e("Ogni Post", 'neednote'); ?><br/>
			<input type="checkbox" name="conditionals[is_page]"<?php echo ($conditionals['is_page']) ? ' checked="checked"' : ''; ?> /> <?php _e('Ogni Pagina', 'neednote'); ?><br/>
			<input type="checkbox" name="conditionals[is_category]"<?php echo ($conditionals['is_category']) ? ' checked="checked"' : ''; ?> /> <?php _e("Categoria Archivi", 'neednote'); ?><br/>
			<input type="checkbox" name="conditionals[is_tag]"<?php echo ($conditionals['is_tag']) ? ' checked="checked"' : ''; ?> /> <?php _e("Lista Tag", 'neednote'); ?><br/>
			<input type="checkbox" name="conditionals[is_date]"<?php echo ($conditionals['is_date']) ? ' checked="checked"' : ''; ?> /> <?php _e("Archivi per data", 'neednote'); ?><br/>
			<input type="checkbox" name="conditionals[is_search]"<?php echo ($conditionals['is_search']) ? ' checked="checked"' : ''; ?> /> <?php _e("Risultati ricerche", 'neednote'); ?><br/>
		</td>
	</tr>
		<td>&nbsp;</td>
		<td>
			<span class="submit"><input name="save" value="<?php _e("Salva", 'neednote'); ?>" type="submit" /></span>
			<span class="submit"><input name="restore" value="<?php _e("Ripristina Defaults", 'neednote'); ?>" type="submit"/></span>
		</td>
	</tr>
</table>
</div>
</form>
<?php
}
?>