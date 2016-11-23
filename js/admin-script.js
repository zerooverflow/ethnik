/* ETHNIK USER LIST OBJECT  **/

function ethnik_userlist(data){
	if ( data instanceof Array ) this.data = data;
	else this.data = [];
}

ethnik_userlist.prototype.get_userpos_by_id = function ( userid ){
	
	for ( k in this.data ){
		if ( this.data[k]['ID'] == userid ) return k;
	}
	return -1;
	
};

ethnik_userlist.prototype.remove_user = function ( index ){
	
	 this.data[index] = null;
	 this.data.splice(index,1);
	
};

ethnik_userlist.prototype.add_user = function ( id, nicename){
	results = this.data.push( { ID : id, user_nicename : nicename });
	this.ordina();
	return results;
}; 

ethnik_userlist.prototype.ordina = function (){
	this.data.sort ( function ( a, b) {
		if ( a.user_nicename < b.user_nicename )
	        return -1;
	    if ( a.user_nicename > b.user_nicename )
	        return 1;
	    return 0;
	}  );
}



jQuery( document ).ready( function ( $ ) {
	
	function refresh_list( listview, listmodel ){
		$(listview).html('');
		for ( var i in listmodel.data ){
			row = '<li><label for="member-user-'+ listmodel.data[i].ID +'"><input id="member-user-'+ listmodel.data[i].ID +'" type="checkbox" value="'+ listmodel.data[i].ID +'"/>'+ listmodel.data[i].user_nicename +'</label></li>';
			$(listview).append(row);
		}
	}
	
	function update_subscribed_field(){
		var value = '';
		if (typeof ethnik_subscribed == 'undefined') return;
		for ( var i in ethnik_subscribed.data ){
			value += ethnik_subscribed.data[i].ID;
			if ( i < ethnik_subscribed.data.length-1 )  value +=',';
		}
		
		$('#ethnik-group-form #ethnik_formgroup_members_inputfield').attr('value',value);
	}
	update_subscribed_field();
		
	$('#ethnik-group-form .ethnik-remove').on( "click", function() {
		
		$('#ethnik-members-list li input:checked').each( function (index ){
			uid =  $(this).val();
			i = ethnik_subscribed.get_userpos_by_id ( uid );
			
			ethnik_unsubscribed.add_user( ethnik_subscribed.data[i].ID, ethnik_subscribed.data[i].user_nicename );
			ethnik_subscribed.remove_user ( i );
			
			
			
		});
		
		refresh_list( '#ethnik-users-list', ethnik_unsubscribed );
		refresh_list( '#ethnik-members-list', ethnik_subscribed );
		update_subscribed_field();
		
	});
	
	$('#ethnik-group-form .ethnik-add').on( "click", function() {
		
		$('#ethnik-users-list li input:checked').each( function (index ){
			uid =  $(this).val();
			i = ethnik_unsubscribed.get_userpos_by_id ( uid );
			
			ethnik_subscribed.add_user( ethnik_unsubscribed.data[i].ID, ethnik_unsubscribed.data[i].user_nicename );
			ethnik_unsubscribed.remove_user ( i );
			update_subscribed_field();
			
			
		});
		
		refresh_list( '#ethnik-users-list', ethnik_unsubscribed );
		refresh_list( '#ethnik-members-list', ethnik_subscribed );
		
	});
	
	
});
