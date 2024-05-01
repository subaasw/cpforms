<?php

class CPforms_DB {

    private $wpdb;
    private $table_name_prefix = 'cpforms_entries';

   function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;

        $this->maybe_create_table();
    }

    function get_table_name(){
        $table_name = $this->wpdb->prefix . $this->table_name_prefix;

        return $table_name;
    }

    function maybe_create_table(){
        $charset_collate = $this->wpdb->get_charset_collate();
        $table_name = $this->get_table_name();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            full_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL UNIQUE,
            message TEXT NOT NULL,
            PRIMARY KEY  (id)
          ) $charset_collate;";
          
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    function insert_data_to_my_table( $full_name, $email, $message ){

       $res = $this->wpdb->insert(
            $this->get_table_name(), 
            array( 
                'full_name' => $full_name, 
                'email' => $email,
                'message' => $message
            ) 
        );

        return $res;
    }

    function get_my_table_data( $page, $per_page, $orderby, $order, $search ){
        $offset_value = $per_page * ( $page - 1 );

        // Constructing the WHERE clause based on the presence of search text
        $where_clause = $search ? "WHERE full_name REGEXP '^${search}' OR email REGEXP '${search}^'" : "";

        // Constructing the SQL query with dynamic values using the prepare() method
        $sql_query = $this->wpdb->prepare(
            "SELECT * FROM {$this->get_table_name()} $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d",
            array( $per_page, $offset_value )
        );

        $total_size = (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM {$this->get_table_name()}" );

        $results = $this->wpdb->get_results( $sql_query );

        return array( 
            'data' => $results, 
            'size' => $total_size,
        );
    }

}
