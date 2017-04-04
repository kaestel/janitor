<?php
global $action;
global $model;
$SC = new Shop();

$user_id = session()->value("user_id");


$user = $model->getUser();
$membership = $model->getMembership();


// FOR TESTING EMAIL SENDING
// $subscription = $model->getSubscriptions(array("subscription_id" => $membership["subscription_id"]));
// $IC = new Items();
// $mem = $IC->typeObject("membership");
// $mem->subscribed($subscription);

?>
<div class="scene i:scene defaultView userMembership">
	<h1>Membership</h1>
	<h2><?= $user["nickname"] ?></h2>


	<?= $JML->profileTabs("membership") ?>


	<? if($membership): ?>
	<div class="item">
		<h2>Membership details</h2>

		<? if($membership["subscription_id"]):
			$subscription = $model->getSubscriptions(array("subscription_id" => $membership["subscription_id"])); ?>
		<h3><span>#<?= $membership["id"] ?></span> - <?= $membership["item"]["name"] ?></h3>
		<? else: ?>
		<h3><span>#<?= $membership["id"] ?></span> - Inactive membership</h3>
		<? endif; ?>
		
		<dl class="info">
			<dt class="created_at">Created at</dt>
			<dd class="created_at"><?= date("d. F, Y", strtotime($membership["created_at"])) ?></dd>

		<? if($membership["modified_at"]): ?>
			<dt class="modified_at">Modified at</dt>
			<dd class="modified_at"><?= date("d. F, Y", strtotime($membership["modified_at"])) ?></dd>
		<? endif; ?>

		<? if($membership["renewed_at"]): ?>
			<dt class="renewed_at">Renewed at</dt>
			<dd class="renewed_at"><?= date("d. F, Y", strtotime($membership["renewed_at"])) ?></dd>
		<? endif; ?>

		<? if($membership["expires_at"]): ?>
			<dt class="expires_at">Expires at</dt>
			<dd class="expires_at"><?= date("d. F, Y", strtotime($membership["expires_at"])) ?></dd>
		<? endif; ?>


		<? if($membership["item"] && $membership["item"]["prices"]):
			$offer = arrayKeyValue($membership["item"]["prices"], "type", "offer");
			$default = arrayKeyValue($membership["item"]["prices"], "type", "default");
			?>
		
			<? if($offer !== false && $default !== false): ?>
				<dt class="price default">Normal price</dt>
				<dd class="price default"><?= formatPrice($membership["item"]["prices"][$default]).($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
				<dt class="price offer">Special offer</dt>
				<dd class="price offer"><?= formatPrice($membership["item"]["prices"][$offer]).($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
			<? elseif($default !== false): ?>
				<dt class="price">Price</dt>
				<dd class="price"><?= formatPrice($membership["item"]["prices"][$default]).($membership["item"]["subscription_method"] ? " / " . $membership["item"]["subscription_method"]["name"] : "") ?></dd>
			<? endif; ?>

			<? if($default !== false && $membership["item"]["prices"][$default]["price"] !== "0"): ?>
			<dt class="payment_method">Payment method</dt>
			<dd class="payment_method"><?= $subscription["payment_method"] ? $subscription["payment_method"]["name"] : "N/A" ?></dd>

				<? if($membership["order_id"]): ?>
			<dt class="payment_status">Payment status</dt>
			<dd class="payment_status<?= $membership["order"]["payment_status"] < 2 ? " missing" : "" ?>"><?= $SC->payment_statuses[$membership["order"]["payment_status"]] ?></dd>
				<? endif; ?>
			<? endif; ?>
				
		<? endif; ?>

		</dl>

	</div>
	
	<div class="change i:collapseHeader">
		<h2>Change your membership</h2>

		<p>
			Changing your membership does not affect any
			existing orders.
		</p>

		<div class="option">
			<h3>Switch to a new membership</h3>
			<p>
				- cancel your existing membership and add a new one, starting today.	
			</p>
			<ul class="actions">
				<?= $HTML->link("New membership", "/janitor/admin/profile/membership/switch", array("class" => "button", "wrapper" => "li.edit")) ?>
			</ul>
		</div>

		<? if($membership["order"]): ?>

		<div class="option">
			<h3>Upgrade your existing membership</h3>
			<p>
				- just pay the price difference and maintain the current renewal cyclus.
			</p>
			<ul class="actions">
				<?= $HTML->link("Upgrade membership", "/janitor/admin/profile/membership/upgrade", array("class" => "button", "wrapper" => "li.edit")) ?>
			</ul>
		</div>

		<div class="option">
			<h3>Cancel the membership</h3>
			<p>
				- cancel the existing subscription, and leave membership inactive.
			</p>
			<ul class="actions">
				<?= $JML->oneButtonForm("Cancel membership", "/janitor/admin/profile/cancelMembership/".$membership["id"], array(
					"confirm-value" => "Confirm cancellation",
					"wrapper" => "li.cancel",
					"class" => "secondary",
					"success-location" => "/janitor/admin/user/membership/view/".$user_id
				)) ?>
			</ul>
		</div>


		<p>
			If you want to cancel your account entirely, goto the <em>Cancellation</em> section on the <a href="/janitor/admin/profile">profile</a> page.
		</p>


		<? else: ?>

		<p>
			You cannot change your membership until the current subscription has been paid.<br />
		</p>
		<ul class="actions">
			<li class="pay"><a href="<?= SITE_URL ?>/shop/payment/<?= $membership["order"]["order_no"] ?>" class="button primary">Pay now</a></li>
		</ul>

		<? endif; ?>


	<? else: ?>
		<p>You do not have a membership.</p>
	<? endif; ?>
	</div>

</div>