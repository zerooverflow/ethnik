<div class="wrap">
    <h2>Ethnik</h2>
    <?php /*
    <form method="post" action="options.php"> 
        <?php @settings_fields('wp_ethnik-group'); ?>
        <?php @do_settings_fields('wp_ethnik-group'); ?>

        <table class="form-table">  
            <tr valign="top">
                <th scope="row"><label for="ethnik_activate">Attiva Gruppi</label></th>
                <td><input type="checkbox" name="ethnik_activate" id="ethnik_activate" value="on" <?php if (get_option('ethnik_activate')=='on') echo 'checked="checked"'; ?> /></td>
            </tr>
        </table>

        <?php @submit_button(); ?>
    </form>
    
    */
    ?>
    <div class="ethnik-information">
	    <p>&copy; Simone Buono, 2016.</p>
	    <p>
		    <img class="alignleft focusgroup" src="<?php echo PLUGIN_URL.'/images/focusgroup.jpg'; ?>" />
		    
		    Il plugin Ethnik permette la gestione dei contenuti orientata a gruppi di utenti<br/>
		    separando la visibilità dei contenuti nel pannello di amministrazione.<br/> 
		    Ogni utente appartenente ad un gruppo puo' vedere tutti i contenuti creati dagli utenti dello stesso gruppo.
	    </p>
    </div>

    <div class="ethnik-information">
    	<h4>AVVERTENZE</h4>
    	<ul>
    		<li>Solo gli utenti di ruolo amministratore possono creare e gestire i gruppi di utenti, quindi solo lo sviluppatore dovrebbe avere il ruolo di amministratore.</li>
    		<li>Ogni utente può appartenere a più di un gruppo.</li>
    		<li>Gli utenti di un gruppo possono vedere solo gli articoli, le pagine e le immagini caricate da altri utenti del suo stesso gruppo.</li>
    		<li>Un utente amministratore vede gli articoli, le pagine le immagini e qualsiasi altra cosa presente nel Wordpress.</li>
    	</ul>
    </div>

</div>