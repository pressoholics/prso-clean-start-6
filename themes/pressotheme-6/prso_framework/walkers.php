<?php
/**
* description_walker
* 
* Add the 'has-flyout' class to any li's that have children and add the arrows to li's with children
* 
* @access 	public
* @author	Ben Moody
*/
if( !class_exists('main_nav_walker') ) {
	
	class main_nav_walker extends Walker_Nav_Menu {

		function start_lvl(&$output, $depth = 0, $args = array()) {
			$indent = str_repeat("\t", $depth);
			$output .= "\n$indent<ul class=\"menu vertical\">\n";
		}
	              
	}
	
}

if( !class_exists('mobile_nav_walker') ) {
	
	class mobile_nav_walker extends Walker_Nav_Menu {
	            
	        function start_lvl(&$output, $depth = 0, $args = array()) {
	            $indent = str_repeat("\t", $depth);
	            $output .= "\n$indent<ul class=\"menu vertical nested\">\n";
	        }
	              
	}
	
}

/**
* accordion_nav_walker
* 
* Walker class to create nav menu using zurb foundation accordion
* 
	
	Use this php to add nav to template::
	 
	wp_nav_menu( 
    	array( 
    		'menu' 				=> 'sidebar_nav',
    		'menu_class' 		=> 'accordion sidebar-nav-container',
    		'theme_location' 	=> 'sidebar_nav',
    		'container' 		=> false,
    		'depth' 			=> '2',
    		'items_wrap'      	=> '<dl id="%1$s" class="%2$s" data-accordion>%3$s</dl>',
    		'walker' 			=> new accordion_nav_walker(),
    		'fallback_cb'		=> false
    	)
    );
	
	
	Use this javascript to init custom zurb foundation accordion nav::
	
	function prso_sidebar_nav_init() {
			
		//Init vars
		var navConainter 	= $('dl.sidebar-nav-container');
		var navItems		= $('dl.sidebar-nav-container dd');
		
		if( navConainter.length > 0 ) {
			
			navItems.each(function(index) {
				
				var itemID = null;
				
				//Cache item ID
				itemID = $(this).find('a').data('side-nav-id');
				
				//Add item ID and css ID attr to content container
				$(this).find('.content').attr('id', itemID);
				
				//Is this item active
				if( $(this).hasClass('active') ) {
					$(this).find('.content').addClass( 'active' );
				}
				
			});
			
		}
		
	}
	prso_sidebar_nav_init();
	
* @access 	public
* @author	Ben Moody
*/
if( !class_exists('accordion_nav_walker') ) {
	
	class accordion_nav_walker extends Walker_Nav_Menu {
	
	      function start_el(&$output, $item, $depth = 0, $args = array(), $current_object_id = 0)
	      {
	            global $wp_query, $post;
	            $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
	            
	            $class_names = $value = '';
	            
	            $classes = empty( $item->classes ) ? array() : (array) $item->classes;
	            
	            $class_names .= join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
	           
	           
				//If is parent item add accordion attributes
	            if( $depth === 0 ) {
	            
					$class_names = ' class="accordion-navigation '. esc_attr( $class_names ) . '"';
					
					//Is this page active
					if( isset($item->object_id) && ($item->object_id === $post->ID) ) {
						$class_names.= ' active';
					}
					
	            	$output .= $indent . '<dd ' . $value . $class_names .'>';
	            	
	            } else {
	            
		            $class_names = ' class="'. esc_attr( $class_names ) . '"';
		            $output .= $indent . '<li ' . $value . $class_names .'>';
		            
	            }

				
				//prso_debug($item);
				//exit();
				
	            $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
	            $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
	            $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
	            
	            //If is parent item add accordion attributes
	            if( $depth === 0 ) {
	            
		            $attributes .= ! empty( $item->ID )        ? ' href="#side-nav-item-'   . esc_attr( $item->ID        ) .'"' : '';
					$attributes .= ! empty( $item->ID )        ? ' data-side-nav-id="side-nav-item-'   . esc_attr( $item->ID        ) .'"' : '';
					
	            } else {
		            
		            $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
		            
	            }
	            
	
	            $item_output = $args->before;
	            $item_output .= '<a'. $attributes .'>';
	            $item_output .= $args->link_before .apply_filters( 'the_title', $item->title, $item->ID );
	            $item_output .= $args->link_after;
	            
	            $item_output .= '</a>';
	            $item_output .= $args->after;
				
	            $output .= $item_output;
	        }
	            
	        function start_lvl(&$output, $depth = 0, $args = array()) {
	            $indent = str_repeat("\t", $depth);
	            
	            $output .= "\n$indent<div class='content'>\n";
	            
	            $output .= "\n$indent<ul class=\"side-nav-sub\">\n";
	        }
			
			// Displays end of a level. E.g '</ul>'
		    // @see Walker::end_lvl()
		    function end_lvl(&$output, $depth=0, $args=array()) {
		        $output .= "</ul>\n</div>\n";
		    }
			
		    // Displays end of an element. E.g '</li>'
		    // @see Walker::end_el()
		    function end_el(&$output, $item, $depth=0, $args=array()) {
		    	
		    	//If is parent item add accordion attributes
	            if( $depth === 0 ) {
	           
	            	$output .= "</dd>\n";
	            	
	            } else {
		            
		            $output .= "</li>\n";
		            
	            }
	            
		    }
	            
	        function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output )
            {
                $id_field = $this->db_fields['id'];
                if ( is_object( $args[0] ) ) {
                    $args[0]->has_children = ! empty( $children_elements[$element->$id_field] );
                }
                return parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
            }       
	}
	
}

/**
* footer_links_walker
* 
* Walker class to customize footer links
* 
* @access 	public
* @author	Ben Moody
*/
if( !class_exists('footer_links_walker') ) {
	
	class footer_links_walker extends Walker_Nav_Menu {
	      function start_el(&$output, $item, $depth = 0, $args = array(), $current_object_id = 0)
	      {
	            global $wp_query;
	            $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
	            
	            $class_names = $value = '';
	            
	            $classes = empty( $item->classes ) ? array() : (array) $item->classes;
	            
	            $class_names .= join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
	            $class_names = ' class="'. esc_attr( $class_names ) . '"';
	           
	            $output .= $indent . '<li ' . $value . $class_names .'>';
	
	            $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
	            $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
	            $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
	            $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
	
	            $item_output = $args->before;
	            $item_output .= '<a'. $attributes .'>';
	            $item_output .= $args->link_before .apply_filters( 'the_title', $item->title, $item->ID );
	            $item_output .= $args->link_after;
	            
	            $item_output .= '</a>';
	            $item_output .= $args->after;
	
	            $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	            }
	            
	        function start_lvl(&$output, $depth = 0, $args = array()) {
	            $indent = str_repeat("\t", $depth);
	            $output .= "\n$indent<ul class=\"flyout\">\n";
	        }
	            
	        function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output )
	            {
	                $id_field = $this->db_fields['id'];
	                if ( is_object( $args[0] ) ) {
	                    $args[0]->has_children = ! empty( $children_elements[$element->$id_field] );
	                }
	                return parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
	            }       
	}
	
}
