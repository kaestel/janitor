<?php
/**
* @package janitor.items
* This file contains item type functionality
*/

class TypeMembership extends Itemtype {

	/**
	* Init, set varnames, validation rules
	*/
	function __construct() {

		parent::__construct(get_class());


		// itemtype database
		$this->db = SITE_DB.".item_membership";


		// Name
		$this->addToModel("name", [
			"type" => "string",
			"label" => "Name",
			"required" => true,
			"hint_message" => "Membership name", 
			"error_message" => "Membership needs a name."
		]);

		// Class
		$this->addToModel("classname", [
			"type" => "string",
			"label" => "CSS Class",
			"hint_message" => "CSS class for custom styling. If you don't know what this is, just leave it empty"
		]);

		// subscribed_message
		$this->addToModel("subscribed_message_id", [
			"type" => "integer",
			"label" => "Welcome message",
			"required" => true,
			"hint_message" => "Select a message to send to users when they subscribe to this membership"
		]);

		// Description
		$this->addToModel("description", [
			"type" => "text",
			"label" => "Short SEO description",
			"max" => 155,
			"hint_message" => "Write a short description of the membership for SEO.",
			"error_message" => "Your membership needs a description – max 155 characters."
		]);

		// HTML
		$this->addToModel("introduction", [
			"type" => "html",
			"label" => "Introduction for overview",
			"allowed_tags" => "p,h2,h3,h4,ul",
			"hint_message" => "Write a short introduction of the membership.",
			"error_message" => "A short introduction without any words? How weird."
		]);

		// HTML
		$this->addToModel("html", [
			"type" => "html",
			"label" => "Full description",
			"allowed_tags" => "p,h2,h3,h4,ul,ol,code,download,jpg,png", //,mp4,vimeo,youtube",
			"hint_message" => "Write a full description of the membership.",
			"error_message" => "A full description without any words? How weird."
		]);

		// Single media
		$this->addToModel("single_media", array(
			"type" => "files",
			"label" => "Add media here",
			"allowed_sizes" => "960x540",
			"max" => 1,
			"allowed_formats" => "png,jpg",
			"hint_message" => "Add single image by dragging it here. PNG or JPG allowed in 960x540",
			"error_message" => "Media does not fit requirements."
		));

	}

	function saved($item_id) {
		$query = new Query();
		$IC = new Items();

		$item = $IC->getItem(["id" => $item_id, "extend" => true]);
		
		// insert price type for membership
		$item_id = $item["id"];
		$item_name = $item["name"];
		$normalized_item_name = superNormalize(substr($item_name, 0, 60));
		$sql = "INSERT INTO ".UT_PRICE_TYPES." (item_id, name, description) VALUES ($item_id, '$normalized_item_name', 'Price for \\'$item_name\\' members')";
		$query->sql($sql);
	}
	
	function deleting($item_id) {
		$query = new Query();
		$IC = new Items();
		
		$item = $IC->getItem(["id" => $item_id, "extend" => true]);
		$item_id = $item["id"];
		
		$sql = "DELETE FROM ".UT_PRICE_TYPES." WHERE item_id = '$item_id'";
		if($query->sql($sql)) {
			 return true;
		}
		
		message()->addMessage("Can't delete. Could not delete associated price type.", ["type" => "error"]);
		return false;
	}
	
	function enabling($item) {

		if(!$item["subscription_method"]) {

			message()->addMessage("Can't enable. Membership items must have a subscription method.", ["type" => "error"]);
			return false;
		}
	}


