<?php get_header();
echo do_shortcode( '[wcia_image_all]' );
?>

    <main class="section" id="section">

		<?php
		if ( have_posts() ) : ?>

			<?php the_archive_title( '<h3 class="pagetitle">', '</h3>' ); ?>
			<?php the_archive_description( '<div class="pagetitle-desc">', '</div>' ); ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', 'list' ); ?>

			<?php endwhile; ?>

		<?php else : get_template_part( 'content', 'none' ); endif; ?>

    </main><!-- .section -->


<?php get_footer(); ?>