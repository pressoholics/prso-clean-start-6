<?php
/*
Template Name: Parallax Demo
*/
?>

<?php get_header(); ?>
	
<section id="slide-1" class="parallax-slide" style="height: 974px;">
    <div class="parallax-slide-bg"
        data-center="background-position: 50% 0px;"
        data-top-bottom="background-position: 50% -100px;"
        data-anchor-target="#slide-1"
    >
        <div class="parallax-slide-row row">
        	
    		<div class="parallax-slide-content"
                data-center="opacity: 1"
                data-106-top="opacity: 0"
                data-anchor-target="#slide-1 h2"
            >
            	
            	<div class="vcenter-outer">
					<div class="vcenter-inner">
						<h2>Fade out elements before <br>they leave viewport</h2>
					</div>
            	</div>
            	
            </div>
            
        </div>
    </div>
</section>

<section id="slide-2" class="parallax-slide" style="height: 575px;">
    <div class="parallax-slide-bg"
        data-0="background-color:rgb(1,27,59);"
        data-top="background-color:(0,0,0);"
        data-anchor-target="#slide-2"
    >
        <div class="parallax-slide-row row">
            <div class="parallax-slide-content">
            	
            	<div class="vcenter-outer">
					<div class="vcenter-inner">
						<h2
							data--200-bottom="opacity: 0"
							data-center="opacity: 1"
							data-206-top="opacity: 1"
							data-106-top="opacity: 0"
							data-anchor-target="#slide-2 h2"
						>
							Fade me in and out
						</h2>
					</div>
            	</div>
               
            </div>
        </div>
    </div>
</section>

<section id="slide-5" class="parallax-slide" style="height: 2925px;">
    <div class="parallax-slide-bg">&nbsp;</div>
    <div class="parallax-slide-bg parallax-slide-bg-2"
        data-bottom-top="opacity: 0;"
        data--33p-top="opacity: 0;"
        data--66p-top="opacity: 1;"
        data-anchor-target="#slide-5"
    >
        <div class="parallax-slide-row row">
        
            <div class="parallax-slide-content"
                data-bottom-top="opacity: 0;"
                data-center="opacity: 1"
                data-anchor-target="#slide-5"
            >
            
            	<div class="vcenter-outer">
					<div class="vcenter-inner">
						<h2>Fixed element fading in and out</h2>
					</div>
            	</div>
            	
            </div>
            
        </div>
    </div>
    <div class="parallax-slide-bg parallax-slide-bg-3"
        data-300-bottom="opacity: 0;"
        data-100-bottom="opacity: 1;"
        data-anchor-target="#slide-5"
    >
        <div class="parallax-slide-row row">
        
            <div class="parallax-slide-content"
                data-100-bottom="opacity: 0;"
                data-bottom="opacity: 1;"
                data-anchor-target="#slide-5"
            >
            
            	<div class="vcenter-outer">
					<div class="vcenter-inner">
						<h2>The End</h2>
					</div>
            	</div>
            	
            </div>
            
        </div>
    </div>
</section>

<?php get_footer(); ?>