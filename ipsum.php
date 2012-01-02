<?php
/*
Plugin Name: Dummy Content Generator
Description: Generates Dummy Content
Author: Benjamin Balter
Version: 1.0
Author URI: http://ben.balter.com/
*/

class ipsum {
	
	public $words;
	
	function __construct() {
	
		$words = file_get_contents( dirname( __FILE__ ) . '/words.txt' );
		$this->words = explode( "\n", $words );
		
		add_action( 'init', array( &$this, 'init' ) );

	}
	
	function word() {
		return $this->words[ array_rand( $this->words ) ];
	}

	function sentance() {
		
		$i = 0;
		$sentance = '';
		while( $i < rand( 10, 20 ) ) {
			
			if ( $i == 0 )
				$sentance .= ucfirst( $this->word() );
			else
				$sentance .= $this->word();
			
			$i++;
			$sentance .= ' ';
			
		}
		
		$sentance = rtrim( $sentance );
		$sentance .= '.';
		
		return $sentance;
		
	}
	
	function paragraph() {
		
		$i = 0;
		$paragraph = '';
		while ( $i < rand( 2, 10 ) ) {
			$paragraph .= $this->sentance() . ' ';
			$i++;
		}
		
		//remove trailing space
		$paragraph = rtrim( $paragraph );
		
		return '<p>' . $paragraph . '</p>';
		
	}
	
	function post() {
		
		$i = 0;
		$post = '';
		while ( $i < rand( 3, 5 ) ) {
			$post .= $this->paragraph();
			$i++;
		}
		
		return $post;
	}
	
	function title() {
		
		$i = 0;
		$title = '';
		while ( $i < rand( 5, 10 ) ) {
			$title .= ucfirst( $this->word() ) . ' ';
			$i++;
		}
		
		return rtrim ( $title );
		
	}
	
	function insert_terms() {
		
		global $wpdb;
		
		foreach ( array( 'post_tag', 'category' ) as $tax ) {
			$i = 0;
			while( $i < rand( 50, sizeof( $this->words ) ) ) {
				if ( $tax == 'category' && rand( 0, 1 ) ) {
					$parent = $wpdb->get_var( "SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy = 'category' ORDER BY RAND() LIMIT 1" );
				} else {
					$parent = 0;
				}
				wp_insert_term( $this->word(), $tax, array( 'parent' => $parent ) );
				$i++;
			} 
		}
	}
	
	function terms( ) {
		
		$i = 0;
		$terms = array();
		while( $i < rand( 2, 10 ) ) {
			$terms[] = $this->word();
			$i++;
		}
	
		return $terms;	
	}
	
	function set_terms( $id ) {
		global $wpdb;
		
		$cat = $wpdb->get_var( "SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy = 'category' ORDER BY RAND() LIMIT 1" );
		wp_set_post_terms( $id, $cat, 'category' );	
		wp_set_post_terms( $id, $this->terms(), 'post_tag' );
			
	}
	
	function insert_post() {
		
		$post = array( 
				'post_content' => $this->post(),
				'post_title' => $this->title(),
				'post_status' => 'publish',
				);
				
		$id = wp_insert_post( $post );
		$this->set_terms( $id );
		
		return $id;
	}
	
	function insert_posts() {
		
		$i = 0;
		while( $i < rand( 100, 500) ) {
			$this->insert_post();
			$i++;
		}
		
	}
	
	function init() {
	
		global $wpdb;
		
		if ( !isset( $_GET['dummy'] ) )
			return;
			
		$tables = array( 'posts', 'terms', 'term_relationships', 'term_taxonomy' );
		
		foreach ( $tables as $table )
			$wpdb->query( 'TRUNCATE TABLE `' . $wpdb->$table . '`' );
		
		$this->insert_terms();
		$this->insert_posts();
			
	}

}

new ipsum();