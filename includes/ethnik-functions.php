<?php 

function to_javascript( $name, $arr ){
	
	echo '<script>';
	echo 'var '.$name.'='.json_encode($arr).';';
	echo '</script>';
	
}

function create_userlist( $name, $arr ){
	?>
	<script>
		var <?php echo $name ?> = new ethnik_userlist( <?php  echo json_encode($arr) ?>);
	</script>
	<?php 
}

