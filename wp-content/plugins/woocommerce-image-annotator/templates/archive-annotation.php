<?php get_header(); ?>

	<main class="section" id="section">

		<?php if( have_posts() ) : ?>

			<?php the_archive_title( '<h3 class="pagetitle">', '</h3>' ); ?>
			<?php the_archive_description( '<div class="pagetitle-desc">', '</div>' ); ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', 'list' ); ?>

			<?php endwhile; ?>

			<?php echo adelle_theme_pagination_links(); ?>

		<?php else : get_template_part( 'content', 'none' ); endif; ?>

	</main><!-- .section -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>