	function addedToCart($added_item, $cart) {

		$added_item_id = $added_item["id"];
		// print "\n<br>###$added_item_id### added to cart (membership)\n<br>";
		$SC = new Shop;
		$IC = new Items;
		$query = new Query;

		foreach($cart["items"] as $cart_item) {
			
			$existing_item = $IC->getItem(["id" => $cart_item["item_id"]]);

			// another membership type already exists in cart
			if($existing_item["itemtype"] == "membership" && $existing_item["id"] != $added_item["id"]) {

				// keep the newest membership item
				$SC->deleteFromCart(["deleteFromCart", $cart["cart_reference"], $cart_item["id"]]);

			}
		}
		
		// check quantity
		$sql = "SELECT quantity FROM ".SITE_DB.".shop_cart_items WHERE item_id = ".$added_item["id"]." AND cart_id = ".$cart["id"];
		if($query->sql($sql) && $query->result(0, "quantity") > 1) {

			// ensure that membership item has quantity of 1 
			$sql = "UPDATE ".SITE_DB.".shop_cart_items SET quantity = 1 WHERE item_id = ".$added_item["id"]." AND cart_id = ".$cart["id"];
			// print $sql;
			$query->sql($sql);

			message()->addMessage("Can't update quantity. A Membership can only have a quantity of 1.", ["type" => "error"]);
		}  
		



		global $page;
		$page->addLog("membership->addedToCart: added_item:".$added_item_id);

	}

	function ordered($order_item, $order) {

		include_once("classes/shop/supersubscription.class.php");
		include_once("classes/users/supermember.class.php");
		$SuperSubscriptionClass = new SuperSubscription();
		$MC = new SuperMember();
		$IC = new Items();

		$item = $IC->getItem(["id" => $order_item["item_id"], "extend" => ["subscription_method" => true]]);
		$item_id = $order_item["item_id"];
		
		$order_id = $order ? $order["id"] : false;
		$user_id = $order["user_id"];

		if(isset($order_item["custom_price"]) && $order_item["custom_price"] !== false) {
			$custom_price = $order_item["custom_price"];
		}

		$existing_membership = $MC->getMembers(["user_id" => $user_id]);
		
		// user is already member (active or inactive)
		if($existing_membership) {

			// new membership item has a subscription method
			if(SITE_SUBSCRIPTIONS && $item["subscription_method"]) {
				
				// existing membership is active
				if($existing_membership["subscription_id"]) {
					
					// update subscription
					$subscription_id = $existing_membership["subscription_id"];
					$_POST["item_id"] = $item_id;
					$_POST["user_id"] = $user_id;
					$_POST["order_id"] = $order_id;
					if(isset($custom_price) && ($custom_price || $custom_price === "0")) {
						$_POST["custom_price"] = $custom_price;
					}
					else {
						$_POST["custom_price"] = null;
					}					
					
					$subscription = $SuperSubscriptionClass->updateSubscription(["updateSubscription", $subscription_id]);
					unset($_POST);
				}
				// existing membership is inactive
				else {

					// add subscription
					$_POST["item_id"] = $item_id;
					$_POST["user_id"] = $user_id;
					$_POST["order_id"] = $order_id;
					if(isset($custom_price) && ($custom_price || $custom_price === "0")) {
						$_POST["custom_price"] = $custom_price;
					}
					else {
						$_POST["custom_price"] = null;
					}					
					
					$subscription = $SuperSubscriptionClass->addSubscription(["addSubscription"]);
					unset($_POST);
				}

				// update membership with subscription_id
				$subscription_id = $subscription ? $subscription["id"] : false;
				$MC->updateMembership(["user_id" => $user_id, "subscription_id" => $subscription_id]);
			}
			
			// new membership item has no subscription method
			else {
				
				return false;
			}
			
		}
		
		// user is not yet a member
		else {

			// new membership has a subscription method
			if(SITE_SUBSCRIPTIONS && isset($item["subscription_method"]) && $item["subscription_method"]) {
				
				// add subscription
				$_POST["item_id"] = $item_id;
				$_POST["user_id"] = $user_id;
				$_POST["order_id"] = $order_id;
				if(isset($custom_price) && ($custom_price || $custom_price === "0")) {
					$_POST["custom_price"] = $custom_price;
				}
				else {
					$_POST["custom_price"] = null;
				}					$subscription = $SuperSubscriptionClass->addSubscription(["addSubscription"]);
				$subscription_id = $subscription ? $subscription["id"] : false;
				unset($_POST);
	
				// add membership
				$MC->addMembership($item_id, $subscription_id, ["user_id" => $user_id]);
			}
			else {

				return false;
			}
		}
		
		global $page;
		$page->addLog("membership->ordered: order_id:".$order["id"]);
		// print "\n<br>###$item_id### ordered (membership)\n<br>";
	}

