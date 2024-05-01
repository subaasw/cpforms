<?php

class CPForms_Core {
    private $db_handler;

    public function __construct(){
        add_shortcode( 'my_form', array( $this, 'my_shortcode_form' ) );
        add_shortcode( 'my_list', array( $this, 'my_shortcode_list' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles' ) );

        $this->db_handler = new CPforms_DB();
    }

    function enqueue_scripts_and_styles(){
        // Enqueue styles
        wp_enqueue_style( 'cpforms-styles', CPFORMS_URL . 'assets/css/style.css' );

        // Enqueue Scripts
        wp_enqueue_script( 'cpforms-script', CPFORMS_URL . 'assets/js/main.js', array('jquery'), '1.0', true );

        wp_localize_script( 'cpforms-script', 
            'cpformsApi',
            array( 
                'rest_url' => esc_url_raw( rest_url() . 'cpforms/v1' ),
                'nonce'    => wp_create_nonce( 'cpforms_nonce' ),
            )
        );
    }

    function my_shortcode_form(){
        ob_start(); ?>

        <form method="POST" class="cpforms-form">
            <h2 align="center"><?php _e( 'My Form', 'cpforms' ) ?></h2>
            <input name="full_name" type="text" placeholder="Full Name*"  required />
            <input name="email" type="email" placeholder="Email Address*"  required />
            <textarea name="message" placeholder="Your Message*" rows="4" required ></textarea>
            <button>Submit</button>
        </form>

        <?php
        return ob_get_clean();
    }

    function my_shortcode_list(){
        $page = 1;
        $per_page = 5;

        $results = $this->db_handler->get_my_table_data( $page, $per_page, 'id', 'asc', '' );
        $has_nextpage = 0;

        if ( $results['size'] ){
            $has_nextpage = $results['size'] / ( $page * $per_page ) > 1 ? $page + 1 : 0;
        }

        ob_start();
        
        if ( $results && count( $results['data'] ) > 0 ){
            $data = $results['data'];
            ?>
            <div class="cpforms-list-container">
                <h2 align="center"><?php _e( 'My List', 'cpforms' ) ?></h2>
                <form action="GET" class="cpforms-list-filter">
                    <select name="order">
                        <option value="">Sorting By</option>
                        <option value="asc">Acending </option>
                        <option value="desc">Decending </option>
                    </select>
                    
                    <select name="orderby">
                        <option value="">Filter By</option>
                        <option value="full_name">Name</option>
                        <option value="email">Email</option>
                    </select>
                    
                    <input name="search" type="search" placeholder="Search.." />

                    <button class="cpforms-filter-btn">Filter</button>
                </form>
                
                <table border="1" class="cpforms-table-list">
                    <thead>
                        <tr>
                            <th> S.N </th>
                            <th> Name </th>
                            <th> Email Address </th>
                            <th> Message </th>
                        </tr>
                    </thead>
                    <tbody class="cpforms-list-body">
                        <?php
                        $index = 0;

                        foreach( $data as $row ){
                            $index++;
                            echo "<tr>";
                            echo "<td>". $index . "</td>";
                            echo "<td>". esc_html( $row->full_name ) . "</td>";
                            echo "<td>". esc_html( $row->email ) . "</td>";
                            echo "<td>". esc_textarea( $row->message ) . "</td>";
                            echo "</tr>";
                        }

                        ?>
                    </tbody>
                </table>
                <div class="cpforms-nav-btns">
                    <button data-attr="<?php echo $page > 1 ? $page - 1 : 0 ?>" type="button" class="prev-btn" disabled><span> < </span></button>
                    <button data-attr="<?php echo $has_nextpage ?>" type="button" class="next-btn"><span> > </span></button>
                </div>
            </div>
        <?php 
        }
        else {
            echo "<h3>Sorry no data found!</h3>";
        }
        return ob_get_clean();
    }

}

new CPForms_Core();
