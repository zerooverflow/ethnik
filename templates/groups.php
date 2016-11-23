<?php 

global $ethnik_groups;


if ( isset( $_POST['ethnik_action'] ) ) {
	 
	$ethnik_action = $_POST['ethnik_action'];
	$ethnik_name = $_POST['ethnik_formgroup_name'];
	$ethnik_description = $_POST['ethnik_formgroup_description'];
	$ethnik_formgroup_members = $_POST['ethnik_formgroup_members'];
	$ethnik_groupid = $_POST['ethnik_groupid'];
	
	$newmembers = explode (',',$ethnik_formgroup_members);
	
	switch ( $ethnik_action ){

		case 'edit':

			$result = $ethnik_groups->modify_group($ethnik_groupid, $ethnik_name, $ethnik_description );
			
			$oldmembers = $ethnik_groups->get_members($ethnik_groupid);
			
			
			$unsubscribe_members =  array_diff ( $oldmembers, $newmembers );
			$result_unsubsribe = $ethnik_groups->user_unsubscribe($unsubscribe_members,$ethnik_groupid );
			$result_subscribe = $ethnik_groups->user_subscribe ($newmembers,$ethnik_groupid);
			break;
		case 'addnew':
			
			$existing_groups = $ethnik_groups->get_group_byname($ethnik_name);
			
			// verifico che non esista già un gruppo con il nome scelto:
			if ( count( $existing_groups )==0 ){
				
				$write_result = $ethnik_groups->write_group(array( 'name'=>$ethnik_name, 'description'=>$ethnik_description));
				
				$newgroup = $ethnik_groups->get_group_byname($ethnik_name);
				$newgroupid = $newgroup[0]->id;
				
				//salvo utenti legati al gruppo:
				$ethnik_groups->user_subscribe( $newmembers,$newgroupid );				 
			}
			else echo '<div class="error">Esiste già un gruppo con questo nome. Impossibile salvare il gruppo.</div>';
			break; 
	}
	

}

// DELETE ACTION

if ( !isset( $_POST['ethnik_action']) && isset($_GET['action']) &&  $_GET['action']=='delete' && current_user_can( 'manage_options' ) ) {
		if ( isset($_GET['group']) ) $params['groupid'] = $_GET['group'];
		$ethnik_groups->delete_group( $params['groupid'] );

}

include (sprintf("%s/includes/ethnik-grouptable.php", PLUGIN_DIRPATH));
include (sprintf("%s/templates/ethnik-groupform.php", PLUGIN_DIRPATH));


$ethnik_groups->get_unsubscribed_users( 1 );

//Create an instance of our package class...
    $testListTable = new Ethnik_Group_Table($ethnik_groups);
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items();
    
    ?>
 	<div class="wrap">
		<h2>
		Gestisci Gruppi
		<a href="?page=<?php echo $_REQUEST['page'] ?>&action=addnew" class="add-new-h2">Aggiungi Gruppo</a>
		</h2>
		
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="ethnik-groups-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $testListTable->display() ?>
        </form>
     </div>    
    <?php 

    // ADD NEW E EDIT ACTION
    
    if ( !isset( $_POST['ethnik_action']) && isset($_GET['action']) && current_user_can( 'manage_options' ) ) {
    	
    	$params = array();
    	$params['action'] = $_GET['action'];
    	

    	
    	if ( isset($_GET['group']) ) $params['groupid'] = $_GET['group'];
    	
    	switch ( $params['action'] ){
    		
    		case 'addnew':
    			
    			$params['formtitle'] = 'Aggiungi un nuovo gruppo';
    			$params['subscribeds'] = $ethnik_groups->get_subscribed_users();
    			$params['unsubscribeds'] = $ethnik_groups->get_unsubscribed_users();
    			$params['submit_text'] = 'Crea Gruppo';
    			break;
    			
    		case 'edit':
    			
    			$params['name'] = $ethnik_groups->get_group_byid( $params['groupid'] )->name;
    			$params['description'] =$ethnik_groups->get_group_byid( $params['groupid'] )->description;   			
    			$params['subscribeds'] = $ethnik_groups->get_subscribed_users( $params['groupid'] );
    			
    			
    			$params['unsubscribeds'] = $ethnik_groups->get_unsubscribed_users( $params['groupid'] );
    			$params['formtitle'] = 'Modifica il gruppo "'.$params['name'].'"';
    			break;
    		
    		case 'delete':
    			return;
    			break;
    			
    		
    	}
    	
    	create_userlist( 'ethnik_unsubscribed', $params['unsubscribeds'] );
    	create_userlist( 'ethnik_subscribed', $params['subscribeds'] );
	    
    	$_GET['action'] = '';
    	$_GET['group'] = '';
	    
	    render_groupform( $params );
	    
    }
    
?>
