
</div>
<p>&nbsp;</p>
<div class="noprint"></div>
<div class="print_page_footer"></div>

</div>
<div class="footer">
	Rock Medical &copy; 2017 - All Right Reserved
<?php
	if (isset($_SESSION['user_id']) && isset($log))		$log->show(3);
?>
</div>
<script type="text/javascript">
$(function(){
	$('.table_form select, .table_filter select, select.select2').select2({
  placeholder: "Select a state",
  allowClear: true
});

	$('#logo').click(function(){
		window.location.href='/';
	});
	$('#daterange').daterangepicker({
		dateFormat: 'YYYY-MM-DD',
		locale: {
		  format: 'YYYY-MM-DD'
		},
	});
});

if (/\/(index\.php|$)/.test(window.location.href) === true) {
	$('ul.navbar-left > li:nth-child(1)').addClass('selected-menu');
}
else {
	$('ul.navbar-left a[href="'+window.location.pathname+'"]').parent().parent().parent().addClass('selected-menu');
}
</script>
</body>
</html>
