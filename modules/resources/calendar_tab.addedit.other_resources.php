<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $AppUI, $users, $event_id, $obj, $currentTabId;
global $loadFromTab;

// Need to get all of the resources that this user is allowed to view
require_once $AppUI->getModuleClass('resources');
//$AppUI->loadModuleLanguage('resources');
$resource = new CResource;

$resource_types =& $resource->typeSelect();
$q = new DBQuery;

$q->addTable('resources');
$q->addOrder('resource_type', 'resource_name');
$res = $q->exec();
$all_resources = array();
$resource_max = array();

while ($row = db_fetch_assoc($res)) {
	$type = $row['resource_type'];
	$all_resources[$row['resource_id']] = $AppUI->_($resource_types[$row['resource_type']]) . ': ' . $row['resource_name'];
//	$resource_max[$row['resource_id']] = $row['resource_max_allocation'];
}
$q->clear();

$assigned_resources = array();


$resources = array();
if ($loadFromTab && isset($_SESSION['event_subform']['hresource_assign'])) {
	$initResAssignment = '';
	foreach (explode(';', $_SESSION['event_subform']['hresource_assign']) as $perc) {
		if ($perc) {
			list ($rid, $perc) = explode('=', $perc);
			$assigned_resources[$rid] = $perc;
			$initResAssignment .= "$rid,";
			$resources[$rid] = $all_resources[$rid];
		}
	}
} else if ($event_id == 0) {
} else {
	$initResAssignment = '';
	// Pull resources on this task
	$q = new DBQuery;
	$q->addTable('event_resources');
	$q->addQuery('resource_id');
	$q->addWhere('event_id = ' . $event_id);
	$sql = $q->prepareSelect();
	$assigned_res = $q->exec();
	while ($row = db_fetch_assoc($assigned_res)) {
		$initResAssignment .= $row['resource_id'].',';
		$resources[$row['resource_id']] = $all_resources[$row['resource_id']];
	}
	$q->clear();
}

$AppUI->getModuleJS('resources', 'event_tabs');
?>
<script language="javascript" type="text/javascript">
	<?php
//	echo "var projTasksWithEndDates=new Array();\n";
//	$keys = array_keys($projTasksWithEndDates);
//	for ($i = 1, $sz=sizeof($keys); $i < $sz; $i++) {
//		array[task_is] = end_date, end_hour, end_minutes
//		echo ('projTasksWithEndDates[' . $keys[$i] . ']=new Array("'
//			. $projTasksWithEndDates[$keys[$i]][1] . '", "' . $projTasksWithEndDates[$keys[$i]][2]
//			. '", "' . $projTasksWithEndDates[$keys[$i]][3] . '");' . "\n");
//	}
	?>
</script>
<form action="?m=calendar&amp;a=addedit&amp;event_id=<?php echo $event_id; ?>"
	  method="post" name="otherFrm">
	<input type="hidden" name="sub_form" value="1" />
	<input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
	<input type="hidden" name="dosql" value="do_event_other_resources_aed" />
	<input name="hresource_assign" type="hidden" value="<?php echo
	$initResAssignment;?>"/>
	<table width="100%" border="1" cellpadding="4" cellspacing="0" class="std">
		<tr>
			<td valign="top" align="center">
				<table cellspacing="0" cellpadding="2" border="0">
					<tr>
						<td><?php echo $AppUI->_('Resources');?>:</td>
						<td><?php echo $AppUI->_('Assigned to event');?>:</td>
					</tr>
					<tr>
						<td>
							<?php echo arraySelect($all_resources, 'resources', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
						</td>
						<td>
							<?php echo arraySelect($resources, 'assigned', 'style="width:220px" size="10" class="text" multiple="multiple" ', null); ?>
						</td>
					<tr>
						<td colspan="2" align="center">
							<table>
								<tr>
									<td align="right"><input type="button" class="button" value="&gt;" onclick="javascript:addResource(document.otherFrm)" /></td>
									<td align="left"><input type="button" class="button" value="&lt;" onclick="javascript:removeResource(document.otherFrm)" /></td>
								</tr>
							</table>
						</td>
					</tr>
					</tr>
					<!-- 			<tr>
				<td colspan=3 align="center">
					<input type="checkbox" name="task_notify" value="1" <?php //if ($obj->task_notify!="0") echo "checked"?> /> <?php //echo $AppUI->_('notifyChange');?>
				</td>
			</tr> -->
				</table>
			</td>
		</tr>
	</table>
</form>
<script language="javascript" type="text/javascript">
	subForm.push(new FormDefinition(<?php echo $currentTabId; ?>, document.otherFrm, checkOther, saveOther));
</script>
