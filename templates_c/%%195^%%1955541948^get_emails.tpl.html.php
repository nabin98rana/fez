<?php /* Smarty version 2.6.2, created on 2004-06-25 11:23:32
         compiled from get_emails.tpl.html */ ?>
<html>
<head>
<title><?php echo $this->_tpl_vars['application_title']; ?>
</title>
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['rel_url']; ?>
css/style.css" type="text/css">
<script language="JavaScript" src="<?php echo $this->_tpl_vars['rel_url']; ?>
js/validation.js"></script>
<script language="JavaScript" src="<?php echo $this->_tpl_vars['rel_url']; ?>
js/browserSniffer.js"></script>
<?php echo '
<script language="JavaScript">
<!--
function scrollBottom()
{
    if (is_ie) {
        var winBottom = document.body.scrollHeight;
    } else if (is_nav) {
        var winBottom = document.height;
    }
    if (winBottom != null) {
        window.scrollTo(0, winBottom);
    }
}
//-->
</script>
'; ?>

</head>

<body bgcolor="#FFFFFF">