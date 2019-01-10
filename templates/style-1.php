<?php for($j=1; $j<=2; $j++){?>
			<div class="kslide-img slide<?php echo $j; ?>" style="animation: moveSlideshow<?php echo $j;?> <?php echo ($duration)?$duration:12;?>s linear infinite;">
		<?php
		$i=1;
        $childrens = get_children( $args );
        foreach ( $childrens as $children ) {
        ?>
			<img id="slider_image_<?php echo $i;?>" src="<?php echo $children->guid; ?>" style="max-width:300px;" />
			
		<?php
			$i++;
        }
		?>
		</div>
	<?php } ?>