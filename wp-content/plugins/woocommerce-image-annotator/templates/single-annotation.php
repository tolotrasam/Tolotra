<?php

get_header(); ?>
<?php


while ( have_posts() ) : the_post();
	echo '<div id="wcia-single-image-annotation">';
	do_shortcode( '[wcia_image id=' . get_the_ID() . ']' );
	echo '</div>'; ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <div class="wrap">
			<?php

			get_template_part( 'content', 'page' );
			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

			//showing the post


			?>
            </div>
        </main><!-- #main -->

    </div><!-- #primary -->
<?php endwhile; // end of the loop.?>

<?php
//get_sidebar();
get_footer();

