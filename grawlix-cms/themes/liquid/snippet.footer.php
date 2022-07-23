<!-- GRAWLIX TEMPLATE: This comes from snippet.footer -->

		<footer role="complementary" itemscope itemtype="http://schema.org/CreativeWork">
			<div>

				<!-- Edit this with fields in the admin panel or write your own copyright statement here. -->
				<?=show('copyright')?>-<script>document.write(new Date().getFullYear())</script> by <?=show('artist_name')?>
				   • Powered by <a href="https://github.com/Ayemae/Grawlix-Webcomic-CMS" target="_blank">The Grawlix CMS</a>
			</div>
			<?=snippet('swipenav')?>
				<?=show_ad('slot-1') ?>
		</footer>
		<?=snippet('googleanalytics')?>

	</body>
</html>
