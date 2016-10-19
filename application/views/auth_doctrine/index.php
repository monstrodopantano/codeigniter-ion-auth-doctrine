<h1><?php echo lang('index_heading');?></h1>
<p><?php echo lang('index_subheading');?></p>

<div id="infoMessage"><?php echo $message;?></div>

<table class="table table-striped" cellpadding=0 cellspacing=10>
	<tr>
		<th><?php echo lang('index_fname_th');?></th>
		<th><?php echo lang('index_lname_th');?></th>
		<th><?php echo lang('index_email_th');?></th>
		<th><?php echo lang('index_groups_th');?></th>
		<th><?php echo lang('index_status_th');?></th>
		<th><?php echo lang('index_action_th');?></th>
	</tr>
	<?php foreach ($users as $user):?>
		<tr>
			<td><?php echo $user->getFirstName();?></td>
			<td><?php echo $user->getLastName();?></td>
			<td><?php echo $user->getEmail();?></td>
			<td>
				<?php foreach ($user->getUsersGroups()->toArray() as $user_group):?>
					<?php echo anchor("auth/edit_group/".$user_group->getGroups()->getId(), $user_group->getGroups()->getName()) ;?><span> | </span>
                <?php endforeach?>
			</td>
			<td><?php echo ($user->getActive()) ? anchor("auth/deactivate/".$user->getId(), lang('index_active_link')) : anchor("auth/activate/". $user->getId(), lang('index_inactive_link'));?></td>
			<td><?php echo anchor("auth/edit_user/".$user->getId(), 'Edit') ;?></td>
		</tr>
	<?php endforeach;?>
</table>

<p><?php echo anchor('auth/create_user', lang('index_create_user_link'))?> | <?php echo anchor('auth/create_group', lang('index_create_group_link'))?></p>