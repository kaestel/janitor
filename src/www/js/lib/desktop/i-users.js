// default new form
Util.Objects["usernames"] = new function() {
	this.init = function(div) {

//		u.bug("div usernames")
		var form;

		form = u.qs("form.email", div);
		u.f.init(form);

		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);

				if(response.cms_status == "error") {
					u.f.fieldError(this.fields["email"]);
				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
		}

		form = u.qs("form.mobile", div);
		u.f.init(form);

		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);

				if(response.cms_status == "error") {
					u.f.fieldError(this.fields["mobile"]);
				}
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
		}

	}
}

// password form
Util.Objects["password"] = new function() {
	this.init = function(div) {

		var password_state = u.qs("div.password_state", div);
		var new_password = u.qs("div.new_password", div);

		var a_create = u.qs(".password_missing a");
		var a_change = u.qs(".password_set a");

		a_create.new_password = new_password;
		a_change.new_password = new_password;
		a_create.password_state = password_state;
		a_change.password_state = password_state;

		u.ce(a_create);
		u.ce(a_change);
		a_create.clicked = a_change.clicked = function() {
			u.as(this.password_state, "display", "none");
			u.as(this.new_password, "display", "block");
		}

		var form = u.qs("form", div);
		form.password_state = password_state;
		form.new_password = new_password;

		u.f.init(form);

		form.submitted = function(iN) {

			this.response = function(response) {
				if(response.cms_status == "success") {
					u.ac(this.password_state, "set");
					u.as(this.password_state, "display", "block");
					u.as(this.new_password, "display", "none");
				}
				page.notify(response);
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
			this.fields["password"].val("");

		}

	}
}

// userNewsletters subscribe+unsubscribe form
Util.Objects["newsletters"] = new function() {
	this.init = function(div) {

		var i, node;
		div.newsletters = u.qsa("ul.newsletters > li", div);
		for(i = 0; node = div.newsletters[i]; i++) {

			node.li_delete = u.qs("li.delete", node);
			node.li_subscribe = u.qs("li.subscribe", node);

			// init if form is available
			if(node.li_delete) {

				// look for form
				node.li_delete.form = u.qs("form", node.li_delete)

				u.f.init(node.li_delete.form);
				node.li_delete.form.node = node;

				node.li_delete.form.restore = function(event) {
					this.actions["delete"].value = "Unsubscribe";
					u.rc(this.actions["delete"], "confirm");
				}

				node.li_delete.form.submitted = function() {

					// first click
					if(!u.hc(this.actions["delete"], "confirm")) {
						u.ac(this.actions["delete"], "confirm");
						this.actions["delete"].value = "Confirm";
						this.t_confirm = u.t.setTimer(this, this.restore, 3000);
					}
					// confirm click
					else {
						u.t.resetTimer(this.t_confirm);

						this.response = function(response) {

							// show message
							page.notify(response);

							if(response.cms_status == "success") {
								u.rc(this.node, "subscribed");
							}
							this.restore();

						}
						u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
					}
				}
			}


			// init if form is available
			if(node.li_subscribe) {

				// look for form
				node.li_subscribe.form = u.qs("form", node.li_subscribe)

				u.f.init(node.li_subscribe.form);
				node.li_subscribe.form.node = node;

				node.li_subscribe.form.submitted = function() {


					this.response = function(response) {

						// show message
						page.notify(response);

						if(response.cms_status == "success") {
							u.ac(this.node, "subscribed");
							//this.node.parentNode.removeChild(this.node);
						}
					}
					u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});

				}
			}
		}
	}
}


Util.Objects["accessEdit"] = new function() {
	this.init = function(div) {

		div._item_id = u.cv(div, "item_id");

		// primary form
		var form = u.qs("form", div);
		u.f.init(form);
		form.actions["cancel"].clicked = function(event) {
			location.href = this.url;
		}
		form.submitted = function(iN) {

			this.response = function(response) {
				page.notify(response);
			}
			u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});

		}

		// enable select all on controller heading
		var i, group;
		var groups = u.qsa("li.action", form);
		for(i = 0; group = groups[i]; i++) {

			var h3 = u.qs("h3", group);
			h3.group = group;
			u.ce(h3)
			h3.clicked = function() {

				var i, input;
				var inputs = u.qsa("input[type=checkbox]", this.group);
				for(i = 0; input = inputs[i]; i++) {
					input.val(1);
				}

			}

		}


	}
}