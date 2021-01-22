	<div class="text-center">
	<?php
	echo $this->Paginator->counter([
		'format' => __('合計 : {:count}件　{:page} / {:pages}ページ')
	]);
	?>
	</div>
	<div class="text-center">
		<?= $this->Paginator->pagination(['ul' => 'pagination']); ?>
	</div>