	function shipped($order_item, $order) {

		$item_id = $order_item["item_id"];		

		global $page;
		$page->addLog("membership->shipped: order_id:".$order["id"]);

	}

	// user subscribed to a membership item
	function subscribed($subscription) {

		// check for subscription error
		if($subscription && $subscription["item_id"] && $subscription["user_id"]) {

			$item_id = $subscription["item_id"];
			$user_id = $subscription["user_id"];
			$order_id = NULL;
			$price = NULL;
			
			if(isset($subscription["order"])) {
				$order = $subscription["order"];
				$item_key = arrayKeyValue($order["items"], "item_id", $item_id);
				$order_id = $order ? $order["id"] : false;
				$order_item = $order["items"][$item_key];
				
				// variables for email
				$price = formatPrice(["price" => $order_item["total_price"], "vat" => $order_item["total_vat"],  "country" => $order["country"], "currency" => $order["currency"]]);
			}

			$message_id = $subscription["item"]["subscribed_message_id"];

			$IC = new Items();
			$model = $IC->typeObject("message");

			$model->sendMessage([
				"item_id" => $message_id, 
				"user_id" => $user_id, 
				"values" => ["MEMBERSHIP_PRICE" => $price]
			]);

			global $page;
			$page->addLog("membership->subscribed: item_id:$item_id, user_id:$user_id, order_id:".$order_id);


//
//
// 			$classname = $subscription["item"]["classname"];
//
//
// 			$UC = new User();
//
// 			// switch user id to enable user data collection
// 			$current_user_id = session()->value("user_id");
// 			session()->value("user_id", $user_id);
//
// 			// get user, order and  info
// 			$user = $UC->getUser();
//
// 			// switch back to correct user
// 			session()->value("user_id", $current_user_id);
//
//
// //			print "subscription:\n";
// //			print_r($subscription);
//
// 			// variables for email
// 			$nickname = $user["nickname"];
// 			$email = $user["email"];
// 			$membership = $user["membership"];
//
// 			// print "nickname:" . $nickname."<br>\n";
// 			// print "email:" . $email."<br>\n";
// 			// print "classname:" . $classname."<br>\n";
// 			// print "member no:" . $membership["id"]."<br>\n";
// 			// print "membership:" . $membership["item"]["name"]."<br>\n";
// 			// print "price:" . $price."\n";
//
//
// 			//$nickname = false;
// 			if($nickname && $email && $membership && $price && $classname) {
//
// 				mailer()->send(array(
// 					"values" => array(
// 						"ORDER_NO" => $order["order_no"],
// 						"MEMBER_ID" => $membership["id"],
// 						"MEMBERSHIP" => $membership["item"]["name"],
// 						"PRICE" => $price,
// 						"EMAIL" => $email,
// 						"NICKNAME" => $nickname
// 					),
// 					"recipients" => $email,
// 					"template" => "subscription_".$classname
// 				));
//
// 				// send notification email to admin
// 				mailer()->send(array(
// 					"recipients" => SHOP_ORDER_NOTIFIES,
// 					"subject" => SITE_URL . " - New ".$subscription["item"]["name"].": " . $email,
// 					"message" => "Do something"
// 				));
//
// 			}
// 			else {
//
// 				// send notification email to admin
// 				mailer()->send(array(
// 					"subject" => "ERROR: subscription creation: " . $email,
// 					"message" => "Do something",
// 					"template" => "system"
// 				));
//
// 			}

		}

	}

	function unsubscribed($subscription) {

		// check for subscription error
		if($subscription) {

			global $page;
			$page->addLog("membership->unsubscribed: item_id:".$subscription["item_id"].", user_id:".$subscription["user_id"]);

		}

	}

}

?>