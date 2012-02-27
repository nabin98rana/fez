<script type="text/javascript">
<!--

var page_url = '{$PAGE_URL}';
var url_wo_sort = '{if $url_wo_sort}{$url_wo_sort}&{/if}';
var url_wo_rows = '{if $url_wo_rows}{$url_wo_rows}&{/if}';
var url_wo_tpl = '{if $url_wo_tpl}{$url_wo_tpl}&{/if}';
var rel_url = '{$rel_url}';
var custom_filter_element = getPageElement('custom_filter_form' + '1');
var basic_element = getPageElement('basic_filter_form' + '1');

{if (($browse_type == "" && $list_type <> "all_records_list") || $browse_type != "")}
	{literal}
	if (isElementVisible(custom_filter_element)) {
		toggleVisibility('custom_filter_form');
	}
	{/literal}
{/if}

{literal}
if ((isElementVisible(basic_element) && isElementVisible(custom_filter_element)) || (!isElementVisible(basic_element) && !isElementVisible(custom_filter_element))) {
	toggleVisibility('basic_filter_form');
} 
{/literal}

-->
</script>