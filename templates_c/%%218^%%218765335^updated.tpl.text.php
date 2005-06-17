<?php /* Smarty version 2.6.2, created on 2004-06-25 06:13:21
         compiled from notifications/updated.tpl.text */ ?>
This is an automated message sent at your request from <?php echo $this->_tpl_vars['app_title']; ?>
.

To view more details of this issue, or to update it, please visit the 
following URL:
<?php echo $this->_tpl_vars['app_base_url']; ?>
view.php?id=<?php echo $this->_tpl_vars['data']['iss_id']; ?>


----------------------------------------------------------------------
          Issue #: <?php echo $this->_tpl_vars['data']['iss_id']; ?>

          Summary: <?php echo $this->_tpl_vars['data']['iss_summary']; ?>

   Changed Fields: 
----------------------------------------------------------------------
<?php echo $this->_tpl_vars['data']['diffs']; ?>

----------------------------------------------------------------------

Please Note: If you do not wish to receive any future email 
notifications from <?php echo $this->_tpl_vars['app_title']; ?>
, please change your account preferences by 
visiting the URL below:
<?php echo $this->_tpl_vars['app_base_url']; ?>
preferences.php
