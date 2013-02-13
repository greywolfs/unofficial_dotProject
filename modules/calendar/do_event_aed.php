<?php /* CALENDAR $Id: do_event_aed.php 6149 2012-01-09 11:58:40Z ajdonnison $ */
if (!defined('DP_BASE_DIR')) {
  die('You should not access this file directly.');
}

$obj = new CEvent();
$msg = '';

$del = (bool)dPgetParam($_POST, 'del', 0);

// bind the POST parameter to the object record
if (!$obj->bind($_POST)) {
	$AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
	$AppUI->redirect();
}
// configure the date and times to insert into the db table
if ($obj->event_start_date) {
	$start_date = new CDate($obj->event_start_date.$_POST['start_time']);
	$obj->event_start_date = $start_date->format(FMT_DATETIME_MYSQL);
}
if ($obj->event_end_date) {
	$end_date = new CDate($obj->event_end_date.$_POST['end_time']);
	$obj->event_end_date = $end_date->format(FMT_DATETIME_MYSQL);
}

if (!$del && $start_date->compare ($start_date, $end_date) >= 0) {
	$AppUI->setMsg('Start-Date >= End-Date, please correct', UI_MSG_ERROR);
	$AppUI->redirect();
	exit;
}

// prepare (and translate) the module name ready for the suffix
$do_redirect = true;
require_once $AppUI->getSystemClass('CustomFields');

foreach (findTabModules('calendar', 'addedit') as $mod) {
	$fname = (DP_BASE_DIR . '/modules/' . $mod . '/calendar_dosql.addedit.php');
	dprint(__FILE__, __LINE__, 3, ('checking for ' . $fname));
	if (file_exists($fname)) {
		require_once $fname;
	}
}
if ($del) {
	$AppUI->setMsg('Event');
	if (!$obj->canDelete($msg)) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg($msg, UI_MSG_ERROR);
	} else {
		$AppUI->setMsg('deleted', UI_MSG_OK, true);
	}
	$AppUI->redirect('m=calendar');
} else {
	$isNotNew = @$_POST['event_id'];
	if (!$isNotNew) {
		$obj->event_owner = $AppUI->user_id;
	}
	// Check for existence of clashes.
	if ($_POST['event_assigned'] > '' && ($clash = $obj->checkClash($_POST['event_assigned']))) {
	  $last_a = $a;
	  $GLOBALS['a'] = "clash";
	  $do_redirect = false;
	} else {
		$AppUI->setMsg('Event');
	    if (($msg = $obj->store())) {
			$AppUI->setMsg('Event');
			$AppUI->setMsg($msg, UI_MSG_ERROR);
		} else {
		$custom_fields = New CustomFields('calendar', 'addedit', $obj->event_id, "edit");
			$custom_fields->bind($_POST);
			$sql = $custom_fields->store($obj->event_id); // Store Custom Fields
			
			$AppUI->setMsg($isNotNew ? 'updated' : 'added', UI_MSG_OK, true);
			if (isset($_POST['event_assigned'])) {
		    	$obj->updateAssigned(explode(',', $_POST['event_assigned']));
			}
			if (isset($_POST['mail_invited'])) {
		    	$obj->notify($_POST['event_assigned'], $isNotNew);
			}
		}
	}
}
// If there is a set of post_save functions, then we process them
if (isset($post_save)) {
	foreach ($post_save as $post_save_function) {
		$post_save_function();
	}
}
if ($do_redirect)
  $AppUI->redirect();
?>
