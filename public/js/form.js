window.addEvent('domready',function(){
	$$("input").addEvents({
		"focus": function(){
			if(this.get("value") == this.get("alt")){
				this.set("value", "");
			}
		},
		"blur": function(){
			if(this.get("value") == ""){
				this.set("value", this.get("alt"));
			}
		}
	});
});