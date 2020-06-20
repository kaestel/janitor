<?php
global $action;
global $model;


$user_id = $action[1];
$IC = new Items();


$user = $model->getUsers(array("user_id" => $user_id));

$orders = false;
$carts = false;

if(defined("SITE_SHOP") && SITE_SHOP) {
	include_once("classes/shop/supershop.class.php");
	$SC = new SuperShop();

	$orders = $SC->getOrders(array("user_id" => $user_id));
	$carts = $SC->getCarts(array("user_id" => $user_id));
}

?>
<div class="scene i:scene defaultList shopList orderList userOrderList">
	<h1>Orders</h1>
	<h2><?= $user["nickname"] ?></h2>

	<ul class="actions">
		<?= $HTML->link("All users", "/janitor/admin/user/list/".$user["user_group_id"], array("class" => "button", "wrapper" => "li.list")) ?>
	</ul>


	<?= $JML->userTabs($user_id, "orders") ?>


	<div class="orders item i:collapseHeader">
		<h2>Orders</h2>
		<div class="all_items i:defaultList filters">
	<?		if($orders): ?>
			<ul class="items">
	<?			foreach($orders as $order): ?>
				<li class="item">
					<h3><?= $order["order_no"] ?> (<?= pluralize(count($order["items"]), "item", "items" ) ?>)</h3>
					<p class="description"><?= $order["comment"] ?></p>

					<dl class="info">
						<dt class="created_at">Created at</dt>
						<dd class="created_at"><?= $order["created_at"] ?></dd>
						<dt class="status">Status</dt>
						<dd class="status <?= superNormalize($SC->order_statuses[$order["status"]]) ?>"><?= $SC->order_statuses[$order["status"]] ?></dd>
	<?					if($order["status"] < 2): ?>
						<dt class="payment_status">Payment status</dt>
						<dd class="payment_status <?= ["unpaid", "partial", "paid"][$order["payment_status"]] ?>"><?= $SC->payment_statuses[$order["payment_status"]] ?></dd>
	<?					endif; ?>
						<dt class="price">Total price</dt>
						<dd class="price"><?= formatPrice($SC->getTotalOrderPrice($order["id"])) ?></dd>
						<dt class="order_content">Order content</dt>
						<dd class="order_content"><?
							$order_content = [];
							$IC = new Items();
							foreach($order["items"] as $order_item):
								$item = $IC->getItem(["id" => $order_item["item_id"]]);
								if(array_search($item["itemtype"], $order_content) === false) {
									array_push($order_content, $item["itemtype"]);
								}
							endforeach;
							print implode(", ", $order_content);
						?></dd>
					</dl>

					<ul class="actions">
						<?= $HTML->link("View", "/janitor/admin/shop/order/edit/".$order["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
					</ul>
				 </li>
	<?			endforeach; ?>
			</ul>
	<?		else: ?>
			<p>No orders.</p>
	<?		endif; ?>
		</div>
	</div>

	<div class="carts item i:collapseHeader">
		<h2>Carts</h2>
		<div class="all_items i:defaultList filters">
	<?		if($carts): ?>
			<ul class="items">
	<?			foreach($carts as $cart): ?>
				<li class="item">
					<h3><?= $cart["cart_reference"] ?> (<?= pluralize(count($cart["items"]), "item", "items" ) ?>)</h3>

					<dl class="info">
						<dt class="created_at">Created at</dt>
						<dd class="created_at"><?= $cart["created_at"] ?></dd>
						<dt class="price">Total price</dt>
						<dd class="price"><?= formatPrice($SC->getTotalCartPrice($cart["id"])) ?></dd>
					</dl>

					<ul class="actions">
						<?= $HTML->link("Edit", "/janitor/admin/shop/cart/edit/".$cart["id"], array("class" => "button", "wrapper" => "li.edit")) ?>
					</ul>
				 </li>
	<?			endforeach; ?>
			</ul>
	<?		else: ?>
			<p>No carts.</p>
	<?		endif; ?>
		</div>

	</div>

</div>