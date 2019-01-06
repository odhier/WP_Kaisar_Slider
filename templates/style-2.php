<section class="kslide-img slider">
	<?php
		$i=1;
        $childrens = get_children( $args );
        foreach ( $childrens as $children ) { ?>
      <div class="slide"><img src="<?php echo $children->guid; ?>"></div>
      <?php
			$i++;
        }
	?>
</section>