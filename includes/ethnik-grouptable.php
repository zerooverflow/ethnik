<?php

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}



class Ethnik_Group_Table extends WP_List_Table {
    
	private $ethnik_groups;
	
    function __construct($ethnik_groups){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'Gruppo',     //singular name of the listed records
            'plural'    => 'Gruppi',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
        $this->ethnik_groups = $ethnik_groups;

    }


    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name){
        switch($column_name){
            case 'description':
                return $item[$column_name];
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }


    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_name($item){
        
        //Build row actions
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&group=%s">Edit</a>',$_REQUEST['page'],'edit',$item['id']),
            'delete'    => sprintf('<a href="?page=%s&action=%s&group=%s">Delete</a>',$_REQUEST['page'],'delete',$item['id']),
        );
        
        //Return the title contents
        return sprintf('<a href="?page=%1$s&action=edit&group=%3$s"><strong>%2$s</strong></a> <span style="color:silver">(id:%3$s)</span>%4$s',
             $_REQUEST['page'],
             $item['name'],
             $item['id'],
        	 $this->row_actions($actions)
        );
    }
    
    function column_members($item){
    	
    	$members = $this->ethnik_groups->get_members( $item['id'] );
    	$cell_content ='';
    	foreach ($members as $memberid) {
    		$user = get_user_by( 'id', $memberid );
    		$cell_content .='<a href="'. get_edit_user_link( $memberid ) .'">'. esc_attr( $user->user_nicename ) .'</a>';
    		if ($memberid !== end($members)) $cell_content .= ', ';
    	}
    	return $cell_content;
    }


    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            /*$2%s*/ $item['id']                //The value of the checkbox should be the record's id
        );
    }


    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'name'     => 'Nome',
            'description'    => 'Descrizione',
        	'members'	=>'Utenti'
        );
        return $columns; 
    }


    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'name'     => array('name',false)     //true means it's already sorted
        );
        return $sortable_columns;
    }




    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @global WPDB $wpdb
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/
    function prepare_items() {
        global $wpdb; //This is used only if making any database queries


        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        $query = 'SELECT id,name,description FROM '.$this->ethnik_groups->get_table_groups();
        
        if ( ! current_user_can('manage_options') ) {
        		
        	$groups = implode( ',',$this->ethnik_groups->get_user_groups( wp_get_current_user()->ID ));
        		
        	$query .= ' WHERE id IN ('.$groups.')';
        		
        }

        /* -- Ordering parameters -- */
        // Parameters that are going to be used to order the result
        $orderby = ! empty ( $_REQUEST ["orderby"] ) ? mysql_real_escape_string ( $_REQUEST ["orderby"] ) : 'ASC';
        $order = ! empty ( $_REQUEST ["order"] ) ? mysql_real_escape_string ( $_REQUEST ["order"] ) : '';
        if (! empty ( $orderby ) & ! empty ( $order )) {
        	$query .= ' ORDER BY ' . $orderby . ' ' . $order;
        }
        
        /* -- Pagination parameters -- */
        // Number of elements in your table?
        $totalitems = $wpdb->query ( $query ); // return the total number of affected rows
        // How many to display per page?
        $perpage = 10;
        // Which page is this?
        $paged = ! empty ( $_GET ["paged"] ) ? mysql_real_escape_string ( $_GET ["paged"] ) : '';
        // Page Number
        if (empty ( $paged ) || ! is_numeric ( $paged ) || $paged <= 0) {
        	$paged = 1;
        }
        
        // How many pages do we have in total?
        $totalpages = ceil ( $totalitems / $perpage );
        // adjust the query to take pagination into account
        if (! empty ( $paged ) && ! empty ( $perpage )) {
        	$offset = ($paged - 1) * $perpage;
        	$query .= ' LIMIT ' . ( int ) $offset . ',' . ( int ) $perpage;
        }
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $wpdb->get_results( $query, ARRAY_A);
        

        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }


}
