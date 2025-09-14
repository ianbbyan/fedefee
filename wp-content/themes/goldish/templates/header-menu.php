<?php $menu = str_replace( '<nav', '<nav itemscope itemtype="http://schema.org/SiteNavigationElement"', wp_nav_menu( [
	'container'       => 'nav',
	'container_class' => 'c-top-menu js-top-menu',
	'echo'            => false,
	'menu_id'         => 'top-menu-desktop',
	'menu_class'      => 'c-top-menu__list c-top-menu__list--popup-' . ideapark_mod( 'top_menu_submenu_layout' ),
	'theme_location'  => 'primary',
	'fallback_cb'     => '',
	'depth'           => ideapark_mod( 'top_menu_depth' ) == 'unlim' || ideapark_mod( 'top_menu_depth' ) < 1 ? 0 : (int) ideapark_mod( 'top_menu_depth' )
] ) );
echo ideapark_wrap( $menu );