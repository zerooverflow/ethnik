<?php 

class EthnikGroups {

	private $wp_db;
	private $current_user_groups;
	private $table_groups;
	private $table_relationships;

	public function __construct(){

		global $wpdb;
		$this->wp_db = $wpdb;

		$this->table_groups = $wpdb->prefix . 'ethnik_groups';
		$this->table_relationships =  $wpdb->prefix . 'ethnik_relationships';
		
		$this->current_user_groups = $this->read_current_user_groups();
		
				
	}
	/**
	 * get table groups name
	 */
	public function get_table_groups(){ return $this->table_groups;}
	
	/**
	 * Restituisce gli utenti che non sono membri del gruppo dato
	 * @param unknown $groupid identificativo del gruppo dato
	 * @return array di oggetti WP_User
	 */
	public function get_unsubscribed_users( $groupid=0 ){
	
		// escludo gli utenti che sono membri del gruppo dato:
	
		$args = array(
				'exclude'	=> $this->get_members( $groupid ),
				'orderby'	=> 'nicename', 
				'fields'	=> array('ID','user_nicename'),
		);
	
		$users = get_users( $args );
		return $users;
	}
	
	public function get_subscribed_users( $groupid=0 ){
		
		if ( $groupid == 0 ) return null;
		
		$members = $this->get_members( $groupid );
		
		if ( empty( $members) ) return null;
		
		$args = array(
				'include'	=> $members,
				'orderby'	=> 'nicename',
				'fields'	=> array('ID','user_nicename'),
		);
	
		$users = get_users( $args );
		
		return $users;
	
	
	}
	
	
	
	/**
	 * 
	 * @param int $id gruppo
	 * @return array informazioni del gruppo dato l'id
	 */
	public function get_group_byid($id){
		
		$results = $this->wp_db->get_results( $this->wp_db->prepare('SELECT * FROM '.$this->table_groups.' WHERE id = %s', $id) );
		return $results[0];
	}
	
	/**
	 * 
	 * @param string $name nome del gruppo
	 * @return array informazioni del gruppo dato il nome
	 */
	public function get_group_byname($name){
		
		$results = $this->wp_db->get_results( $this->wp_db->prepare('SELECT * FROM '.$this->table_groups.' WHERE name = %s', $name) );
		return $results;
	}
	
	/**
	 *
	 * @param int $groupid identificativo del gruppo
	 * @return multitype: tutti i membri di un dato gruppo
	 */
	public function get_members($groupid){
	
		$results = $this->wp_db->get_results( "SELECT userid FROM ".$this->table_relationships." WHERE groupid ='".$groupid."'", ARRAY_N );
	
		$members = array();
		foreach ( $results as $result){
			array_push ( $members, $result[0]);
		}
		return $members;
	}
	
	/**
	 *
	 * @param int $userid identificativo dell'utente
	 * @return multitype: restituisce i gruppi di un utente dato
	 */
	public function get_user_groups( $userid ){
	
		$results = $this->wp_db->get_results( "SELECT groupid FROM ".$this->table_relationships." WHERE userid ='".$userid."'", ARRAY_N );
	
		$groups = array();
		foreach ( $results as $result){
			array_push ( $groups, $result[0]);
		}
		return $groups;
	}
	
	/**
	 *  restituisce i gruppi dell'utente loggato
	 */
	public
	 function get_current_user_groups () {
		return $this->current_user_groups;
	}
	
	/**
	 *
	 * @param int $userid identificativo dell'utente
	 * @return multitype: restituisce le connessioni dell'utente dato, cioè tutti i membri di tutti i gruppi di cui fa parte
	 */
	public function get_user_connections( $userid ){
	
		$groups = implode(',',$this->get_user_groups( $userid ));
		$results = $this->wp_db->get_results( "SELECT userid FROM ".$this->table_relationships." WHERE groupid in (".$groups.")", ARRAY_N );
	
		$members = array();
		foreach ( $results as $result){
			array_push ( $members, $result[0]);
		}
		return $members;
	}
	
	
	/**
	 * Legge tutti i gruppi salvati
	 * @return array con le informazioni dei gruppi
	 */
	public function read_groups(){
		$results = $this->wp_db->get_results( 'SELECT id,name,description FROM '.$this->table_groups, OBJECT );
		return $results;
	}
	
