<?php
global $post_save;
$post_save[]='saveOtherResources';
function saveOtherResources(){
	global $AppUI, $obj;
	$assigned=explode(",",$_POST['other_resource']);
	$q = new DBQuery;
	$q->setDelete('event_resources');
	$q->addWhere('event_id = ' . $obj->event_id);
	$q->exec();
	$q->clear();

	if (is_array($assigned) && count($assigned)) {

	 	 	foreach ($assigned as $uid) {
				  $uid=(int)$uid;
				  if ($uid) {
					  $q->addTable('event_resources', 'ue');
					  $q->addInsert('event_id', $obj->event_id);
					  $q->addInsert('resource_id', $uid);
					  $q->exec();
					  $q->clear();
				  }
			  }

	 	 	if ($msg = db_error()) {
				  $AppUI->setMsg($msg, UI_MSG_ERROR);
			  }
	}
}
?>
