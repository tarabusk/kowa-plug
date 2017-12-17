<?php
/*
Plugin Name: Kowala
Plugin URI: http://kowala.fr
Description: Plugin développé pour ajouter des fonctionnalités au site kowala.fr
Version: 0.1
Author: Gaëlle Vaudaine
License: GPL2
*/


/********************************************/
/* Déclaration des scripts et feuilles de style */
/********************************************/
function kowala_plugin_scripts() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style( 'kowala-style', $plugin_url . 'css/kowala.css' );
		wp_enqueue_style( 'load-fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css' );
		wp_register_script( 'kowala-script', $plugin_url . '/js/kowala.js', array( 'jquery' ), '1.0.0', true );
    wp_enqueue_script( 'kowala-script' );

}
add_action( 'wp_enqueue_scripts', 'kowala_plugin_scripts', 99999999 );

/********************************************/
/* Bouton back to top */
/********************************************/
function kowala_plugin_button_to_top () {
    echo '<div class="kw_bcktop"><img src="'.plugin_dir_url( __FILE__ ).'/img/back-to-top.png" alt =""/> </div>';
}
add_action( 'wp_footer', 'kowala_plugin_button_to_top' );

/********************************************/
/* Déclaration du code GA pour les non connectés uniquement */
/********************************************/

/* Déclaration du code GA pour les non connectés uniquement */
/********************************************/


function azalyne_add_analytics_script() {

   $analytics_code = "
	 <!-- Global site tag (gtag.js) - Google Analytics -->
	 <script async src='https://www.googletagmanager.com/gtag/js?id=UA-32891045-1'></script>
	 <script>
	 window.dataLayer = window.dataLayer || [];
	 function gtag(){dataLayer.push(arguments);}
	 gtag('js', new Date());

	 gtag('config', 'UA-32891045-1');
	 </script>
   ";

   if (!is_user_logged_in()) {
      echo $analytics_code;
   }
}
add_action('wp_head', __NAMESPACE__ . '\\azalyne_add_analytics_script');

/********************************************/
/* Sommaire  */
/********************************************/

/*
Ancres
*/
function replace_ca($matches){
  return '<h'.$matches[1].$matches[2].' id="'.sanitize_title($matches[3]).'">'.$matches[3].'</h'.$matches[4].'>';
}

//Ajout d'un filtre sur le contenu
add_filter('the_content', 'add_anchor_to_title', 12);
function add_anchor_to_title($content){
  if(is_singular('post')){ // s'il s'agit d'un article
    global $post;
    $pattern = "/<h([1-4])(.*?)>(.*?)<\/h([1-4])>/i";

    $content = preg_replace_callback($pattern, 'replace_ca', $content);
    return $content;
  }else{
    return $content;
  }
}

/*

*/
function kowala_automenu($atts){
  $a = shortcode_atts( array(
        'insertmobile' => false,
    ), $atts );

  global $post;
  setup_postdata( $post );
	if ($post->post_type = 'post' && (($a['insertmobile'] == false) || wp_is_mobile())) {
	  $obj1 = '<ul id="kowala-sommaire">';
	  $original_content = $post->post_content;

	//  $patt = "/<h([1-4])(.*?)>(.*?)<\/h([1-4])>/i";
	  $patt = "/<h([1-2])(.*?)>(.*?)<\/h([1-2])>/i";
	  preg_match_all($patt, $original_content, $results);

	  $lvl1 = 0;
	  $lvl2 = 0;
	  $lvl3 = 0;
	  $lvl4 = 0;

	  foreach ($results[3] as $k=> $r) {

	    switch($results[1][$k]){

	      case 1:
	      $niveau = '';
	        break;
	      case 2:
	        $lvl1++;
	        $niveau = '';//'<span class="title_lvl">'.$lvl1.'/</span>';
	        $lvl2 = 0;
	        $lvl3 = 0;
	        break;

	      case 3:
	        $lvl2++;
	        $niveau = '<span class="title_lvl">'.base_convert(($lvl2+9),10,36).'.</span>';
	        $lvl3 = 0;
	        break;

	      case 4:
	        $lvl3++;
	        $niveau = '<span class="title_lvl">'.$lvl3.')</span>';
	        break;
	    }

	    $obj .= '<li><a href="#'.sanitize_title($r).'" class="title_lvl'.$results[1][$k].'">'.$niveau.$r.'</a></li>';
	  }
    $retour = '';
    if ($obj != '') {
      if ($a['insertmobile'] == true) {
        $retour = '<div class="sommaire-mobile">';
      }
			$retour .= '<div class="kowala-cont-sommaire"><div class="widget-title"><h2 class="title"> Sommaire </h2></div>'.$obj1 . $obj.'</ul></div>';
      if ($a['insertmobile'] == true) {
        $retour .= '</div>';
      }
		}

	  return $retour;
	} else {
		return '';
	}
}

// shortcode
add_shortcode('sommaire','kowala_automenu');

/********************************************/
/* Remove Query Strings From Static Resources  */
/* https://www.keycdn.com/support/remove-query-strings-from-static-resources/ */
/********************************************/

function kowala_remove_script_version( $src ){
$parts = explode( '?ver', $src );
return $parts[0];
}
add_filter( 'script_loader_src', 'kowala_remove_script_version', 15, 1 );
add_filter( 'style_loader_src', 'kowala_remove_script_version', 15, 1 );


/********************************************/
/* Create shortcode for JetPack sharing buttons */
/********************************************/

// Move Jetpack from the_content / the_excerpt using shortcode

function kowala_remove_share     () {
    remove_filter( 'the_content', 'sharing_display',19 );
    remove_filter( 'the_excerpt', 'sharing_display',19 );
    if ( class_exists( 'Jetpack_Likes' ) ) {
        remove_filter( 'the_content', array( Jetpack_Likes::init(), 'post_likes' ), 30, 1 );
    }
}

add_action( 'loop_start', 'kowala_remove_share' );

function kowala_jetpack_share_func ( $atts ){

    if ( function_exists( 'sharing_display' ) ) {
        sharing_display( '', true );
    }

    if ( class_exists( 'Jetpack_Likes' ) ) {
        $custom_likes = new Jetpack_Likes;
        return $custom_likes->post_likes( '' );
    }

}
add_shortcode( 'kowala_share', 'kowala_jetpack_share_func' );



?>