	/**
	 * Legge i gruppi a cui appartiene l'utente opppure tutti i gruppi se l'utente è admin
	 * @return array con le informazioni dei gruppi dell'utente
	 */
	public function read_current_user_groups(){
		$query = 'SELECT id,name,description FROM '.$this->table_groups;
		if ( ! current_user_can('manage_options') ) {
				
			$groups = implode( ',',$this->get_user_groups( wp_get_current_user()->ID ));
				
			$query .= ' WHERE id IN ('.$groups.')';
				
		}
	
		$results = $this->wp_db->get_results( $query, OBJECT );
		return $results;
	}
	
	
	/**
	 * 
	 * @param int $id identificativo del gruppo
	 * @return esito della query
	 */
	public function delete_group($id){
		
		$results = $this->wp_db->get_results( $this->wp_db->prepare('DELETE FROM '.$this->table_groups.' WHERE id = %s', $id) );
		
		$remove_relationships = $this->wp_db->get_results( $this->wp_db->prepare('DELETE FROM '.$this->table_relationships.' WHERE groupid = %s', $id) );
		return array('groupdb'=>$results,'relationshipsdb'=>$remove_relationships);
	}
	
	/**
	 * 
	 * @param int $id identificativo del gruppo
	 * @param string $name nome del gruppo
	 * @param string $description descrizione del gruppo
	 * @return boolean|unknown esito della modifica
	 */
	public function modify_group($id, $name = null, $description = null ){
		
		if ( $name == null && $description == null ) return false;
		
		$sql = "UPDATE ".$this->table_groups." SET ";
		if ( $name !==null ) $sql .="name='".$name."' ";
		if ( $description !==null ) $sql .=",description='".$description."' ";
		$sql .= " WHERE id='".$id."'";
		
		$results = $this->wp_db->get_results( $sql, OBJECT );
		return $results;
		
	}
	
	/**
	 * Crea un nuovo gruppo dato nome e descrizione opzionale
	 * @param array $args nome e descrizione(opzionale) del gruppo
	 * @return boolean|unknown esito della query di inserimento
	 */
	public function write_group($args){

		if (! is_array($args)) return false;
		if ( isset ( $args['name']) ){

			$name = $args['name'];
			
			// evita di salvare un gruppo con un nome esistente:
			if ( count( $this->get_group_byname( $name ) ) > 0)
				return false;
			
			$description = (isset($args['description']))?$args['description']:'';
			
			$results = $this->wp_db->query( $this->wp_db->prepare(
					"INSERT INTO $this->table_groups ( name, description ) VALUES ( %s, %s )",
					$name,
					$description
			) );

			return $results;

		}

		return false;

	}
	
	
	
	/**
	 * 
	 * @param int $userid identificativo dell'utente
	 * @param int $groupid identificativo del gruppo
	 * @return boolean: restituisce true|false se l'utente dato è|non è membro del gruppo dato
	 */
	public function is_user_member ($userid, $groupid){
		
		$user_groups =  $this->get_user_groups( $userid );
		return in_array( $groupid, $user_groups);
		
	}
	
	/**
	 * 
	 * @param int $userids identificativi degli utenti
	 * @param int $groupid identificativo del gruppo
	 * @return boolean restituisce l'esito dell'operazione di inserimento di uno o piu utenti nel gruppo dato
	 */
	public function user_subscribe( $userids, $groupid){
		
		$results_array = array();
		
		foreach ( $userids as $userid) {
			if ( $this->is_user_member ($userid, $groupid) == true ) {
				$results_array[]= true;
				continue;
			}
			
			
			$results = $this->wp_db->query( $this->wp_db->prepare(
					"INSERT INTO $this->table_relationships ( userid, groupid ) VALUES ( %d, %d )",
					$userid,
					$groupid
			) );
			
			$results_array[]= ( $results==1 );  
		}
		
		return $results_array;  
		
	}
	
