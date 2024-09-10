<!doctype html>
<!-- GRAWLIX TEMPLATE: This comes from snippet.header -->
<html>
	<head>
		<?=snippet('meta-head')?>
	</head>

	<body role="document">

		<!-- Only people who have logged in to this site's admin panel can see the admin bar. DO NOT REMOVE. -->
		<?=snippet('adminbar')?>

        <!-- This site's main menu -->
        <nav role="navigation" class="site-nav">
        <div class="nav-container">
    		<header role="contentinfo">
	    		<!-- Site/comic logo and name -->
		    	<a class="logo-link" href="<?=show('home_url')?>">
    				<img class="logo-img" src="<?=show('directory')?>/themes/classique/images/layout/logo-square.png" alt="<?=show('site_name')?>"/>
	    		</a>
		    </header>

    		<label for="show-menu" id="show-menu-btn">
                <i class="fa fa-bars"></i>
    		</label>
    		<input type="checkbox" id="show-menu" role="button">
    			<ul id="menu-list">
    				<?=show('menu')?>
    			</ul>
        <!-- end nav container -->
        </div>
        </nav>
