<?php 

function render_users_list( $users ) {
	foreach ( $users as $user ) {
		?>
			<li>
				<label for="member-user-<?php echo $user->ID ?>"><input id="member-user-<?php echo $user->ID ?>" type="checkbox" value="<?php echo $user->ID ?>"/><?php echo $user->user_nicename ?></label>
			</li>
		<?php 
	}
}

function feed_subscribed_field( $subscribeds ){
	$value = '';
	foreach ( $subscribeds as $subscribed ){
		$value .= $subscribed->ID;
		if ($subscribed !== end($subscribeds)) $value .=',';
	}
	echo $value;
}

function render_groupform( $params ){ 
?>
<?php if ( isset( $params['formtitle'] ) ): ?>
<h2><?php echo $params['formtitle']; ?></h2>
<?php endif; ?>
<form method="post" id="ethnik-group-form">
	<input type="hidden" id="ethnik_formgroup_members_inputfield" name="ethnik_formgroup_members" id="" value="<?php if ( isset( $params['subscribeds'] ) ) feed_subscribed_field( $params['subscribeds'] ); ?>"/>
	<input type="hidden" name="ethnik_action" value="<?php if ( isset( $params['action'] ) ) echo $params['action']; ?>" />
	<input type="hidden" name="ethnik_groupid" value="<?php if ( isset( $params['groupid'] ) ) echo $params['groupid']; ?>" />
	<table class="form-table">
		<tr valign="top">   
			<th scope="row">
				<label for="nome">Nome</label>
			</th>
            <td>
            	<input type="text" value="<?php if ( isset( $params['name'] ) ) echo $params['name']; ?>" name="ethnik_formgroup_name" placeholder="inserisci il nome del gruppo" />
            </td>
        </tr>
        <tr valign="top">    
			<th scope="row">
				<label for="descrizione">Descrizione</label>
			</th>
            <td>
            	<textarea name="ethnik_formgroup_description" placeholder="inserisci una descrizione del gruppo"><?php if ( isset( $params['description'] ) ) echo $params['description']; ?></textarea>
            </td>
        </tr>
        <tr valign="top">    
			<th scope="row">
				<label for="lista-utenti">Utenti del Gruppo</label>
			</th>
            <td>
            	<div class="ethnik-container alignleft">
            		<ul id="ethnik-members-list" class="ethnik-list"> 
	            		<?php if ( isset( $params['subscribeds'] ) ) render_users_list( $params['subscribeds'] ); ?>
            		</ul>
            		<a class="ethnik-remove button alignright">Rimuovi dal gruppo >></a>
            	</div>
            	<div class="ethnik-container alignleft">
            		<ul id="ethnik-users-list" class="ethnik-list">
	            		<?php if ( isset( $params['unsubscribeds'] ) ) render_users_list( $params['unsubscribeds'] ); ?>
            		</ul>
            		<a class="ethnik-add button"><< Aggiungi al gruppo</a>
            	</div>
            	
            
            	
            </td>
        </tr>
	</table> 
	
	<?php @submit_button($params['submit_text']); ?>	
</form>
<?php 
}