	/**
	 * 
	 * @param int $userids identificativi degli utenti
	 * @param int $groupid identificativo del gruppo
	 * @return boolean esisto dell'operazione di cancellazione di uno o piu utenti dal gruppo dato
	 */
	public function user_unsubscribe ($userids, $groupid){
		
		$results_array = array();
		
		foreach ( $userids as $userid) {
			
			if ( $this->is_user_member ($userid, $groupid) == true ) {
					
				$results = $this->wp_db->get_results( $this->wp_db->prepare("DELETE FROM ".$this->table_relationships." WHERE userid = '%d' AND groupid = '%d'", $userid, $groupid ) );
				$results_array[]= ( $results==1 );  
			}
			
			else $results_array[]= true;
		}
		
		
		
	}
	

	


}//END CLASS

function exclude($query){
	
	global $ethnik_groups;
	
	$authors = $ethnik_groups->get_user_connections( wp_get_current_user()->ID );
	if ( count ($authors)==0 ) $authors = array ( wp_get_current_user()->ID );
	
	
	if ($query->is_main_query() && !$query->is_home()) {
	 $query->set('author__in', $authors );
	 
	}

}

function show_current_group_attachments( $query) {
	//$user_id = get_current_user_id();
	
	global $ethnik_groups;
	
	$authors = $ethnik_groups->get_user_connections( wp_get_current_user()->ID );
	if ( count ($authors)==0 ) $authors = array ( wp_get_current_user()->ID );
	
	//var_dump($query);
	$query['author__in'] = $authors;
	//$query->set('author__in', $authors );
	
	return $query;
}

function my_tweaked_admin_bar() {
	global $wp_admin_bar;
	global $ethnik_groups;
	
	if (current_user_can('manage_options')) return;
	
	$groupName = $ethnik_groups->get_current_user_groups();
	
	if ($groupName == null) return;
	
	$groups = '';
	
	foreach ( $groupName as $groupItem) {
		
		if (strlen($groups)>100){
			break;
		}
		
		$groups.= $groupItem->name;
		if( next( $groupName ) ) $groups.= ', ';
	}
	
	
	$wp_admin_bar->add_menu( array(
			'id'    => 'ethnik-group',
			'title' => '<span class="group-icon"></span> '.$groups,
			'meta'  => array( 'class' => 'wpse--item' )
	));

}

function getGroupsListByUserId($user_id){
	global $ethnik_groups;
	
	$groupsIds = $ethnik_groups->get_user_groups( $user_id );
	
	foreach ($groupsIds as $gId){
		$groupName.= $ethnik_groups->get_group_byid($gId)->name;
		if( next( $groupsIds ) ) $groups.= ', ';
	}
	
	return $groupName;
	
}

/* Display custom column */
function display_ethnik_group_owner( $column, $post_id ) {

	$authorId = get_post($post_id)->post_author;
	$groupName = getGroupsListByUserId($authorId);

	echo $groupName;
}


/* Add custom column to post list */
function add_ethnikGroup_column( $columns ) {
	return array_merge( $columns,array('Ethnik Group') );
}


function add_ethnikGroup_user_column( $columns ) {
	$columns['ethnik'] = 'Ethnik Group';
	return $columns;
}


function display_ethnik_user_group( $val, $column_name, $user_id ){
		
	switch ($column_name) {
		case 'ethnik' :
			$groupName = getGroupsListByUserId($user_id);
			return $groupName;
			break;
	}
	return $val;
}

if (class_exists('EthnikGroups')) {

	global $ethnik_groups;
	$ethnik_groups = new EthnikGroups();
	
	if (!is_admin()) {
		add_action( 'pre_get_posts', 'exclude' );
		add_action( 'wp_before_admin_bar_render', 'my_tweaked_admin_bar' );
	
		add_filter( 'ajax_query_attachments_args', 'show_current_group_attachments', 10, 1 );		
	}
	else {
		add_filter( 'manage_posts_columns' , 'add_ethnikGroup_column' );
		add_filter( 'manage_pages_columns' , 'add_ethnikGroup_column' );
		add_filter('manage_users_columns' , 'add_ethnikGroup_user_column');
		
		add_filter( 'manage_users_custom_column', 'display_ethnik_user_group', 10, 3 );
		add_action( 'manage_posts_custom_column' , 'display_ethnik_group_owner', 10, 2 );
		add_action( 'manage_pages_custom_column' , 'display_ethnik_group_owner', 10, 2 );
	}
	
}


function add_extra_user_column($columns) {
	return array_merge( $columns,
			array('foo' => __('Bar')) );
}







?>