<?php
global $action;
global $model;
$IC = new Items();
include_once("classes/shop/supershop.class.php");
$SC = new SuperShop();
include_once("classes/users/superuser.class.php");
$UC = new SuperUser();


$user_id = $action[1];
$user = $UC->getUsers(array("user_id" => $user_id));

$memberships = $IC->getItems(array("itemtype" => "membership", "extend" => array("subscription_method" => true, "prices" => true)));

$membership_options = array("" => "Choose membership");
foreach($memberships as $membership) {
	$price = $SC->getPrice($membership["item_id"], ["user_id" => $user_id]);
	$membership_options[$membership["item_id"]] = strip_tags($membership["name"])." (".formatPrice($price) . ($membership["subscription_method"] ? "/".$membership["subscription_method"]["name"] : "").")";
}

?>
<div class="scene i:scene defaultEdit userMember">
	<h1>Add a membership</h1>
	<h2><?= $user["nickname"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("Back", "/janitor/admin/member/view/".$user_id, array("class" => "button", "wrapper" => "li.membership")); ?>
	</ul>

	<div class="item">
		<h2>Add a new membership</h2>
		<?= $model->formStart("/janitor/admin/member/addNewMembership/".$user_id, array("class" => "i:defaultNew labelstyle:inject")) ?>
			<fieldset>
				<?= $model->input("item_id", array(
					"label" => "Select a membership",
					"type" => "select",
					"options" => $membership_options
				)) ?>
			</fieldset>

			<p>This will add a membership and an order – and a subscription if required by the selected membership.</p>

			<ul class="actions">
				<?= $model->link("Cancel", "/janitor/admin/member/view/".$user_id, array("class" => "button key:esc", "wrapper" => "li.cancel")) ?>
				<?= $model->submit("Add", array("class" => "primary key:s", "wrapper" => "li.update")) ?>
			</ul>
		<?= $model->formEnd() ?>
	</div>

</div>